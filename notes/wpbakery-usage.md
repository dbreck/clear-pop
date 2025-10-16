# WPBakery Usage Guide

## Triggering Popups in WPBakery

### Simple Class Method (Recommended)
Just add the popup-specific class to any button/link:

**Class to add:** `hsp-popup-trigger-{post_id}`

Example: If your popup post ID is `456`, use: `hsp-popup-trigger-456`

### Where to Add in WPBakery

1. **Button Element**
   - Edit button → Design Options tab
   - Extra Class Name field: `hsp-popup-trigger-456`

2. **Text/Image Link**
   - Edit element → Design Options tab  
   - Extra Class Name field: `hsp-popup-trigger-456`

3. **Custom Link**
   - Add the class directly to any `<a>` tag

### You Can Also Combine Classes
- `hsp-popup-trigger hsp-popup-trigger-456` ← Both work
- `hsp-popup-trigger-456 my-custom-class` ← Mix with your styles

### Legacy Method (Still Supported)
If you can add data attributes elsewhere:
```html
<a href="#" class="hsp-popup-trigger" data-popup-id="456">Click Me</a>
```

---

## Finding Popup IDs

1. Go to **Popups** in WP Admin
2. Hover over popup name
3. Look at browser status bar: `post=456` ← that's your ID
4. Or click Edit and check URL: `post.php?post=456&action=edit`

---

## Pro Tip
The class method is cleaner and works everywhere. Even works in:
- Navigation menus (CSS Classes field)
- Salient Theme custom elements
- Elementor (Advanced → CSS Classes)
- Any HTML editor
