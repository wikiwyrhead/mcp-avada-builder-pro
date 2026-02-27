<?php

if (!defined('ABSPATH')) {
    exit;
}

class MCP_Avada_Pro_Grid_Normalization_Pattern implements MCP_Avada_Pro_Module_Interface
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
            'avada-pro/normalize-grid-pattern',
            array(
                'label' => __('Normalize Grid Pattern (Pro)', 'mcp-avada-builder-pro'),
                'description' => __('Normalize responsive column and spacing attributes for card-like grids selected by class.', 'mcp-avada-builder-pro'),
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
                        'desktop' => array('type' => 'string', 'default' => '1_3'),
                        'tablet' => array('type' => 'string', 'default' => '1_2'),
                        'mobile' => array('type' => 'string', 'default' => '1_1'),
                        'spacing_left' => array('type' => 'string', 'default' => '5.76%'),
                        'spacing_right' => array('type' => 'string', 'default' => '5.76%'),
                        'spacing_left_medium' => array('type' => 'string', 'default' => '3.84%'),
                        'spacing_right_medium' => array('type' => 'string', 'default' => '3.84%'),
                        'spacing_left_small' => array('type' => 'string', 'default' => '1.92%'),
                        'spacing_right_small' => array('type' => 'string', 'default' => '1.92%'),
                        'dry_run' => array('type' => 'boolean', 'default' => true),
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
                        'readonly' => false,
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
            return array('success' => false, 'data' => array('error' => 'page_id and class_name are required.'));
        }

        $post = get_post($post_id);
        if (!$post || 'trash' === $post->post_status) {
            return array('success' => false, 'data' => array('error' => 'Post not found.'));
        }

        $desktop = isset($params['desktop']) ? (string) $params['desktop'] : '1_3';
        $tablet = isset($params['tablet']) ? (string) $params['tablet'] : '1_2';
        $mobile = isset($params['mobile']) ? (string) $params['mobile'] : '1_1';
        $dry_run = !isset($params['dry_run']) || (bool) $params['dry_run'];

        $attrs = array(
            'type' => $desktop,
            'type_medium' => $tablet,
            'type_small' => $mobile,
            'spacing_left' => isset($params['spacing_left']) ? (string) $params['spacing_left'] : '5.76%',
            'spacing_right' => isset($params['spacing_right']) ? (string) $params['spacing_right'] : '5.76%',
            'spacing_left_medium' => isset($params['spacing_left_medium']) ? (string) $params['spacing_left_medium'] : '3.84%',
            'spacing_right_medium' => isset($params['spacing_right_medium']) ? (string) $params['spacing_right_medium'] : '3.84%',
            'spacing_left_small' => isset($params['spacing_left_small']) ? (string) $params['spacing_left_small'] : '1.92%',
            'spacing_right_small' => isset($params['spacing_right_small']) ? (string) $params['spacing_right_small'] : '1.92%',
        );

        $content = (string) $post->post_content;
        preg_match_all('/\[fusion_builder_column[^\]]*\]/i', $content, $matches, PREG_OFFSET_CAPTURE);
        $tokens = $matches[0];

        $updated = 0;
        $rewritten = $content;

        for ($i = count($tokens) - 1; $i >= 0; $i--) {
            $token = $tokens[$i][0];
            $offset = $tokens[$i][1];

            if (!$this->tag_has_class($token, $class_name)) {
                continue;
            }

            $new_token = $token;
            foreach ($attrs as $name => $value) {
                $new_token = $this->set_attr($new_token, $name, $value);
            }

            if ($new_token !== $token) {
                $rewritten = substr_replace($rewritten, $new_token, $offset, strlen($token));
                $updated++;
            }
        }

        if ($dry_run) {
            return array(
                'success' => true,
                'data' => array(
                    'post_id' => $post_id,
                    'class_name' => $class_name,
                    'dry_run' => true,
                    'columns_updated' => $updated,
                    'attributes_applied' => $attrs,
                ),
            );
        }

        if ($updated > 0) {
            $result = wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $rewritten,
            ), true);
            if (is_wp_error($result)) {
                return array('success' => false, 'data' => array('error' => $result->get_error_message()));
            }

            if (function_exists('mcp_avada_pro_ensure_post_meta')) {
                mcp_avada_pro_ensure_post_meta($post_id);
            } else {
                update_post_meta($post_id, '_fusion_builder_status', 'active');
            }
        }

        return array(
            'success' => true,
            'data' => array(
                'post_id' => $post_id,
                'class_name' => $class_name,
                'dry_run' => false,
                'columns_updated' => $updated,
                'attributes_applied' => $attrs,
            ),
        );
    }

    protected function set_attr(string $tag, string $name, string $value): string
    {
        if (preg_match('/\b' . preg_quote($name, '/') . '="[^"]*"/i', $tag)) {
            return preg_replace('/\b' . preg_quote($name, '/') . '="[^"]*"/i', $name . '="' . esc_attr($value) . '"', $tag, 1) ?? $tag;
        }
        return preg_replace('/\]$/', ' ' . $name . '="' . esc_attr($value) . '"]', $tag, 1) ?? $tag;
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

