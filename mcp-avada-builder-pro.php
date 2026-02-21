<?php

/**
 * Plugin Name: MCP Avada Builder Pro
 * Description: Advanced MCP integration for Avada Fusion Builder with full shortcode parsing and element management
 * Version: 3.2.0
 * Author: arnelG
 * Author URI: https://github.com/wikiwyrhead
 * Plugin URI: https://github.com/wikiwyrhead/mcp-avada-builder
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mcp-avada-builder-pro
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MCP_AVADA_VERSION', '3.2.0');

add_action('wp_abilities_api_categories_init', 'mcp_avada_pro_register_category');
function mcp_avada_pro_register_category(): void
{
    wp_register_ability_category(
        'avada-builder-pro',
        array(
            'label' => __('Avada Builder Pro', 'mcp-avada-builder-pro'),
            'description' => __('Advanced abilities for controlling Avada Fusion Builder with full element support.', 'mcp-avada-builder-pro'),
        )
    );
}

add_action('wp_abilities_api_init', 'mcp_avada_pro_register_abilities');
function mcp_avada_pro_register_abilities(): void
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-avada-parser.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-avada-elements.php';

    wp_register_ability(
        'avada-pro/get-info',
        array(
            'label' => __('Get Builder Info (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Get detailed Avada Builder version, theme version, and status', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_get_info',
            'permission_callback' => function (): bool {
                return current_user_can('edit_posts');
            },
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

    wp_register_ability(
        'avada-pro/get-page-structure',
        array(
            'label' => __('Get Page Structure (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Get full page structure including containers, rows, columns, and elements with proper hierarchy', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_get_page_structure',
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
                    'include_content' => array('type' => 'boolean', 'default' => true),
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
                    'readonly' => true,
                    'destructive' => false,
                    'idempotent' => true,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/list-all-elements',
        array(
            'label' => __('List All Elements (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('List all elements on a page with their full details and hierarchy', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_list_elements',
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
                    'element_type' => array('type' => 'string'),
                ),
                'required' => array('page_id'),
            ),
            'output_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'success' => array('type' => 'boolean'),
                    'data' => array('type' => 'array'),
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

    wp_register_ability(
        'avada-pro/add-container',
        array(
            'label' => __('Add Container (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Add a new container with optional row and column', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_add_container',
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
                    'container_attrs' => array('type' => 'object'),
                    'add_row' => array('type' => 'boolean', 'default' => true),
                    'column_type' => array('type' => 'string', 'default' => '1_1'),
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
                'mcp' => array(
                    'public' => true,
                    'type' => 'tool',
                ),
                'annotations' => array(
                    'readonly' => false,
                    'destructive' => false,
                    'idempotent' => false,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/add-element',
        array(
            'label' => __('Add Element (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Add a new element to a specific container/row/column position', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_add_element',
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
                    'element_type' => array('type' => 'string'),
                    'container_index' => array('type' => 'integer', 'default' => 0),
                    'row_index' => array('type' => 'integer', 'default' => 0),
                    'column_index' => array('type' => 'integer', 'default' => 0),
                    'attributes' => array('type' => 'object'),
                    'content' => array('type' => 'string'),
                ),
                'required' => array('page_id', 'element_type'),
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
                'mcp' => array(
                    'public' => true,
                    'type' => 'tool',
                ),
                'annotations' => array(
                    'readonly' => false,
                    'destructive' => false,
                    'idempotent' => false,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/update-element',
        array(
            'label' => __('Update Element (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Update an element by its unique ID with full path tracking', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_update_element',
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
                    'element_path' => array('type' => 'string', 'description' => 'Path like container_0/row_0/column_0/element_0'),
                    'attributes' => array('type' => 'object'),
                    'content' => array('type' => 'string'),
                ),
                'required' => array('page_id', 'element_path'),
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
                'mcp' => array(
                    'public' => true,
                    'type' => 'tool',
                ),
                'annotations' => array(
                    'readonly' => false,
                    'destructive' => false,
                    'idempotent' => true,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/delete-element',
        array(
            'label' => __('Delete Element (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Delete an element by its path', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_delete_element',
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
                    'element_path' => array('type' => 'string'),
                ),
                'required' => array('page_id', 'element_path'),
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
                'mcp' => array(
                    'public' => true,
                    'type' => 'tool',
                ),
                'annotations' => array(
                    'readonly' => false,
                    'destructive' => true,
                    'idempotent' => false,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/replace-content',
        array(
            'label' => __('Replace Page Content (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Replace entire page content with new structure', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_replace_content',
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
                    'structure' => array('type' => 'object'),
                ),
                'required' => array('page_id', 'structure'),
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
                'mcp' => array(
                    'public' => true,
                    'type' => 'tool',
                ),
                'annotations' => array(
                    'readonly' => false,
                    'destructive' => true,
                    'idempotent' => false,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/list-element-types',
        array(
            'label' => __('List Element Types (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Get all available Avada element types with their schemas', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_list_element_types',
            'permission_callback' => function (): bool {
                return current_user_can('edit_posts');
            },
            'output_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'success' => array('type' => 'boolean'),
                    'data' => array('type' => 'array'),
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

    wp_register_ability(
        'avada-pro/clean-duplicates',
        array(
            'label' => __('Clean Duplicates (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Remove duplicate elements from a page', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_clean_duplicates',
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
                'mcp' => array(
                    'public' => true,
                    'type' => 'tool',
                ),
                'annotations' => array(
                    'readonly' => false,
                    'destructive' => true,
                    'idempotent' => true,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/restructure-layout',
        array(
            'label' => __('Restructure Layout (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Restructure elements in a container - convert inline icons to vertical columns, reorder elements, etc.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_restructure_layout',
            'permission_callback' => function ($params): bool {
                if (!isset($params['page_id']) || !is_int($params['page_id']) || $params['page_id'] <= 0) {
                    return false;
                }
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'page_id' => array('type' => 'integer', 'description' => 'Page ID'),
                    'container_index' => array('type' => 'integer', 'default' => 0, 'description' => 'Container index'),
                    'row_index' => array('type' => 'integer', 'default' => null, 'description' => 'Specific row index to restructure (optional)'),
                    'layout_type' => array('type' => 'string', 'enum' => array('icon_vertical'), 'description' => 'Type of restructuring. Currently supported: icon_vertical'),
                    'column_type' => array('type' => 'string', 'default' => '1_4', 'description' => 'Column type for new columns (1_2, 1_3, 1_4, 1_5, 1_6)'),
                    'element_indices' => array('type' => 'array', 'items' => array('type' => 'integer'), 'description' => 'Indices of elements to restructure'),
                    'filter_content' => array('type' => 'array', 'items' => array('type' => 'string'), 'description' => 'Keywords to match elements to restructure'),
                ),
                'required' => array('page_id', 'layout_type'),
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
                'mcp' => array(
                    'public' => true,
                    'type' => 'tool',
                ),
                'annotations' => array(
                    'readonly' => false,
                    'destructive' => true,
                    'idempotent' => false,
                ),
            ),
        )
    );

    // --- Priority 1: New abilities (v3.0.0) ---

    wp_register_ability(
        'avada-pro/create-page',
        array(
            'label' => __('Create Page (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Create a new WordPress page with Avada Builder enabled and optional initial content.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_create_page',
            'permission_callback' => function ($params): bool {
                $post_type = isset($params['post_type']) && 'post' === $params['post_type'] ? 'post' : 'page';
                $cap = ('post' === $post_type) ? 'publish_posts' : 'publish_pages';
                return current_user_can($cap);
            },
            'input_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'title' => array('type' => 'string', 'description' => 'Page title'),
                    'status' => array('type' => 'string', 'default' => 'draft', 'description' => 'Post status: draft, publish, pending, private'),
                    'template' => array('type' => 'string', 'description' => 'Page template slug (e.g. "100-width.php" for full width)'),
                    'post_type' => array('type' => 'string', 'default' => 'page', 'description' => 'Post type: page or post'),
                ),
                'required' => array('title'),
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
                    'idempotent' => false,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/list-pages',
        array(
            'label' => __('List Avada Pages (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('List all pages/posts that have Avada Builder enabled, with builder status and element counts.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_list_pages',
            'permission_callback' => function (): bool {
                return current_user_can('edit_posts');
            },
            'input_schema' => array(
                'type' => 'object',
                'default' => array(),
                'properties' => array(
                    'post_type' => array('type' => 'string', 'default' => 'page', 'description' => 'Post type: page, post, or any'),
                    'per_page' => array('type' => 'integer', 'default' => 50, 'description' => 'Max results to return'),
                    'status' => array('type' => 'string', 'default' => 'any', 'description' => 'Filter by post status'),
                ),
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

    wp_register_ability(
        'avada-pro/duplicate-element',
        array(
            'label' => __('Duplicate Element (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Deep-clone an element at its current position. The clone is inserted immediately after the original.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_duplicate_element',
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
                    'element_path' => array('type' => 'string', 'description' => 'Path like container_0/row_0/column_0/fusion_text_0'),
                ),
                'required' => array('page_id', 'element_path'),
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
                    'idempotent' => false,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/move-element',
        array(
            'label' => __('Move Element (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Move an element from one position to another container/row/column. The element is removed from source and inserted at target.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_move_element',
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
                    'element_path' => array('type' => 'string', 'description' => 'Source path: container_X/row_Y/column_Z/element_W'),
                    'target_container' => array('type' => 'integer', 'description' => 'Target container index'),
                    'target_row' => array('type' => 'integer', 'default' => 0, 'description' => 'Target row index'),
                    'target_column' => array('type' => 'integer', 'default' => 0, 'description' => 'Target column index'),
                    'position' => array('type' => 'integer', 'default' => -1, 'description' => 'Insert position in target column (-1 = append)'),
                ),
                'required' => array('page_id', 'element_path', 'target_container'),
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
                    'idempotent' => false,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/find-element',
        array(
            'label' => __('Find Element (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Search for elements on a page by type, content keyword, or attribute value. Returns matching elements with their paths.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_find_element',
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
                    'element_type' => array('type' => 'string', 'description' => 'Filter by element type (e.g. fusion_text)'),
                    'search' => array('type' => 'string', 'description' => 'Search keyword in element content and attribute values'),
                    'attribute' => array('type' => 'string', 'description' => 'Filter by a specific attribute name'),
                    'attribute_value' => array('type' => 'string', 'description' => 'Filter by attribute value (used with attribute param)'),
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
                    'readonly' => true,
                    'destructive' => false,
                    'idempotent' => true,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/bulk-update',
        array(
            'label' => __('Bulk Update Elements (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Update multiple elements in a single save operation. Each update specifies an element_path with new attributes and/or content.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_bulk_update',
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
                    'updates' => array(
                        'type' => 'array',
                        'items' => array(
                            'type' => 'object',
                            'properties' => array(
                                'element_path' => array('type' => 'string'),
                                'attributes' => array('type' => 'object'),
                                'content' => array('type' => 'string'),
                            ),
                            'required' => array('element_path'),
                        ),
                        'description' => 'Array of updates, each with element_path and optional attributes/content',
                    ),
                ),
                'required' => array('page_id', 'updates'),
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

    // --- Priority 2: Schema Introspection abilities (v3.1.0) ---

    wp_register_ability(
        'avada-pro/get-element-schema',
        array(
            'label' => __('Get Element Schema (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Return the full parameter schema for a Fusion Builder element type -- all params with types, defaults, options, dependencies, and descriptions. Uses Avada runtime registry when available.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_get_element_schema',
            'permission_callback' => function (): bool {
                return current_user_can('edit_posts');
            },
            'input_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'element_type' => array('type' => 'string', 'description' => 'Fusion Builder element shortcode name, e.g. fusion_button, fusion_text'),
                ),
                'required' => array('element_type'),
            ),
            'output_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'success' => array('type' => 'boolean'),
                    'data' => array('type' => 'object', 'description' => 'Element schema with name, shortcode, params array'),
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

    wp_register_ability(
        'avada-pro/get-element-defaults',
        array(
            'label' => __('Get Element Defaults (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Return default values for all parameters of an element type. Reads from Avada FusionSC_* class at runtime when available.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_get_element_defaults',
            'permission_callback' => function (): bool {
                return current_user_can('edit_posts');
            },
            'input_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'element_type' => array('type' => 'string', 'description' => 'Fusion Builder element shortcode name'),
                ),
                'required' => array('element_type'),
            ),
            'output_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'success' => array('type' => 'boolean'),
                    'data' => array('type' => 'object', 'description' => 'Associative array of param_name => default_value'),
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

    wp_register_ability(
        'avada-pro/list-element-categories',
        array(
            'label' => __('List Element Categories (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('List all Fusion Builder element categories with counts and element type lists.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_list_element_categories',
            'permission_callback' => function (): bool {
                return current_user_can('edit_posts');
            },
            'output_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'success' => array('type' => 'boolean'),
                    'data' => array('type' => 'object', 'description' => 'Object with source, total, and categories array'),
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

    wp_register_ability(
        'avada-pro/search-elements',
        array(
            'label' => __('Search Element Types (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Search available Fusion Builder element types by keyword in name, description, or shortcode.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_search_elements',
            'permission_callback' => function (): bool {
                return current_user_can('edit_posts');
            },
            'input_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'search' => array('type' => 'string', 'description' => 'Search keyword'),
                    'category' => array('type' => 'string', 'description' => 'Optional category filter'),
                ),
                'required' => array('search'),
            ),
            'output_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'success' => array('type' => 'boolean'),
                    'data' => array('type' => 'object', 'description' => 'Matching elements with count and results'),
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

    // --- v3.2.0: Validation & Repair abilities ---

    wp_register_ability(
        'avada-pro/validate-page-structure',
        array(
            'label' => __('Validate Page Structure (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Validate a page\'s shortcode structure integrity. Checks tag balance, duplicates, round-trip fidelity, and builder status. Returns health score and detailed issues list.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_validate_page_structure',
            'permission_callback' => function ($params): bool {
                if (!isset($params['page_id']) || !is_int($params['page_id']) || $params['page_id'] <= 0) {
                    return false;
                }
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'page_id' => array('type' => 'integer', 'description' => 'Post ID to validate'),
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
                    'readonly' => true,
                    'destructive' => false,
                    'idempotent' => true,
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/repair-shortcode-tree',
        array(
            'label' => __('Repair Shortcode Tree (Pro)', 'mcp-avada-builder-pro'),
            'description' => __('Repair malformed shortcode trees. Removes duplicate elements, cleans empty containers, and normalizes structure. Defaults to dry_run=true (preview only). Set dry_run=false to apply. Always creates a backup before modifying.', 'mcp-avada-builder-pro'),
            'category' => 'avada-builder-pro',
            'execute_callback' => 'mcp_avada_pro_repair_shortcode_tree',
            'permission_callback' => function ($params): bool {
                if (!isset($params['page_id']) || !is_int($params['page_id']) || $params['page_id'] <= 0) {
                    return false;
                }
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema' => array(
                'type' => 'object',
                'properties' => array(
                    'page_id' => array('type' => 'integer', 'description' => 'Post ID to repair'),
                    'dry_run' => array('type' => 'boolean', 'default' => true, 'description' => 'If true, preview repairs without applying. Set false to apply.'),
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

add_action('mcp_adapter_init', 'mcp_avada_pro_register_mcp_server');
function mcp_avada_pro_register_mcp_server($adapter): void
{
    $abilities = array(
        'avada-pro/get-info',
        'avada-pro/get-page-structure',
        'avada-pro/list-all-elements',
        'avada-pro/add-container',
        'avada-pro/add-element',
        'avada-pro/update-element',
        'avada-pro/delete-element',
        'avada-pro/replace-content',
        'avada-pro/list-element-types',
        'avada-pro/clean-duplicates',
        'avada-pro/restructure-layout',
        'avada-pro/create-page',
        'avada-pro/list-pages',
        'avada-pro/duplicate-element',
        'avada-pro/move-element',
        'avada-pro/find-element',
        'avada-pro/bulk-update',
        'avada-pro/get-element-schema',
        'avada-pro/get-element-defaults',
        'avada-pro/list-element-categories',
        'avada-pro/search-elements',
        'avada-pro/validate-page-structure',
        'avada-pro/repair-shortcode-tree'
    );

    $adapter->create_server(
        'mcp-avada-builder-pro',
        'mcp',
        'mcp-avada-builder-pro',
        'MCP Avada Builder Pro',
        'Advanced MCP integration for Avada Fusion Builder',
        MCP_AVADA_VERSION,
        array(
            \WP\MCP\Transport\HttpTransport::class,
        ),
        \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
        \WP\MCP\Infrastructure\Observability\NullMcpObservabilityHandler::class,
        $abilities,
        array(),
        array()
    );
}


function mcp_avada_pro_get_info(): array
{
    $theme_version = 'unknown';
    if (defined('AVADA_VERSION')) {
        $theme_version = AVADA_VERSION;
    } elseif (function_exists('Avada')) {
        $avada = Avada();
        if (method_exists($avada, 'get_version')) {
            $theme_version = $avada->get_version();
        }
    }

    $builder_version = defined('FUSION_BUILDER_VERSION') ? FUSION_BUILDER_VERSION : 'unknown';
    $elements = new MCP_Avada_Elements();

    return array(
        'success' => true,
        'data' => array(
            'builder_version' => $builder_version,
            'theme_version' => $theme_version,
            'is_active' => class_exists('FusionBuilder'),
            'plugin_version' => MCP_AVADA_VERSION,
            'total_element_types' => count($elements->get_all()),
        ),
    );
}

function mcp_avada_pro_get_page_structure(array $params)
{
    $post_id = $params['page_id'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, isset($params['include_content']) ? $params['include_content'] : true);

    return array(
        'success' => true,
        'data' => array(
            'post_id' => $post_id,
            'title' => $post->post_title,
            'builder_enabled' => get_post_meta($post_id, '_fusion_builder_status', true) === 'active',
            'containers_count' => count($structure['containers']),
            'structure' => $structure,
        ),
    );
}

function mcp_avada_pro_list_elements(array $params)
{
    $post_id = $params['page_id'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);

    $elements = $parser->extract_all_elements($structure);

    if (isset($params['element_type'])) {
        $elements = array_filter($elements, function ($e) use ($params) {
            return $e['type'] === $params['element_type'];
        });
    }

    return array(
        'success' => true,
        'data' => array_values($elements),
    );
}

function mcp_avada_pro_add_container(array $params)
{
    $post_id = $params['page_id'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);

    $container = array(
        'id' => 'container_' . count($structure['containers']),
        'attributes' => isset($params['container_attrs']) ? $params['container_attrs'] : array(
            'padding_top' => '40px',
            'padding_bottom' => '40px',
        ),
        'rows' => array(),
    );

    if (isset($params['add_row']) && $params['add_row']) {
        $row = array(
            'id' => 'row_0',
            'attributes' => array(),
            'columns' => array(
                array(
                    'id' => 'column_0',
                    'attributes' => array('type' => isset($params['column_type']) ? $params['column_type'] : '1_1'),
                    'elements' => array(),
                ),
            ),
        );
        $container['rows'][] = $row;
    }

    $structure['containers'][] = $container;

    $content = $parser->generate($structure);

    // P1-3: Dry-run validation — expect new container/row/column tags
    $expected_delta = array('fusion_builder_container' => 1);
    if (isset($params['add_row']) && $params['add_row']) {
        $expected_delta['fusion_builder_row'] = 1;
        $expected_delta['fusion_builder_column'] = 1;
    }
    $validation = mcp_avada_pro_validate_no_data_loss($post->post_content, $content, $expected_delta);
    if (!$validation['valid']) {
        return new WP_Error('avada_pro_data_loss', 'Cannot proceed: ' . $validation['error'], array('status' => 409));
    }

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ), true);
    if (is_wp_error($result)) {
        return $result;
    }

    update_post_meta($post_id, '_fusion_builder_status', 'active');
    update_post_meta($post_id, '_fusion_builder_version', defined('FUSION_BUILDER_VERSION') ? FUSION_BUILDER_VERSION : '3.0');

    return array(
        'success' => true,
        'data' => array(
            'container_id' => $container['id'],
            'index' => count($structure['containers']) - 1,
        ),
    );
}

function mcp_avada_pro_add_element(array $params)
{
    $post_id = $params['page_id'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    // ✅ PRIORITY 1 FIX: Validate element_type against registry
    $element_type = sanitize_key($params['element_type']);
    $validation = mcp_avada_pro_validate_element_type($element_type);
    if (is_wp_error($validation)) {
        return $validation;
    }

    // ✅ PRIORITY 1 FIX: Validate attributes if provided
    if (isset($params['attributes']) && !empty($params['attributes'])) {
        $attr_validation = mcp_avada_pro_validate_element_attributes($params['attributes']);
        if (is_wp_error($attr_validation)) {
            return $attr_validation;
        }
        // P2-3: Validate media-specific fields
        $media_validation = mcp_avada_pro_validate_media_attributes($element_type, $params['attributes']);
        if (is_wp_error($media_validation)) {
            return $media_validation;
        }
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);

    $container_index = isset($params['container_index']) ? (int) $params['container_index'] : 0;
    $row_index = isset($params['row_index']) ? (int) $params['row_index'] : 0;
    $column_index = isset($params['column_index']) ? (int) $params['column_index'] : 0;

    if (!isset($structure['containers'][$container_index])) {
        return new WP_Error('container_not_found', 'Container not found at index ' . $container_index);
    }

    $container = &$structure['containers'][$container_index];

    if (!isset($container['rows'][$row_index])) {
        $container['rows'][$row_index] = array(
            'id' => 'row_' . $row_index,
            'attributes' => array(),
            'columns' => array(),
        );
    }

    $row = &$container['rows'][$row_index];

    if (!isset($row['columns'][$column_index])) {
        $row['columns'][$column_index] = array(
            'id' => 'column_' . $column_index,
            'attributes' => array('type' => '1_1'),
            'elements' => array(),
        );
    }

    $column = &$row['columns'][$column_index];

    $element_type = $params['element_type'];
    $element_count = count(array_filter($column['elements'], function ($e) use ($element_type) {
        return strpos($e['id'], $element_type) === 0;
    }));

    $element = array(
        'id' => $element_type . '_' . $element_count,
        'type' => $element_type,
        'attributes' => isset($params['attributes']) ? $params['attributes'] : array(),
        'content' => isset($params['content']) ? $params['content'] : '',
    );

    $column['elements'][] = $element;

    $content = $parser->generate($structure);

    // P1-3: Dry-run validation — expect one new element of this type
    $validation = mcp_avada_pro_validate_no_data_loss($post->post_content, $content, array($element_type => 1));
    if (!$validation['valid']) {
        return new WP_Error('avada_pro_data_loss', 'Cannot proceed: ' . $validation['error'], array('status' => 409));
    }

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ), true);
    if (is_wp_error($result)) {
        return $result;
    }

    // ✅ AUDIT LOGGING: Log the creation
    mcp_avada_pro_log_audit_event($post_id, 'avada-pro/add-element', 'Added ' . $element_type . ' at container_' . $container_index . '/row_' . $row_index . '/column_' . $column_index);

    // P2-4: Re-read and return the canonical path after save (stable identity)
    $saved_post = get_post($post_id);
    $new_structure = $parser->parse($saved_post->post_content, true);
    $canonical_path = mcp_avada_pro_find_canonical_path($new_structure, $container_index, $row_index, $column_index, $element_type, $params);

    return array(
        'success' => true,
        'data' => array(
            'element' => $element,
            'path' => "container_{$container_index}/row_{$row_index}/column_{$column_index}/{$element['id']}",
            'canonical_path' => $canonical_path,
        ),
    );
}

function mcp_avada_pro_update_element(array $params)
{
    $post_id = $params['page_id'];
    $element_path = $params['element_path'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    // ✅ PRIORITY 1 FIX: Validate attributes if provided
    if (isset($params['attributes']) && !empty($params['attributes'])) {
        $attr_validation = mcp_avada_pro_validate_element_attributes($params['attributes']);
        if (is_wp_error($attr_validation)) {
            return $attr_validation;
        }
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);

    // P2-3: Validate media-specific fields after we can determine element type
    $parts = explode('/', $element_path);
    if (count($parts) < 4 || strpos($parts[0], 'container_') !== 0 || strpos($parts[1], 'row_') !== 0 || strpos($parts[2], 'column_') !== 0) {
        return new WP_Error('invalid_path', 'Invalid element path. Use format: container_X/row_Y/column_Z/element_W');
    }

    $container_index = (int) str_replace('container_', '', $parts[0]);
    $row_index = (int) str_replace('row_', '', $parts[1]);
    $column_index = (int) str_replace('column_', '', $parts[2]);
    $element_id = $parts[3];

    if (!isset($structure['containers'][$container_index])) {
        return new WP_Error('container_not_found', 'Container not found');
    }

    $container = &$structure['containers'][$container_index];

    if (!isset($container['rows'][$row_index])) {
        return new WP_Error('row_not_found', 'Row not found');
    }

    $row = &$container['rows'][$row_index];

    if (!isset($row['columns'][$column_index])) {
        return new WP_Error('column_not_found', 'Column not found');
    }

    $column = &$row['columns'][$column_index];

    $found = false;
    $found_element_type = null;
    foreach ($column['elements'] as &$element) {
        if ($element['id'] === $element_id) {
            $found_element_type = $element['type'];
            // P2-3: Validate media-specific fields now that we know the element type
            if (isset($params['attributes']) && !empty($params['attributes'])) {
                $media_validation = mcp_avada_pro_validate_media_attributes($found_element_type, $params['attributes']);
                if (is_wp_error($media_validation)) {
                    return $media_validation;
                }
            }
            if (isset($params['attributes'])) {
                $element['attributes'] = array_merge($element['attributes'], $params['attributes']);
            }
            if (isset($params['content'])) {
                $element['content'] = $params['content'];
            }
            $found = true;
            break;
        }
    }

    if (!$found) {
        return new WP_Error('element_not_found', 'Element not found: ' . $element_id);
    }

    $content = $parser->generate($structure);

    // P1-3: Dry-run validation — no element count changes expected
    $validation = mcp_avada_pro_validate_no_data_loss($post->post_content, $content);
    if (!$validation['valid']) {
        return new WP_Error('avada_pro_data_loss', 'Cannot proceed: ' . $validation['error'], array('status' => 409));
    }

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ), true);
    if (is_wp_error($result)) {
        return $result;
    }

    // ✅ AUDIT LOGGING: Log the update
    mcp_avada_pro_log_audit_event($post_id, 'avada-pro/update-element', 'Updated element: ' . $element_path);

    // P2-4: Re-read and return canonical path for ID stability
    $new_structure = $parser->parse(get_post($post_id)->post_content, true);
    $canonical_path = mcp_avada_pro_find_canonical_path($new_structure, $container_index, $row_index, $column_index, $found_element_type, $params);

    return array(
        'success' => true,
        'data' => array(
            'updated' => true,
            'path' => $element_path,
            'canonical_path' => $canonical_path,
        ),
    );
}

function mcp_avada_pro_delete_element(array $params)
{
    $post_id = $params['page_id'];
    $element_path = $params['element_path'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);

    $parts = explode('/', $element_path);
    if (count($parts) < 4 || strpos($parts[0], 'container_') !== 0 || strpos($parts[1], 'row_') !== 0 || strpos($parts[2], 'column_') !== 0) {
        return new WP_Error('invalid_path', 'Invalid element path. Use format: container_X/row_Y/column_Z/element_W');
    }

    $container_index = (int) str_replace('container_', '', $parts[0]);
    $row_index = (int) str_replace('row_', '', $parts[1]);
    $column_index = (int) str_replace('column_', '', $parts[2]);
    $element_id = $parts[3];

    if (!isset($structure['containers'][$container_index]['rows'][$row_index]['columns'][$column_index])) {
        return new WP_Error('position_not_found', 'Position not found');
    }

    $column = &$structure['containers'][$container_index]['rows'][$row_index]['columns'][$column_index];

    // Find the element type before deletion for validation
    $deleted_type = null;
    foreach ($column['elements'] as $el) {
        if ($el['id'] === $element_id) {
            $deleted_type = $el['type'];
            break;
        }
    }

    $original_count = count($column['elements']);
    $column['elements'] = array_values(array_filter($column['elements'], function ($e) use ($element_id) {
        return $e['id'] !== $element_id;
    }));

    if (count($column['elements']) === $original_count) {
        return new WP_Error('element_not_found', 'Element not found: ' . $element_id);
    }

    $content = $parser->generate($structure);

    // P1-3: Dry-run validation — expect one fewer element of the deleted type
    $expected_delta = array();
    if ($deleted_type && $deleted_type !== '__passthrough__') {
        $expected_delta[$deleted_type] = -1;
    }
    $validation = mcp_avada_pro_validate_no_data_loss($post->post_content, $content, $expected_delta);
    if (!$validation['valid']) {
        return new WP_Error('avada_pro_data_loss', 'Cannot proceed: ' . $validation['error'], array('status' => 409));
    }

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ), true);
    if (is_wp_error($result)) {
        return $result;
    }

    // ✅ AUDIT LOGGING: Log the deletion
    mcp_avada_pro_log_audit_event($post_id, 'avada-pro/delete-element', 'Deleted element: ' . $element_id);

    return array(
        'success' => true,
        'data' => array(
            'deleted' => true,
            'element_id' => $element_id,
        ),
    );
}

function mcp_avada_pro_replace_content(array $params)
{
    $post_id = $params['page_id'];
    $structure = $params['structure'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    // P2-5: Structural validation — reject malformed layout objects
    $struct_validation = mcp_avada_pro_validate_structure_schema($structure);
    if (is_wp_error($struct_validation)) {
        return $struct_validation;
    }

    $parser = new MCP_Avada_Parser();
    $content = $parser->generate($structure);

    // P2-5: Reject empty/trivial content that would wipe the page
    if (empty(trim($content))) {
        return new WP_Error(
            'empty_content',
            'Generated content is empty. The provided structure would wipe the page. Aborting.',
            array('status' => 409)
        );
    }

    // P2-5: Verify generated content has at least one container
    if (substr_count($content, '[fusion_builder_container') === 0) {
        return new WP_Error(
            'no_containers',
            'Generated content has no containers. The structure is malformed or incomplete.',
            array('status' => 409)
        );
    }

    // P2-5: Create a backup revision before destructive replace
    $backup_meta = array(
        'pre_replace_content' => $post->post_content,
        'pre_replace_timestamp' => current_time('mysql'),
        'pre_replace_user' => get_current_user_id(),
    );
    update_post_meta($post_id, '_fusion_builder_pre_replace_backup', $backup_meta);

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ), true);
    if (is_wp_error($result)) {
        return $result;
    }

    update_post_meta($post_id, '_fusion_builder_status', 'active');
    update_post_meta($post_id, '_fusion_builder_version', defined('FUSION_BUILDER_VERSION') ? FUSION_BUILDER_VERSION : '3.0');

    // ✅ AUDIT LOGGING
    mcp_avada_pro_log_audit_event($post_id, 'avada-pro/replace-content', 'Full content replaced (backup stored in _fusion_builder_pre_replace_backup)');

    // Re-read to confirm what was actually saved
    $saved = get_post($post_id);
    $saved_structure = $parser->parse($saved->post_content, true);

    return array(
        'success' => true,
        'data' => array(
            'post_id' => $post_id,
            'updated' => true,
            'containers_count' => count($saved_structure['containers']),
            'backup_stored' => true,
            'preview_url' => get_permalink($post_id) . '?preview=true',
        ),
    );
}

function mcp_avada_pro_list_element_types(): array
{
    $elements = new MCP_Avada_Elements();
    return array(
        'success' => true,
        'data' => $elements->get_all(),
    );
}

function mcp_avada_pro_clean_duplicates(array $params)
{
    $post_id = $params['page_id'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);

    $seen = array();
    $duplicates = 0;

    foreach ($structure['containers'] as &$container) {
        foreach ($container['rows'] as &$row) {
            foreach ($row['columns'] as &$column) {
                $column['elements'] = array_values(array_filter($column['elements'], function ($element) use (&$seen, &$duplicates) {
                    $key = $element['type'] . '|' . md5($element['content'] . json_encode($element['attributes']));
                    if (isset($seen[$key])) {
                        $duplicates++;
                        return false;
                    }
                    $seen[$key] = true;
                    return true;
                }));
            }
        }
    }

    $content = $parser->generate($structure);

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ), true);
    if (is_wp_error($result)) {
        return $result;
    }

    return array(
        'success' => true,
        'data' => array(
            'duplicates_removed' => $duplicates,
            'remaining_elements' => count($parser->extract_all_elements($structure)),
        ),
    );
}

function mcp_avada_pro_restructure_layout(array $params)
{
    $post_id = $params['page_id'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);

    $container_index = isset($params['container_index']) ? $params['container_index'] : 0;
    $row_index = isset($params['row_index']) ? $params['row_index'] : null;
    $layout_type = $params['layout_type'];
    $column_type = isset($params['column_type']) ? $params['column_type'] : '1_4';
    $element_indices = isset($params['element_indices']) ? $params['element_indices'] : null;
    $filter_content = isset($params['filter_content']) ? $params['filter_content'] : null;

    $supported_layouts = array('icon_vertical');
    if (!in_array($layout_type, $supported_layouts)) {
        return new WP_Error('not_implemented', "Layout type '{$layout_type}' is not yet implemented. Currently supported: " . implode(', ', $supported_layouts));
    }

    if (!isset($structure['containers'][$container_index])) {
        return new WP_Error('container_not_found', 'Container not found at index ' . $container_index);
    }

    $container = &$structure['containers'][$container_index];

    if ($layout_type === 'icon_vertical') {
        $elements_to_restructure = array();

        // If no specific row is targeted, find the row with most matching elements
        $target_row_index = $row_index;

        if ($target_row_index === null) {
            $best_row = -1;
            $best_count = 0;

            foreach ($container['rows'] as $ri => &$row) {
                $count = 0;
                foreach ($row['columns'] as &$column) {
                    foreach ($column['elements'] as $element) {
                        if ($element['type'] === 'fusion_text' && isset($element['content'])) {
                            if ($filter_content) {
                                foreach ($filter_content as $keyword) {
                                    if (stripos($element['content'], $keyword) !== false) {
                                        $count++;
                                        break;
                                    }
                                }
                            } elseif (preg_match('/<i\s+class="[^"]*fa-check-circle/', $element['content'])) {
                                $count++;
                            }
                        }
                    }
                }
                if ($count > $best_count) {
                    $best_count = $count;
                    $best_row = $ri;
                }
            }

            if ($best_row >= 0) {
                $target_row_index = $best_row;
            }
        }

        if ($target_row_index === null || !isset($container['rows'][$target_row_index])) {
            return new WP_Error('row_not_found', 'No suitable row found. Try specifying row_index.');
        }

        $target_row = &$container['rows'][$target_row_index];

        // Extract matching elements from target row, keep others
        foreach ($target_row['columns'] as &$column) {
            $remaining_elements = array();

            foreach ($column['elements'] as $element) {
                $should_restructure = false;

                if ($filter_content && isset($element['content'])) {
                    foreach ($filter_content as $keyword) {
                        if (stripos($element['content'], $keyword) !== false) {
                            $should_restructure = true;
                            break;
                        }
                    }
                } else {
                    if ($element['type'] === 'fusion_text' && isset($element['content'])) {
                        if (preg_match('/<i\s+class="[^"]*fa-check-circle/', $element['content'])) {
                            $should_restructure = true;
                        }
                    }
                }

                if ($should_restructure) {
                    $elements_to_restructure[] = $element;
                } else {
                    $remaining_elements[] = $element;
                }
            }

            $column['elements'] = $remaining_elements;
        }

        if (empty($elements_to_restructure)) {
            return new WP_Error('no_elements_found', 'No matching elements found in row ' . $target_row_index);
        }

        // Clear all columns in the target row and replace with new columns
        $column_map = array(
            '1_2' => 2,
            '1_3' => 3,
            '1_4' => 4,
            '1_5' => 5,
            '1_6' => 6,
        );
        $num_cols = isset($column_map[$column_type]) ? $column_map[$column_type] : 4;

        $chunks = array_chunk($elements_to_restructure, $num_cols);

        // Replace columns in the target row
        $target_row['columns'] = array();

        $col_index = 0;
        foreach ($chunks as $chunk) {
            $column = array(
                'id' => 'column_restructured_' . $col_index,
                'attributes' => array(
                    'type' => $column_type,
                    'layout' => $column_type,
                    'center_content' => 'yes',
                    'content_layout' => 'column',
                    'align_content' => 'center',
                    'valign_content' => 'flex-start',
                ),
                'elements' => array(),
            );

            foreach ($chunk as $element) {
                $column['elements'][] = $element;
            }

            $target_row['columns'][] = $column;
            $col_index++;
        }
    }

    $content = $parser->generate($structure);

    // P1-3: Dry-run validation — restructure should not lose any elements
    // Column count may change, but element tags should all be preserved
    $validation = mcp_avada_pro_validate_no_data_loss($post->post_content, $content);
    if (!$validation['valid']) {
        return new WP_Error('avada_pro_data_loss', 'Cannot proceed: ' . $validation['error'], array('status' => 409));
    }

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ), true);
    if (is_wp_error($result)) {
        return $result;
    }

    update_post_meta($post_id, '_fusion_builder_status', 'active');

    return array(
        'success' => true,
        'data' => array(
            'container_index' => $container_index,
            'row_index' => $target_row_index,
            'layout_type' => $layout_type,
            'elements_restructured' => count($elements_to_restructure),
            'columns_created' => count($target_row['columns']),
        ),
    );
}

// =========================================================================
// PRIORITY 1: NEW ABILITIES (v3.0.0)
// =========================================================================

/**
 * Helper: Validate element type against static registry.
 * Returns bool true if valid, WP_Error if invalid.
 * 
 * @param string $element_type
 * @return bool|WP_Error
 */
