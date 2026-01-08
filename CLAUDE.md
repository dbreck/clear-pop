# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Clear Pop is a WordPress plugin providing a popup modal system with WPBakery Page Builder integration, designed for the Salient theme. Version 1.2.0.

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
- Modal: `_popup_size`, `_popup_height_mode` (auto/fixed), `_popup_height_value`, `_popup_height_unit` (vh/px/%), `_popup_bg_color`, `_popup_bg_opacity`, `_popup_close_position`, `_popup_close_style`, `_popup_close_border`, `_popup_content_padding`, `_popup_border_radius_value`, `_popup_border_radius_unit`
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
- `hsp-popup-trigger-{ID}` - Click trigger
- `hsp-tab-{N}` - Open with specific tab active (1-based)

## WPBakery Integration

The plugin modifies `wpb_js_content_types` option on activation to add `hsp_popup`. Tab initialization uses `:nth-of-type()` selectors (not `:nth-child()`) to correctly skip the `<ul>` nav element when targeting `.wpb_tab` panels.

## Development Notes

- No build process required - vanilla PHP/JS/CSS
- No test framework configured
- Checkbox meta fields require special save handling (explicit check for `isset($_POST[$field])`)
- Assets only load when published `hsp_popup` posts exist
