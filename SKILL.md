---
name: wordpress-avada-builder
description: "Manage Avada Fusion Builder content in WordPress using 23 MCP abilities - full CRUD for pages, elements, containers; schema introspection; search; batch operations; validation and repair. Uses shortcode parsing with element path addressing."
license: MIT
compatibility: opencode
metadata:
  audience: developers
  workflow: wordpress-content-management
---

## What I do

I help you manage Avada Fusion Builder content in WordPress using the MCP Avada Builder Pro plugin (v3.2.0). I can:

- Create pages with Avada Builder enabled
- Parse shortcode structures and extract full element hierarchy (including inner rows/columns)
- Add, update, delete, duplicate, and move elements with media field validation
- Search and find elements by type, content, or attributes
- Batch update multiple elements in a single transactional operation
- Discover element types, parameters, defaults, and categories
- Clean up duplicate elements and restructure icon/text rows
- Validate page structure health (tag balance, duplicates, round-trip fidelity)
- Repair malformed shortcode trees (dry-run first, with backup)

## When to use me

Use this skill when you need to:
- Build or modify Avada Builder pages programmatically
- Update links, text, or attributes in specific elements
- Add, remove, duplicate, or reorder elements
- Discover what Fusion Builder element types and parameters are available
- Perform bulk content updates across multiple elements on a page
- Fix duplicate content issues or run `icon_vertical` row restructuring
- Diagnose page structure problems or repair malformed shortcodes

## Prerequisites

- WordPress site with Avada Theme (7.x) and Fusion Builder
- MCP Avada Builder Pro plugin installed and active (v3.2.0+)
- Abilities API plugin installed and active
- User must have `edit_posts` capability (read) or `edit_post` for specific pages (write)

## Available Abilities (23)

### Page Management

#### avada-pro/get-info
Get builder version, theme version, plugin version, and available element type count. No parameters required.

#### avada-pro/create-page
Create a new WordPress page with Avada Builder enabled.

**Parameters:**
- `title` (required): Page title
- `status` (optional, default: `draft`): Post status (draft, publish, pending, private)
- `template` (optional): Page template slug (e.g., `100-width.php`)
- `post_type` (optional, default: `page`): Post type

#### avada-pro/list-pages
List all pages/posts that have Avada Builder enabled, with builder status and element counts. All parameters optional.

**Parameters:**
- `post_type` (optional, default: `page`): Post type filter
- `per_page` (optional, default: `50`): Max results
- `status` (optional, default: `any`): Post status filter

### Structure & Discovery

#### avada-pro/get-page-structure
Get full page hierarchy: containers, rows, columns, and elements with attributes.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `include_content` (optional, default: `true`): Include element content in response

#### avada-pro/list-all-elements
List all elements on a page with full details and hierarchy.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `element_type` (optional): Filter by element type (e.g., `fusion_text`)

#### avada-pro/find-element
Search for elements on a page by type, content keyword, or attribute value. Returns matching elements with their paths.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `element_type` (optional): Filter by element type
- `search` (optional): Search keyword in content
- `attribute` (optional): Attribute name to match
- `attribute_value` (optional): Attribute value to match

At least one filter (`element_type`, `search`, or `attribute`) is required.

### Element CRUD

#### avada-pro/add-container
Add a new container with row and column to a page.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `container_attrs` (optional): Container attributes object
- `add_row` (optional, default: `true`): Add default row+column automatically
- `column_type` (optional, default: `1_1`): Column type

#### avada-pro/add-element
Add a new element to a specific container/row/column position. Validates element type against registry and media fields.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `element_type` (required): Element shortcode name (e.g., `fusion_text`, `fusion_button`)
- `container_index` (optional, default: `0`): Target container index
- `row_index` (optional, default: `0`): Target row index
- `column_index` (optional, default: `0`): Target column index
- `attributes` (optional): Element attributes object
- `content` (optional): Element content (HTML/text)

**Returns:** `path` and `canonical_path` (stable identity after save).

#### avada-pro/update-element
Update an existing element's attributes and/or content by path. Validates media fields.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `element_path` (required): Path format: `container_X/row_Y/column_Z/element_W`
- `attributes` (optional): Updated attributes object
- `content` (optional): Updated content

**Returns:** `path` and `canonical_path` (stable identity after save).

#### avada-pro/delete-element
Delete an element by its path.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `element_path` (required): Element path to delete

#### avada-pro/duplicate-element
Deep-clone an element at its current position.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `element_path` (required): Path of element to duplicate