function mcp_avada_pro_validate_element_type($element_type)
{
    if (!is_string($element_type) || empty($element_type)) {
        return new WP_Error('invalid_element_type', 'Element type must be a non-empty string');
    }

    $registry = new MCP_Avada_Elements();
    $element = $registry->get_by_type($element_type);

    if (!$element) {
        return new WP_Error('element_type_not_registered', 'Element type not found in registry: ' . sanitize_key($element_type));
    }

    return true;
}

/**
 * Helper: Validate element attributes (keys must be sanitizable).
 * Returns bool true if valid, WP_Error if invalid.
 * 
 * @param array $attributes
 * @return bool|WP_Error
 */
function mcp_avada_pro_validate_element_attributes($attributes)
{
    if (!is_array($attributes)) {
        return new WP_Error('invalid_attributes_type', 'Attributes must be an array');
    }

    // Validate each key and value can be safely escaped/sanitized
    foreach ($attributes as $key => $value) {
        if (!is_string($key) || empty($key)) {
            return new WP_Error('invalid_attribute_key', 'All attribute keys must be non-empty strings');
        }

        // Check key is alphanumeric + underscore/hyphen (safe shortcode attribute names)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $key)) {
            return new WP_Error('invalid_attribute_name', 'Attribute key contains invalid characters: ' . $key);
        }

        // Value must be scalar or null (not array/object)
        if (!is_scalar($value) && $value !== null) {
            return new WP_Error('invalid_attribute_value', 'Attribute values must be scalar or null');
        }
    }

    return true;
}

