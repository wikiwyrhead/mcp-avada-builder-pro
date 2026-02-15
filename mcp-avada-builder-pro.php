<?php
/**
 * Plugin Name: MCP Avada Builder Pro
 * Description: Advanced MCP integration for Avada Fusion Builder with full shortcode parsing and element management
 * Version: 2.0.0
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

define('MCP_AVADA_VERSION', '2.0.0');

add_action('wp_abilities_api_categories_init', 'mcp_avada_pro_register_category');
function mcp_avada_pro_register_category(): void {
    wp_register_ability_category(
        'avada-builder-pro',
        array(
            'label'       => __('Avada Builder Pro', 'mcp-avada-builder-pro'),
            'description' => __('Advanced abilities for controlling Avada Fusion Builder with full element support.', 'mcp-avada-builder-pro'),
        )
    );
}

add_action('wp_abilities_api_init', 'mcp_avada_pro_register_abilities');
function mcp_avada_pro_register_abilities(): void {
    require_once plugin_dir_path(__FILE__) . 'includes/class-avada-parser.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-avada-elements.php';

    wp_register_ability(
        'avada-pro/get-info',
        array(
            'label'               => __('Get Builder Info (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('Get detailed Avada Builder version, theme version, and status', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_get_info',
            'permission_callback' => function(): bool {
                return current_user_can('edit_posts');
            },
            'output_schema'       => array(
                'type'        => 'object',
                'properties'  => array(
                    'success' => array('type' => 'boolean'),
                ),
            ),
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/get-page-structure',
        array(
            'label'               => __('Get Page Structure (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('Get full page structure including containers, rows, columns, and elements with proper hierarchy', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_get_page_structure',
            'permission_callback' => function($params): bool {
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema'        => array(
                'type'        => 'object',
                'properties'  => array(
                    'page_id' => array('type' => 'integer'),
                    'include_content' => array('type' => 'boolean', 'default' => true),
                ),
                'required'    => array('page_id'),
            ),
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/list-all-elements',
        array(
            'label'               => __('List All Elements (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('List all elements on a page with their full details and hierarchy', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_list_elements',
            'permission_callback' => function($params): bool {
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema'        => array(
                'type'        => 'object',
                'properties'  => array(
                    'page_id' => array('type' => 'integer'),
                    'element_type' => array('type' => 'string'),
                ),
                'required'    => array('page_id'),
            ),
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/add-container',
        array(
            'label'               => __('Add Container (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('Add a new container with optional row and column', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_add_container',
            'permission_callback' => function($params): bool {
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema'        => array(
                'type'        => 'object',
                'properties'  => array(
                    'page_id' => array('type' => 'integer'),
                    'container_attrs' => array('type' => 'object'),
                    'add_row' => array('type' => 'boolean', 'default' => true),
                    'column_type' => array('type' => 'string', 'default' => '1_1'),
                ),
                'required'    => array('page_id'),
            ),
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/add-element',
        array(
            'label'               => __('Add Element (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('Add a new element to a specific container/row/column position', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_add_element',
            'permission_callback' => function($params): bool {
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema'        => array(
                'type'        => 'object',
                'properties'  => array(
                    'page_id' => array('type' => 'integer'),
                    'element_type' => array('type' => 'string'),
                    'container_index' => array('type' => 'integer', 'default' => 0),
                    'row_index' => array('type' => 'integer', 'default' => 0),
                    'column_index' => array('type' => 'integer', 'default' => 0),
                    'attributes' => array('type' => 'object'),
                    'content' => array('type' => 'string'),
                ),
                'required'    => array('page_id', 'element_type'),
            ),
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/update-element',
        array(
            'label'               => __('Update Element (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('Update an element by its unique ID with full path tracking', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_update_element',
            'permission_callback' => function($params): bool {
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema'        => array(
                'type'        => 'object',
                'properties'  => array(
                    'page_id' => array('type' => 'integer'),
                    'element_path' => array('type' => 'string', 'description' => 'Path like container_0/row_0/column_0/element_0'),
                    'attributes' => array('type' => 'object'),
                    'content' => array('type' => 'string'),
                ),
                'required'    => array('page_id', 'element_path'),
            ),
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/delete-element',
        array(
            'label'               => __('Delete Element (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('Delete an element by its path', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_delete_element',
            'permission_callback' => function($params): bool {
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema'        => array(
                'type'        => 'object',
                'properties'  => array(
                    'page_id' => array('type' => 'integer'),
                    'element_path' => array('type' => 'string'),
                ),
                'required'    => array('page_id', 'element_path'),
            ),
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/replace-content',
        array(
            'label'               => __('Replace Page Content (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('Replace entire page content with new structure', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_replace_content',
            'permission_callback' => function($params): bool {
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema'        => array(
                'type'        => 'object',
                'properties'  => array(
                    'page_id' => array('type' => 'integer'),
                    'structure' => array('type' => 'object'),
                ),
                'required'    => array('page_id', 'structure'),
            ),
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/list-element-types',
        array(
            'label'               => __('List Element Types (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('Get all available Avada element types with their schemas', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_list_element_types',
            'permission_callback' => function(): bool {
                return current_user_can('edit_posts');
            },
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/clean-duplicates',
        array(
            'label'               => __('Clean Duplicates (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('Remove duplicate elements from a page', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_clean_duplicates',
            'permission_callback' => function($params): bool {
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema'        => array(
                'type'        => 'object',
                'properties'  => array(
                    'page_id' => array('type' => 'integer'),
                ),
                'required'    => array('page_id'),
            ),
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );

    wp_register_ability(
        'avada-pro/restructure-layout',
        array(
            'label'               => __('Restructure Layout (Pro)', 'mcp-avada-builder-pro'),
            'description'         => __('Restructure elements in a container - convert inline icons to vertical columns, reorder elements, etc.', 'mcp-avada-builder-pro'),
            'category'            => 'avada-builder-pro',
            'execute_callback'    => 'mcp_avada_pro_restructure_layout',
            'permission_callback' => function($params): bool {
                return current_user_can('edit_post', $params['page_id']);
            },
            'input_schema'        => array(
                'type'        => 'object',
                'properties'  => array(
                    'page_id' => array('type' => 'integer', 'description' => 'Page ID'),
                    'container_index' => array('type' => 'integer', 'default' => 0, 'description' => 'Container index to restructure'),
                    'layout_type' => array('type' => 'string', 'enum' => array('icon_vertical', 'icon_horizontal', 'to_columns', 'reorder'), 'description' => 'Type of restructuring'),
                    'column_type' => array('type' => 'string', 'default' => '1_4', 'description' => 'Column type for new columns (1_2, 1_3, 1_4, 1_5, 1_6)'),
                    'element_indices' => array('type' => 'array', 'items' => array('type' => 'integer'), 'description' => 'Indices of elements to restructure'),
                ),
                'required'    => array('page_id', 'layout_type'),
            ),
            'meta'                => array(
                'mcp' => array(
                    'public' => true,
                    'type'   => 'tool',
                ),
            ),
        )
    );
}

function mcp_avada_pro_get_info(): array {
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

function mcp_avada_pro_get_page_structure(array $params) {
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

function mcp_avada_pro_list_elements(array $params) {
    $post_id = $params['page_id'];
    $post = get_post($post_id);
    
    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }
    
    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);
    
    $elements = $parser->extract_all_elements($structure);
    
    if (isset($params['element_type'])) {
        $elements = array_filter($elements, function($e) use ($params) {
            return $e['type'] === $params['element_type'];
        });
    }
    
    return array(
        'success' => true,
        'data' => array_values($elements),
    );
}

function mcp_avada_pro_add_container(array $params) {
    $post_id = $params['page_id'];
    $post = get_post($post_id);
    
    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }
    
    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, false);
    
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
    
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ));
    
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

function mcp_avada_pro_add_element(array $params) {
    $post_id = $params['page_id'];
    $post = get_post($post_id);
    
    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }
    
    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, false);
    
    $container_index = isset($params['container_index']) ? $params['container_index'] : 0;
    $row_index = isset($params['row_index']) ? $params['row_index'] : 0;
    $column_index = isset($params['column_index']) ? $params['column_index'] : 0;
    
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
    $element_count = count(array_filter($column['elements'], function($e) use ($element_type) {
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
    
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ));
    
    return array(
        'success' => true,
        'data' => array(
            'element' => $element,
            'path' => "container_{$container_index}/row_{$row_index}/column_{$column_index}/{$element['id']}",
        ),
    );
}

function mcp_avada_pro_update_element(array $params) {
    $post_id = $params['page_id'];
    $element_path = $params['element_path'];
    $post = get_post($post_id);
    
    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }
    
    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, false);
    
    $parts = explode('/', $element_path);
    if (count($parts) < 4 || $parts[0] !== 'container' || $parts[1] !== 'row' || $parts[2] !== 'column') {
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
    foreach ($column['elements'] as &$element) {
        if ($element['id'] === $element_id) {
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
    
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ));
    
    return array(
        'success' => true,
        'data' => array(
            'updated' => true,
            'path' => $element_path,
        ),
    );
}

function mcp_avada_pro_delete_element(array $params) {
    $post_id = $params['page_id'];
    $element_path = $params['element_path'];
    $post = get_post($post_id);
    
    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }
    
    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, false);
    
    $parts = explode('/', $element_path);
    $container_index = (int) str_replace('container_', '', $parts[0]);
    $row_index = (int) str_replace('row_', '', $parts[1]);
    $column_index = (int) str_replace('column_', '', $parts[2]);
    $element_id = $parts[3];
    
    if (!isset($structure['containers'][$container_index]['rows'][$row_index]['columns'][$column_index])) {
        return new WP_Error('position_not_found', 'Position not found');
    }
    
    $column = &$structure['containers'][$container_index]['rows'][$row_index]['columns'][$column_index];
    
    $original_count = count($column['elements']);
    $column['elements'] = array_values(array_filter($column['elements'], function($e) use ($element_id) {
        return $e['id'] !== $element_id;
    }));
    
    if (count($column['elements']) === $original_count) {
        return new WP_Error('element_not_found', 'Element not found: ' . $element_id);
    }
    
    $content = $parser->generate($structure);
    
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ));
    
    return array(
        'success' => true,
        'data' => array(
            'deleted' => true,
            'element_id' => $element_id,
        ),
    );
}

function mcp_avada_pro_replace_content(array $params) {
    $post_id = $params['page_id'];
    $structure = $params['structure'];
    $post = get_post($post_id);
    
    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }
    
    $parser = new MCP_Avada_Parser();
    $content = $parser->generate($structure);
    
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ));
    
    update_post_meta($post_id, '_fusion_builder_status', 'active');
    update_post_meta($post_id, '_fusion_builder_version', defined('FUSION_BUILDER_VERSION') ? FUSION_BUILDER_VERSION : '3.0');
    
    return array(
        'success' => true,
        'data' => array(
            'post_id' => $post_id,
            'updated' => true,
            'preview_url' => get_permalink($post_id) . '?preview=true',
        ),
    );
}

function mcp_avada_pro_list_element_types(): array {
    $elements = new MCP_Avada_Elements();
    return array(
        'success' => true,
        'data' => $elements->get_all(),
    );
}

function mcp_avada_pro_clean_duplicates(array $params) {
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
                $column['elements'] = array_values(array_filter($column['elements'], function($element) use (&$seen, &$duplicates) {
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
    
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ));
    
    return array(
        'success' => true,
        'data' => array(
            'duplicates_removed' => $duplicates,
            'remaining_elements' => count($parser->extract_all_elements($structure)),
        ),
    );
}

function mcp_avada_pro_restructure_layout(array $params) {
    $post_id = $params['page_id'];
    $post = get_post($post_id);
    
    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }
    
    $parser = new MCP_Avada_Parser();
    $structure = $parser->parse($post->post_content, true);
    
    $container_index = isset($params['container_index']) ? $params['container_index'] : 0;
    $layout_type = $params['layout_type'];
    $column_type = isset($params['column_type']) ? $params['column_type'] : '1_4';
    $element_indices = isset($params['element_indices']) ? $params['element_indices'] : null;
    $filter_content = isset($params['filter_content']) ? $params['filter_content'] : null;
    
    if (!isset($structure['containers'][$container_index])) {
        return new WP_Error('container_not_found', 'Container not found at index ' . $container_index);
    }
    
    $container = &$structure['containers'][$container_index];
    
    if ($layout_type === 'icon_vertical') {
        $elements_to_restructure = array();
        
        foreach ($container['rows'] as &$row) {
            foreach ($row['columns'] as &$column) {
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
        }
        
        if (empty($elements_to_restructure)) {
            return new WP_Error('no_elements_found', 'No elements found to restructure. Try adding filter_content keywords.');
        }
        
        $column_map = array(
            '1_2' => 2,
            '1_3' => 3,
            '1_4' => 4,
            '1_5' => 5,
            '1_6' => 6,
        );
        $num_cols = isset($column_map[$column_type]) ? $column_map[$column_type] : 4;
        
        $chunks = array_chunk($elements_to_restructure, $num_cols);
        
        $new_row = array(
            'id' => 'row_restructured',
            'attributes' => array(),
            'columns' => array(),
        );
        
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
            
            $new_row['columns'][] = $column;
            $col_index++;
        }
        
        $container['rows'][] = $new_row;
    }
    
    $content = $parser->generate($structure);
    
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ));
    
    update_post_meta($post_id, '_fusion_builder_status', 'active');
    
    return array(
        'success' => true,
        'data' => array(
            'container_index' => $container_index,
            'layout_type' => $layout_type,
            'elements_restructured' => count($elements_to_restructure),
            'columns_created' => isset($new_row['columns']) ? count($new_row['columns']) : 0,
        ),
    );
}