#### avada-pro/move-element
Move an element from one position to another container/row/column.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `element_path` (required): Source path: `container_X/row_Y/column_Z/element_W`
- `target_container` (required): Target container index
- `target_row` (optional, default: `0`): Target row index
- `target_column` (optional, default: `0`): Target column index
- `position` (optional, default: `-1`): Insert position (-1 = append)

#### avada-pro/bulk-update
Batch update multiple elements in a single operation. Uses all-or-nothing validation: either all updates pass or none are applied.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `updates` (required): Array of updates, each with:
  - `element_path` (required): Element path
  - `attributes` (optional): Updated attributes
  - `content` (optional): Updated content

#### avada-pro/replace-content
Replace entire page content with new Avada Builder content. Validates structure schema, rejects empty/malformed content, and creates a backup before writing.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `structure` (required): Full parsed structure object (containers/rows/columns/elements)

#### avada-pro/clean-duplicates
Remove duplicate elements from a page based on content hash.

**Parameters:**
- `page_id` (required): WordPress page/post ID

#### avada-pro/restructure-layout
Restructure matching row elements into vertical columns.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `layout_type` (required): Type: `icon_vertical` (currently the only supported mode)
- `container_index` (optional, default: `0`): Target container
- `row_index` (optional): Specific row to restructure
- `column_type` (optional, default: `1_4`): Column type for new columns
- `filter_content` (optional): Keywords to match elements

### Schema Introspection

#### avada-pro/list-element-types
List all 56 registered Fusion Builder element types with their names, categories, and descriptions. No parameters required.

#### avada-pro/get-element-schema
Get the full parameter schema for a specific element type.

**Parameters:**
- `element_type` (required): Shortcode name (e.g., `fusion_button`)

#### avada-pro/get-element-defaults
Get default values for all parameters of an element type. Uses runtime `FusionSC_*` class when available.

**Parameters:**
- `element_type` (required): Shortcode name (e.g., `fusion_button`)

#### avada-pro/list-element-categories
List all element categories with counts and element type lists. No parameters required.

#### avada-pro/search-elements
Search available element types by keyword (name, description, shortcode).

**Parameters:**
- `search` (required): Search keyword
- `category` (optional): Category filter

### Validation & Repair (v3.2.0)

#### avada-pro/validate-page-structure
Validate a page's shortcode structure integrity. Returns health score, tag balance issues, duplicate detection, round-trip fidelity, and builder status.

**Parameters:**
- `page_id` (required): WordPress page/post ID

#### avada-pro/repair-shortcode-tree
Repair malformed shortcode trees. Defaults to `dry_run=true` (preview only). Removes duplicates, cleans empty containers, normalizes structure. Always creates backup before applying.

**Parameters:**
- `page_id` (required): WordPress page/post ID
- `dry_run` (optional, default: `true`): If true, preview repairs without applying. Set false to apply.

## Element Path Format

Elements are identified by unique paths:
```
container_0/row_0/column_0/fusion_text_2
```

Format: `container_{index}/row_{index}/column_{index}/{element_type}_{index}`

After write operations, a `canonical_path` is returned reflecting the actual persisted path (stable across save cycles).

## Architecture Notes

- Avada stores page layouts as **nested shortcodes** in `post_content` (not JSON like Elementor)
- The `MCP_Avada_Parser` uses recursive `parse_nested_shortcode()` for containers, rows, and columns
- Parser regex patterns use negative lookaheads to prevent prefix collisions (e.g., `fusion_image` vs `fusion_imageframe`)
- `MCP_Avada_Elements` provides a static registry of 56 element types as fallback
- Unregistered shortcodes are preserved as `__passthrough__` elements (no data loss)
- Runtime registry (`$all_fusion_builder_elements` global) provides rich param schemas but is only populated in admin/editor context
- `FusionSC_*` classes provide element defaults and are available in REST context
- All write operations use `parse(content, true)` to preserve element content during round-trips
- Element ordering uses `PREG_OFFSET_CAPTURE` to preserve document order
- Data loss guard compares shortcode tag counts before/after every write

## Common Element Types

**Layout:** `fusion_builder_container`, `fusion_builder_row`, `fusion_builder_column`
**Content:** `fusion_text`, `fusion_title`, `fusion_button`, `fusion_separator`, `fusion_checklist`
**Media:** `fusion_image`, `fusion_imageframe`, `fusion_images`, `fusion_fontawesome`, `fusion_video`, `fusion_gallery`
**Typography:** `fusion_dropcap`, `fusion_highlight`, `fusion_tooltip`

