<?php
/**
 * Post Type Registration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Clear_Pop_Post_Type {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('vc_before_init', array($this, 'enable_wpbakery_support'));
        add_filter('vc_check_post_type_validation', array($this, 'allow_wpbakery_on_popup'), 10, 2);
    }
    
    /**
     * Register the popup post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Popups', 'Post Type General Name', 'clear-pop'),
            'singular_name'      => _x('Popup', 'Post Type Singular Name', 'clear-pop'),
            'menu_name'          => __('Popups', 'clear-pop'),
            'all_items'          => __('All Popups', 'clear-pop'),
            'view_item'          => __('View Popup', 'clear-pop'),
            'add_new_item'       => __('Add New Popup', 'clear-pop'),
            'add_new'            => __('Add New', 'clear-pop'),
            'edit_item'          => __('Edit Popup', 'clear-pop'),
            'update_item'        => __('Update Popup', 'clear-pop'),
            'search_items'       => __('Search Popups', 'clear-pop'),
            'not_found'          => __('No popups found', 'clear-pop'),
            'not_found_in_trash' => __('No popups found in Trash', 'clear-pop'),
        );
        
        $args = array(
            'label'               => __('Popup', 'clear-pop'),
            'description'         => __('Popup modals', 'clear-pop'),
            'labels'              => $labels,
            'supports'            => array('title', 'editor', 'revisions'),
            'hierarchical'        => false,
            'public'              => true,  // Must be true for WPBakery Role Manager to see it
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-welcome-view-site',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,  // Keeps popups out of front-end queries
            'capability_type'     => 'post',
            'show_in_rest'        => false,
        );
        
        register_post_type('hsp_popup', $args);
    }

    /**
     * Enable WPBakery Page Builder support for popup post type
     */
    public function enable_wpbakery_support() {
        if (function_exists('vc_set_default_editor_post_types')) {
            // Get existing post types that have WPBakery enabled
            $current_post_types = vc_default_editor_post_types();

            // Add our popup post type if it's not already in the list
            if (!in_array('hsp_popup', $current_post_types)) {
                $current_post_types[] = 'hsp_popup';
            }

            // Update the list
            vc_set_default_editor_post_types($current_post_types);
        }
    }

    /**
     * Allow WPBakery to work on popup post type
     */
    public function allow_wpbakery_on_popup($validation, $type) {
        if ($type === 'hsp_popup') {
            return true;
        }
        return $validation;
    }
}
