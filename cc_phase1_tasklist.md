# Clear Pop - Phase 1: Automatic Display Triggers

## Overview

Build automatic display trigger system (time delay, scroll depth, first visit, exit intent) with cookie management. This is **purely additive**—do not touch existing click-to-open functionality.

**Key constraint:** WPBakery does not support data attributes on links/buttons. All existing trigger logic uses CSS classes only (`hsp-popup-trigger-{id}`). Keep it that way.

---

## Phase 1A: Cookie Management Foundation

Build the cookie management system that all triggers will depend on. Do NOT touch any existing functionality—this is purely additive.

**New file:** `includes/class-cookie-manager.php`

Create a singleton class that handles:
- Setting/getting cookies for popup view tracking
- Checking if popup should be shown based on cookie rules
- Cookie duration logic (never, session, 1hour, 24hours, 7days, 30days)
- Cookie structure: JSON with popup_id, shown_count, last_shown timestamp, closed_method

**Cookie naming:** `clear_pop_{popup_id}` (e.g., `clear_pop_456`)

**Public methods needed:**
```php
- should_show_popup($popup_id, $duration_setting)  // Returns bool
- record_popup_shown($popup_id)                     // Sets cookie
- record_popup_closed($popup_id, $method)           // Updates cookie with close method
- clear_popup_cookie($popup_id)                     // For testing
```

**Duration logic:**
- 'never' = 10 year cookie, never show again
- 'session' = session cookie, show again on new visit
- '1hour' = Check last_shown timestamp, show if >1 hour ago
- '24hours' = Check last_shown timestamp, show if >24 hours ago
- '7days' = Check last_shown timestamp, show if >7 days ago
- '30days' = Check last_shown timestamp, show if >30 days ago

**Important:** Use vanilla PHP setcookie(), not WordPress transients. Front-end needs to read these cookies via JavaScript too.

Load this class in the main plugin file's load_dependencies() method.

DO NOT add any UI yet. DO NOT modify existing modal.js or metabox. Just build the cookie infrastructure.

Test by calling methods manually in functions.php to verify cookie setting/reading works.

**Commit when done:** "Add cookie management foundation for trigger system"

---

## Phase 1B: Add Trigger Settings UI

Add the new "Display Triggers" metabox to the popup editor. This goes BELOW the existing "Popup Settings" metabox.

**Modify:** `includes/class-metabox.php`

Add second metabox with ID `clear_pop_triggers`:
- Title: "Display Triggers"
- Position: 'normal', 'default' (shows below existing settings)

**Trigger options to include:**

1. **Click to Open (Manual Trigger)** - Default, always enabled
   - Just explanatory text: "Manual triggers via CSS class (hsp-popup-trigger-{id}) always work regardless of automatic triggers below."
   - No checkbox, no settings—this is the existing behavior

2. **Time Delay** - Checkbox + number input
   - ☐ Show automatically after [__] seconds on page
   - Input: 0-120 seconds
   - Default: unchecked

3. **Scroll Depth** - Checkbox + number input  
   - ☐ Show automatically after scrolling [__]% down page
   - Input: 0-100 percentage
   - Default: unchecked

4. **First Visit Only** - Checkbox
   - ☐ Only show on user's first visit to the site
   - Help text: "Requires cookies enabled. Combines with other triggers."
   - Default: unchecked

5. **Exit Intent** - Checkbox
   - ☐ Show when cursor moves toward browser top (desktop only)
   - Help text: "Does not work on mobile devices."
   - Default: unchecked

6. **Trigger Logic** - Radio buttons (only show if 2+ triggers enabled)
   - Show when: ○ ANY condition is met  ○ ALL conditions are met
   - Help text for ANY: "Popup shows as soon as any one trigger fires"
   - Help text for ALL: "Popup waits until all enabled triggers have fired"
   - Default: 'any'

7. **Cookie & Frequency** - Radio buttons
   - Show again after user closes popup:
   - ○ Never (user will never see it again)
   - ○ Same session (show again on next visit)
   - ○ After 1 hour
   - ○ After 24 hours  
   - ○ After 7 days
   - ○ After 30 days
   - Default: 'never'

