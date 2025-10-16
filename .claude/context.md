# Clear Pop - Current Implementation Context

## Version
1.1.0 (Released October 16, 2025)

## Recent Changes (v1.1.0)

### New Features Added
1. **Full Screen Modal Size**
   - 96vw × 96vh dimensions
   - CSS class: `.hsp-popup-size-fullscreen`
   - File: `assets/css/modal.css`

2. **Close Button Border Option**
   - Checkbox in metabox settings
   - Meta key: `_popup_close_border`
   - CSS class: `.hsp-popup-close-border`
   - Adds 1px solid border with currentColor

3. **Custom Border Radius**
   - Value + unit selector (px, rem, em, vw, %)
   - Meta keys: `_popup_border_radius_value`, `_popup_border_radius_unit`
   - Applied as inline style on container

4. **Tab-Specific Popup Opening**
   - Add `hsp-tab-{N}` class to trigger button
   - Opens popup with specified tab active
   - 1-based indexing (hsp-tab-1 = first tab)
   - Implemented in `modal.js`

5. **WPBakery Tab Support**
   - `initWPBakeryTabs()` function in modal.js
   - Automatically shows first tab content on popup open
   - Handles Salient theme tab structure
   - Uses `:nth-of-type()` for accurate tab targeting

6. **WPBakery Role Manager Integration**
   - Post type `public` set to `true`
   - `publicly_queryable` remains `false`
   - Activation hook updates `wpb_js_content_types` option
   - Plugin now visible in WPBakery Settings → Role Manager

## Key Implementation Details

### Tab Initialization Logic
When popup opens:
1. Find all `.tabbed` containers
2. Hide all `.wpb_tab` panels
3. Parse tab index from trigger class (or default to 1)
4. Show selected tab panel with proper CSS
5. Update `.active-tab` classes on navigation
6. Trigger window resize for proper layout

### WPBakery Integration Strategy
- Uses `vc_before_init` hook for early integration
- Calls `vc_set_default_editor_post_types()` to add support
- Adds `vc_check_post_type_validation` filter
- Activation hook writes to WPBakery's database option

### Metabox Fields
All stored with `_` prefix:
- `_popup_size` - Size preset key
- `_popup_bg_color` - Hex color
- `_popup_bg_opacity` - 0-1 float
- `_popup_close_position` - Position key
- `_popup_close_style` - light|dark
- `_popup_close_border` - 1|empty string
- `_popup_content_padding` - default|none
- `_popup_border_radius_value` - Number
- `_popup_border_radius_unit` - px|rem|em|vw|%

## Known Behaviors

### Tab Structure Requirements
The tab initialization expects this DOM structure:
```html
<div class="wpb_wrapper tabbed">
    <ul class="wpb_tabs_nav">...</ul>
    <div class="wpb_tab">...</div>  <!-- Tab panel 1 -->
    <div class="wpb_tab">...</div>  <!-- Tab panel 2 -->
</div>
```

### CSS Selector Strategy
- Uses `.wpb_tab:nth-of-type(N)` to target tab panels
- This skips the `<ul>` element in child counting
- `:nth-child()` would incorrectly include the nav in count

### Asset Loading
- Checks for published popups before enqueuing
- CSS/JS only load when `hsp_popup` posts exist
- Salient dynamic CSS generated on `wp_head` hook
- NectarElAssets integration for proper element loading

## Development Notes

### Why `public => true`?
WPBakery's Role Manager only shows post types where:
```php
get_post_types( [ 'public' => true ] )
```
By setting `public => true` while keeping `publicly_queryable => false`:
- ✅ Visible in WPBakery Role Manager
- ✅ WPBakery editor works
- ✅ Shows in admin
- ❌ Hidden from front-end queries/archives

### Checkbox Handling
Checkboxes in metabox save function require special handling:
```php
if ('popup_close_border' === $field) {
    $value = isset($_POST[$field]) ? '1' : '';
    update_post_meta($post_id, '_' . $field, $value);
    continue;
}
```
Standard field loop skips if `$_POST` not set, so checkboxes need separate logic.

## File Locations

### Core Files
- Main: `/clear-pop.php`
- Post Type: `/includes/class-post-type.php`
- Metabox: `/includes/class-metabox.php`
- Renderer: `/includes/class-modal-renderer.php`
- Assets: `/includes/class-assets.php`

### Frontend Assets
- CSS: `/assets/css/modal.css`
- JS: `/assets/js/modal.js`

### Documentation
- Changelog: `/notes/changelog.md`
- Architecture: `/notes/architecture.md`
- Usage: `/notes/wpbakery-usage.md`
- Claude Context: `/.claude/` (this directory)
