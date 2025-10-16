# Clear Pop - Plugin Overview

## Purpose
Clean, simple popup modal system for WordPress with deep WPBakery Page Builder integration. Designed for use with the Salient theme.

## Architecture

### File Structure
```
clear-pop/
├── clear-pop.php              # Main plugin file
├── includes/
│   ├── class-post-type.php    # Post type registration & WPBakery integration
│   ├── class-metabox.php      # Admin settings interface
│   ├── class-modal-renderer.php # Frontend popup HTML output
│   └── class-assets.php       # CSS/JS asset management
├── assets/
│   ├── css/
│   │   └── modal.css          # Popup styles
│   └── js/
│       └── modal.js           # Popup functionality & tab initialization
└── notes/
    ├── changelog.md
    ├── architecture.md
    └── wpbakery-usage.md
```

### Design Patterns
- **Singleton Pattern**: All classes use singleton instances
- **OOP Structure**: Separate concerns into focused classes
- **Hook-Based**: WordPress hooks for initialization and activation
- **Asset Optimization**: Only loads CSS/JS when popups exist on page

## Post Type
- **Slug**: `hsp_popup`
- **Public**: `true` (for WPBakery Role Manager visibility)
- **Publicly Queryable**: `false` (hidden from front-end queries)
- **Supports**: Title, Editor, Revisions

## Key Features

### Modal Settings
1. **Size Options** (6 presets)
   - Small (400px)
   - Medium (600px)
   - Large (800px)
   - Extra Large (1000px)
   - Full Width (90vw)
   - Full Screen (96vw × 96vh)

2. **Customization**
   - Background color with opacity control
   - Close button position (4 corners)
   - Close button style (light/dark)
   - Optional close button border
   - Custom border radius (with unit selector)
   - Padding control (default/edge-to-edge)

### Trigger System
- **Class-based triggers**: `hsp-popup-trigger-{ID}`
- **Tab-specific opening**: `hsp-tab-1`, `hsp-tab-2`, etc.
- **Legacy support**: `data-popup-id` attribute

### WPBakery Integration
- Appears in WPBakery Role Manager
- Full editor support on popup post type
- Automatic tab initialization inside popups
- Proper Salient dynamic CSS generation
- Asset detection for nested elements

## Technical Details

### JavaScript API
```javascript
// Programmatic control
window.hspPopup.open(popupId);
window.hspPopup.close(popupId);
window.hspPopup.closeAll();

// Custom events
document.addEventListener('hspPopupOpen', function(e) {
    console.log('Popup opened:', e.detail.popup);
});
```

### CSS Classes
- `.hsp-popup-overlay` - Overlay wrapper
- `.hsp-popup-container` - Modal container
- `.hsp-popup-size-{size}` - Size variant classes
- `.hsp-popup-close` - Close button
- `.hsp-popup-content` - Content wrapper
- `.hsp-active` - Active state

### Activation Hooks
- Registers post type
- Flushes rewrite rules
- Adds `hsp_popup` to WPBakery's enabled post types
- Ensures Role Manager visibility

## Browser Support
- Modern browsers (ES6+)
- No jQuery dependency on frontend
- Uses vanilla JavaScript for performance
