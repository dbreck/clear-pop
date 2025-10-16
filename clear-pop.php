<?php
/**
 * Plugin Name: Clear Pop
 * Plugin URI: https://clearph.com
 * Description: Clean, simple popup modal system for WordPress with WPBakery support
 * Version: 1.1.0
 * Author: Danny Breckenridge
 * Author URI: https://clearph.com
 * Text Domain: clear-pop
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CLEAR_POP_VERSION', '1.1.0');
define('CLEAR_POP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CLEAR_POP_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Clear_Pop_Plugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once CLEAR_POP_PLUGIN_DIR . 'includes/class-post-type.php';
        require_once CLEAR_POP_PLUGIN_DIR . 'includes/class-metabox.php';
        require_once CLEAR_POP_PLUGIN_DIR . 'includes/class-modal-renderer.php';
        require_once CLEAR_POP_PLUGIN_DIR . 'includes/class-assets.php';
        require_once CLEAR_POP_PLUGIN_DIR . 'includes/class-cookie-manager.php';
        require_once CLEAR_POP_PLUGIN_DIR . 'includes/class-trigger-handler.php';
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        Clear_Pop_Post_Type::get_instance();
        Clear_Pop_Metabox::get_instance();
        Clear_Pop_Modal_Renderer::get_instance();
        Clear_Pop_Assets::get_instance();
        Clear_Pop_Trigger_Handler::get_instance();
    }
}

// Initialize plugin
function clear_pop_init() {
    Clear_Pop_Plugin::get_instance();
}
add_action('plugins_loaded', 'clear_pop_init', 5);  // Load early, before WPBakery initializes

// Activation hook - flush rewrite rules and enable WPBakery
function clear_pop_activate() {
    // Load the post type class
    require_once CLEAR_POP_PLUGIN_DIR . 'includes/class-post-type.php';
    // Register the post type
    Clear_Pop_Post_Type::get_instance()->register_post_type();
    // Flush rewrite rules
    flush_rewrite_rules();

    // Enable WPBakery support by updating WPBakery settings
    if (function_exists('vc_set_default_editor_post_types')) {
        // Get existing enabled post types
        $current_types = get_option('wpb_js_content_types', array());
        if (!is_array($current_types)) {
            $current_types = array();
        }

        // Add our post type if not already present
        if (!in_array('hsp_popup', $current_types)) {
            $current_types[] = 'hsp_popup';
            update_option('wpb_js_content_types', $current_types);
        }
    }
}
register_activation_hook(__FILE__, 'clear_pop_activate');

// Deactivation hook - flush rewrite rules
function clear_pop_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'clear_pop_deactivate');
