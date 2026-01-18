# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Clear Pop is a WordPress plugin providing a popup modal system with WPBakery Page Builder integration, designed for the Salient theme.

## Architecture

**Singleton Pattern**: All classes use `get_instance()` singleton pattern.

**Core Classes** (in `includes/`):
- `Clear_Pop_Post_Type` - Registers `hsp_popup` custom post type
- `Clear_Pop_Metabox` - Admin settings interface and trigger configuration
- `Clear_Pop_Modal_Renderer` - Frontend HTML output in footer
- `Clear_Pop_Assets` - CSS/JS enqueuing (conditional on popup existence)
- `Clear_Pop_Cookie_Manager` - Cookie-based view tracking and frequency control
- `Clear_Pop_Trigger_Handler` - AJAX endpoints and automatic trigger logic

**Frontend Assets** (in `assets/`):
- `js/modal.js` - Modal open/close, tab initialization, close button handling
- `js/triggers.js` - Client-side trigger evaluation (time delay, scroll, exit intent)
- `css/modal.css` - Modal styles and animations

## Key Technical Details

**Post Type**: `hsp_popup` with `public => true`, `publicly_queryable => false` (visible in WPBakery Role Manager but hidden from frontend queries)

**Meta Keys** (all prefixed with `_`):
- Modal Width: `_popup_size` (small/medium/large/xlarge/fullwidth/fullscreen/custom), `_popup_width_value`, `_popup_width_unit` (px/vw/%)
- Modal Height: `_popup_height_mode` (auto/custom), `_popup_height_value`, `_popup_height_unit` (vh/px/%)
- Background: `_popup_bg_color`, `_popup_bg_opacity`
- Close Button: `_popup_close_position`, `_popup_close_style`, `_popup_close_border`, `_popup_close_button_width` (20-100px)
- Content: `_popup_content_padding`, `_popup_border_radius_value`, `_popup_border_radius_unit`
- Triggers: `_trigger_time_delay`, `_trigger_scroll_depth`, `_trigger_first_visit`, `_trigger_exit_intent`, `_trigger_logic`, `_cookie_duration`

**Cookie System**: Cookie name pattern `clear_pop_{popup_id}`, stores JSON with `last_shown`, `shown_count`, `closed_method`, `last_closed`

**AJAX Endpoints**:
- `clear_pop_close` - Records popup close (no nonce, fire-and-forget)
- `clear_pop_cookie` - Clears cookie for testing (admin only, nonce protected)

**JavaScript API**:
```javascript
window.hspPopup.open(popupId);
window.hspPopup.close(popupId);
window.hspPopup.closeAll();
// Events: 'hspPopupOpen', 'hspPopupClose'
```

**Trigger Classes**:
- `hsp-popup-trigger-{ID}` - Click trigger by post ID
- `hsp-popup-trigger-{slug}` - Click trigger by post slug (e.g., `hsp-popup-trigger-contact-form`)
- `hsp-tab-{N}` - Open with specific tab active (1-based)

**Data Attributes** (on `.hsp-popup-overlay`):
- `data-popup-id` - Post ID
- `data-popup-slug` - Post slug (for slug-based triggers)

## WPBakery Integration

The plugin modifies `wpb_js_content_types` option on activation to add `hsp_popup`. Tab initialization uses `:nth-of-type()` selectors (not `:nth-child()`) to correctly skip the `<ul>` nav element when targeting `.wpb_tab` panels.

## Salient Theme Gotchas

**Avoid these Salient CSS classes** on popup elements - they trigger Salient's JavaScript which injects unwanted inline styles:
- `container-wrap` - Injects `min-height`, `padding-top`, `padding-bottom`
- Other layout classes may have similar issues

The `nectar-global-section` class on `.hsp-popup-content` is intentional and needed for proper Salient/WPBakery element rendering.

## Development Notes

- No build process required - vanilla PHP/JS/CSS
- No test framework configured
- Checkbox meta fields require special save handling (explicit check for `isset($_POST[$field])`)
- Assets only load when published `hsp_popup` posts exist
- **Backward compatibility**: `_popup_height_mode` accepts both 'fixed' (legacy) and 'custom' (current) - renderer handles both, metabox displays 'fixed' as 'custom'

## Releases & Auto-Updates

Uses [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) v5.5 for GitHub-based updates.

**Release process:**
1. Update version in `clear-pop.php` (header comment AND `CLEAR_POP_VERSION` constant)
2. Update `Stable tag` and changelog in `readme.txt`
3. Commit and push
4. Create release zip (exclude dev files):
   ```bash
   zip -r /tmp/clear-pop.zip . -x "*.git*" -x "*.claude*" -x "*notes/*" -x "CLAUDE.md" -x "cc_phase1_tasklist.md" -x "*.DS_Store" -x "README.md"
   ```
5. Create GitHub release with tag `v{version}` and attach `clear-pop.zip` (not versioned filename)