**Total: 56 element types available**

## Page Template Structure

For proper Avada Builder backend compatibility, pages must have:

1. **Container attributes** - Always use `type="flex"` and include:
   - `hundred_percent="no"`
   - `hundred_percent_height="no"`
   - `align_content="stretch"`
   - `flex_align_items="flex-start"`
   - `hide_on_mobile="small-visibility,medium-visibility,large-visibility"`
   - `status="published"`

2. **Column attributes** - Include `layout` attribute and position flags:
   - `type="1_1"` (full), `type="1_2"` (half), `type="1_3"` (third)
   - `layout="1_2"` (required)
   - `first="true"` / `first="false"`
   - `last="true"` / `last="false"`

3. **Required post meta** - Must be set for backend to work:
   - `_fusion` - Page settings (serialized array)
   - `fusion_builder_status` = "active" (NOT `_fusion_builder_status`)

**Reference:** See [AVADA_TEMPLATE_REFERENCE.md](./AVADA_TEMPLATE_REFERENCE.md) for complete working page example.

## Known Limitations

### Shortcode Attribute Generation

The MCP Avada Builder abilities generate shortcodes with minimal default attributes. For full Avada Builder backend compatibility, pages must include:

1. **Container attributes** - Must include `type="flex"` and other flexbox attributes
2. **Column attributes** - Must include `layout` attribute and `first`/`last` position flags
3. **Required post meta** - Must have proper `_fusion` and `fusion_builder_status` meta

**Version 3.2.1 Update:** The plugin now automatically:
- Adds default container attributes (`type="flex"`, `hundred_percent="no"`, etc.)
- Adds default column attributes (`layout`, `first`, `last`)
- Sets required post meta (`fusion_builder_status`, `_fusion`)

If using an older version, either:
- Use `content/update-page` with pre-formatted shortcode content
- Manually add the required attributes and post meta

## Execution Contract (Mandatory)

Use this deterministic flow for every write task:

1. **MCP adapter preflight**
   - Run `avada-pro/get-info`.
   - Confirm plugin/theme/element registry is reachable.
2. **Page integrity preflight**
   - Run `avada-pro/validate-page-structure`.
   - If health issues are reported, stop and run repair flow (`dry_run=true`) before edits.
3. **Structured read phase**
   - Run `avada-pro/get-page-structure` with `include_content=true`.
   - Identify exact target paths (`container_X/row_Y/column_Z/...`) and source-of-truth path when applicable.
4. **Structured mutation only**
   - Use `update-element` / `bulk-update` / `replace-content` with parsed structure.
   - For repeated card styling, copy only approved attributes from source-of-truth, never text/links unless explicitly requested.
5. **Post-edit verification**
   - Re-run `validate-page-structure`.
   - Re-run `get-page-structure` and verify path counts/target attributes.
   - Verify frontend at desktop/tablet/mobile for overflow, spacing, and contrast regressions.

## Strict Safety Rules

- Never edit `post_content` directly with ad-hoc string replacements.
- Never run direct DB mutations (`UPDATE wp_posts ...`) for builder changes.
- Never bypass MCP/Abilities write paths for Avada page edits.
- Never perform breakpoint-blind changes; set desktop/tablet/mobile values explicitly.
- Prefer `bulk-update` for multi-card synchronization to reduce partial-edit risk.
- Use `replace-content` only with parser-generated structure and only after successful validation.

## Reusable Playbooks

### 1) Bulk Card Standardization (Source-of-Truth)
Use when one card design must be replicated to sibling cards.

1. Locate section and card paths using `find-element` / `list-all-elements`.
2. Capture source card attributes (container/column + child element attrs).
3. Build a target path list excluding source card.
4. Apply one `bulk-update` transaction for all target cards.
5. Verify structure + responsive render.

### 2) Shadow Sync Across Cards
Use for consistent box shadow (`2,2` etc.) across a card grid.

1. Identify all card wrappers/columns in section.
2. Update only shadow attributes (`box_shadow`, position offsets, blur/spread when required).
3. Do not touch content or layout attributes.
4. Verify hover state remains unchanged when hover is specified as none.

### 3) Responsive Spacing Adjustment (Mobile-Only)
Use when title/image/button spacing fails only on mobile.

1. Update `*_small` attributes first (`margin_top_small`, `padding_*_small`, `alignment_small`).
2. Leave desktop/tablet values untouched unless explicitly requested.
3. Validate mobile stacking and text readability after change.

