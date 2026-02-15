<?php
/**
 * Advanced Shortcode Parser for Avada Fusion Builder
 * Handles nested shortcodes properly with full hierarchy support
 */

class MCP_Avada_Parser {
    
    private $element_patterns = array();
    
    public function __construct() {
        $this->init_element_patterns();
    }
    
    private function init_element_patterns() {
        $this->element_patterns = array(
            'fusion_text' => '/\[fusion_text([^\]]*)\](.*?)\[\/fusion_text\]/s',
            'fusion_image' => '/\[fusion_image([^\]]*)\]/',
            'fusion_button' => '/\[fusion_button([^\]]*)\](.*?)\[\/fusion_button\]/s',
            'fusion_title' => '/\[fusion_title([^\]]*)\](.*?)\[\/fusion_title\]/s',
            'fusion_separator' => '/\[fusion_separator([^\]]*)\]/',
            'fusion_gallery' => '/\[fusion_gallery([^\]]*)\]/',
            'fusion_video' => '/\[fusion_video([^\]]*)\](.*?)\[\/fusion_video\]/s',
            'fusion_audio' => '/\[fusion_audio([^\]]*)\](.*?)\[\/fusion_audio\]/s',
            'fusion_map' => '/\[fusion_map([^\]]*)\](.*?)\[\/fusion_map\]/s',
            'fusion_fontawesome' => '/\[fusion_fontawesome([^\]]*)\]/',
            'fusion_google_fonts' => '/\[fusion_google_fonts([^\]]*)\]/',
            'fusion_code' => '/\[fusion_code([^\]]*)\](.*?)\[\/fusion_code\]/s',
            'fusion_alert' => '/\[fusion_alert([^\]]*)\](.*?)\[\/fusion_alert\]/s',
            'fusion_accordion' => '/\[fusion_accordion([^\]]*)\](.*?)\[\/fusion_accordion\]/s',
            'fusion_toggle' => '/\[fusion_toggle([^\]]*)\](.*?)\[\/fusion_toggle\]/s',
            'fusion_tabs' => '/\[fusion_tabs([^\]]*)\](.*?)\[\/fusion_tabs\]/s',
            'fusion_tab' => '/\[fusion_tab([^\]]*)\](.*?)\[\/fusion_tab\]/s',
            'fusion_tooltip' => '/\[fusion_tooltip([^\]]*)\](.*?)\[\/fusion_tooltip\]/s',
            'fusion_popover' => '/\[fusion_popover([^\]]*)\](.*?)\[\/fusion_popover\]/s',
            'fusion_modal' => '/\[fusion_modal([^\]]*)\](.*?)\[\/fusion_modal\]/s',
            'fusion_modal_text_link' => '/\[fusion_modal_text_link([^\]]*)\]/',
            'fusion_pricing_table' => '/\[fusion_pricing_table([^\]]*)\](.*?)\[\/fusion_pricing_table\]/s',
            'fusion_pricing_column' => '/\[fusion_pricing_column([^\]]*)\](.*?)\[\/fusion_pricing_column\]/s',
            'fusion_pricing_price' => '/\[fusion_pricing_price([^\]]*)\](.*?)\[\/fusion_pricing_price\]/s',
            'fusion_pricing_button' => '/\[fusion_pricing_button([^\]]*)\]/',
            'fusion_counter' => '/\[fusion_counter([^\]]*)\](.*?)\[\/fusion_counter\]/s',
            'fusion_progress' => '/\[fusion_progress([^\]]*)\](.*?)\[\/fusion_progress\]/s',
            'fusion_counters' => '/\[fusion_counters([^\]]*)\](.*?)\[\/fusion_counters\]/s',
            'fusion_testimonial' => '/\[fusion_testimonial([^\]]*)\](.*?)\[\/fusion_testimonial\]/s',
            'fusion_testimonials' => '/\[fusion_testimonials([^\]]*)\](.*?)\[\/fusion_testimonials\]/s',
            'fusion_blog' => '/\[fusion_blog([^\]]*)\]/',
            'fusion_portfolio' => '/\[fusion_portfolio([^\]]*)\]/',
            'fusion_faq' => '/\[fusion_faq([^\]]*)\](.*?)\[\/fusion_faq\]/s',
            'fusion_fusion_builder_container' => '/\[fusion_builder_container([^\]]*)\](.*?)\[\/fusion_builder_container\]/s',
            'fusion_builder_row' => '/\[fusion_builder_row([^\]]*)\](.*?)\[\/fusion_builder_row\]/s',
            'fusion_builder_column' => '/\[fusion_builder_column([^\]]*)\](.*?)\[\/fusion_builder_column\]/s',
            'fusion_checklist' => '/\[fusion_checklist([^\]]*)\](.*?)\[\/fusion_checklist\]/s',
            'fusion_content_boxes' => '/\[fusion_content_boxes([^\]]*)\](.*?)\[\/fusion_content_boxes\]/s',
            'fusion_content_box' => '/\[fusion_content_box([^\]]*)\](.*?)\[\/fusion_content_box\]/s',
            'fusion_social_links' => '/\[fusion_social_links([^\]]*)\](.*?)\[\/fusion_social_links\]/s',
            'fusion_person' => '/\[fusion_person([^\]]*)\](.*?)\[\/fusion_person\]/s',
            'fusion_slider' => '/\[fusion_slider([^\]]*)\](.*?)\[\/fusion_slider\]/s',
            'fusion_rev_slider' => '/\[fusion_rev_slider([^\]]*)\]/',
            'fusion_layerslider' => '/\[fusion_layerslider([^\]]*)\]/',
            'fusion_post_slider' => '/\[fusion_post_slider([^\]]*)\]/',
            'fusion_post_grid' => '/\[fusion_post_grid([^\]]*)\]/',
            'fusion_portfolio_masonry' => '/\[fusion_portfolio_masonry([^\]]*)\]/',
            'fusion_fusion_slider' => '/\[fusion_fusion_slider([^\]]*)\]/',
            'fusion_table' => '/\[fusion_table([^\]]*)\](.*?)\[\/fusion_table\]/s',
            'fusion_tagline' => '/\[fusion_tagline([^\]]*)\](.*?)\[\/fusion_tagline\]/s',
            'fusion_highlight' => '/\[fusion_highlight([^\]]*)\](.*?)\[\/fusion_highlight\]/s',
            'fusion_dropcap' => '/\[fusion_dropcap([^\]]*)\](.*?)\[\/fusion_dropcap\]/s',
            'fusion_woo_products' => '/\[fusion_woo_products([^\]]*)\]/',
            'fusion_woo_cart' => '/\[fusion_woo_cart([^\]]*)\]/',
            'fusion_woo_shortcodes' => '/\[fusion_woo_shortcodes([^\]]*)\]/',
            'fusion_syntax_highlighter' => '/\[fusion_syntax_highlighter([^\]]*)\](.*?)\[\/fusion_syntax_highlighter\]/s',
            'fusion_code_block' => '/\[fusion_code_block([^\]]*)\]/',
            'fusion_viewport' => '/\[fusion_viewport([^\]]*)\]/',
            'fusion_builder_container' => '/\[fusion_builder_container([^\]]*)\](.*?)\[\/fusion_builder_container\]/s',
        );
    }
    