**Post meta keys to save:**
```php
_trigger_time_delay      // int, 0 = disabled
_trigger_scroll_depth    // int, 0 = disabled  
_trigger_first_visit     // '1' or ''
_trigger_exit_intent     // '1' or ''
_trigger_logic           // 'any' or 'all', default 'any'
_cookie_duration         // 'never', 'session', '1hour', '24hours', '7days', '30days'
```

**Styling:** Use same grid layout as existing settings metabox for consistency.

**Important:** DO NOT modify existing settings metabox or modal rendering. This is purely additive UI. We're not hooking up functionality yet—that's next phase.

**Commit when done:** "Add display triggers settings UI to popup editor"

---

## Phase 1C: JavaScript Trigger Evaluation

Create the JavaScript that evaluates trigger conditions and automatically shows popups. This runs alongside (not replacing) the existing manual trigger system.

**New file:** `assets/js/triggers.js`

**Enqueue in:** `includes/class-assets.php` (load after modal.js, only when popups exist)

**What this file does:**

1. On page load, find all popups that have automatic triggers enabled
2. For each popup with triggers:
   - Check cookie via `document.cookie` (does `clear_pop_{id}` exist and is it still valid?)
   - If cookie says "don't show", skip this popup
   - Initialize enabled triggers (time delay, scroll depth, exit intent)
   - Track which triggers have fired
   - When logic condition met ('any' or 'all'), call `window.hspPopup.open(popup_id)`
   - On close, send AJAX to record close event (so PHP can set cookie)

**Trigger implementations:**

**Time Delay:**
```javascript
setTimeout(() => {
  triggerFired('time_delay');
  evaluateLogic();
}, delayInSeconds * 1000);
```

**Scroll Depth:**
```javascript
// Throttled scroll handler
window.addEventListener('scroll', throttle(() => {
  const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
  if (scrollPercent >= targetPercent) {
    triggerFired('scroll_depth');
    evaluateLogic();
  }
}, 100));
```

**First Visit:**
```javascript
// Check for long-term cookie (set by PHP or JS on first visit)
if (!getCookie('clear_pop_first_visit')) {
  // This IS first visit
  setCookie('clear_pop_first_visit', '1', 365); // 1 year cookie
  // Allow other triggers to fire
} else {
  // Not first visit, skip this popup entirely
}
```

**Exit Intent:**
```javascript
// Desktop only
if (window.innerWidth > 768) {
  document.addEventListener('mousemove', (e) => {
    if (e.clientY < 10) { // Cursor near top
      triggerFired('exit_intent');
      evaluateLogic();
    }
  });
}
```

**AJAX endpoint for close tracking:**
- When user closes popup (X button, ESC, overlay click), send AJAX to PHP
- PHP calls `Clear_Pop_Cookie_Manager::record_popup_closed($popup_id, $method)`
- This sets the cookie with proper duration based on settings

**CRITICAL:** DO NOT break existing manual triggers. The `hsp-popup-trigger-{id}` class system must continue working exactly as it does now.

**WPBakery reminder:** We use CSS classes only, never data attributes. Automatic triggers read settings from a global JS object localized by PHP.

**Localize trigger data:** In `class-assets.php`, use `wp_localize_script()` to pass each popup's trigger settings to JavaScript:
```php
window.clearPopTriggers = {
  '456': {
    time_delay: 5,
    scroll_depth: 50,
    first_visit: true,
    exit_intent: false,
    logic: 'any',
    cookie_duration: 'never'
  }
};
```

**Commit when done:** "Add JavaScript trigger evaluation system"

---

## Phase 1D: PHP Trigger Handler & Cookie Integration

Connect the cookie system to the front-end rendering. Popups should respect cookies and not render if they've been shown based on duration settings.

**New file:** `includes/class-trigger-handler.php`

This class handles:
1. Checking cookies before rendering popup HTML
2. Providing trigger data for JS localization  
3. Handling AJAX endpoint for close tracking

**Modify:** `includes/class-modal-renderer.php`

