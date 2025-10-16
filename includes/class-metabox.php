<?php
/**
 * Metabox for Popup Settings
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Clear_Pop_Metabox {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_metabox'));
        add_action('save_post_hsp_popup', array($this, 'save_metabox'), 10, 2);
    }
    
    /**
     * Add metabox
     */
    public function add_metabox() {
        add_meta_box(
            'clear_pop_settings',
            __('Popup Settings', 'clear-pop'),
            array($this, 'render_metabox'),
            'hsp_popup',
            'normal',
            'high'
        );
        
        add_meta_box(
            'clear_pop_trigger_class',
            __('Trigger Class', 'clear-pop'),
            array($this, 'render_trigger_metabox'),
            'hsp_popup',
            'side',
            'default'
        );
    }
    
    /**
     * Render metabox
     */
    public function render_metabox($post) {
        wp_nonce_field('clear_pop_metabox', 'clear_pop_nonce');
        
        // Get saved values
        $size = get_post_meta($post->ID, '_popup_size', true) ?: 'medium';
        $bg_color = get_post_meta($post->ID, '_popup_bg_color', true) ?: '#000000';
        $bg_opacity = get_post_meta($post->ID, '_popup_bg_opacity', true) ?: '0.8';
        $close_position = get_post_meta($post->ID, '_popup_close_position', true) ?: 'top-right';
        $close_style = get_post_meta($post->ID, '_popup_close_style', true) ?: 'light';
        $close_border = get_post_meta($post->ID, '_popup_close_border', true);
        $content_padding = get_post_meta($post->ID, '_popup_content_padding', true) ?: 'default';
        $border_radius_value = get_post_meta($post->ID, '_popup_border_radius_value', true);
        $border_radius_unit = get_post_meta($post->ID, '_popup_border_radius_unit', true) ?: 'px';
        
        ?>
        <style>
            .popup-settings-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin-top: 15px;
            }
            .popup-setting-field {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .popup-setting-field label {
                font-weight: 600;
                font-size: 13px;
            }
            .popup-setting-field select,
            .popup-setting-field input[type="text"],
            .popup-setting-field input[type="number"] {
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .popup-setting-field small {
                color: #666;
                font-size: 12px;
            }
            .color-opacity-group {
                display: grid;
                grid-template-columns: 1fr 120px;
                gap: 10px;
            }
        </style>
        
        <div class="popup-settings-grid">
            <div class="popup-setting-field">
                <label for="popup_size"><?php _e('Modal Size', 'clear-pop'); ?></label>
                <select name="popup_size" id="popup_size">
                    <option value="small" <?php selected($size, 'small'); ?>><?php _e('Small (400px)', 'clear-pop'); ?></option>
                    <option value="medium" <?php selected($size, 'medium'); ?>><?php _e('Medium (600px)', 'clear-pop'); ?></option>
                    <option value="large" <?php selected($size, 'large'); ?>><?php _e('Large (800px)', 'clear-pop'); ?></option>
                    <option value="xlarge" <?php selected($size, 'xlarge'); ?>><?php _e('Extra Large (1000px)', 'clear-pop'); ?></option>
                    <option value="fullwidth" <?php selected($size, 'fullwidth'); ?>><?php _e('Full Width (90vw)', 'clear-pop'); ?></option>
                    <option value="fullscreen" <?php selected($size, 'fullscreen'); ?>><?php _e('Full Screen (96vw × 96vh)', 'clear-pop'); ?></option>
                </select>
                <small><?php _e('Width of the modal window', 'clear-pop'); ?></small>
            </div>
            
            <div class="popup-setting-field">
                <label for="popup_close_position"><?php _e('Close Button Position', 'clear-pop'); ?></label>
                <select name="popup_close_position" id="popup_close_position">
                    <option value="top-right" <?php selected($close_position, 'top-right'); ?>><?php _e('Top Right', 'clear-pop'); ?></option>
                    <option value="top-left" <?php selected($close_position, 'top-left'); ?>><?php _e('Top Left', 'clear-pop'); ?></option>
                    <option value="bottom-right" <?php selected($close_position, 'bottom-right'); ?>><?php _e('Bottom Right', 'clear-pop'); ?></option>
                    <option value="bottom-left" <?php selected($close_position, 'bottom-left'); ?>><?php _e('Bottom Left', 'clear-pop'); ?></option>
                </select>
                <small><?php _e('Position of the close button', 'clear-pop'); ?></small>
            </div>
            
            <div class="popup-setting-field">
                <label><?php _e('Background Color & Opacity', 'clear-pop'); ?></label>
                <div class="color-opacity-group">
                    <input type="text" name="popup_bg_color" id="popup_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="popup-color-picker" />
                    <input type="number" name="popup_bg_opacity" id="popup_bg_opacity" value="<?php echo esc_attr($bg_opacity); ?>" min="0" max="1" step="0.1" />
                </div>
                <small><?php _e('Overlay background color and opacity (0-1)', 'clear-pop'); ?></small>
            </div>
            
            <div class="popup-setting-field">
                <label for="popup_close_style"><?php _e('Close Button Style', 'clear-pop'); ?></label>
                <select name="popup_close_style" id="popup_close_style">
                    <option value="light" <?php selected($close_style, 'light'); ?>><?php _e('Light (White)', 'clear-pop'); ?></option>
                    <option value="dark" <?php selected($close_style, 'dark'); ?>><?php _e('Dark (Black)', 'clear-pop'); ?></option>
                </select>
                <small><?php _e('Color theme for close button', 'clear-pop'); ?></small>
            </div>

            <div class="popup-setting-field">
                <label for="popup_close_border">
                    <input type="checkbox" name="popup_close_border" id="popup_close_border" value="1" <?php checked($close_border, '1'); ?> />
                    <?php _e('Add Border to Close Button', 'clear-pop'); ?>
                </label>
                <small><?php _e('Adds a 1px solid border around the close button', 'clear-pop'); ?></small>
            </div>
            
            <div class="popup-setting-field">
                <label for="popup_content_padding"><?php _e('Popup Padding', 'clear-pop'); ?></label>
                <select name="popup_content_padding" id="popup_content_padding">
                    <option value="default" <?php selected($content_padding, 'default'); ?>><?php _e('Default Padding', 'clear-pop'); ?></option>
                    <option value="none" <?php selected($content_padding, 'none'); ?>><?php _e('Edge to Edge (No Padding)', 'clear-pop'); ?></option>
                </select>
                <small><?php _e('Control white space around popup content.', 'clear-pop'); ?></small>
            </div>
            
            <div class="popup-setting-field">
                <label for="popup_border_radius_value"><?php _e('Border Radius', 'clear-pop'); ?></label>
                <div style="display: grid; grid-template-columns: 1fr 110px; gap: 10px;">
                    <input
                        type="number"
                        name="popup_border_radius_value"
                        id="popup_border_radius_value"
                        min="0"
                        step="0.5"
                        placeholder="<?php esc_attr_e('Default', 'clear-pop'); ?>"
                        value="<?php echo esc_attr($border_radius_value); ?>"
                    />
                    <select name="popup_border_radius_unit" id="popup_border_radius_unit">
                        <?php
                        $radius_units = array('px', 'rem', 'em', 'vw', '%');
                        foreach ($radius_units as $unit) {
                            printf(
                                '<option value="%1$s" %2$s>%1$s</option>',
                                esc_attr($unit),
                                selected($border_radius_unit, $unit, false)
                            );
                        }
                        ?>
                    </select>
                </div>
                <small><?php _e('Leave blank to use theme default (8px).', 'clear-pop'); ?></small>
            </div>
        </div>
        
        <script>
            jQuery(document).ready(function($) {
                $('.popup-color-picker').wpColorPicker();
            });
        </script>
        <?php
    }
    
    /**
     * Render trigger class metabox
     */
    public function render_trigger_metabox($post) {
        if ('hsp_popup' !== $post->post_type) {
            return;
        }
        
        if (empty($post->ID) || 'auto-draft' === $post->post_status) {
            echo '<p>' . esc_html__('Save this popup to generate its trigger class.', 'clear-pop') . '</p>';
            return;
        }
        
        $trigger_class = 'hsp-popup-trigger-' . $post->ID;
        ?>
        <p><?php esc_html_e('Add this CSS class to any button or link to trigger the popup:', 'clear-pop'); ?></p>
        <input
            type="text"
            readonly
            value="<?php echo esc_attr($trigger_class); ?>"
            style="width: 100%;"
            onclick="this.select();"
        />
        <p style="margin-top: 8px;"><?php esc_html_e('Works everywhere you can set an extra class (WPBakery, menus, etc.).', 'clear-pop'); ?></p>
        <?php
    }
    
    /**
     * Save metabox
     */
    public function save_metabox($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['clear_pop_nonce']) || !wp_verify_nonce($_POST['clear_pop_nonce'], 'clear_pop_metabox')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save fields
        $fields = array(
            'popup_size',
            'popup_bg_color',
            'popup_bg_opacity',
            'popup_close_position',
            'popup_close_style',
            'popup_close_border',
            'popup_content_padding',
            'popup_border_radius_value',
            'popup_border_radius_unit',
        );
        
        foreach ($fields as $field) {
            // Handle checkbox separately
            if ('popup_close_border' === $field) {
                $value = isset($_POST[$field]) ? '1' : '';
                update_post_meta($post_id, '_' . $field, $value);
                continue;
            }

            if (!isset($_POST[$field])) {
                continue;
            }

            if ('popup_border_radius_value' === $field) {
                $raw = trim(wp_unslash($_POST[$field]));
                
                if ($raw === '') {
                    delete_post_meta($post_id, '_' . $field);
                    continue;
                }
                
                $number = floatval($raw);
                
                if ($number < 0) {
                    $number = 0;
                }
                
                update_post_meta($post_id, '_' . $field, $number);
                continue;
            }
            
            $value = sanitize_text_field($_POST[$field]);
            
            if ('popup_content_padding' === $field) {
                $allowed = array('default', 'none');
                if (!in_array($value, $allowed, true)) {
                    continue;
                }
            }
            
            if ('popup_border_radius_unit' === $field) {
                $allowed_units = array('px', 'rem', 'em', 'vw', '%');
                if (!in_array($value, $allowed_units, true)) {
                    continue;
                }
            }
            
            update_post_meta($post_id, '_' . $field, $value);
        }
    }
}