    public function parse($content, $include_content = true) {
        $structure = array(
            'containers' => array(),
            'raw_content' => $content,
        );
        
        if (empty(trim($content))) {
            return $structure;
        }
        
        preg_match_all('/\[fusion_builder_container([^\]]*)\](.*?)\[\/fusion_builder_container\]/s', $content, $container_matches, PREG_SET_ORDER);
        
        foreach ($container_matches as $cindex => $cmatch) {
            $container = array(
                'id' => 'container_' . $cindex,
                'index' => $cindex,
                'attributes' => $this->parse_attributes($cmatch[1]),
                'rows' => array(),
            );
            
            if ($include_content) {
                $container['raw_content'] = $cmatch[2];
            }
            
            preg_match_all('/\[fusion_builder_row([^\]]*)\](.*?)\[\/fusion_builder_row\]/s', $cmatch[2], $row_matches, PREG_SET_ORDER);
            
            foreach ($row_matches as $rindex => $rmatch) {
                $row = array(
                    'id' => 'row_' . $cindex . '_' . $rindex,
                    'index' => $rindex,
                    'attributes' => $this->parse_attributes($rmatch[1]),
                    'columns' => array(),
                );
                
                if ($include_content) {
                    $row['raw_content'] = $rmatch[2];
                }
                
                preg_match_all('/\[fusion_builder_column([^\]]*)\](.*?)\[\/fusion_builder_column\]/s', $rmatch[2], $col_matches, PREG_SET_ORDER);
                
                foreach ($col_matches as $colindex => $colmatch) {
                    $column = array(
                        'id' => 'column_' . $cindex . '_' . $rindex . '_' . $colindex,
                        'index' => $colindex,
                        'attributes' => $this->parse_attributes($colmatch[1]),
                        'elements' => array(),
                        'type' => $this->get_column_type($colmatch[1]),
                    );
                    
                    if ($include_content) {
                        $column['raw_content'] = $colmatch[2];
                        $column['elements'] = $this->parse_elements($colmatch[2], $cindex, $rindex, $colindex);
                    }
                    
                    $row['columns'][] = $column;
                }
                
                $container['rows'][] = $row;
            }
            
            $structure['containers'][] = $container;
        }
        
        return $structure;
    }
    
