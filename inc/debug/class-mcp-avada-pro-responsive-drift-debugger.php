<?php

if (!defined('ABSPATH')) {
    exit;
}

class MCP_Avada_Pro_Responsive_Drift_Debugger implements MCP_Avada_Pro_Module_Interface
{
    public function register(): void
    {
        add_action('wp_abilities_api_init', array($this, 'register_ability'));
    }

    public function register_ability(): void
    {
        if (!function_exists('wp_register_ability')) {
            return;
        }

        wp_register_ability(
            'avada-pro/debug-responsive-drift',
            array(
                'label' => __('Debug Responsive Drift (Pro)', 'mcp-avada-builder-pro'),
                'description' => __('Compare shortcode card attributes vs rendered Avada CSS variables for a target class.', 'mcp-avada-builder-pro'),
                'category' => 'avada-builder-pro',
                'execute_callback' => array($this, 'execute'),
                'permission_callback' => function ($params): bool {
                    if (!isset($params['page_id']) || !is_int($params['page_id']) || $params['page_id'] <= 0) {
                        return false;
                    }
                    return current_user_can('edit_post', $params['page_id']);
                },
                'input_schema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'page_id' => array('type' => 'integer'),
                        'class_name' => array('type' => 'string'),
                        'page_url' => array('type' => 'string'),
                    ),
                    'required' => array('page_id', 'class_name'),
                ),
                'output_schema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'success' => array('type' => 'boolean'),
                        'data' => array('type' => 'object'),
                    ),
                ),
                'meta' => array(
                    'show_in_rest' => true,
                    'mcp' => array('public' => true, 'type' => 'tool'),
                    'annotations' => array(
                        'readonly' => true,
                        'destructive' => false,
                        'idempotent' => true,
                    ),
                ),
            )
        );
    }

    public function execute(array $params): array
    {
        $post_id = isset($params['page_id']) ? (int) $params['page_id'] : 0;
        $class_name = isset($params['class_name']) ? sanitize_text_field((string) $params['class_name']) : '';

        if ($post_id <= 0 || empty($class_name)) {
            return array(
                'success' => false,
                'data' => array('error' => 'page_id and class_name are required.'),
            );
        }

        $post = get_post($post_id);
        if (!$post || 'trash' === $post->post_status) {
            return array(
                'success' => false,
                'data' => array('error' => 'Post not found.'),
            );
        }

        $content = (string) $post->post_content;
        $shortcode_groups = $this->analyze_shortcode_groups($content, $class_name);

        $page_url = isset($params['page_url']) ? esc_url_raw((string) $params['page_url']) : get_permalink($post_id);
        if (empty($page_url)) {
            return array(
                'success' => false,
                'data' => array('error' => 'Could not resolve page URL.'),
            );
        }

        $response = wp_remote_get($page_url, array('timeout' => 20));
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'data' => array('error' => $response->get_error_message()),
            );
        }

        $html = (string) wp_remote_retrieve_body($response);
        $rendered = MCP_Avada_Pro_Rendered_CSS_Variable_Verifier::analyze_html_by_class($html, $class_name);

        return array(
            'success' => true,
            'data' => array(
                'post_id' => $post_id,
                'page_url' => $page_url,
                'class_name' => $class_name,
                'shortcode_groups' => $shortcode_groups['groups'],
                'shortcode_nodes_found' => $shortcode_groups['nodes_found'],
                'rendered_groups' => $rendered['groups'],
                'rendered_nodes_found' => $rendered['nodes_found'],
                'shortcode_uniform' => count($shortcode_groups['groups']) <= 1,
                'rendered_uniform' => !empty($rendered['is_uniform']),
            ),
        );
    }

    protected function analyze_shortcode_groups(string $content, string $class_name): array
    {
        preg_match_all('/\[fusion_builder_column[^\]]*\]/i', $content, $matches);
        $tags = $matches[0];

        $rows = array();
        foreach ($tags as $tag) {
            if (!$this->tag_has_class($tag, $class_name)) {
                continue;
            }

            $rows[] = array(
                'type' => $this->extract_attr($tag, 'type'),
                'type_medium' => $this->extract_attr($tag, 'type_medium'),
                'type_small' => $this->extract_attr($tag, 'type_small'),
                'spacing_left' => $this->extract_attr($tag, 'spacing_left'),
                'spacing_right' => $this->extract_attr($tag, 'spacing_right'),
                'spacing_left_medium' => $this->extract_attr($tag, 'spacing_left_medium'),
                'spacing_right_medium' => $this->extract_attr($tag, 'spacing_right_medium'),
                'spacing_left_small' => $this->extract_attr($tag, 'spacing_left_small'),
                'spacing_right_small' => $this->extract_attr($tag, 'spacing_right_small'),
            );
        }

        $groups = array();
        foreach ($rows as $row) {
            $signature = implode('|', $row);
            if (!isset($groups[$signature])) {
                $groups[$signature] = 0;
            }
            $groups[$signature]++;
        }
        arsort($groups);

        $grouped = array();
        foreach ($groups as $signature => $count) {
            $grouped[] = array(
                'signature' => $signature,
                'count' => $count,
            );
        }

        return array(
            'nodes_found' => count($rows),
            'groups' => $grouped,
        );
    }

    protected function extract_attr(string $tag, string $attr): string
    {
        if (preg_match('/\b' . preg_quote($attr, '/') . '="([^"]*)"/i', $tag, $match)) {
            return $match[1];
        }
        return '';
    }

    protected function tag_has_class(string $tag, string $class_name): bool
    {
        if (!preg_match('/\bclass="([^"]*)"/i', $tag, $match)) {
            return false;
        }
        $classes = preg_split('/\s+/', trim($match[1])) ?: array();
        return in_array($class_name, $classes, true);
    }
}