/**
 * Helper: Validate media-specific attributes for image/gallery/video elements.
 * Enforces strict field validation for media elements.
 *
 * @param string $element_type
 * @param array  $attributes
 * @return bool|WP_Error
 */
function mcp_avada_pro_validate_media_attributes($element_type, $attributes)
{
    $media_types = array(
        'fusion_image',
        'fusion_imageframe',
        'fusion_images',
        'fusion_gallery',
        'fusion_video',
        'fusion_person',
        'fusion_slider',
    );

    if (!in_array($element_type, $media_types, true)) {
        return true;
    }

    $image_types = array('fusion_image', 'fusion_imageframe', 'fusion_person');
    if (in_array($element_type, $image_types, true)) {
        // Validate image_id if provided — must be a positive integer or empty string
        if (isset($attributes['image_id']) && $attributes['image_id'] !== '') {
            $image_id = $attributes['image_id'];
            if (!is_numeric($image_id) || (int) $image_id <= 0) {
                return new WP_Error(
                    'invalid_media_field',
                    'image_id must be a positive integer or empty. Got: ' . $image_id
                );
            }
            // Verify attachment exists in WordPress
            if (get_post_type((int) $image_id) !== 'attachment') {
                return new WP_Error(
                    'attachment_not_found',
                    'image_id ' . (int) $image_id . ' does not reference a valid media attachment. Use a valid attachment ID from the media library.'
                );
            }
        }

        // Validate image URL if provided
        if (isset($attributes['image']) && $attributes['image'] !== '') {
            if (!filter_var($attributes['image'], FILTER_VALIDATE_URL) && strpos($attributes['image'], '/') !== 0) {
                return new WP_Error(
                    'invalid_media_field',
                    'image attribute must be a valid URL or absolute path. Got: ' . $attributes['image']
                );
            }
        }
    }

    // Validate gallery image_ids if provided
    if ($element_type === 'fusion_gallery' && isset($attributes['image_ids'])) {
        $ids = $attributes['image_ids'];
        if (!empty($ids)) {
            $id_list = explode(',', $ids);
            foreach ($id_list as $id) {
                $id = trim($id);
                if (!is_numeric($id) || (int) $id <= 0) {
                    return new WP_Error(
                        'invalid_media_field',
                        'gallery image_ids must be comma-separated positive integers. Invalid: ' . $id
                    );
                }
            }
        }
    }

    return true;
}

