# Clear Pop

Clean, simple popup modal system for WordPress with WPBakery support.

## Quick Start

1. **Create a Popup**
   - Go to Popups → Add New
   - Add your content using WPBakery or the editor
   - Configure settings (size, colors, close button)
   - Publish

2. **Trigger the Popup**
   - Copy the class from the trigger code box
   - Add it to any button/link in WPBakery
   - Example: `hsp-popup-trigger-456`

## Settings

- **Modal Size** - 6 presets from small (400px) to full screen (96vw × 96vh)
  - Small (400px)
  - Medium (600px)
  - Large (800px)
  - Extra Large (1000px)
  - Full Width (90vw)
  - Full Screen (96vw × 96vh)
- **Background Color & Opacity** - Overlay color with transparency control
- **Close Button Position** - Place in any corner
- **Close Button Style** - Light or dark theme
- **Close Button Border** - Optional 1px border
- **Popup Padding** - Default or edge-to-edge
- **Border Radius** - Custom radius with unit selector (px, rem, em, vw, %)

## Trigger Methods

**WPBakery (recommended):**
```
Class: hsp-popup-trigger-456
```

**Open Specific Tab:**
```
Class: hsp-popup-trigger-456 hsp-tab-2
```
Use `hsp-tab-1`, `hsp-tab-2`, `hsp-tab-3`, etc. to open popup with that tab active.

**Custom HTML:**
```html
<a href="#" class="hsp-popup-trigger" data-popup-id="456">Open</a>
```

## Features

- Class-only triggers (WPBakery compatible)
- Tab-specific popup opening
- WPBakery tab support inside popups
- ESC key to close
- Click overlay to close
- Body scroll lock
- Smooth animations
- Mobile responsive
- Customizable border radius
- Optional close button border
- Full screen modal option
- JavaScript API for programmatic control

## Documentation

See `/notes/` folder for:
- `changelog.md` - Version history
- `wpbakery-usage.md` - Detailed usage guide
- `architecture.md` - Technical documentation

## Support

Built for use with Salient theme and WPBakery Page Builder.

## Version

1.1.0
