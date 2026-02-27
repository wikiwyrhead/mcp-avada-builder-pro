<?php

if (!defined('ABSPATH')) {
    exit;
}

class MCP_Avada_Pro_Rendered_CSS_Variable_Verifier implements MCP_Avada_Pro_Module_Interface
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
            'avada-pro/verify-rendered-css-variables',
            array(
                'label' => __('Verify Rendered CSS Variables (Pro)', 'mcp-avada-builder-pro'),
                'description' => __('Fetch rendered frontend HTML and group Avada CSS variable signatures for a target class.', 'mcp-avada-builder-pro'),
                'category' => 'avada-builder-pro',
                'execute_callback' => array($this, 'execute'),
                'permission_callback' => function (): bool {
                    return current_user_can('edit_posts');
                },
                'input_schema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'page_url' => array('type' => 'string'),
                        'class_name' => array('type' => 'string'),
                    ),
                    'required' => array('page_url', 'class_name'),
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
        $page_url = isset($params['page_url']) ? esc_url_raw((string) $params['page_url']) : '';
        $class_name = isset($params['class_name']) ? sanitize_text_field((string) $params['class_name']) : '';

        if (empty($page_url) || empty($class_name)) {
            return array(
                'success' => false,
                'data' => array('error' => 'page_url and class_name are required.'),
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
        $report = self::analyze_html_by_class($html, $class_name);
        $report['page_url'] = $page_url;

        return array(
            'success' => true,
            'data' => $report,
        );
    }

    public static function analyze_html_by_class(string $html, string $class_name): array
    {
        $pattern = '/class="[^"]*\\b' . preg_quote($class_name, '/') . '\\b[^"]*"\\s+style="([^"]*)"/i';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        $rows = array();
        foreach ($matches as $match) {
            $style = $match[1];
            $rows[] = array(
                'width_large' => self::extract_style_value($style, '--awb-width-large'),
                'width_medium' => self::extract_style_value($style, '--awb-width-medium'),
                'width_small' => self::extract_style_value($style, '--awb-width-small'),
                'spacing_left_large' => self::extract_style_value($style, '--awb-spacing-left-large'),
                'spacing_right_large' => self::extract_style_value($style, '--awb-spacing-right-large'),
                'spacing_left_medium' => self::extract_style_value($style, '--awb-spacing-left-medium'),
                'spacing_right_medium' => self::extract_style_value($style, '--awb-spacing-right-medium'),
                'spacing_left_small' => self::extract_style_value($style, '--awb-spacing-left-small'),
                'spacing_right_small' => self::extract_style_value($style, '--awb-spacing-right-small'),
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
            'class_name' => $class_name,
            'nodes_found' => count($rows),
            'groups' => $grouped,
            'is_uniform' => count($groups) <= 1,
        );
    }

    protected static function extract_style_value(string $style, string $variable): string
    {
        if (preg_match('/' . preg_quote($variable, '/') . '\s*:\s*([^;]+);/i', $style, $match)) {
            return trim($match[1]);
        }
        return '';
    }
}