/**
 * Helper: Validate column type against Avada supported layout fractions.
 * Returns bool true if valid, WP_Error if invalid.
 * 
 * @param string $column_type
 * @return bool|WP_Error
 */
function mcp_avada_pro_validate_column_type($column_type)
{
    $supported = array('1_1', '1_2', '2_3', '1_3', '3_5', '1_4', '3_4', '1_5', '2_5', '4_5', '1_6', '5_6');
    if (!in_array($column_type, $supported, true)) {
        return new WP_Error('invalid_column_type', 'Column type not supported. Allowed: ' . implode(', ', $supported));
    }
    return true;
}

/**
 * Helper: Validate a structure object before using it with replace-content.
 * Ensures the structure has valid containers/rows/columns hierarchy.
 *
 * @param mixed $structure
 * @return bool|WP_Error
 */
function mcp_avada_pro_validate_structure_schema($structure)
{
    if (!is_array($structure)) {
        return new WP_Error('invalid_structure', 'Structure must be an array/object');
    }

    if (!isset($structure['containers']) || !is_array($structure['containers'])) {
        return new WP_Error('invalid_structure', 'Structure must have a "containers" array');
    }

    if (empty($structure['containers'])) {
        return new WP_Error('empty_structure', 'Structure must have at least one container');
    }

    foreach ($structure['containers'] as $ci => $container) {
        if (!is_array($container)) {
            return new WP_Error('invalid_container', 'Container at index ' . $ci . ' must be an array/object');
        }

        if (!isset($container['attributes']) || !is_array($container['attributes'])) {
            // Allow missing attributes — default to empty
            $structure['containers'][$ci]['attributes'] = array();
        }

        if (!isset($container['rows']) || !is_array($container['rows'])) {
            return new WP_Error('invalid_container', 'Container at index ' . $ci . ' must have a "rows" array');
        }

        foreach ($container['rows'] as $ri => $row) {
            if (!is_array($row)) {
                return new WP_Error('invalid_row', 'Row at container ' . $ci . ' index ' . $ri . ' must be an array/object');
            }

            if (!isset($row['columns']) || !is_array($row['columns'])) {
                return new WP_Error('invalid_row', 'Row at container ' . $ci . ' index ' . $ri . ' must have a "columns" array');
            }

            foreach ($row['columns'] as $coli => $column) {
                if (!is_array($column)) {
                    return new WP_Error('invalid_column', 'Column at c' . $ci . '/r' . $ri . '/col' . $coli . ' must be an array/object');
                }

                if (!isset($column['attributes']) || !is_array($column['attributes'])) {
                    $structure['containers'][$ci]['rows'][$ri]['columns'][$coli]['attributes'] = array('type' => '1_1');
                }

                // Validate elements if present
                if (isset($column['elements']) && is_array($column['elements'])) {
                    foreach ($column['elements'] as $ei => $element) {
                        if (!is_array($element)) {
                            return new WP_Error('invalid_element', 'Element at c' . $ci . '/r' . $ri . '/col' . $coli . '/el' . $ei . ' must be an array/object');
                        }
                        if (!isset($element['type']) || empty($element['type'])) {
                            return new WP_Error('invalid_element', 'Element at c' . $ci . '/r' . $ri . '/col' . $coli . '/el' . $ei . ' must have a "type" field');
                        }
                    }
                }
            }
        }
    }

    return true;
}