Before rendering each popup in `render_popups()`, check:
```php
$cookie_manager = Clear_Pop_Cookie_Manager::get_instance();
$cookie_duration = get_post_meta($popup->ID, '_cookie_duration', true) ?: 'never';

if (!$cookie_manager->should_show_popup($popup->ID, $cookie_duration)) {
  continue; // Skip rendering this popup's HTML
}
```

**Modify:** `includes/class-assets.php`

Add the trigger data localization when enqueueing triggers.js:
```php
$trigger_data = Clear_Pop_Trigger_Handler::get_instance()->get_localized_data();
wp_localize_script('clear-pop-triggers', 'clearPopTriggers', $trigger_data);
```

**Add AJAX handler** in `class-trigger-handler.php`:
```php
add_action('wp_ajax_clear_pop_close', 'handle_popup_close');
add_action('wp_ajax_nopriv_clear_pop_close', 'handle_popup_close');
```

This receives popup_id and close_method from JavaScript, then calls cookie manager to set cookie.

Load this class in main plugin file's load_dependencies().

**Commit when done:** "Integrate cookie system with popup rendering and AJAX tracking"

---

## Phase 1E: Testing & Polish

Add admin conveniences for testing and verify everything works:

1. **Clear Cookie Button** - Add to "Display Triggers" metabox
   - Button: "Clear Cookie for Testing"
   - AJAX handler that calls `Clear_Pop_Cookie_Manager::clear_popup_cookie($popup_id)`
   - Shows confirmation message

2. **Trigger Preview** - Add help text showing active triggers
   - If no triggers enabled: "This popup uses manual triggers only (click to open)."
   - If triggers enabled: "This popup will automatically show when: [list enabled triggers with logic]"

3. **Test all scenarios:**
   - Manual trigger still works (hsp-popup-trigger class)
   - Time delay works (5 second delay)
   - Scroll depth works (50% scroll)
   - First visit cookie sets correctly
   - Exit intent fires on desktop
   - Cookie duration "never" prevents showing again
   - Cookie duration "session" allows showing on new session
   - Timed durations (1 hour, 24 hours, etc.) respect timestamps
   - AND logic waits for all triggers
   - ANY logic fires on first trigger

4. **Update changelog** in `/notes/changelog.md`:
```markdown
## [1.2.0] - 2025-10-16

### Added
- **Automatic Display Triggers** - Show popups based on conditions
  - Time delay (show after X seconds)
  - Scroll depth (show after X% scroll)
  - First visit detection (one-time cookie)
  - Exit intent (desktop only)
- **Trigger Logic** - ANY or ALL conditions
- **Cookie Management** - Control show frequency after close
  - Never show again
  - Show after session, 1 hour, 24 hours, 7 days, 30 days
- **Manual triggers still work** - Click-to-open unchanged

### Technical
- New class: Clear_Pop_Cookie_Manager
- New class: Clear_Pop_Trigger_Handler
- New file: assets/js/triggers.js
- AJAX endpoint for close tracking
- Backwards compatible with existing popups
```

5. **Update main README.md** with trigger examples

**Commit when done:** "Add testing tools and update documentation for v1.2.0"

---

## Bonus: Rename Admin Menu

Quick hit while we're at it—rename the admin menu from "Popups" to "Clear Pop" to match the plugin name.

**Modify:** `includes/class-post-type.php`

Update the `labels` array in `register_post_type()`:
- Change 'menu_name' from 'Popups' to 'Clear Pop'

**Commit:** "Rename admin menu to Clear Pop"

---

## Usage Instructions

**To execute phases:**
1. Copy Phase 1A into Claude Code
2. Wait for completion and commit
3. Copy Phase 1B into Claude Code
4. Repeat through Phase 1E

**To resume mid-phase:**
- Prompt: "Continue with Phase 1B" (or whatever phase you're on)

**To skip ahead:**
- Prompt: "Do Phase 1C next" (Claude Code will read this file for context)

**When complete:**
- All phases done = Full automatic trigger system with cookie management
- Version bump to 1.2.0
- Ready to test on live site
