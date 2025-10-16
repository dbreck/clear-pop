# Clear Pop - Features & Usage Examples

## Trigger Methods

### Basic Popup Trigger
Add this class to any button/link in WPBakery:
```
hsp-popup-trigger-133
```
Replace `133` with your popup's post ID.

### Open Specific Tab
To open a popup with a specific tab already active:
```
hsp-popup-trigger-133 hsp-tab-2
```

**Tab Index Reference:**
- `hsp-tab-1` = First tab
- `hsp-tab-2` = Second tab
- `hsp-tab-3` = Third tab
- `hsp-tab-4` = Fourth tab

**Example Use Case:**
```
Button 1: hsp-popup-trigger-133 hsp-tab-1  (Opens to Clearwater/Dunedin)
Button 2: hsp-popup-trigger-133 hsp-tab-2  (Opens to St. Petersburg)
Button 3: hsp-popup-trigger-133 hsp-tab-3  (Opens to Daytona Beach)
Button 4: hsp-popup-trigger-133 hsp-tab-4  (Opens to Clearwater)
```

### Programmatic Control
JavaScript API for advanced use:
```javascript
// Open popup by ID
window.hspPopup.open(133);

// Close specific popup
window.hspPopup.close(133);

// Close all popups
window.hspPopup.closeAll();
```

### Listen to Events
```javascript
// When popup opens
document.addEventListener('hspPopupOpen', function(e) {
    console.log('Popup opened:', e.detail.popup);
    console.log('Tab index:', e.detail.tabIndex);
});

// When popup closes
document.addEventListener('hspPopupClose', function(e) {
    console.log('Popup closed:', e.detail.popup);
});
```

## Modal Size Options

### Size Reference
| Option | Dimensions | Best For |
|--------|-----------|----------|
| Small | 400px | Simple messages, confirmations |
| Medium | 600px | Forms, short content (default) |
| Large | 800px | Rich content, multiple sections |
| Extra Large | 1000px | Complex layouts, galleries |
| Full Width | 90vw | Wide content, panoramic images |
| Full Screen | 96vw × 96vh | Immersive experiences, video |

### Mobile Behavior
All sizes automatically become `90vw` on screens under 768px.

## Customization Options

### Close Button
**Position Options:**
- Top Right (default)
- Top Left
- Bottom Right
- Bottom Left

**Style Options:**
- Light (white, for dark backgrounds)
- Dark (black, for light backgrounds)

**Border:**
- Optional 1px solid border
- Inherits color from light/dark style

### Padding Control
- **Default Padding**: 40px all around
- **Edge to Edge**: No padding (for full-bleed images/video)

When using edge-to-edge, close button automatically adjusts to 12px from edge.

### Border Radius
Custom radius with unit selector:
- **Units**: px, rem, em, vw, %
- **Range**: 0 to any positive number
- **Default**: 8px (if left blank)

**Common Values:**
- `0px` = Sharp corners
- `8px` = Default subtle rounding
- `16px` = Noticeable rounded corners
- `50%` = Circular (for square containers)

## Background Overlay

### Color & Opacity
- **Color**: Any hex color via color picker
- **Opacity**: 0 (transparent) to 1 (solid)
- **Default**: Black (#000000) at 0.8 opacity

**Common Presets:**
- Light overlay: `#FFFFFF` at `0.9`
- Dark overlay: `#000000` at `0.8`
- Subtle: `#000000` at `0.5`
- Heavy: `#000000` at `0.95`

## WPBakery Tab Support

### Automatic Initialization
When a popup contains WPBakery tabs, they automatically:
1. Display the correct first tab content
2. Work properly after popup opens
3. Maintain all tab switching functionality

### Requirements
- Tabs must be built with WPBakery Tabs element
- Works with Salient theme tab styles
- No additional configuration needed

### How It Works
When popup opens:
1. Finds all `.tabbed` containers
2. Hides all tab panels
3. Shows the appropriate tab (first by default, or specified via `hsp-tab-X`)
4. Sets correct active states on navigation
5. Triggers resize for proper layout

## User Interactions

### Close Methods
Users can close popups by:
1. Clicking the X button
2. Pressing ESC key
3. Clicking the dark overlay background

### Scroll Behavior
- Body scroll locked when popup open
- Content inside popup can scroll
- Scroll position preserved when closing

### Animations
- **Opening**: Fade-in overlay + slide-up container
- **Closing**: Reverse animations
- **Duration**: 300ms transition
- **Easing**: CSS ease functions

## Best Practices

### Content
- Keep popup content focused
- Use appropriate size for content amount
- Test on mobile devices
- Consider accessibility (keyboard navigation works)

### Performance
- Assets only load when popups exist
- Vanilla JavaScript (no jQuery overhead)
- CSS animations (GPU accelerated)
- Minimal DOM manipulation

### WPBakery Usage
- Build popup content normally in WPBakery
- Use tabs for multi-section content
- Images load properly via Salient integration
- All WPBakery elements supported

### Multiple Popups
- Only one popup can be open at a time
- Opening new popup closes current one
- Each popup maintains independent settings
- Share trigger classes across site

## Troubleshooting

### Tabs Not Showing Content
- Ensure popup has finished opening
- Check tab structure matches WPBakery format
- Verify tab index matches actual tabs (1-based)

### Close Button Not Visible
- Check close button style (light vs dark)
- Ensure contrasts with popup content
- Try adding border option for visibility

### Popup Too Small/Large
- Test different size presets
- Remember mobile auto-adjusts to 90vw
- Use Full Screen for maximum space

### WPBakery Editor Missing
- Go to WPBakery → Role Manager
- Check `hsp_popup` is enabled for your role
- Deactivate/reactivate plugin if needed
