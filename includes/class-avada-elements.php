<?php
/**
 * Avada Fusion Builder Elements Registry
 * Complete list of all Fusion Builder elements with their categories and properties
 */

class MCP_Avada_Elements {
    
    private $elements = array();
    
    public function __construct() {
        $this->init_elements();
    }
    
    private function init_elements() {
        $this->elements = array(
            array(
                'type' => 'fusion_builder_container',
                'name' => 'Container',
                'category' => 'layout',
                'description' => 'Main wrapper for page sections',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_builder_row',
                'name' => 'Row',
                'category' => 'layout',
                'description' => 'Row for organizing columns',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_builder_column',
                'name' => 'Column',
                'category' => 'layout',
                'description' => 'Column for placing elements',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_text',
                'name' => 'Text Block',
                'category' => 'content',
                'description' => 'Add formatted text content with HTML support',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_title',
                'name' => 'Heading',
                'category' => 'content',
                'description' => 'Add styled headings (H1-H6)',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_button',
                'name' => 'Button',
                'category' => 'content',
                'description' => 'Add clickable buttons with links',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_separator',
                'name' => 'Separator',
                'category' => 'content',
                'description' => 'Add visual dividers and spacers',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_image',
                'name' => 'Image',
                'category' => 'media',
                'description' => 'Display images with optional lightbox',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_gallery',
                'name' => 'Image Gallery',
                'category' => 'media',
                'description' => 'Create image galleries and carousels',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_video',
                'name' => 'Video',
                'category' => 'media',
                'description' => 'Embed videos from URL or self-hosted',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_audio',
                'name' => 'Audio',
                'category' => 'media',
                'description' => 'Embed audio files',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_fontawesome',
                'name' => 'Icon',
                'category' => 'media',
                'description' => 'Add Font Awesome icons',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_google_fonts',
                'name' => 'Google Fonts',
                'category' => 'typography',
                'description' => 'Import Google Fonts',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_code',
                'name' => 'Code Block',
                'category' => 'content',
                'description' => 'Display code with syntax highlighting',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_syntax_highlighter',
                'name' => 'Syntax Highlighter',
                'category' => 'content',
                'description' => 'Display code with advanced highlighting',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_alert',
                'name' => 'Alert Box',
                'category' => 'content',
                'description' => 'Create notification alerts',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_accordion',
                'name' => 'Accordion',
                'category' => 'layout',
                'description' => 'Collapsible content sections',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_toggle',
                'name' => 'Toggle',
                'category' => 'layout',
                'description' => 'Single toggle element',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_tabs',
                'name' => 'Tabs',
                'category' => 'layout',
                'description' => 'Tabbed content interface',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_tab',
                'name' => 'Tab',
                'category' => 'layout',
                'description' => 'Individual tab content',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_tooltip',
                'name' => 'Tooltip',
                'category' => 'layout',
                'description' => 'Add hover tooltips to text',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_popover',
                'name' => 'Popover',
                'category' => 'layout',
                'description' => 'Click-triggered popover content',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_modal',
                'name' => 'Modal',
                'category' => 'layout',
                'description' => 'Create popup modals',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_modal_text_link',
                'name' => 'Modal Link',
                'category' => 'layout',
                'description' => 'Trigger modal with link',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_pricing_table',
                'name' => 'Pricing Table',
                'category' => 'content',
                'description' => 'Create pricing columns',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_pricing_column',
                'name' => 'Pricing Column',
                'category' => 'content',
                'description' => 'Individual pricing tier',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_pricing_price',
                'name' => 'Pricing Price',
                'category' => 'content',
                'description' => 'Price display for pricing table',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_pricing_button',
                'name' => 'Pricing Button',
                'category' => 'content',
                'description' => 'CTA button for pricing table',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_counter',
                'name' => 'Counter',
                'category' => 'content',
                'description' => 'Animated number counters',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_counters',
                'name' => 'Counters Circle',
                'category' => 'content',
                'description' => 'Multiple animated counters',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_progress',
                'name' => 'Progress Bar',
                'category' => 'content',
                'description' => 'Animated progress bars',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_testimonial',
                'name' => 'Testimonial',
                'category' => 'content',
                'description' => 'Display client testimonials',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_testimonials',
                'name' => 'Testimonials',
                'category' => 'content',
                'description' => 'Testimonial slider',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_blog',
                'name' => 'Blog',
                'category' => 'content',
                'description' => 'Display blog posts',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_portfolio',
                'name' => 'Portfolio',
                'category' => 'content',
                'description' => 'Display portfolio items',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_portfolio_masonry',
                'name' => 'Portfolio Masonry',
                'category' => 'content',
                'description' => 'Masonry layout portfolio',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_faq',
                'name' => 'FAQ',
                'category' => 'content',
                'description' => 'Frequently asked questions',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_checklist',
                'name' => 'Checklist',
                'category' => 'content',
                'description' => 'Styled list with icons',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_content_boxes',
                'name' => 'Content Boxes',
                'category' => 'content',
                'description' => 'Grid of content boxes',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_content_box',
                'name' => 'Content Box',
                'category' => 'content',
                'description' => 'Individual content box',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_social_links',
                'name' => 'Social Links',
                'category' => 'social',
                'description' => 'Social media icon links',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_person',
                'name' => 'Person',
                'category' => 'content',
                'description' => 'Team member profile',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_slider',
                'name' => 'Slider',
                'category' => 'media',
                'description' => 'Image/content slider',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_post_slider',
                'name' => 'Post Slider',
                'category' => 'media',
                'description' => 'Slider from posts',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_post_grid',
                'name' => 'Post Grid',
                'category' => 'content',
                'description' => 'Grid of post excerpts',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_rev_slider',
                'name' => 'Revolution Slider',
                'category' => 'media',
                'description' => 'Revolution Slider integration',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_layerslider',
                'name' => 'LayerSlider',
                'category' => 'media',
                'description' => 'LayerSlider integration',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_fusion_slider',
                'name' => 'Fusion Slider',
                'category' => 'media',
                'description' => 'Avada built-in slider',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_table',
                'name' => 'Table',
                'category' => 'content',
                'description' => 'Styled data tables',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_tagline',
                'name' => 'Tagline',
                'category' => 'content',
                'description' => 'Callout tagline box',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_highlight',
                'name' => 'Highlight',
                'category' => 'typography',
                'description' => 'Highlight text inline',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_dropcap',
                'name' => 'Dropcap',
                'category' => 'typography',
                'description' => 'Large first letter',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_map',
                'name' => 'Google Map',
                'category' => 'media',
                'description' => 'Embed Google Maps',
                'has_content' => true,
            ),
            array(
                'type' => 'fusion_viewport',
                'name' => 'Visibility',
                'category' => 'layout',
                'description' => 'Control element visibility',
                'has_content' => false,
            ),
            array(
                'type' => 'fusion_code_block',
                'name' => 'Code Block',
                'category' => 'content',
                'description' => 'Code block wrapper',
                'has_content' => false,
            ),
        );
    }
    
    public function get_all() {
        return $this->elements;
    }
    
    public function get_by_category($category) {
        return array_filter($this->elements, function($e) use ($category) {
            return $e['category'] === $category;
        });
    }
    
    public function get_by_type($type) {
        foreach ($this->elements as $element) {
            if ($element['type'] === $type) {
                return $element;
            }
        }
        return null;
    }
    
    public function get_categories() {
        $categories = array();
        foreach ($this->elements as $element) {
            if (!isset($categories[$element['category']])) {
                $categories[$element['category']] = array(
                    'name' => ucfirst($element['category']),
                    'count' => 0,
                );
            }
            $categories[$element['category']]['count']++;
        }
        return $categories;
    }
}