    private function parse_attributes($attr_string) {
        $attributes = array();
        
        preg_match_all('/(\w+)=["\']([^"\']*)["\']/', $attr_string, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[2];
        }
        
        return $attributes;
    }
    
    private function get_column_type($attr_string) {
        $types = array(
            '1_1' => '1/1',
            '1_2' => '1/2',
            '2_3' => '2/3',
            '1_3' => '1/3',
            '3_5' => '3/5',
            '1_4' => '1/4',
            '3_4' => '3/4',
            '1_5' => '1/5',
            '2_5' => '2/5',
            '4_5' => '4/5',
            '1_6' => '1/6',
            '5_6' => '5/6',
        );
        
        foreach ($types as $key => $label) {
            if (strpos($attr_string, 'type="' . $key . '"') !== false) {
                return $label;
            }
        }
        
        return '1/1';
    }
    
    public function parse_elements($content, $cindex = 0, $rindex = 0, $colindex = 0) {
        $elements = array();
        $index = 0;
        
        foreach ($this->element_patterns as $type => $pattern) {
            if (strpos($type, 'fusion_builder_') === 0) {
                continue;
            }
            
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $element = array(
                    'id' => $type . '_' . $index,
                    'type' => $type,
                    'attributes' => $this->parse_attributes($match[1]),
                    'content' => isset($match[2]) ? $match[2] : '',
                    'path' => "container_{$cindex}/row_{$rindex}/column_{$colindex}/{$type}_{$index}",
                );
                
                $elements[] = $element;
                $index++;
            }
        }
        
        return $elements;
    }
    
    public function extract_all_elements($structure) {
        $elements = array();
        
        foreach ($structure['containers'] as $cindex => $container) {
            foreach ($container['rows'] as $rindex => $row) {
                foreach ($row['columns'] as $colindex => $column) {
                    foreach ($column['elements'] as $element) {
                        $element['path'] = "container_{$cindex}/row_{$rindex}/column_{$colindex}/{$element['id']}";
                        $element['container_index'] = $cindex;
                        $element['row_index'] = $rindex;
                        $element['column_index'] = $colindex;
                        $elements[] = $element;
                    }
                }
            }
        }
        
        return $elements;
    }
    
    public function generate($structure) {
        $content = '';
        
        if (!isset($structure['containers'])) {
            return $content;
        }
        
        foreach ($structure['containers'] as $container) {
            $container_attrs = $this->build_attributes($container['attributes']);
            $content .= '[fusion_builder_container' . $container_attrs . ']';
            
            if (isset($container['rows'])) {
                foreach ($container['rows'] as $row) {
                    $row_attrs = $this->build_attributes($row['attributes']);
                    $content .= '[fusion_builder_row' . $row_attrs . ']';
                    
                    if (isset($row['columns'])) {
                        foreach ($row['columns'] as $column) {
                            $col_attrs = $this->build_attributes($column['attributes']);
                            $content .= '[fusion_builder_column' . $col_attrs . ']';
                            
                            if (isset($column['elements'])) {
                                foreach ($column['elements'] as $element) {
                                    $content .= $this->generate_element($element);
                                }
                            }
                            
                            $content .= '[/fusion_builder_column]';
                        }
                    }
                    
                    $content .= '[/fusion_builder_row]';
                }
            }
            
            $content .= '[/fusion_builder_container]';
        }
        
        return $content;
    }
    
    private function build_attributes($attributes) {
        if (empty($attributes)) {
            return '';
        }
        
        $attrs = array();
        foreach ($attributes as $key => $value) {
            $attrs[] = $key . '="' . esc_attr($value) . '"';
        }
        
        return ' ' . implode(' ', $attrs);
    }
    
    private function generate_element($element) {
        $type = $element['type'];
        $attrs = $this->build_attributes($element['attributes']);
        $element_content = isset($element['content']) ? $element['content'] : '';
        
        if (!empty($element_content)) {
            return '[' . $type . $attrs . ']' . $element_content . '[/' . $type . ']';
        } else {
            return '[' . $type . $attrs . ']';
        }
    }
}
