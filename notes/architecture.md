# Clear Pop - Architecture

## File Structure

```
clear-pop/
├── clear-pop.php              # Main plugin file
├── includes/
│   ├── class-post-type.php    # CPT registration
│   ├── class-metabox.php      # Admin settings interface
│   ├── class-modal-renderer.php # Frontend HTML output
│   └── class-assets.php       # CSS/JS enqueuing
├── assets/
│   ├── css/
│   │   └── modal.css          # Modal styles
│   └── js/
│       └── modal.js           # Modal functionality
└── notes/
    ├── changelog.md           # Version history
    ├── wpbakery-usage.md      # Usage instructions
    └── architecture.md        # This file

```

## Class Structure

### Clear_Pop_Plugin (main)
- Singleton pattern
- Loads all dependencies
- Initializes component classes

### Clear_Pop_Post_Type
- Registers `hsp_popup` custom post type
- Handles post type labels and settings
- Sets visibility and capabilities

### Clear_Pop_Metabox
- Adds settings metabox to popup editor
- Renders settings interface
- Saves popup configuration
- Displays trigger code instructions

### Clear_Pop_Modal_Renderer
- Outputs popup HTML in footer
- Applies settings to each popup
- Handles WPBakery content rendering
- Converts color/opacity to RGBA

### Clear_Pop_Assets
- Enqueues frontend CSS/JS
- Only loads when published popups exist
- Enqueues admin color picker
- Conditional loading for performance

## Settings Saved as Post Meta

- `_popup_size` - Modal width (small|medium|large|xlarge|fullwidth)
- `_popup_bg_color` - Overlay background hex color
- `_popup_bg_opacity` - Overlay opacity (0-1)
- `_popup_close_position` - Close button corner position
- `_popup_close_style` - Close button color theme (light|dark)

## Frontend JavaScript API

```javascript
// Open popup by ID
window.hspPopup.open(456);

// Close specific popup
window.hspPopup.close(456);

// Close all open popups
window.hspPopup.closeAll();
```

## Custom Events

```javascript
// Listen for popup open
document.addEventListener('hspPopupOpen', function(e) {
    console.log('Popup opened:', e.detail.popup);
});

// Listen for popup close
document.addEventListener('hspPopupClose', function(e) {
    console.log('Popup closed:', e.detail.popup);
});
```

## Trigger Methods

### Method 1: Class-only (Recommended)
```html
<a href="#" class="hsp-popup-trigger-456">Open</a>
```

### Method 2: Legacy data-attribute
```html
<a href="#" class="hsp-popup-trigger" data-popup-id="456">Open</a>
```

Both methods work. Method 1 is preferred for WPBakery compatibility.

## Design Principles

1. **Clean OOP** - Singleton pattern, single responsibility
2. **Performance** - Assets only load when needed
3. **Compatibility** - Works with WPBakery and Salient
4. **Flexibility** - Multiple trigger methods
5. **Accessibility** - Proper ARIA labels, ESC key support
6. **Mobile-first** - Responsive at all breakpoints
