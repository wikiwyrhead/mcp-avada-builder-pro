---
name: wordpress-avada-builder
description: Manage Avada Fusion Builder content in WordPress using MCP abilities - parse shortcodes, update elements, manage containers/rows/columns
license: MIT
compatibility: opencode
metadata:
  audience: developers
  workflow: wordpress-content-management
---

## What I do

I help you manage Avada Fusion Builder content in WordPress using the MCP Avada Builder Pro plugin. I can:

- Parse Avada Builder shortcodes and extract element structure
- List all elements on a page with their hierarchy (container → row → column → element)
- Update element content and attributes by path
- Add new containers, rows, columns, and elements
- Delete elements by their unique path
- Clean up duplicate elements
- Replace entire page content with new structure

## When to use me

Use this skill when you need to:
- Modify Avada Builder page content programmatically
- Update links, text, or attributes in specific elements
- Add or remove elements from a page
- Fix duplicate content issues
- Restructure page layouts
- Bulk update content across multiple elements

## Prerequisites

- WordPress site with Avada Theme and Fusion Builder
- MCP Avada Builder Pro plugin installed and active
- Abilities API plugin installed
- User must have `edit_posts` capability

## Available Abilities

### avada-pro/get-info
Get builder version, theme version, and available element types.

### avada-pro/get-page-structure
Get full page hierarchy including containers, rows, columns, and elements.

**Parameters:**
- `page_id` (required): The WordPress page ID
- `include_content` (optional): Include element content in response

### avada-pro/list-all-elements
List all elements on a page with their paths and details.

**Parameters:**
- `page_id` (required): The WordPress page ID
- `element_type` (optional): Filter by element type (e.g., "fusion_text", "fusion_button")

### avada-pro/add-element
Add a new element to a specific position.

**Parameters:**
- `page_id` (required): The WordPress page ID
- `element_type` (required): Type of element (e.g., "fusion_text", "fusion_button")
- `container_index` (optional): Target container index (default: 0)
- `row_index` (optional): Target row index (default: 0)
- `column_index` (optional): Target column index (default: 0)
- `attributes` (optional): Element attributes object
- `content` (optional): Element content (HTML/text)

### avada-pro/update-element
Update an existing element by its path.

**Parameters:**
- `page_id` (required): The WordPress page ID
- `element_path` (required): Path format: `container_X/row_Y/column_Z/element_W`
- `attributes` (optional): Updated attributes object
- `content` (optional): Updated content

### avada-pro/delete-element
Delete an element by its path.

**Parameters:**
- `page_id` (required): The WordPress page ID
- `element_path` (required): Element path to delete

### avada-pro/clean-duplicates
Remove duplicate elements from a page based on content hash.

**Parameters:**
- `page_id` (required): The WordPress page ID

## Element Path Format

Elements are identified by unique paths:
```
container_0/row_0/column_0/fusion_text_2
```

Format: `container_{index}/row_{index}/column_{index}/{element_type}_{index}`

## Common Element Types

**Layout:**
- `fusion_builder_container` - Main wrapper
- `fusion_builder_row` - Row organizer
- `fusion_builder_column` - Column layout

**Content:**
- `fusion_text` - Text blocks
- `fusion_title` - Headings
- `fusion_button` - Buttons
- `fusion_separator` - Dividers

**Media:**
- `fusion_image` - Images
- `fusion_fontawesome` - Icons
- `fusion_video` - Videos

**Total: 55 element types available**

## Example Workflow

1. Get page structure to understand the layout
2. List elements to find the target element
3. Note the element path (e.g., `container_2/row_0/column_1/fusion_text_0`)
4. Update the element with new content/attributes
5. Verify changes on the frontend

## Troubleshooting

- **Element not found**: Use `list-all-elements` to see available paths
- **Permission denied**: Ensure user has `edit_posts` capability
- **Changes not visible**: Clear cache and hard refresh browser
- **Parser errors**: Check for malformed shortcodes in raw content

## Best Practices

- Always verify element paths before updating
- Test changes on a staging environment first
- Keep backups before bulk operations
- Use `clean-duplicates` periodically to maintain clean content
- When adding elements, specify exact container/row/column indices

## Related Skills

- wordpress-content-management
- wordpress-database-queries
- wordpress-seo-rankmath
