<?php
/**
 * Assets Management - CSS and JS
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Clear_Pop_Assets {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp', array($this, 'register_salient_popup_assets'), 20);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only enqueue if there are published popups
        $popups = get_posts(array(
            'post_type'      => 'hsp_popup',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ));

        if (empty($popups)) {
            return;
        }

        wp_enqueue_style(
            'clear-pop-css',
            CLEAR_POP_PLUGIN_URL . 'assets/css/modal.css',
            array(),
            CLEAR_POP_VERSION
        );

        wp_enqueue_script(
            'clear-pop-js',
            CLEAR_POP_PLUGIN_URL . 'assets/js/modal.js',
            array(),
            CLEAR_POP_VERSION,
            true
        );

        // Enqueue triggers.js for automatic display triggers
        wp_enqueue_script(
            'clear-pop-triggers',
            CLEAR_POP_PLUGIN_URL . 'assets/js/triggers.js',
            array('clear-pop-js'),
            CLEAR_POP_VERSION,
            true
        );

        // Localize trigger data
        $trigger_handler = Clear_Pop_Trigger_Handler::get_instance();
        wp_localize_script(
            'clear-pop-triggers',
            'clearPopTriggers',
            $trigger_handler->get_localized_data()
        );

        // Localize AJAX URL
        wp_localize_script(
            'clear-pop-triggers',
            'clearPopAjax',
            array(
                'ajax_url' => admin_url('admin-ajax.php')
            )
        );

        add_action('wp_head', array($this, 'output_salient_popup_css'), 999);
    }
    
    /**
     * Output Salient dynamic CSS for popup content
     */
    public function output_salient_popup_css() {
        $popups = get_posts(array(
            'post_type'      => 'hsp_popup',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));
        
        if (empty($popups)) {
            return;
        }
        
        if (!class_exists('Salient_Core')) {
            return;
        }
        
        if (class_exists('NectarElDynamicStyles')) {
            $dynamic_css = $this->generate_salient_dynamic_css($popups);
            
            if (!empty($dynamic_css)) {
                echo '<style id="clear-pop-salient-css">' . $dynamic_css . '</style>';
            }
            
            return;
        }
        
        $fallback_css = $this->generate_salient_padding_fallback($popups);
        
        if (!empty($fallback_css)) {
            echo '<style id="clear-pop-salient-css">' . $fallback_css . '</style>';
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        if ('hsp_popup' !== $post_type) {
            return;
        }
        
        // Enqueue color picker
        if ('post.php' === $hook || 'post-new.php' === $hook) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        }
    }
    
    /**
     * Ensure Salient's asset detection sees popup content
     */
    public function register_salient_popup_assets() {
        if (is_admin()) {
            return;
        }
        
        if (!class_exists('Salient_Core') || !class_exists('NectarElAssets')) {
            return;
        }
        
        if (!is_string(NectarElAssets::$post_content)) {
            NectarElAssets::$post_content = (string) NectarElAssets::$post_content;
        }
        
        if (!is_array(NectarElAssets::$templatera_content)) {
            NectarElAssets::$templatera_content = array();
        }
        
        $popups = get_posts(array(
            'post_type'      => 'hsp_popup',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));
        
        if (empty($popups)) {
            return;
        }
        
        foreach ($popups as $popup_id) {
            $content = get_post_field('post_content', $popup_id);
            
            if (!is_string($content) || '' === trim($content)) {
                continue;
            }
            
            NectarElAssets::$post_content .= "\n" . $content;
            NectarElAssets::$templatera_content[] = $content;
        }
    }
    
    /**
     * Run Salient dynamic CSS generator for popup content
     *
     * @param array $popup_ids
     * @return string
     */
    private function generate_salient_dynamic_css($popup_ids) {
        $output = '';
        
        foreach ($popup_ids as $popup_id) {
            $content = get_post_field('post_content', $popup_id);
            
            if (!is_string($content) || '' === trim($content)) {
                continue;
            }
            
            if (property_exists('NectarElDynamicStyles', 'element_css')) {
                NectarElDynamicStyles::$element_css = array();
            }
            
            $css = NectarElDynamicStyles::generate_styles($content);
            
            if (!empty($css)) {
                $output .= $this->retarget_salient_css($css);
            }
        }
        
        return $output;
    }
    
    /**
     * Adjust Salient CSS selectors so they apply inside the popup container
     *
     * @param string $css
     * @return string
     */
    private function retarget_salient_css($css) {
        return str_replace('#ajax-content-wrap', '.hsp-popup-content', $css);
    }
    
    /**
     * Minimal padding support when dynamic generator isn't available
     *
     * @param array $popup_ids
     * @return string
     */
    private function generate_salient_padding_fallback($popup_ids) {
        $css = '';

        foreach ($popup_ids as $popup_id) {
            $content = get_post_field('post_content', $popup_id);

            if (!is_string($content) || '' === trim($content)) {
                continue;
            }

            preg_match_all('/\[vc_column([^\]]+)\]/', $content, $matches);

            if (empty($matches[1])) {
                continue;
            }

            foreach ($matches[1] as $attrs) {
                $padding_attrs = array(
                    'left_padding'   => 'padding-left',
                    'right_padding'  => 'padding-right',
                    'top_padding'    => 'padding-top',
                    'bottom_padding' => 'padding-bottom',
                );

                foreach ($padding_attrs as $attr => $css_prop) {
                    if (preg_match('/' . $attr . '="([^"]+)"/', $attrs, $value_match)) {
                        $value = $value_match[1];
                        $class = $attr . '_desktop_' . $value . 'px';
                        $css .= '.hsp-popup-content .' . esc_attr($class) . ' { ' . $css_prop . ': ' . intval($value) . 'px !important; }';
                    }
                }
            }
        }

        return $css;
    }
}
