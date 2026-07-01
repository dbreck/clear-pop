=== Clear Pop ===
Contributors: dbreck
Tags: popup, modal, wpbakery, salient
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.8.0
Requires PHP: 7.4
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Clean, simple popup modal system for WordPress with WPBakery support.

== Description ==

Clear Pop provides a clean, simple popup modal system designed for use with the Salient theme and WPBakery Page Builder.

**Features:**

* 6 width presets (Small to Full Screen) plus Custom
* Modal height control (Auto or Custom)
* Custom background color and opacity
* Close button positioning, styling, and width control
* Border radius control
* WPBakery Page Builder integration
* Tab-specific popup opening
* Automatic display triggers (time delay, scroll depth, exit intent, first visit)
* Cookie-based frequency control
* JavaScript API for programmatic control
* ESC key and overlay click to close
* Mobile responsive

== Installation ==

1. Upload the `clear-pop` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Popups > Add New to create your first popup

== Changelog ==

= 1.8.2 =
* Fix: stray focus ring on the close button when a popup is opened by mouse. On open, focus was moved to the close button; after the lazy-loaded body arrived and the modal grew to full height, the browser re-evaluated :focus-visible and painted the close button's outline (looked like an unwanted border that vanished on the next interaction). Focus now moves to the labeled dialog container (role="dialog", tabindex="-1") instead — the recommended ARIA pattern, so screen readers still announce the dialog — and the close button's keyboard focus ring is preserved (it only shows when a keyboard user Tabs to it). Also suppresses the programmatically-focused dialog container's own default outline (it is not Tab-reachable, so no keyboard affordance is lost).

= 1.8.1 =
* Fix: when "Add Border to Close Button" is OFF, the close button now forces `border: none !important`. The close button is a <button>, so a theme's global button styling (e.g. Salient's rounded / see-through button border) could paint a border the setting was meant to suppress, making the option look ignored. Scoped via :not(.hsp-popup-close-border) so the opt-in border still works when checked.
* Fix: the AJAX popup-body endpoint (clearpop_content) no longer requires a nonce. Popup bodies are public content served to anonymous visitors over a cacheable GET, so a per-user nonce was both unnecessary and actively broke the lazy-load: a nonce baked into a full-page-cached document expires (~24h) and 403s every visitor site-wide, and a stale wordpress_logged_in cookie poisons the session token so even "logged out" visitors fail verification. The request is now validated by confirming it targets a PUBLISHED hsp_popup (unpublished/non-popup ids still 404) — appropriate protection for a public, side-effect-free read. No change to accessibility (dialog roles/focus management) or any other behavior.

= 1.8.0 =
* Popup bodies now lazy-load over AJAX on first open (with a loading spinner) instead of being printed into the page at load. This keeps heavy markup — and, critically, any embedded Gravity Form whose element IDs would otherwise collide with an inline copy of the same form on the page — out of the initial DOM, fixing duplicate-ID accessibility/validation errors at page load.
* The footer still renders popup content once for its side effects only (priming Gravity Forms / Salient asset enqueue) and discards the markup, so script/style enqueue is unchanged.
* AJAX render force-registers WPBakery mapped shortcodes (WPBMap::addAllMappedShortcodes) so content renders correctly in the is_admin() admin-ajax context.
* Note: on a page that ALSO embeds the same form inline, opening the popup re-introduces the shared IDs only while the popup is open; load-time and popup-only pages are fully clean.

= 1.7.0 =
* Added responsive Close Button Width — separate Desktop / Tablet (<=1024px) / Mobile (<=767px) values (blank inherits desktop)
* Added responsive Popup Padding — optional Tablet (<=1024px) and Mobile (<=767px) px overrides that win over the desktop padding mode at those breakpoints
* Padding and close-button width now render via a per-popup scoped <style> block (#hsp-popup-<id>) instead of inline styles, so responsive media-query overrides apply reliably

= 1.6.4 =
* Fixed close button / overlay-click / ESC not binding on auto-triggered popups (init bailed when no on-page click-trigger existed)
* Added "Custom (set amount)" Popup Padding option
* Added "Testing — Every refresh" cookie duration option (bypasses cookies entirely; never writes a suppression cookie)
* Changed close-button focus ring to :focus-visible (no ring on mouse click, still shown for keyboard nav)

= 1.6.0 =
* Added Popup Background Color setting (inherits the Salient theme background when left blank)
* Added Close Button per-edge Offset (top/right/bottom/left) for nudging the button off the edges
* Added Close Button Color picker (overrides the Light/Dark style)
* Color pickers now show the Salient theme palette as swatches
* Fixed images inside popups ignoring the Salient element's Custom Max Width (popups render outside #ajax-content-wrap, so base image sizing is now re-established inside the popup)
* Fixed Close Button Offset inputs overflowing the settings metabox

= 1.5.0 =
* Added page targeting - display popup on all pages or specific pages only
* Fixed cookie not being set on popup close (cookie now set client-side immediately)
* Changed cookie to non-httpOnly so both JavaScript and PHP can read it

= 1.4.1 =
* Fixed close button X icon not scaling with custom button width

= 1.4.0 =
* Renamed "Modal Size" to "Modal Width"
* Added Custom width option with value and unit (px, vw, %)
* Changed height "Fixed" option to "Custom" for consistency
* Added Close Button Width field (height auto with 1:1 aspect ratio)
* Added slug-based trigger class option (e.g., hsp-popup-trigger-my-popup)

= 1.3.1 =
* Fixed Salient theme injecting min-height styles on popup content

= 1.3.0 =
* Added modal height control with Auto/Fixed modes
* Added GitHub auto-update support
* Fixed height accepts vh, px, or % units

= 1.2.0 =
* Added automatic display triggers (time delay, scroll depth, first visit, exit intent)
* Added trigger logic (ANY/ALL conditions)
* Added cookie-based frequency control
* Added testing tools (clear cookie button)

= 1.1.0 =
* Added Full Screen modal size option
* Added close button border option
* Added custom border radius control
* Added tab-specific popup opening
* Added WPBakery tab support inside popups
* Added WPBakery Role Manager integration

= 1.0.0 =
* Initial release
