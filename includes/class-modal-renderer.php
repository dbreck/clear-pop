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

        // Get trigger handler for cookie checking
        $trigger_handler = Clear_Pop_Trigger_Handler::get_instance();

        foreach ($popups as $popup) {
            // Check if popup should be shown based on cookies
            if (!$trigger_handler->should_render_popup($popup->ID)) {
                continue; // Skip rendering this popup
            }

            $this->render_single_popup($popup);
        }
    }
    
    /**
     * Render single popup
     */
    private function render_single_popup($popup) {
        // Get settings
        $size = get_post_meta($popup->ID, '_popup_size', true) ?: 'medium';
        $bg_color = get_post_meta($popup->ID, '_popup_bg_color', true) ?: '#000000';
        $bg_opacity = get_post_meta($popup->ID, '_popup_bg_opacity', true) ?: '0.8';
        $close_position = get_post_meta($popup->ID, '_popup_close_position', true) ?: 'top-right';
        $close_style = get_post_meta($popup->ID, '_popup_close_style', true) ?: 'light';
        $close_border = get_post_meta($popup->ID, '_popup_close_border', true);
        $padding = get_post_meta($popup->ID, '_popup_content_padding', true) ?: 'default';
        $border_radius_value = get_post_meta($popup->ID, '_popup_border_radius_value', true);
        $border_radius_unit = get_post_meta($popup->ID, '_popup_border_radius_unit', true) ?: 'px';
        $allowed_radius_units = array('px', 'rem', 'em', 'vw', '%');
        if (!in_array($border_radius_unit, $allowed_radius_units, true)) {
            $border_radius_unit = 'px';
        }
        
        // Convert hex to rgba
        $rgba = $this->hex_to_rgba($bg_color, $bg_opacity);
        
        // Force Salient to generate CSS for this content
        if (class_exists('Salient_Core')) {
            // Trigger Salient's shortcode CSS generation
            do_action('nectar_store_post_page_css', $popup->ID);
        }
        
        // Get content
        $content = apply_filters('the_content', $popup->post_content);
        
        $padding_class = ('none' === $padding) ? 'hsp-popup-padding-none' : 'hsp-popup-padding-default';
        
        $container_classes = array(
            'hsp-popup-container',
            'hsp-popup-size-' . sanitize_html_class($size),
            $padding_class,
        );
        
        $inline_styles = array();
        if ('' !== $border_radius_value && is_numeric($border_radius_value)) {
            $radius = (float) $border_radius_value;
            $radius = rtrim(rtrim(sprintf('%.4f', $radius), '0'), '.');
            $inline_styles[] = 'border-radius:' . $radius . $border_radius_unit;
        }
        $style_attr = $inline_styles ? ' style="' . esc_attr(implode('; ', $inline_styles)) . '"' : '';
        
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
        <div class="hsp-popup-overlay" id="hsp-popup-<?php echo esc_attr($popup->ID); ?>" style="background-color: <?php echo esc_attr($rgba); ?>;" data-popup-id="<?php echo esc_attr($popup->ID); ?>">
            <div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"<?php echo $style_attr; ?>>
                <button class="<?php echo esc_attr(implode(' ', $close_classes)); ?>" aria-label="<?php esc_attr_e('Close', 'clear-pop'); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="hsp-popup-content nectar-global-section">
                    <div class="hsp-popup-content-inner container-wrap">
                        <?php echo $content; ?>
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
