# MCP Avada Builder Pro

Advanced MCP integration for Avada Fusion Builder with full shortcode parsing and element management.

## Version: 3.2.0

---

## Overview

MCP Avada Builder Pro provides comprehensive abilities for controlling Avada Fusion Builder programmatically via the WordPress Abilities API and MCP protocol. Features:

- **Recursive Shortcode Parser** - Handles deeply nested structures including inner rows/columns
- **Full Hierarchy Support** - Container > Row > Column > Element (including inner rows/columns)
- **56 Element Types** - Complete registry of Fusion Builder elements including `fusion_imageframe`
- **Passthrough Preservation** - Unregistered element types preserved as raw shortcode (no data loss)
- **Path-based Element Access** - Unique paths like `container_0/row_0/column_0/fusion_text_1`
- **Media Field Validation** - Strict validation for image_id, image URLs, gallery IDs
- **Structural Validation** - replace-content validates structure schema before writing
- **Write-path ID Stability** - Canonical paths returned after every write operation
- **Data Loss Guard** - Dry-run validation on all mutations prevents silent element loss
- **Audit Logging** - All mutations logged to post meta with timestamp/user/action
- **Page Structure Health Check** - Validate tag balance, duplicates, round-trip fidelity
- **Shortcode Tree Repair** - Dry-run-first repair for malformed shortcode trees

---

## Abilities (23 Total)

### Core Page + Element Management
- `avada-pro/get-info` - Get builder/theme/plugin versions and element count.
- `avada-pro/get-page-structure` - Get full parsed structure for a page.
- `avada-pro/list-all-elements` - List all page elements (optional type filter).
- `avada-pro/add-container` - Add a container (optionally with row/column).
- `avada-pro/add-element` - Add an element to container/row/column coordinates.
- `avada-pro/update-element` - Update element content/attributes by path.
- `avada-pro/delete-element` - Delete element by path.
- `avada-pro/replace-content` - Replace whole page content from structure object (with structural validation and backup).
- `avada-pro/list-element-types` - List all static element types (56).
- `avada-pro/clean-duplicates` - Remove duplicate elements in a page.

### Layout Operations
- `avada-pro/restructure-layout` - Restructure a target row based on matching content.
- `avada-pro/duplicate-element` - Clone element in place.
- `avada-pro/move-element` - Move element across container/row/column.
- `avada-pro/find-element` - Search page elements by type/content/attribute.
- `avada-pro/bulk-update` - Update multiple elements in one save (transactional, all-or-nothing).

### Page Lifecycle
- `avada-pro/create-page` - Create draft/published page/post with Avada enabled.
- `avada-pro/list-pages` - List posts/pages where Avada Builder is active.

### Element Schema Introspection
- `avada-pro/get-element-schema` - Get runtime/static parameter schema for a shortcode.
- `avada-pro/get-element-defaults` - Get runtime defaults from FusionSC/runtime params.
- `avada-pro/list-element-categories` - List categories + per-category elements.
- `avada-pro/search-elements` - Search available element types by keyword/category.

### Validation & Repair (v3.2.0)
- `avada-pro/validate-page-structure` - Health check: tag balance, duplicates, round-trip fidelity, builder status. Returns health score.
- `avada-pro/repair-shortcode-tree` - Repair malformed shortcode trees (dry-run by default). Removes duplicates, cleans empty containers, creates backup.

---

## Supported Element Types (56 Total)

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
- `fusion_modal_text_link` - Modal trigger link
- `fusion_viewport` - Visibility control

### Content Elements
- `fusion_text` - Text blocks
- `fusion_title` - Headings (H1-H6)
- `fusion_button` - Buttons with links
- `fusion_separator` - Visual dividers
- `fusion_alert` - Notification boxes
- `fusion_code` - Code blocks
- `fusion_code_block` - Code block wrapper
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
- `fusion_counter` - Animated counters
- `fusion_counters` - Multiple counters
- `fusion_progress` - Progress bars
- `fusion_testimonial` - Testimonials
- `fusion_testimonials` - Testimonial slider
- `fusion_pricing_table` - Pricing columns
- `fusion_pricing_column` - Single tier
- `fusion_pricing_price` - Price display
- `fusion_pricing_button` - CTA buttons
- `fusion_person` - Team profiles

### Media Elements
- `fusion_image` - Images
- `fusion_imageframe` - Image frames with styling and effects
- `fusion_images` - Image carousel
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

### Social Elements
- `fusion_social_links` - Social icons

---

## Column Types

All Avada column layouts supported:
`1_1`, `1_2`, `2_3`, `1_3`, `3_5`, `1_4`, `3_4`, `1_5`, `2_5`, `4_5`, `1_6`, `5_6`

---

## Element Paths

Each element has a unique path:
```
container_{index}/row_{index}/column_{index}/{element_type}_{index}
```
Example: `container_0/row_0/column_0/fusion_text_2`

After write operations, a `canonical_path` is returned that reflects the actual persisted path (stable across save cycles).

---

## Safety Features

- **Data Loss Guard** - Every mutation validates shortcode tag counts before/after write. Blocks operations that would lose elements.
- **Structural Validation** - `replace-content` validates container/row/column hierarchy before accepting.
- **Media Validation** - `image_id` verified against media library, URLs validated, gallery IDs checked.
- **Transactional Bulk Updates** - All-or-nothing validation: either all updates pass or none are applied.
- **Automatic Backups** - `replace-content` and `repair-shortcode-tree` store backups in post meta before destructive operations.
- **Audit Logging** - All mutations logged with timestamp, user, ability, and action description (last 100 events per post).
- **Passthrough Elements** - Unregistered shortcodes are preserved as raw text during parse/generate cycles.

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

### 3.2.0
- **Parser fixes**: Fixed all prefix-collision regex patterns (fusion_image/imageframe/images, tab/tabs, counter/counters, content_box/content_boxes, etc.)
- **Parser fixes**: Container parser now uses recursive `parse_nested_shortcode` instead of non-greedy regex
- **Media validation**: Added strict validation for image_id, image URLs, gallery image_ids in add-element and update-element
- **Write-path stability**: All write operations now return `canonical_path` from re-read after save
- **replace-content hardened**: Structural schema validation, empty content rejection, automatic backup before replace
- **New ability**: `validate-page-structure` - Health check with tag balance, duplicates, round-trip fidelity
- **New ability**: `repair-shortcode-tree` - Dry-run-first repair with duplicate removal and empty container cleanup
- **Registry**: Added `fusion_imageframe` to static elements registry (56 types total)
- **Version**: Bumped to 3.2.0 (23 abilities total)

### 3.1.2
- Hardened permission callbacks with input validation on all 12 post-specific abilities
- Added element type and attribute validation helpers
- Transactional safety for bulk-update (all-or-nothing validation)
- Audit logging system for all mutation operations

### 3.1.0
- Added schema introspection abilities: `get-element-schema`, `get-element-defaults`, `list-element-categories`, `search-elements`

### 3.0.0
- Added advanced editing abilities: `create-page`, `list-pages`, `duplicate-element`, `move-element`, `find-element`, `bulk-update`, `restructure-layout`

### 2.0.0
- Initial Pro release with parser, hierarchy management, and core CRUD abilities.

---

## Author

**arnelG**
- GitHub: @wikiwyrhead
