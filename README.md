# Clear Pop

Clean, simple popup modal system for WordPress with WPBakery support.

## Quick Start

1. **Create a Popup**
   - Go to Popups → Add New
   - Add your content using WPBakery or the editor
   - Configure settings (size, colors, close button)
   - Publish

2. **Trigger the Popup**
   - **Manual (click to open):** Copy the class from the trigger code box and add to any button/link
   - **Automatic:** Configure display triggers (time delay, scroll depth, first visit, exit intent)
   - Or use both methods together!

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

### Manual Triggers (Click to Open)

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

### Automatic Triggers (Display on Conditions)

Configure in the **Display Triggers** metabox:

**Time Delay**
- Show popup after X seconds on page (0-120 seconds)
- Example: Welcome message after 3 seconds

**Scroll Depth**
- Show popup after scrolling X% down page (0-100%)
- Example: Newsletter signup after 50% scroll

**First Visit**
- Show only on user's first visit to the site
- Uses long-term cookie detection
- Ideal for one-time announcements

**Exit Intent**
- Show when cursor moves toward browser top (desktop only)
- Great for exit offers and retention

**Trigger Logic**
- **ANY** - Show popup when first trigger fires
- **ALL** - Wait for all enabled triggers before showing

**Cookie Duration**
- Control how often popup shows after user closes it:
  - Never (10 year cookie - one time only)
  - Same session (show again on next browser session)
  - After 1 hour, 24 hours, 7 days, or 30 days

**Examples:**

*Newsletter signup on scroll:*
- Enable scroll depth: 50%
- Cookie duration: After 7 days
- Logic: ANY (single trigger)

*Exit offer with delay:*
- Enable time delay: 5 seconds
- Enable exit intent
- Logic: ALL (user must stay 5 seconds AND move cursor to exit)
- Cookie duration: Same session

*First-time visitor welcome:*
- Enable first visit
- Enable time delay: 2 seconds
- Logic: ALL
- Cookie duration: Never (only shows once ever)

## Features

**Trigger Options:**
- Manual click triggers (WPBakery compatible)
- Automatic display triggers (time, scroll, first visit, exit intent)
- Trigger logic (ANY/ALL conditions)
- Cookie-based frequency control
- Testing tools (clear cookie button)

**Display & Styling:**
- 6 size presets (small to full screen)
- Customizable colors and opacity
- Border radius control
- Close button positioning
- Tab-specific popup opening
- WPBakery tab support inside popups

**User Experience:**
- ESC key to close
- Click overlay to close
- Body scroll lock
- Smooth animations
- Mobile responsive
- JavaScript API for programmatic control

## Documentation

See `/notes/` folder for:
- `changelog.md` - Version history
- `wpbakery-usage.md` - Detailed usage guide
- `architecture.md` - Technical documentation

## Support

Built for use with Salient theme and WPBakery Page Builder.

## Version

1.2.0