/**
 * Helper: Log ability execution for audit trail using WordPress revisions.
 *
 * @param int $post_id
 * @param string $ability_id
 * @param string $action Description of what happened
 * @param int $user_id (optional) defaults to current user
 */
function mcp_avada_pro_log_audit_event($post_id, $ability_id, $action, $user_id = null)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // Store audit info in post meta (lightweight alternative to revisions)
    $audit_log = get_post_meta($post_id, '_fusion_builder_audit_log', true);
    if (!is_array($audit_log)) {
        $audit_log = array();
    }

    // Add new event (keep last 100)
    $audit_log[] = array(
        'timestamp' => current_time('mysql'),
        'user_id' => $user_id,
        'ability_id' => $ability_id,
        'action' => $action,
    );

    if (count($audit_log) > 100) {
        $audit_log = array_slice($audit_log, -100);
    }

    update_post_meta($post_id, '_fusion_builder_audit_log', $audit_log);
}

/**
 * Helper: Parse an element path string into validated indices.
 * Returns array with container_index, row_index, column_index, element_id or WP_Error.
 */
function mcp_avada_pro_parse_element_path($element_path)
{
    $parts = explode('/', $element_path);
    if (count($parts) < 4 || strpos($parts[0], 'container_') !== 0 || strpos($parts[1], 'row_') !== 0 || strpos($parts[2], 'column_') !== 0) {
        return new WP_Error('invalid_path', 'Invalid element path. Use format: container_X/row_Y/column_Z/element_W');
    }
    return array(
        'container_index' => (int) str_replace('container_', '', $parts[0]),
        'row_index' => (int) str_replace('row_', '', $parts[1]),
        'column_index' => (int) str_replace('column_', '', $parts[2]),
        'element_id' => $parts[3],
    );
}

/**
 * Helper: Re-read a structure after save and find the canonical path of an element.
 * This provides stable identity across save cycles by reading what was actually persisted.
 *
 * @param array  $structure       Freshly parsed structure from saved content.
 * @param int    $container_index Expected container index.
 * @param int    $row_index       Expected row index.
 * @param int    $column_index    Expected column index.
 * @param string $element_type    Element type to look for.
 * @param array  $params          Original params (used for matching by attributes/content).
 * @return string|null Canonical path or null if not found.
 */
function mcp_avada_pro_find_canonical_path($structure, $container_index, $row_index, $column_index, $element_type, $params = array())
{
    if (!isset($structure['containers'][$container_index]['rows'][$row_index]['columns'][$column_index])) {
        return null;
    }

    $column = $structure['containers'][$container_index]['rows'][$row_index]['columns'][$column_index];

    // Find matching element — prefer exact type match, then fallback to last of type
    $best_match = null;
    foreach ($column['elements'] as $el) {
        if ($el['type'] === $element_type) {
            $best_match = $el;
            // If we have content/attributes to match against, try exact match
            if (isset($params['content']) && isset($el['content']) && $el['content'] === $params['content']) {
                return $el['path'];
            }
        }
    }

    if ($best_match && isset($best_match['path'])) {
        return $best_match['path'];
    }

    return null;
}

/**
 * Create a new page/post with Avada Builder enabled.
 */
