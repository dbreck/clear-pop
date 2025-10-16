# Clear Pop - Changelog

## [1.2.0] - 2025-10-16

### Added
- **Automatic Display Triggers** - Show popups based on user behavior conditions
  - Time delay (show after X seconds)
  - Scroll depth (show after X% scroll)
  - First visit detection (one-time cookie)
  - Exit intent (desktop only, cursor near top)
- **Trigger Logic** - Configure how multiple triggers work together
  - ANY conditions (show on first trigger)
  - ALL conditions (wait for all triggers)
- **Cookie Management** - Control show frequency after user closes popup
  - Never show again (10 year cookie)
  - Show after same session (session cookie)
  - Show after 1 hour, 24 hours, 7 days, or 30 days
- **Manual triggers still work** - Click-to-open unchanged (`hsp-popup-trigger-{id}`)
- **Testing Tools** - Clear cookie button in admin for testing automatic triggers
- **Trigger Preview** - Live summary in admin showing active triggers

### Technical
- New class: `Clear_Pop_Cookie_Manager` - Cookie-based view tracking
- New class: `Clear_Pop_Trigger_Handler` - AJAX endpoints and trigger logic
- New file: `assets/js/triggers.js` - Client-side trigger evaluation
- AJAX endpoint `clear_pop_close` for close tracking
- AJAX endpoint `clear_pop_cookie` for testing (admin only)
- Popups respect cookies and skip rendering if already shown
- JavaScript reads cookies to prevent unnecessary popup displays
- Throttled scroll handler for performance
- Backwards compatible with existing popups

## [1.1.0] - 2025-10-16

### Added
- **Full Screen Modal Size** - New 96vw × 96vh size option for nearly full-screen modals
- **Close Button Border** - Optional 1px solid border around close button (checkbox in settings)
- **Border Radius Control** - Custom border radius with value + unit selector (px, rem, em, vw, %)
- **Tab-Specific Popup Opening** - Open popup with specific tab active using CSS classes (`hsp-tab-1`, `hsp-tab-2`, etc.)
- **WPBakery Tab Support** - Automatic initialization of WPBakery/Salient tabs inside popups
- **WPBakery Role Manager Integration** - Plugin now appears in WPBakery Settings → Role Manager

### Changed
- Post type `public` parameter set to `true` (while keeping `publicly_queryable` as `false`)
  - Makes plugin visible in WPBakery Role Manager
  - Prevents popups from appearing in front-end queries/archives
- Enhanced WPBakery integration with proper post type registration

### Fixed
- WPBakery tabs inside popups now display first tab content correctly on popup open
- Tab panels properly initialized when popup becomes visible
- Tab switching works correctly after popup opens

### Technical Details
- Added `initWPBakeryTabs()` function in modal.js for tab initialization
- Tab index parsing from trigger button classes (1-based indexing)
- Proper visibility and positioning CSS applied to tab panels
- Window resize trigger after tab init for proper content layout
- Uses `:nth-of-type()` selector for accurate tab panel targeting

## [1.0.2] - 2025-10-09

### Changed
- Complete rebuild after accidental plugin update
- Renamed from "Popup Modal" to "Clear Pop" to prevent future update conflicts
- Post type remains: `hsp_popup`
- Restored original clean settings interface

### Fixed
- All original settings restored:
  - Modal Size (5 presets)
  - Background Color & Opacity
  - Close Button Position (4 corners)
  - Close Button Style (light/dark)
- Trigger code display with both methods (class-only + legacy)

## [1.0.1] - 2025-10-08

### Changed
- Modified trigger system to support class-only triggers for WPBakery compatibility
- Now accepts both methods:
  - Legacy: `class="hsp-popup-trigger" data-popup-id="123"`
  - New: `class="hsp-popup-trigger-123"` (or both classes combined)
- Trigger class pattern: `hsp-popup-trigger-{post_id}`
- Backward compatible with existing data-attribute method

### Why
- WPBakery Page Builder doesn't allow custom data attributes in its button/link elements
- Class-only approach works universally across all page builders

## [1.0.0] - 2025-10-08

### Added
- Initial plugin release
- Custom post type `hsp_popup` for creating modal content
- WPBakery Page Builder integration
- Click-trigger system using CSS class and data attribute
- Modal settings metabox with:
  - 5 size presets (small, medium, large, xlarge, fullwidth)
  - Background color picker with opacity control
  - Close button position options (4 corners)
  - Close button style (light/dark theme)
- Trigger code display in admin for easy copy/paste
- Frontend modal rendering system
- Fade-in animation for modal appearance
- Slide-up animation for modal container
- Responsive mobile support (full-width on mobile)
- ESC key to close functionality
- Click overlay to close functionality
- Body scroll lock when modal is open
- JavaScript API for programmatic control (`window.hspPopup`)
- Custom events for open/close tracking

### Technical Details
- Singleton pattern for all classes
- Clean OOP structure with separate class files
- Efficient asset loading (only loads when popups exist)
- Color picker integration in admin
- WPBakery content rendering with proper filters
- RGBA background color conversion
- Vanilla JavaScript (no jQuery dependency on frontend)
- Custom animation keyframes
- Z-index management for proper layering
