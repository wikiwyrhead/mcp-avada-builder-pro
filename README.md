# MCP Avada Builder Pro

Advanced MCP integration for Avada Fusion Builder with full shortcode parsing and element management.

## Version: 2.0.0

---

## Overview

MCP Avada Builder Pro provides comprehensive abilities for controlling Avada Fusion Builder programmatically. Unlike the basic version, this plugin features:

- **Advanced Shortcode Parser** - Handles complex nested structures
- **Full Hierarchy Support** - Container → Row → Column → Element
- **55 Element Types** - Complete registry of Fusion Builder elements
- **Path-based Element Access** - Unique paths like `container_0/row_0/column_0/element_1`
- **Duplicate Detection & Cleanup** - Built-in tools to clean up page duplicates

---

## Abilities (9 Total)

### 1. **avada-pro/get-info**
Get detailed Avada Builder information including version numbers and element count.

**Returns:**
- Builder version
- Theme version
- Plugin version
- Total element types available

---

### 2. **avada-pro/get-page-structure**
Get complete page structure with containers, rows, columns, and elements.

**Parameters:**
- `page_id` (required) - Page ID
- `include_content` (optional) - Include element content in response

**Returns:**
- Container count
- Full hierarchy with paths
- Element details

---

### 3. **avada-pro/list-all-elements**
List all elements on a page with their full details and hierarchy.

**Parameters:**
- `page_id` (required) - Page ID
- `element_type` (optional) - Filter by element type

**Returns:**
- Array of elements with paths and locations

---

### 4. **avada-pro/add-container**
Add a new container with optional row and column.

**Parameters:**
- `page_id` (required) - Page ID
- `container_attrs` (optional) - Container attributes
- `add_row` (optional, default: true) - Add a row automatically
- `column_type` (optional, default: '1_1') - Column layout type

---

### 5. **avada-pro/add-element**
Add a new element to a specific container/row/column position.

**Parameters:**
- `page_id` (required) - Page ID
- `element_type` (required) - Type of element to add
- `container_index` (optional, default: 0) - Target container
- `row_index` (optional, default: 0) - Target row
- `column_index` (optional, default: 0) - Target column
- `attributes` (optional) - Element attributes
- `content` (optional) - Element content

**Returns:**
- Element details with generated ID
- Full path to the element

---

### 6. **avada-pro/update-element**
Update an element by its unique path.

**Parameters:**
- `page_id` (required) - Page ID
- `element_path` (required) - Path like `container_0/row_0/column_0/element_1`
- `attributes` (optional) - Updated attributes
- `content` (optional) - Updated content

**Error Messages:**
- Returns available element paths if element not found

---

### 7. **avada-pro/delete-element**
Delete an element by its path.

**Parameters:**
- `page_id` (required) - Page ID
- `element_path` (required) - Element path

**Error Messages:**
- Returns available element paths if element not found

---

### 8. **avada-pro/replace-content**
Replace entire page content with new structure.

**Parameters:**
- `page_id` (required) - Page ID
- `structure` (required) - Complete new structure

**Returns:**
- Preview URL
- Update confirmation

---

### 9. **avada-pro/list-element-types**
Get all 55 available Avada element types with their categories and schemas.

**Returns:**
- Element name
- Category (layout, content, media, typography, social)
- Description
- Has_content flag

---

### 10. **avada-pro/clean-duplicates**
Remove duplicate elements from a page.

**Parameters:**
- `page_id` (required) - Page ID

**Returns:**
- Number of duplicates removed
- Remaining element count

---

## Supported Element Types (55 Total)

### Layout Elements
- `fusion_builder_container` - Main wrapper
- `fusion_builder_row` - Row organizer
- `fusion_builder_column` - Column layout
- `fusion_accordion` - Collapsible sections
- `fusion_toggle` - Single toggle
- `fusion_tabs` - Tabbed interface
- `fusion_tab` - Individual tab
- `fusion_tooltip` - Hover tooltips
- `fusion_popover` - Click popovers
- `fusion_modal` - Popup modals
- `fusion_viewport` - Visibility control

### Content Elements
- `fusion_text` - Text blocks
- `fusion_title` - Headings (H1-H6)
- `fusion_button` - Buttons with links
- `fusion_separator` - Visual dividers
- `fusion_alert` - Notification boxes
- `fusion_code` - Code blocks
- `fusion_syntax_highlighter` - Advanced code
- `fusion_table` - Data tables
- `fusion_tagline` - Callout boxes
- `fusion_checklist` - Styled lists
- `fusion_content_boxes` - Content grids
- `fusion_content_box` - Individual box
- `fusion_blog` - Blog posts
- `fusion_portfolio` - Portfolio items
- `fusion_portfolio_masonry` - Masonry layout
- `fusion_faq` - FAQ sections

### Media Elements
- `fusion_image` - Images
- `fusion_gallery` - Image galleries
- `fusion_video` - Video embeds
- `fusion_audio` - Audio players
- `fusion_map` - Google Maps
- `fusion_fontawesome` - Icons
- `fusion_slider` - Content sliders
- `fusion_post_slider` - Post sliders
- `fusion_rev_slider` - Revolution Slider
- `fusion_layerslider` - LayerSlider
- `fusion_fusion_slider` - Fusion Slider
- `fusion_post_grid` - Post grids

### Typography Elements
- `fusion_google_fonts` - Font imports
- `fusion_highlight` - Text highlighting
- `fusion_dropcap` - Large first letters

### Pricing Elements
- `fusion_pricing_table` - Pricing columns
- `fusion_pricing_column` - Single tier
- `fusion_pricing_price` - Price display
- `fusion_pricing_button` - CTA buttons

### Statistics Elements
- `fusion_counter` - Animated counters
- `fusion_counters` - Multiple counters
- `fusion_progress` - Progress bars
- `fusion_testimonial` - Testimonials
- `fusion_testimonials` - Testimonial slider

### Social Elements
- `fusion_social_links` - Social icons
- `fusion_person` - Team profiles

---

## Column Types

The plugin supports all Avada column layouts:

- `1_1` - Full width (1/1)
- `1_2` - Half width (1/2)
- `2_3` - Two-thirds (2/3)
- `1_3` - One-third (1/3)
- `3_5` - Three-fifths (3/5)
- `1_4` - One-fourth (1/4)
- `3_4` - Three-fourths (3/4)
- `1_5` - One-fifth (1/5)
- `2_5` - Two-fifths (2/5)
- `4_5` - Four-fifths (4/5)
- `1_6` - One-sixth (1/6)
- `5_6` - Five-sixths (5/6)

---

## Element Paths

Each element has a unique path in the format:
```
container_{index}/row_{index}/column_{index}/{element_type}_{index}
```

Example: `container_0/row_0/column_0/fusion_text_2`

---

## Requirements

- WordPress 5.8+
- Avada Theme 7.0+
- Avada Builder Plugin 3.0+
- Abilities API Plugin

---

## Installation

1. Upload plugin to `wp-content/plugins/`
2. Activate through WordPress admin
3. Abilities will be automatically registered

---

## Changelog

### 2.0.0
- Initial Pro release
- Advanced shortcode parser with nested support
- 55 element types
- Path-based element identification
- Container/row/column management
- Duplicate cleanup tool
- Better error messages

---

## Author

**arnelG**
- GitHub: @wikiwyrhead
- Website: https://github.com/wikiwyrhead/mcp-avada-builder

---

## License

GPL-2.0-or-later