function mcp_avada_pro_create_page(array $params)
{
    $title = sanitize_text_field($params['title']);
    $status = isset($params['status']) ? sanitize_key($params['status']) : 'draft';
    $post_type = isset($params['post_type']) ? sanitize_key($params['post_type']) : 'page';

    $valid_statuses = array('draft', 'publish', 'pending', 'private');
    if (!in_array($status, $valid_statuses)) {
        $status = 'draft';
    }

    // Create an empty Avada Builder page with a single container > row > column
    $parser = new MCP_Avada_Parser();
    $structure = array(
        'containers' => array(
            array(
                'id' => 'container_0',
                'attributes' => array('padding_top' => '40px', 'padding_bottom' => '40px'),
                'rows' => array(
                    array(
                        'id' => 'row_0',
                        'attributes' => array(),
                        'columns' => array(
                            array(
                                'id' => 'column_0',
                                'attributes' => array('type' => '1_1'),
                                'elements' => array(),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );
    $content = $parser->generate($structure);

    $post_data = array(
        'post_title' => $title,
        'post_type' => $post_type,
        'post_status' => $status,
        'post_content' => $content,
    );

    $post_id = wp_insert_post($post_data, true);
    if (is_wp_error($post_id)) {
        return $post_id;
    }

    // Set Avada Builder meta
    update_post_meta($post_id, '_fusion_builder_status', 'active');
    update_post_meta($post_id, '_fusion_builder_version', defined('FUSION_BUILDER_VERSION') ? FUSION_BUILDER_VERSION : '3.0');

    // Set page template if provided
    if (isset($params['template']) && !empty($params['template'])) {
        update_post_meta($post_id, '_wp_page_template', sanitize_text_field($params['template']));
    }

    return array(
        'success' => true,
        'data' => array(
            'post_id' => $post_id,
            'title' => $title,
            'status' => $status,
            'post_type' => $post_type,
            'edit_url' => admin_url('post.php?post=' . $post_id . '&action=edit'),
            'preview_url' => get_permalink($post_id) . '?preview=true',
        ),
    );
}

/**
 * List all pages/posts with Avada Builder enabled.
 */
function mcp_avada_pro_list_pages(array $params)
{
    $post_type = isset($params['post_type']) ? sanitize_key($params['post_type']) : 'page';
    $per_page = isset($params['per_page']) ? absint($params['per_page']) : 50;
    $status = isset($params['status']) ? sanitize_key($params['status']) : 'any';

    if ($per_page < 1 || $per_page > 200) {
        $per_page = 50;
    }

    $query_args = array(
        'post_type' => $post_type === 'any' ? array('page', 'post') : $post_type,
        'post_status' => $status,
        'posts_per_page' => $per_page,
        'meta_key' => '_fusion_builder_status',
        'meta_value' => 'active',
        'orderby' => 'modified',
        'order' => 'DESC',
    );

    $query = new WP_Query($query_args);
    $pages = array();

    foreach ($query->posts as $post) {
        $has_containers = preg_match_all('/\[fusion_builder_container/', $post->post_content, $m);
        $pages[] = array(
            'post_id' => $post->ID,
            'title' => $post->post_title,
            'post_type' => $post->post_type,
            'status' => $post->post_status,
            'modified' => $post->post_modified,
            'containers' => $has_containers ? $has_containers : 0,
            'edit_url' => admin_url('post.php?post=' . $post->ID . '&action=edit'),
            'permalink' => get_permalink($post->ID),
        );
    }

    return array(
        'success' => true,
        'data' => array(
            'total' => $query->found_posts,
            'count' => count($pages),
            'pages' => $pages,
        ),
    );
}

/**
 * Duplicate an element at its current position.
 */
function mcp_avada_pro_duplicate_element(array $params)
{
    $post_id = $params['page_id'];
    $element_path = $params['element_path'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    $path = mcp_avada_pro_parse_element_path($element_path);
    if (is_wp_error($path)) {
        return $path;
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);

    $ci = $path['container_index'];
    $ri = $path['row_index'];
    $coli = $path['column_index'];
    $element_id = $path['element_id'];

    if (!isset($structure['containers'][$ci]['rows'][$ri]['columns'][$coli])) {
        return new WP_Error('position_not_found', 'Position not found');
    }

    $column = &$structure['containers'][$ci]['rows'][$ri]['columns'][$coli];

    // Find the element and its index
    $found_index = null;
    foreach ($column['elements'] as $idx => $el) {
        if ($el['id'] === $element_id) {
            $found_index = $idx;
            break;
        }
    }

    if ($found_index === null) {
        return new WP_Error('element_not_found', 'Element not found: ' . $element_id);
    }

    // Deep clone the element with a new ID
    $original = $column['elements'][$found_index];
    $clone = $original;
    $new_id = $original['type'] . '_' . count($column['elements']);
    $clone['id'] = $new_id;
    $clone['path'] = "container_{$ci}/row_{$ri}/column_{$coli}/{$new_id}";

    // Insert clone immediately after the original
    array_splice($column['elements'], $found_index + 1, 0, array($clone));

    $content = $parser->generate($structure);

    // P1-3: Dry-run validation — expect one more element of the duplicated type
    $dup_type = $original['type'];
    $expected_delta = array();
    if ($dup_type !== '__passthrough__') {
        $expected_delta[$dup_type] = 1;
    }
    $validation = mcp_avada_pro_validate_no_data_loss($post->post_content, $content, $expected_delta);
    if (!$validation['valid']) {
        return new WP_Error('avada_pro_data_loss', 'Cannot proceed: ' . $validation['error'], array('status' => 409));
    }

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ), true);
    if (is_wp_error($result)) {
        return $result;
    }

    return array(
        'success' => true,
        'data' => array(
            'original_path' => $element_path,
            'clone_id' => $new_id,
            'clone_path' => $clone['path'],
        ),
    );
}

/**
 * Move an element from one position to another.
 */
function mcp_avada_pro_move_element(array $params)
{
    $post_id = $params['page_id'];
    $element_path = $params['element_path'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    $path = mcp_avada_pro_parse_element_path($element_path);
    if (is_wp_error($path)) {
        return $path;
    }

    $target_ci = (int) $params['target_container'];
    $target_ri = isset($params['target_row']) ? (int) $params['target_row'] : 0;
    $target_coli = isset($params['target_column']) ? (int) $params['target_column'] : 0;
    $position = isset($params['position']) ? (int) $params['position'] : -1;

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);

    $ci = $path['container_index'];
    $ri = $path['row_index'];
    $coli = $path['column_index'];
    $element_id = $path['element_id'];

    // Validate source
    if (!isset($structure['containers'][$ci]['rows'][$ri]['columns'][$coli])) {
        return new WP_Error('position_not_found', 'Source position not found');
    }

    // Find and extract element from source
    $source_column = &$structure['containers'][$ci]['rows'][$ri]['columns'][$coli];
    $element = null;
    $element_index = null;

    foreach ($source_column['elements'] as $idx => $el) {
        if ($el['id'] === $element_id) {
            $element = $el;
            $element_index = $idx;
            break;
        }
    }

    if ($element === null) {
        return new WP_Error('element_not_found', 'Element not found: ' . $element_id);
    }

    // Remove from source
    array_splice($source_column['elements'], $element_index, 1);

    // Validate target
    if (!isset($structure['containers'][$target_ci])) {
        return new WP_Error('target_not_found', 'Target container not found at index ' . $target_ci);
    }
    if (!isset($structure['containers'][$target_ci]['rows'][$target_ri])) {
        return new WP_Error('target_not_found', 'Target row not found at index ' . $target_ri);
    }
    if (!isset($structure['containers'][$target_ci]['rows'][$target_ri]['columns'][$target_coli])) {
        return new WP_Error('target_not_found', 'Target column not found at index ' . $target_coli);
    }

    // Insert at target
    $target_column = &$structure['containers'][$target_ci]['rows'][$target_ri]['columns'][$target_coli];

    // Update element path
    $new_id = $element['type'] . '_' . count($target_column['elements']);
    $element['id'] = $new_id;
    $element['path'] = "container_{$target_ci}/row_{$target_ri}/column_{$target_coli}/{$new_id}";

    if ($position >= 0 && $position < count($target_column['elements'])) {
        array_splice($target_column['elements'], $position, 0, array($element));
    } else {
        $target_column['elements'][] = $element;
    }

    $content = $parser->generate($structure);

    // P1-3: Dry-run validation — move should not change any element counts
    $validation = mcp_avada_pro_validate_no_data_loss($post->post_content, $content);
    if (!$validation['valid']) {
        return new WP_Error('avada_pro_data_loss', 'Cannot proceed: ' . $validation['error'], array('status' => 409));
    }

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ), true);
    if (is_wp_error($result)) {
        return $result;
    }

    return array(
        'success' => true,
        'data' => array(
            'moved' => true,
            'from_path' => $element_path,
            'to_path' => $element['path'],
        ),
    );
}

/**
 * Find elements by type, content keyword, or attribute value.
 */
function mcp_avada_pro_find_element(array $params)
{
    $post_id = $params['page_id'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);
    $all = $parser->extract_all_elements($structure);

    $element_type = isset($params['element_type']) ? $params['element_type'] : null;
    $search = isset($params['search']) ? strtolower($params['search']) : null;
    $attribute = isset($params['attribute']) ? $params['attribute'] : null;
    $attribute_value = isset($params['attribute_value']) ? $params['attribute_value'] : null;

    if (!$element_type && !$search && !$attribute) {
        return new WP_Error('missing_filter', 'At least one filter is required: element_type, search, or attribute');
    }

    $results = array();

    foreach ($all as $el) {
        // Filter by type
        if ($element_type && $el['type'] !== $element_type) {
            continue;
        }

        // Filter by content/attribute keyword search
        if ($search) {
            $found_in_content = isset($el['content']) && strpos(strtolower($el['content']), $search) !== false;
            $found_in_attrs = false;
            if (isset($el['attributes'])) {
                foreach ($el['attributes'] as $val) {
                    if (strpos(strtolower($val), $search) !== false) {
                        $found_in_attrs = true;
                        break;
                    }
                }
            }
            if (!$found_in_content && !$found_in_attrs) {
                continue;
            }
        }

        // Filter by specific attribute
        if ($attribute) {
            if (!isset($el['attributes'][$attribute])) {
                continue;
            }
            if ($attribute_value !== null && $el['attributes'][$attribute] !== $attribute_value) {
                continue;
            }
        }

        $results[] = array(
            'id' => $el['id'],
            'type' => $el['type'],
            'path' => $el['path'],
            'attributes' => isset($el['attributes']) ? $el['attributes'] : array(),
            'content_preview' => isset($el['content']) ? mb_substr(strip_tags($el['content']), 0, 120) : '',
            'container_index' => isset($el['container_index']) ? $el['container_index'] : null,
            'row_index' => isset($el['row_index']) ? $el['row_index'] : null,
            'column_index' => isset($el['column_index']) ? $el['column_index'] : null,
        );
    }

    return array(
        'success' => true,
        'data' => array(
            'count' => count($results),
            'results' => $results,
        ),
    );
}

/**
 * Bulk update multiple elements in a single save operation.
 */
function mcp_avada_pro_bulk_update(array $params)
{
    $post_id = $params['page_id'];
    $updates = $params['updates'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }

    if (empty($updates) || !is_array($updates)) {
        return new WP_Error('invalid_updates', 'Updates array is required and must not be empty');
    }

    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);

    // ✅ PRIORITY 1 FIX: PRE-VALIDATION PASS (transactional safety)
    // Validate ALL updates BEFORE modifying the structure
    $validation_errors = array();
    $path_map = array(); // Cache parsed paths

    foreach ($updates as $idx => $update) {
        // Validate update structure
        if (!isset($update['element_path'])) {
            $validation_errors[] = array('index' => $idx, 'error' => 'Missing element_path');
            continue;
        }

        // Validate element_path format
        $path = mcp_avada_pro_parse_element_path($update['element_path']);
        if (is_wp_error($path)) {
            $validation_errors[] = array('index' => $idx, 'path' => $update['element_path'], 'error' => $path->get_error_message());
            continue;
        }

        // Validate path indices exist in structure
        $ci = $path['container_index'];
        $ri = $path['row_index'];
        $coli = $path['column_index'];
        $element_id = $path['element_id'];

        if (!isset($structure['containers'][$ci])) {
            $validation_errors[] = array('index' => $idx, 'path' => $update['element_path'], 'error' => 'Container not found at index ' . $ci);
            continue;
        }
        if (!isset($structure['containers'][$ci]['rows'][$ri])) {
            $validation_errors[] = array('index' => $idx, 'path' => $update['element_path'], 'error' => 'Row not found at index ' . $ri);
            continue;
        }
        if (!isset($structure['containers'][$ci]['rows'][$ri]['columns'][$coli])) {
            $validation_errors[] = array('index' => $idx, 'path' => $update['element_path'], 'error' => 'Column not found at index ' . $coli);
            continue;
        }

        // Validate element exists at path
        $column = &$structure['containers'][$ci]['rows'][$ri]['columns'][$coli];
        $element_found = false;
        foreach ($column['elements'] as $el) {
            if ($el['id'] === $element_id) {
                $element_found = true;
                break;
            }
        }
        if (!$element_found) {
            $validation_errors[] = array('index' => $idx, 'path' => $update['element_path'], 'error' => 'Element not found: ' . $element_id);
            continue;
        }

        // Validate attributes if provided
        if (isset($update['attributes']) && !empty($update['attributes'])) {
            $attr_validation = mcp_avada_pro_validate_element_attributes($update['attributes']);
            if (is_wp_error($attr_validation)) {
                $validation_errors[] = array('index' => $idx, 'path' => $update['element_path'], 'error' => $attr_validation->get_error_message());
                continue;
            }
        }

        // Cache parsed path if all validations pass
        $path_map[$idx] = $path;
    }

    // ✅ CRITICAL: If ANY validations failed, return error WITHOUT modifying structure
    if (!empty($validation_errors)) {
        return new WP_Error(
            'validation_failed',
            'Bulk update validation failed. No changes were applied.',
            array('validation_errors' => $validation_errors)
        );
    }

    // ✅ Now apply all updates (we know all are valid)
    $updated = 0;

    foreach ($updates as $idx => $update) {
        $path = $path_map[$idx];
        $ci = $path['container_index'];
        $ri = $path['row_index'];
        $coli = $path['column_index'];
        $element_id = $path['element_id'];

        $column = &$structure['containers'][$ci]['rows'][$ri]['columns'][$coli];

        foreach ($column['elements'] as &$element) {
            if ($element['id'] === $element_id) {
                if (isset($update['attributes']) && is_array($update['attributes'])) {
                    $element['attributes'] = array_merge($element['attributes'], $update['attributes']);
                }
                if (isset($update['content'])) {
                    $element['content'] = $update['content'];
                }
                $updated++;
                break;
            }
        }
        unset($element);
    }

    // Generate and save once (single atomic write)
    $content = $parser->generate($structure);

    // P1-3: Dry-run validation — bulk update should not change element counts
    $validation = mcp_avada_pro_validate_no_data_loss($post->post_content, $content);
    if (!$validation['valid']) {
        return new WP_Error('avada_pro_data_loss', 'Cannot proceed: ' . $validation['error'], array('status' => 409));
    }

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ), true);
    if (is_wp_error($result)) {
        return $result;
    }

    // ✅ AUDIT LOGGING: Log bulk update
    mcp_avada_pro_log_audit_event($post_id, 'avada-pro/bulk-update', 'Bulk updated ' . $updated . ' elements (transactional, validated)');

    return array(
        'success' => true,
        'data' => array(
            'total_requested' => count($updates),
            'updated' => $updated,
            'all_successful' => true,
        ),
    );
}

// =========================================================================
// PRIORITY 2: SCHEMA INTROSPECTION ABILITIES (v3.1.0)
// =========================================================================

/**
 * Helper: Get registered Fusion Builder elements from runtime globals.
 * Returns the full registry array or null if unavailable.
 */
