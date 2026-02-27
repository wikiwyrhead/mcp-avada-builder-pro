<?php

if (!defined('ABSPATH')) {
    exit;
}

class MCP_Avada_Pro_Fusion_Code_Block_Sanitizer implements MCP_Avada_Pro_Module_Interface
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
            'avada-pro/sanitize-fusion-code-blocks',
            array(
                'label' => __('Sanitize Fusion Code Blocks (Pro)', 'mcp-avada-builder-pro'),
                'description' => __('Normalize and dedupe [fusion_code] style/script blocks by id while preserving content.', 'mcp-avada-builder-pro'),
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
                        'block_type' => array('type' => 'string', 'default' => 'style'),
                        'keep_strategy' => array('type' => 'string', 'default' => 'last'),
                        'dry_run' => array('type' => 'boolean', 'default' => true),
                    ),
                    'required' => array('page_id'),
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
        if ($post_id <= 0) {
            return array('success' => false, 'data' => array('error' => 'page_id is required.'));
        }

        $post = get_post($post_id);
        if (!$post || 'trash' === $post->post_status) {
            return array('success' => false, 'data' => array('error' => 'Post not found.'));
        }

        $block_type = isset($params['block_type']) ? strtolower((string) $params['block_type']) : 'style';
        if (!in_array($block_type, array('style', 'script'), true)) {
            $block_type = 'style';
        }
        $keep_strategy = isset($params['keep_strategy']) ? strtolower((string) $params['keep_strategy']) : 'last';
        if (!in_array($keep_strategy, array('first', 'last'), true)) {
            $keep_strategy = 'last';
        }
        $dry_run = !isset($params['dry_run']) || (bool) $params['dry_run'];

        $content = (string) $post->post_content;
        $original = $content;

        // Remove wpautop wrappers around fusion_code blocks.
        $content = preg_replace('/<p>\s*(\[fusion_code\])/i', '$1', $content) ?? $content;
        $content = preg_replace('/(\[\/fusion_code\])\s*<\/p>/i', '$1', $content) ?? $content;

        $pattern = '/\[fusion_code\](.*?)\[\/fusion_code\]/is';
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
        $blocks = $matches[0] ?? array();

        $id_pattern = $block_type === 'style'
            ? '/<style[^>]*\sid="([^"]+)"[^>]*>.*?<\/style>/is'
            : '/<script[^>]*\sid="([^"]+)"[^>]*>.*?<\/script>/is';

        $index_by_id = array();
        foreach ($blocks as $idx => $block_match) {
            $block = $block_match[0];
            if (preg_match($id_pattern, $block, $id_match)) {
                $id = $id_match[1];
                if (!isset($index_by_id[$id])) {
                    $index_by_id[$id] = array();
                }
                $index_by_id[$id][] = $idx;
            }
        }

        $remove_indexes = array();
        foreach ($index_by_id as $indexes) {
            if (count($indexes) <= 1) {
                continue;
            }
            if ('first' === $keep_strategy) {
                array_shift($indexes);
            } else {
                array_pop($indexes);
            }
            $remove_indexes = array_merge($remove_indexes, $indexes);
        }
        $remove_indexes = array_unique($remove_indexes);
        rsort($remove_indexes);

        foreach ($remove_indexes as $idx) {
            $block = $blocks[$idx][0];
            $offset = $blocks[$idx][1];
            $content = substr_replace($content, '', $offset, strlen($block));
        }

        $changed = $content !== $original;
        $removed = count($remove_indexes);

        if ($dry_run) {
            return array(
                'success' => true,
                'data' => array(
                    'post_id' => $post_id,
                    'dry_run' => true,
                    'block_type' => $block_type,
                    'keep_strategy' => $keep_strategy,
                    'duplicate_blocks_removed' => $removed,
                    'content_changed' => $changed,
                ),
            );
        }

        if ($changed) {
            $result = wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $content,
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
                'dry_run' => false,
                'block_type' => $block_type,
                'keep_strategy' => $keep_strategy,
                'duplicate_blocks_removed' => $removed,
                'content_changed' => $changed,
            ),
        );
    }
}

