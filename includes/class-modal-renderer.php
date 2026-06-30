<?php
/**
 * Modal Renderer - Outputs popup HTML to page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Clear_Pop_Modal_Renderer {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_footer', array($this, 'render_popups'));
        // Lazy-load endpoint: returns a popup's rendered body for injection on open.
        add_action('wp_ajax_clearpop_content', array($this, 'ajax_render_content'));
        add_action('wp_ajax_nopriv_clearpop_content', array($this, 'ajax_render_content'));
    }

    /**
     * AJAX: render and return a single popup's body HTML.
     *
     * The body (which may contain a Gravity Form whose element IDs would collide
     * with an inline copy of the same form) is kept out of the initial page DOM
     * and fetched here the first time the popup opens. Returns raw HTML for direct
     * injection by modal.js. Echoing the body twice is avoided because the footer
     * render discards its output (see render_single_popup()).
     */
    public function ajax_render_content() {
        $popup_id = isset($_GET['popup_id']) ? absint($_GET['popup_id']) : 0;

        // NOTE: deliberately NO nonce check. This endpoint returns PUBLIC popup
        // body HTML to anonymous visitors and is GET + cacheable, so a per-user
        // nonce is the wrong tool and actively breaks under caching:
        //   - A nonce baked into a full-page-cached document expires (~24h),
        //     after which every anonymous visitor's lazy-load 403s site-wide.
        //   - Even for a "logged out" visitor, a stale wordpress_logged_in
        //     cookie poisons wp_get_session_token(), so the nonce computed at
        //     verify time no longer matches the one rendered into the page.
        // The content is public and this is a read (no state change, no private
        // data), so validating that the request targets a PUBLISHED popup is the
        // appropriate protection — an unpublished/non-popup id is rejected below.
        $popup = $popup_id ? get_post($popup_id) : null;
        if (!$popup || 'hsp_popup' !== $popup->post_type || 'publish' !== $popup->post_status) {
            status_header(404);
            wp_die('', '', array('response' => 404));
        }

        // admin-ajax.php runs in an is_admin() context, where WPBakery does NOT
        // auto-register its mapped shortcodes — without this, [vc_row]/[vc_column]
        // etc. would pass through the_content as raw text. Force-register them so
        // the content renders exactly as it does on the front end.
        if (class_exists('WPBMap') && method_exists('WPBMap', 'addAllMappedShortcodes')) {
            WPBMap::addAllMappedShortcodes();
        }

        // Match the front-end render: prime Salient's per-popup CSS, then render
        // the content through the_content so shortcodes/forms output normally.
        if (class_exists('Salient_Core')) {
            do_action('nectar_store_post_page_css', $popup->ID);
        }

        $content = apply_filters('the_content', $popup->post_content);

        nocache_headers();
        header('Content-Type: text/html; charset=' . get_option('blog_charset'));
        echo $content;
        wp_die();
    }
    
    /**
     * Render all published popups in footer
     */
    public function render_popups() {
        $popups = get_posts(array(
            'post_type'      => 'hsp_popup',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        if (empty($popups)) {
            return;
        }

        // Get current page/post ID
        $current_page_id = get_queried_object_id();

        foreach ($popups as $popup) {
            // Check page targeting
            if (!$this->should_display_on_page($popup->ID, $current_page_id)) {
                continue; // Not targeted for this page
            }

            // Always render popup HTML — click-triggered popups need the DOM element
            // present regardless of cookie state. Cookie checks for auto-triggered
            // popups are handled client-side by triggers.js.
            $this->render_single_popup($popup);
        }
    }
    
    /**
     * Check if popup should display on current page
     *
     * @param int $popup_id      Popup post ID
     * @param int $current_page  Current page/post ID
     * @return bool
     */
    private function should_display_on_page($popup_id, $current_page) {
        $display_pages = get_post_meta($popup_id, '_display_pages', true) ?: 'all';

        if ('all' === $display_pages) {
            return true;
        }

        $display_page_ids = get_post_meta($popup_id, '_display_page_ids', true);

        if (!is_array($display_page_ids) || empty($display_page_ids)) {
            return true; // No specific pages configured = show everywhere (fail safe)
        }

        return in_array($current_page, $display_page_ids, false);
    }

    /**
     * Render single popup
     */
    private function render_single_popup($popup) {
        // Get settings
        $size = get_post_meta($popup->ID, '_popup_size', true) ?: 'medium';
        $width_value = get_post_meta($popup->ID, '_popup_width_value', true);
        $width_unit = get_post_meta($popup->ID, '_popup_width_unit', true) ?: 'px';
        $allowed_width_units = array('px', 'vw', '%');
        if (!in_array($width_unit, $allowed_width_units, true)) {
            $width_unit = 'px';
        }
        $height_mode = get_post_meta($popup->ID, '_popup_height_mode', true) ?: 'auto';
        $height_value = get_post_meta($popup->ID, '_popup_height_value', true);
        $height_unit = get_post_meta($popup->ID, '_popup_height_unit', true) ?: 'vh';
        $allowed_height_units = array('vh', 'px', '%');
        if (!in_array($height_unit, $allowed_height_units, true)) {
            $height_unit = 'vh';
        }
        $bg_color = get_post_meta($popup->ID, '_popup_bg_color', true) ?: '#000000';
        $bg_opacity = get_post_meta($popup->ID, '_popup_bg_opacity', true) ?: '0.8';
        $close_position = get_post_meta($popup->ID, '_popup_close_position', true) ?: 'top-right';
        $close_style = get_post_meta($popup->ID, '_popup_close_style', true) ?: 'light';
        $close_border = get_post_meta($popup->ID, '_popup_close_border', true);
        $close_button_width = get_post_meta($popup->ID, '_popup_close_button_width', true);
        $close_button_width_tablet = get_post_meta($popup->ID, '_popup_close_button_width_tablet', true);
        $close_button_width_mobile = get_post_meta($popup->ID, '_popup_close_button_width_mobile', true);
        $padding = get_post_meta($popup->ID, '_popup_content_padding', true) ?: 'default';
        $padding_value = get_post_meta($popup->ID, '_popup_content_padding_value', true);
        $padding_value_tablet = get_post_meta($popup->ID, '_popup_content_padding_value_tablet', true);
        $padding_value_mobile = get_post_meta($popup->ID, '_popup_content_padding_value_mobile', true);
        $border_radius_value = get_post_meta($popup->ID, '_popup_border_radius_value', true);
        $border_radius_unit = get_post_meta($popup->ID, '_popup_border_radius_unit', true) ?: 'px';
        $allowed_radius_units = array('px', 'rem', 'em', 'vw', '%');
        if (!in_array($border_radius_unit, $allowed_radius_units, true)) {
            $border_radius_unit = 'px';
        }

        // Popup box background: custom value, else inherit Salient theme background.
        $content_bg_color = get_post_meta($popup->ID, '_popup_content_bg_color', true);
        $nectar_options   = function_exists('get_nectar_theme_options') ? get_nectar_theme_options() : array();
        $theme_bg_color   = (is_array($nectar_options) && !empty($nectar_options['background-color'])) ? $nectar_options['background-color'] : '';
        $popup_bg         = ('' !== $content_bg_color) ? $content_bg_color : $theme_bg_color;

        // Close button colour + per-edge offsets.
        $close_color   = get_post_meta($popup->ID, '_popup_close_color', true);
        $close_offsets = array(
            'top'    => get_post_meta($popup->ID, '_popup_close_offset_top', true),
            'right'  => get_post_meta($popup->ID, '_popup_close_offset_right', true),
            'bottom' => get_post_meta($popup->ID, '_popup_close_offset_bottom', true),
            'left'   => get_post_meta($popup->ID, '_popup_close_offset_left', true),
        );

        // Convert hex to rgba
        $rgba = $this->hex_to_rgba($bg_color, $bg_opacity);
        
        // Force Salient to generate CSS for this content
        if (class_exists('Salient_Core')) {
            // Trigger Salient's shortcode CSS generation
            do_action('nectar_store_post_page_css', $popup->ID);
        }

        // Render the content here purely for its SIDE EFFECTS — running
        // `the_content` makes Gravity Forms (and other shortcodes) enqueue their
        // scripts/styles for this page, exactly as before. The rendered markup is
        // intentionally DISCARDED: the body is lazy-loaded over AJAX on first open
        // (see ajax_render_content() + modal.js). This keeps the heavy markup —
        // and any embedded form's duplicate element IDs — out of the initial DOM
        // until the popup is actually opened.
        $content = apply_filters('the_content', $popup->post_content);
        unset($content);

        if ('none' === $padding) {
            $padding_class = 'hsp-popup-padding-none';
        } elseif ('custom' === $padding) {
            $padding_class = 'hsp-popup-padding-custom';
        } else {
            $padding_class = 'hsp-popup-padding-default';
        }

        // Padding + close-button-width are emitted in a per-popup scoped <style>
        // block (keyed off #hsp-popup-<ID>) rather than inline, so the responsive
        // Tablet/Mobile overrides can be expressed as media queries. A stylesheet
        // !important can't beat an inline !important, so the desktop custom padding
        // lives here too (an #id .class !important rule out-specifies the modal.css
        // base rules without needing inline). No padding inline attr is used.
        $content_inner_attr = '';
        $pid       = '#hsp-popup-' . intval($popup->ID);
        $css_rules = array();

        // Desktop custom padding (was inline). Only emitted for the 'custom' mode;
        // 'default'/'none' are handled by their container classes in modal.css.
        if ('custom' === $padding && '' !== $padding_value && is_numeric($padding_value)) {
            $css_rules[] = $pid . ' .hsp-popup-content-inner { padding: ' . absint($padding_value) . 'px !important; }';
        }

        // Tablet (<=1024px) and Mobile (<=767px) overrides. Each overrides the
        // padding/width regardless of the desktop mode; blank = inherit.
        $tablet_css = array();
        $mobile_css = array();

        if ('' !== $padding_value_tablet && is_numeric($padding_value_tablet)) {
            $tablet_css[] = $pid . ' .hsp-popup-content-inner { padding: ' . absint($padding_value_tablet) . 'px !important; }';
        }
        if ('' !== $padding_value_mobile && is_numeric($padding_value_mobile)) {
            $mobile_css[] = $pid . ' .hsp-popup-content-inner { padding: ' . absint($padding_value_mobile) . 'px !important; }';
        }
        if ('' !== $close_button_width_tablet && is_numeric($close_button_width_tablet)) {
            $tablet_css[] = $pid . ' .hsp-popup-close { width: ' . absint($close_button_width_tablet) . 'px !important; height: auto !important; aspect-ratio: 1 !important; }';
        }
        if ('' !== $close_button_width_mobile && is_numeric($close_button_width_mobile)) {
            $mobile_css[] = $pid . ' .hsp-popup-close { width: ' . absint($close_button_width_mobile) . 'px !important; height: auto !important; aspect-ratio: 1 !important; }';
        }

        if (!empty($tablet_css)) {
            $css_rules[] = '@media (max-width: 1024px) { ' . implode(' ', $tablet_css) . ' }';
        }
        if (!empty($mobile_css)) {
            $css_rules[] = '@media (max-width: 767px) { ' . implode(' ', $mobile_css) . ' }';
        }

        $popup_css = !empty($css_rules)
            ? '<style id="hsp-popup-css-' . intval($popup->ID) . '">' . implode("\n", $css_rules) . '</style>' . "\n"
            : '';

        $container_classes = array(
            'hsp-popup-container',
            'hsp-popup-size-' . sanitize_html_class($size),
            $padding_class,
        );
        
        $inline_styles = array();
        if ('' !== $popup_bg) {
            $inline_styles[] = 'background-color:' . $popup_bg;
        }
        if ('' !== $border_radius_value && is_numeric($border_radius_value)) {
            $radius = (float) $border_radius_value;
            $radius = rtrim(rtrim(sprintf('%.4f', $radius), '0'), '.');
            $inline_styles[] = 'border-radius:' . $radius . $border_radius_unit;
        }
        if ('custom' === $size && '' !== $width_value && is_numeric($width_value)) {
            $width = (float) $width_value;
            $width = rtrim(rtrim(sprintf('%.4f', $width), '0'), '.');
            $inline_styles[] = 'width:' . $width . $width_unit;
        }
        // Support both 'custom' (new) and 'fixed' (legacy) for backward compatibility
        if (('custom' === $height_mode || 'fixed' === $height_mode) && '' !== $height_value && is_numeric($height_value)) {
            $height = (float) $height_value;
            $height = rtrim(rtrim(sprintf('%.4f', $height), '0'), '.');
            $inline_styles[] = 'height:' . $height . $height_unit;
            $inline_styles[] = 'max-height:' . $height . $height_unit;
        }
        $style_attr = $inline_styles ? ' style="' . esc_attr(implode('; ', $inline_styles)) . '"' : '';

        // Close button inline styles
        $close_inline_styles = array();
        if ('' !== $close_button_width && is_numeric($close_button_width)) {
            $btn_width = absint($close_button_width);
            $close_inline_styles[] = 'width:' . $btn_width . 'px';
            $close_inline_styles[] = 'height:auto';
            $close_inline_styles[] = 'aspect-ratio:1';
        }
        // Per-edge offsets override the position-class defaults.
        foreach ($close_offsets as $side => $offset_val) {
            if ('' !== $offset_val && is_numeric($offset_val)) {
                $close_inline_styles[] = $side . ':' . (float) $offset_val . 'px';
            }
        }
        // Custom colour overrides the Light/Dark style class (SVG + border use currentColor).
        if ('' !== $close_color) {
            $close_inline_styles[] = 'color:' . $close_color;
        }
        $close_style_attr = $close_inline_styles ? ' style="' . esc_attr(implode('; ', $close_inline_styles)) . '"' : '';
        
        // Build close button classes
        $close_classes = array(
            'hsp-popup-close',
            'hsp-popup-close-' . sanitize_html_class($close_position),
            'hsp-popup-close-' . sanitize_html_class($close_style),
        );
        if ('1' === $close_border) {
            $close_classes[] = 'hsp-popup-close-border';
        }

        ?>
        <?php echo $popup_css; ?>
        <div class="hsp-popup-overlay" id="hsp-popup-<?php echo esc_attr($popup->ID); ?>" style="background-color: <?php echo esc_attr($rgba); ?>;" data-popup-id="<?php echo esc_attr($popup->ID); ?>" data-popup-slug="<?php echo esc_attr($popup->post_name); ?>">
            <div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr($popup->post_title); ?>" tabindex="-1"<?php echo $style_attr; ?>>
                <button class="<?php echo esc_attr(implode(' ', $close_classes)); ?>"<?php echo $close_style_attr; ?> aria-label="<?php esc_attr_e('Close', 'clear-pop'); ?>">
                    <svg width="60%" height="60%" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="hsp-popup-content nectar-global-section">
                    <div class="hsp-popup-content-inner" data-lazy="1" data-popup-id="<?php echo esc_attr($popup->ID); ?>"<?php echo $content_inner_attr; ?>>
                        <div class="hsp-popup-loading" aria-live="polite">
                            <span class="hsp-popup-spinner" role="status" aria-label="<?php esc_attr_e('Loading', 'clear-pop'); ?>"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Convert hex color to rgba
     */
    private function hex_to_rgba($hex, $opacity) {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return "rgba({$r}, {$g}, {$b}, {$opacity})";
    }
}