function mcp_avada_pro_get_runtime_elements()
{
    global $all_fusion_builder_elements;
    if (!empty($all_fusion_builder_elements) && is_array($all_fusion_builder_elements)) {
        return $all_fusion_builder_elements;
    }
    global $fusion_builder_elements;
    if (!empty($fusion_builder_elements) && is_array($fusion_builder_elements)) {
        return $fusion_builder_elements;
    }
    return null;
}

/**
 * Helper: Format a raw Fusion Builder param into a clean schema entry.
 */
function mcp_avada_pro_format_param($param)
{
    $formatted = array(
        'param_name' => isset($param['param_name']) ? $param['param_name'] : '',
        'type' => isset($param['type']) ? $param['type'] : 'textfield',
        'heading' => isset($param['heading']) ? $param['heading'] : '',
        'description' => isset($param['description']) ? wp_strip_all_tags($param['description']) : '',
    );

    // Default value
    if (isset($param['default'])) {
        $formatted['default'] = $param['default'];
    } elseif (isset($param['value']) && !is_array($param['value'])) {
        $formatted['default'] = $param['value'];
    }

    // Options for select/radio types
    if (isset($param['value']) && is_array($param['value'])) {
        $formatted['options'] = $param['value'];
    }

    // Group/tab
    if (isset($param['group']) && !empty($param['group'])) {
        $formatted['group'] = $param['group'];
    }

    // Dependencies
    if (isset($param['dependency']) && !empty($param['dependency'])) {
        $formatted['dependency'] = $param['dependency'];
    }

    // Range constraints
    if (isset($param['min'])) {
        $formatted['min'] = $param['min'];
    }
    if (isset($param['max'])) {
        $formatted['max'] = $param['max'];
    }

    return $formatted;
}

/**
 * Get full parameter schema for a specific element type.
 */
function mcp_avada_pro_get_element_schema(array $params)
{
    $element_type = sanitize_key($params['element_type']);

    // Try runtime registry first
    $runtime = mcp_avada_pro_get_runtime_elements();

    if ($runtime && isset($runtime[$element_type])) {
        $el = $runtime[$element_type];
        $formatted_params = array();

        if (isset($el['params']) && is_array($el['params'])) {
            foreach ($el['params'] as $param) {
                if (isset($param['param_name']) && !empty($param['param_name'])) {
                    $formatted_params[] = mcp_avada_pro_format_param($param);
                }
            }
        }

        return array(
            'success' => true,
            'data' => array(
                'source' => 'runtime',
                'name' => isset($el['name']) ? $el['name'] : $element_type,
                'shortcode' => $element_type,
                'icon' => isset($el['icon']) ? $el['icon'] : '',
                'params' => $formatted_params,
                'param_count' => count($formatted_params),
            ),
        );
    }

    // Fallback to static registry
    $registry = new MCP_Avada_Elements();
    $el_info = $registry->get_by_type($element_type);

    if ($el_info) {
        return array(
            'success' => true,
            'data' => array(
                'source' => 'static',
                'name' => $el_info['name'],
                'shortcode' => $el_info['type'],
                'category' => $el_info['category'],
                'description' => $el_info['description'],
                'has_content' => $el_info['has_content'],
                'params' => array(),
                'param_count' => 0,
                'note' => 'Static registry does not include parameter schemas. Activate Fusion Builder for full schema.',
            ),
        );
    }

    return new WP_Error('element_not_found', 'Element type not found: ' . $element_type);
}

/**
 * Get default values for all parameters of an element type.
 */
function mcp_avada_pro_get_element_defaults(array $params)
{
    $element_type = sanitize_key($params['element_type']);

    // Try to get defaults from the FusionSC_* class at runtime
    // Avada classes use pattern: FusionSC_{PascalName} for fusion_{name}
    $short_name = str_replace('fusion_', '', $element_type);
    // Common class name patterns
    $class_candidates = array(
        'FusionSC_' . str_replace('_', '', ucwords($short_name, '_')),
        'FusionSC_' . ucfirst($short_name),
        'Avada_Widget_Style_' . ucfirst($short_name),
    );

    foreach ($class_candidates as $class_name) {
        if (class_exists($class_name) && method_exists($class_name, 'get_element_defaults')) {
            $defaults = $class_name::get_element_defaults();
            return array(
                'success' => true,
                'data' => array(
                    'source' => 'runtime_class',
                    'class' => $class_name,
                    'element_type' => $element_type,
                    'defaults' => $defaults,
                    'count' => is_array($defaults) ? count($defaults) : 0,
                ),
            );
        }
    }

    // Fallback: extract defaults from runtime params
    $runtime = mcp_avada_pro_get_runtime_elements();

    if ($runtime && isset($runtime[$element_type]['params'])) {
        $defaults = array();
        foreach ($runtime[$element_type]['params'] as $param) {
            if (!isset($param['param_name']) || empty($param['param_name'])) {
                continue;
            }
            if (isset($param['default'])) {
                $defaults[$param['param_name']] = $param['default'];
            } elseif (isset($param['value']) && !is_array($param['value'])) {
                $defaults[$param['param_name']] = $param['value'];
            }
        }

        return array(
            'success' => true,
            'data' => array(
                'source' => 'runtime_params',
                'element_type' => $element_type,
                'defaults' => $defaults,
                'count' => count($defaults),
            ),
        );
    }

    // Final fallback: confirm element exists in static registry and return
    // an empty defaults map instead of hard-failing.
    $registry = new MCP_Avada_Elements();
    $el_info = $registry->get_by_type($element_type);

    if ($el_info) {
        return array(
            'success' => true,
            'data' => array(
                'source' => 'static',
                'element_type' => $element_type,
                'defaults' => array(),
                'count' => 0,
                'note' => 'No runtime defaults available in current context. Element exists in static registry.',
            ),
        );
    }

    return new WP_Error('element_not_found', 'Element type not found: ' . $element_type);
}

/**
 * List all element categories with counts and element type lists.
 */
function mcp_avada_pro_list_element_categories()
{
    $categories = array();

    // Try runtime registry first
    $runtime = mcp_avada_pro_get_runtime_elements();

    if ($runtime) {
        foreach ($runtime as $shortcode => $el) {
            // Fusion Builder uses various category fields
            $cat = 'uncategorized';
            if (isset($el['component']) && !empty($el['component'])) {
                $cat = $el['component'];
            } elseif (isset($el['category']) && !empty($el['category'])) {
                // category can be an array
                $cat = is_array($el['category']) ? implode(', ', $el['category']) : $el['category'];
            }

            if (!isset($categories[$cat])) {
                $categories[$cat] = array(
                    'name' => $cat,
                    'count' => 0,
                    'elements' => array(),
                );
            }
            $categories[$cat]['count']++;
            $categories[$cat]['elements'][] = array(
                'shortcode' => $shortcode,
                'name' => isset($el['name']) ? $el['name'] : $shortcode,
            );
        }

        return array(
            'success' => true,
            'data' => array(
                'source' => 'runtime',
                'total' => count($categories),
                'categories' => array_values($categories),
            ),
        );
    }

    // Fallback to static registry
    $registry = new MCP_Avada_Elements();
    $static_cats = $registry->get_categories();

    foreach ($static_cats as $cat) {
        $elements_in_cat = $registry->get_by_category($cat['name']);
        $categories[] = array(
            'name' => $cat['name'],
            'count' => $cat['count'],
            'elements' => array_map(function ($el) {
                return array('shortcode' => $el['type'], 'name' => $el['name']);
            }, $elements_in_cat),
        );
    }

    return array(
        'success' => true,
        'data' => array(
            'source' => 'static',
            'total' => count($categories),
            'categories' => $categories,
        ),
    );
}

/**
 * Search available element types by keyword.
 */
function mcp_avada_pro_search_elements(array $params)
{
    $search = strtolower(sanitize_text_field($params['search']));
    $category = isset($params['category']) ? strtolower(sanitize_text_field($params['category'])) : null;
    $results = array();

    // Try runtime registry first
    $runtime = mcp_avada_pro_get_runtime_elements();

    if ($runtime) {
        foreach ($runtime as $shortcode => $el) {
            $name = isset($el['name']) ? $el['name'] : '';
            $desc = isset($el['description']) ? $el['description'] : '';
            $el_cat = '';
            if (isset($el['component'])) {
                $el_cat = is_array($el['component']) ? implode(' ', $el['component']) : $el['component'];
            } elseif (isset($el['category'])) {
                $el_cat = is_array($el['category']) ? implode(' ', $el['category']) : $el['category'];
            }

            // Category filter
            if ($category && strpos(strtolower($el_cat), $category) === false) {
                continue;
            }

            // Keyword search across name, shortcode, description
            $haystack = strtolower($name . ' ' . $shortcode . ' ' . $desc . ' ' . $el_cat);
            if (strpos($haystack, $search) === false) {
                continue;
            }

            $param_count = isset($el['params']) && is_array($el['params']) ? count($el['params']) : 0;
            $results[] = array(
                'shortcode' => $shortcode,
                'name' => $name,
                'description' => wp_strip_all_tags($desc),
                'category' => $el_cat,
                'icon' => isset($el['icon']) ? $el['icon'] : '',
                'param_count' => $param_count,
            );
        }

        return array(
            'success' => true,
            'data' => array(
                'source' => 'runtime',
                'query' => $search,
                'count' => count($results),
                'results' => $results,
            ),
        );
    }

    // Fallback to static registry
    $registry = new MCP_Avada_Elements();
    $all = $registry->get_all();

    foreach ($all as $el) {
        if ($category && strtolower($el['category']) !== $category) {
            continue;
        }

        $haystack = strtolower($el['name'] . ' ' . $el['type'] . ' ' . $el['description'] . ' ' . $el['category']);
        if (strpos($haystack, $search) === false) {
            continue;
        }

        $results[] = array(
            'shortcode' => $el['type'],
            'name' => $el['name'],
            'description' => $el['description'],
            'category' => $el['category'],
            'has_content' => $el['has_content'],
        );
    }

    return array(
        'success' => true,
        'data' => array(
            'source' => 'static',
            'query' => $search,
            'count' => count($results),
            'results' => $results,
        ),
    );
}

// =========================================================================
// P1-3: DRY-RUN VALIDATION HELPER
// Validates that a parse→generate cycle does not silently lose elements.
// =========================================================================

/**
 * Validate that regenerated content does not lose elements from the original.
 *
 * Compares shortcode tag counts between original post_content and
 * newly generated content. Returns validation result array.
 *
 * @param string $original_content Original post_content before modification.
 * @param string $new_content      Newly generated post_content after modification.
 * @param array  $expected_delta   Optional. Expected changes in element counts.
 *                                 Keys are shortcode tag names, values are signed integers.
 *                                 Example: array('fusion_text' => 1) means we expect one new fusion_text.
 *                                 Example: array('fusion_text' => -1) means we intentionally removed one.
 * @return array { 'valid': bool, 'error': string|null, 'details': array|null }
 */