### 4) Design Audit Mode (No-Edit)
Use for diagnostics only.

1. Gather computed style and layout data (contrast, overflow, spacing rhythm, card consistency).
2. Produce severity-ranked findings with concrete selectors/paths.
3. Do not call any write ability in this mode.

### 5) Responsive Drift Debugger (Transferable)
Use when builder attributes look correct but frontend spacing/width still renders wrong.

1. Read page source of truth via `GET /wp-json/wp/v2/pages/{id}?context=edit`.
2. Fetch rendered frontend HTML for the same page URL.
3. Compare target section card attrs (`type`, `type_medium`, `type_small`, spacing attrs) against rendered CSS vars (`--awb-width-*`, `--awb-spacing-*`).
4. Normalize structure first (column attrs), then keep page-scoped CSS minimal and only for deterministic overrides.
5. Re-fetch frontend and verify rendered var groups are uniform for the target class.

Use this when troubleshooting:
- mixed card widths (e.g., `1_4` + `1_3`) in one grid
- large unexpected gutters despite matching shortcode attrs
- repeated edits causing malformed or duplicate style blocks

## Utility Script: Rendered Variable Verification

Use `tools/verify_avada_rendered_vars.ps1` for quick frontend checks.

Examples:

```powershell
powershell -ExecutionPolicy Bypass -File tools/verify_avada_rendered_vars.ps1 `
  -Url "https://example.com/target-page/" `
  -ClassName "all-services-card"
```

```powershell
powershell -ExecutionPolicy Bypass -File tools/verify_avada_rendered_vars.ps1 `
  -Url "https://example.com/target-page/" `
  -ClassName "service-card" `
  -OutJson "tools/rendered_vars_report.json"
```

## Corruption Prevention Safeguards

- Before large mutations, keep a backup snapshot of current page content/structure.
- For `replace-content`, reject empty or schema-invalid structures.
- After write, ensure element counts did not unexpectedly drop in target section.
- If canonical paths change after write, continue operations using returned canonical paths only.

## Example Workflows

### Modify existing content
1. `list-pages` to find the target page
2. `get-page-structure` to understand the layout
3. `find-element` to locate the specific element
4. `update-element` with new content/attributes (returns `canonical_path`)
5. Use `canonical_path` for subsequent operations

### Build a new page
1. `create-page` with a title
2. `add-container` to create the layout structure
3. `add-element` for each content element
4. Repeat for additional sections

### Diagnose and repair a page
1. `validate-page-structure` to check health score and identify issues
2. Review issues list (tag imbalance, duplicates, round-trip drift)
3. `repair-shortcode-tree` with `dry_run=true` to preview repairs
4. If repairs look correct, call again with `dry_run=false` to apply

### Bulk content updates
1. `find-element` to locate all matching elements
2. Build an updates array with new attributes/content per path
3. `bulk-update` to apply all changes at once (transactional)

## Troubleshooting

- **Element not found after save**: Use `canonical_path` from the write response, not the original path
- **Data loss detected error**: The mutation would have lost elements; review your structure or use `validate-page-structure`
- **Permission denied**: Ensure user has `edit_posts` / `edit_post` capability
- **Invalid media field**: `image_id` must reference an existing attachment; use media library IDs
- **Empty content error on replace**: Structure must have at least one container with rows/columns
- **Changes not visible**: Clear Avada/page cache and hard refresh browser
- **Schema shows 0 params**: Runtime registry not available in REST context; use `get-element-defaults` instead
- **Input validation error on GET**: Abilities with required params use POST (not GET)

## REST API Usage

Abilities are available via the WordPress Abilities API REST endpoints:

```
GET  /wp-json/wp-abilities/v1/abilities/avada-pro/{ability}/run    (readonly, no required params)
POST /wp-json/wp-abilities/v1/abilities/avada-pro/{ability}/run    (write ops + read ops with required params)
```

POST body format: `{"input": {"param_name": "value"}}`

## Integration Points

This skill integrates with:
- **Abilities API** (`wp_register_ability()`) - for registering the 23 MCP abilities
- **MCP Adapter** - for exposing abilities via MCP protocol
- **Avada Theme/Fusion Builder** - for element types and rendering
- **WordPress REST API** - for `/wp-json/wp-abilities/v1/*` endpoints

## Related Skills

- [wp-abilities-api](../wp-abilities-api/SKILL.md) - For understanding how abilities are registered and exposed
- [wp-plugin-development](../wp-plugin-development/SKILL.md) - For creating similar ability plugins
