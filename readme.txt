=== Clear Pop ===
Contributors: dbreck
Tags: popup, modal, wpbakery, salient
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.6.0
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