function mcp_avada_pro_validate_no_data_loss($original_content, $new_content, $expected_delta = array())
{
    // Count all shortcode opening tags in original
    preg_match_all('/\[([a-zA-Z_][a-zA-Z0-9_-]*)[\s\]]/m', $original_content, $orig_matches);
    $orig_counts = array_count_values($orig_matches[1]);

    // Count all shortcode opening tags in new content
    preg_match_all('/\[([a-zA-Z_][a-zA-Z0-9_-]*)[\s\]]/m', $new_content, $new_matches);
    $new_counts = array_count_values($new_matches[1]);

    // Apply expected deltas to original counts for comparison
    $expected_counts = $orig_counts;
    foreach ($expected_delta as $tag => $delta) {
        if (!isset($expected_counts[$tag])) {
            $expected_counts[$tag] = 0;
        }
        $expected_counts[$tag] += $delta;
        if ($expected_counts[$tag] <= 0) {
            unset($expected_counts[$tag]);
        }
    }

    // Compare: find any tags that are present in expected but missing/reduced in new
    $missing = array();
    foreach ($expected_counts as $tag => $expected_count) {
        $actual = isset($new_counts[$tag]) ? $new_counts[$tag] : 0;
        if ($actual < $expected_count) {
            $missing[$tag] = array(
                'expected' => $expected_count,
                'actual' => $actual,
                'lost' => $expected_count - $actual,
            );
        }
    }

    if (!empty($missing)) {
        $lost_tags = array();
        foreach ($missing as $tag => $info) {
            $lost_tags[] = "{$tag} (expected {$info['expected']}, got {$info['actual']})";
        }

        return array(
            'valid' => false,
            'error' => 'Data loss detected: shortcode tags lost during generation: ' . implode(', ', $lost_tags),
            'details' => $missing,
        );
    }

    return array('valid' => true, 'error' => null, 'details' => null);
}

// =========================================================================
// P2-6: NEW ABILITIES — validate-page-structure & repair-shortcode-tree
// =========================================================================

/**
 * Validate page structure integrity.
 * Checks for: malformed shortcodes, tag balance, duplicate elements,
 * orphaned closing tags, and parse-generate round-trip fidelity.
 */
function mcp_avada_pro_validate_page_structure(array $params)
{
    $post_id = $params['page_id'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
    }

    $content = $post->post_content;
    $issues = array();
    $warnings = array();

    // 1. Check shortcode tag balance (opening vs closing)
    $tag_pattern = '/\[(\/?)([a-zA-Z_][a-zA-Z0-9_-]*)[\s\]]/';
    preg_match_all($tag_pattern, $content, $tag_matches, PREG_SET_ORDER);

    $open_counts = array();
    $close_counts = array();
    foreach ($tag_matches as $m) {
        $is_close = $m[1] === '/';
        $tag = $m[2];
        if ($is_close) {
            $close_counts[$tag] = ($close_counts[$tag] ?? 0) + 1;
        } else {
            $open_counts[$tag] = ($open_counts[$tag] ?? 0) + 1;
        }
    }

    // Self-closing tags (no content) are expected to have no closing tags
    $self_closing_tags = array('fusion_image', 'fusion_separator', 'fusion_fontawesome', 'fusion_gallery',
        'fusion_blog', 'fusion_portfolio', 'fusion_social_links', 'fusion_slider', 'fusion_post_slider',
        'fusion_rev_slider', 'fusion_layerslider', 'fusion_modal_text_link', 'fusion_pricing_button',
        'fusion_viewport', 'fusion_code_block', 'fusion_post_grid', 'fusion_portfolio_masonry',
        'fusion_google_fonts', 'fusion_fusion_slider');

    foreach ($open_counts as $tag => $ocount) {
        if (in_array($tag, $self_closing_tags, true)) {
            continue;
        }
        $ccount = $close_counts[$tag] ?? 0;
        if ($ocount !== $ccount) {
            $issues[] = array(
                'type' => 'tag_imbalance',
                'tag' => $tag,
                'opening' => $ocount,
                'closing' => $ccount,
                'severity' => 'error',
            );
        }
    }

    // 2. Check for orphaned closing tags (close without open)
    foreach ($close_counts as $tag => $ccount) {
        if (!isset($open_counts[$tag])) {
            $issues[] = array(
                'type' => 'orphan_closing_tag',
                'tag' => $tag,
                'count' => $ccount,
                'severity' => 'error',
            );
        }
    }

    // 3. Parse-generate round-trip test
    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($content, true);
    $regenerated = $parser->generate($structure);
    $roundtrip = mcp_avada_pro_validate_no_data_loss($content, $regenerated);

    if (!$roundtrip['valid']) {
        $issues[] = array(
            'type' => 'roundtrip_data_loss',
            'message' => $roundtrip['error'],
            'details' => $roundtrip['details'],
            'severity' => 'error',
        );
    }

    // 4. Check for duplicate elements within same column
    $all_elements = $parser->extract_all_elements($structure);
    $seen_hashes = array();
    $duplicate_count = 0;
    foreach ($all_elements as $el) {
        $hash = $el['type'] . '|' . md5(($el['content'] ?? '') . json_encode($el['attributes'] ?? array()));
        $path = $el['path'] ?? 'unknown';
        if (isset($seen_hashes[$hash])) {
            $duplicate_count++;
            $warnings[] = array(
                'type' => 'potential_duplicate',
                'element_type' => $el['type'],
                'path' => $path,
                'duplicate_of' => $seen_hashes[$hash],
            );
        } else {
            $seen_hashes[$hash] = $path;
        }
    }

    // 5. Check builder_enabled meta
    $builder_status = get_post_meta($post_id, '_fusion_builder_status', true);
    $has_containers = substr_count($content, '[fusion_builder_container');
    if ($builder_status !== 'active' && $has_containers > 0) {
        $warnings[] = array(
            'type' => 'builder_status_mismatch',
            'message' => 'Page has ' . $has_containers . ' containers but _fusion_builder_status is not "active"',
        );
    }

    // Score calculation
    $error_count = count(array_filter($issues, function ($i) { return ($i['severity'] ?? '') === 'error'; }));
    $health_score = max(0, 10 - ($error_count * 2) - count($warnings));

    return array(
        'success' => true,
        'data' => array(
            'post_id' => $post_id,
            'health_score' => $health_score . '/10',
            'containers_count' => count($structure['containers']),
            'total_elements' => count($all_elements),
            'issues_count' => count($issues),
            'warnings_count' => count($warnings),
            'duplicate_elements' => $duplicate_count,
            'roundtrip_ok' => $roundtrip['valid'],
            'builder_enabled' => $builder_status === 'active',
            'issues' => $issues,
            'warnings' => $warnings,
        ),
    );
}

/**
 * Repair malformed shortcode trees.
 * Performs: duplicate removal, tag rebalancing, and structure normalization.
 * Always creates a backup before modifying.
 */
function mcp_avada_pro_repair_shortcode_tree(array $params)
{
    $post_id = $params['page_id'];
    $dry_run = isset($params['dry_run']) ? (bool) $params['dry_run'] : true;
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
    }

    $content = $post->post_content;
    $repairs = array();

    // Step 1: Remove exact duplicate shortcode fragments
    // Find repeated blocks of shortcode content
    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($content, true);

    $duplicates_removed = 0;
    foreach ($structure['containers'] as &$container) {
        foreach ($container['rows'] as &$row) {
            foreach ($row['columns'] as &$column) {
                $seen = array();
                $original_count = count($column['elements']);
                $column['elements'] = array_values(array_filter($column['elements'], function ($element) use (&$seen, &$duplicates_removed) {
                    $hash = $element['type'] . '|' . md5(($element['content'] ?? '') . json_encode($element['attributes'] ?? array()));
                    if (isset($seen[$hash])) {
                        $duplicates_removed++;
                        return false;
                    }
                    $seen[$hash] = true;
                    return true;
                }));
                if (count($column['elements']) < $original_count) {
                    $repairs[] = 'Removed ' . ($original_count - count($column['elements'])) . ' duplicate elements from column';
                }
            }
        }
    }
    unset($container, $row, $column);

    // Step 2: Remove empty containers (containers with no rows or only empty rows)
    $empty_containers_removed = 0;
    $structure['containers'] = array_values(array_filter($structure['containers'], function ($container) use (&$empty_containers_removed) {
        $has_content = false;
        if (!empty($container['rows'])) {
            foreach ($container['rows'] as $row) {
                if (!empty($row['columns'])) {
                    foreach ($row['columns'] as $col) {
                        if (!empty($col['elements'])) {
                            $has_content = true;
                            break 2;
                        }
                    }
                }
            }
        }
        // Keep containers that have content OR have attributes (intentionally styled)
        if (!$has_content && empty($container['attributes'])) {
            $empty_containers_removed++;
            return false;
        }
        return true;
    }));
    if ($empty_containers_removed > 0) {
        $repairs[] = 'Removed ' . $empty_containers_removed . ' empty containers';
    }

    // Step 3: Generate clean content
    $repaired_content = $parser->generate($structure);

    // Step 4: Verify round-trip
    $verification = $parser->parse($repaired_content, true);
    $verification_regenerated = $parser->generate($verification);
    $roundtrip_ok = $repaired_content === $verification_regenerated;
    if (!$roundtrip_ok) {
        $repairs[] = 'WARNING: Round-trip verification detected drift — manual review recommended';
    }

    if ($dry_run) {
        return array(
            'success' => true,
            'data' => array(
                'post_id' => $post_id,
                'dry_run' => true,
                'repairs_planned' => $repairs,
                'duplicates_found' => $duplicates_removed,
                'empty_containers_found' => $empty_containers_removed,
                'original_containers' => substr_count($content, '[fusion_builder_container'),
                'repaired_containers' => count($structure['containers']),
                'roundtrip_ok' => $roundtrip_ok,
                'message' => 'Set dry_run=false to apply these repairs.',
            ),
        );
    }

    // Create backup before repair
    update_post_meta($post_id, '_fusion_builder_pre_repair_backup', array(
        'content' => $content,
        'timestamp' => current_time('mysql'),
        'user_id' => get_current_user_id(),
    ));

    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $repaired_content,
    ), true);

    if (is_wp_error($result)) {
        return $result;
    }

    // Ensure builder status is set
    update_post_meta($post_id, '_fusion_builder_status', 'active');

    mcp_avada_pro_log_audit_event($post_id, 'avada-pro/repair-shortcode-tree', 'Repaired: ' . implode('; ', $repairs));

    return array(
        'success' => true,
        'data' => array(
            'post_id' => $post_id,
            'dry_run' => false,
            'repairs_applied' => $repairs,
            'duplicates_removed' => $duplicates_removed,
            'empty_containers_removed' => $empty_containers_removed,
            'containers_count' => count($structure['containers']),
            'backup_stored' => true,
            'roundtrip_ok' => $roundtrip_ok,
        ),
    );
}
