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
            'clear_pop_triggers',
            __('Display Triggers', 'clear-pop'),
            array($this, 'render_triggers_metabox'),
            'hsp_popup',
            'normal',
            'default'
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
     * Render display triggers metabox
     */
    public function render_triggers_metabox($post) {
        // Get saved values
        $time_delay = get_post_meta($post->ID, '_trigger_time_delay', true);
        $scroll_depth = get_post_meta($post->ID, '_trigger_scroll_depth', true);
        $first_visit = get_post_meta($post->ID, '_trigger_first_visit', true);
        $exit_intent = get_post_meta($post->ID, '_trigger_exit_intent', true);
        $trigger_logic = get_post_meta($post->ID, '_trigger_logic', true) ?: 'any';
        $cookie_duration = get_post_meta($post->ID, '_cookie_duration', true) ?: 'never';

        // Check if multiple triggers enabled (for showing logic selector)
        $enabled_count = 0;
        if (!empty($time_delay) && $time_delay > 0) $enabled_count++;
        if (!empty($scroll_depth) && $scroll_depth > 0) $enabled_count++;
        if ($first_visit === '1') $enabled_count++;
        if ($exit_intent === '1') $enabled_count++;
        ?>
        <style>
            .trigger-settings-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 20px;
                margin-top: 15px;
            }
            .trigger-setting-field {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .trigger-setting-field label {
                font-weight: 600;
                font-size: 13px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .trigger-setting-field input[type="number"] {
                max-width: 150px;
                padding: 6px 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .trigger-setting-field small {
                color: #666;
                font-size: 12px;
                margin-top: -4px;
            }
            .trigger-info-box {
                background: #f0f6fc;
                border-left: 4px solid #0073aa;
                padding: 12px;
                margin-bottom: 20px;
                font-size: 13px;
            }
            .trigger-radio-group {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin-left: 20px;
            }
            .trigger-radio-option {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .trigger-section-divider {
                border-top: 1px solid #ddd;
                margin: 20px 0;
            }
        </style>

        <div class="trigger-info-box">
            <strong><?php _e('Click to Open (Manual Trigger)', 'clear-pop'); ?></strong><br>
            <?php _e('Manual triggers via CSS class (hsp-popup-trigger-{id}) always work regardless of automatic triggers below.', 'clear-pop'); ?>
        </div>

        <div class="trigger-settings-grid">
            <!-- Time Delay -->
            <div class="trigger-setting-field">
                <label>
                    <input type="checkbox" id="enable_time_delay" <?php checked(!empty($time_delay) && $time_delay > 0); ?>>
                    <?php _e('Show automatically after', 'clear-pop'); ?>
                    <input
                        type="number"
                        name="trigger_time_delay"
                        id="trigger_time_delay"
                        min="0"
                        max="120"
                        value="<?php echo esc_attr($time_delay ? $time_delay : 0); ?>"
                        data-default="5"
                        style="width: 70px;"
                    />
                    <?php _e('seconds on page', 'clear-pop'); ?>
                </label>
                <small><?php _e('Range: 0-120 seconds', 'clear-pop'); ?></small>
            </div>

            <!-- Scroll Depth -->
            <div class="trigger-setting-field">
                <label>
                    <input type="checkbox" id="enable_scroll_depth" <?php checked(!empty($scroll_depth) && $scroll_depth > 0); ?>>
                    <?php _e('Show automatically after scrolling', 'clear-pop'); ?>
                    <input
                        type="number"
                        name="trigger_scroll_depth"
                        id="trigger_scroll_depth"
                        min="0"
                        max="100"
                        value="<?php echo esc_attr($scroll_depth ? $scroll_depth : 0); ?>"
                        data-default="50"
                        style="width: 70px;"
                    />
                    <?php _e('% down page', 'clear-pop'); ?>
                </label>
                <small><?php _e('Range: 0-100 percentage', 'clear-pop'); ?></small>
            </div>

            <!-- First Visit -->
            <div class="trigger-setting-field">
                <label>
                    <input type="checkbox" name="trigger_first_visit" id="trigger_first_visit" value="1" <?php checked($first_visit, '1'); ?>>
                    <?php _e('Only show on user\'s first visit to the site', 'clear-pop'); ?>
                </label>
                <small><?php _e('Requires cookies enabled. Combines with other triggers.', 'clear-pop'); ?></small>
            </div>

            <!-- Exit Intent -->
            <div class="trigger-setting-field">
                <label>
                    <input type="checkbox" name="trigger_exit_intent" id="trigger_exit_intent" value="1" <?php checked($exit_intent, '1'); ?>>
                    <?php _e('Show when cursor moves toward browser top (desktop only)', 'clear-pop'); ?>
                </label>
                <small><?php _e('Does not work on mobile devices.', 'clear-pop'); ?></small>
            </div>
        </div>

        <!-- Trigger Logic (only show if 2+ triggers enabled) -->
        <div id="trigger_logic_section" style="<?php echo $enabled_count < 2 ? 'display:none;' : ''; ?>">
            <div class="trigger-section-divider"></div>
            <div class="trigger-setting-field">
                <label><?php _e('Trigger Logic', 'clear-pop'); ?></label>
                <div class="trigger-radio-group">
                    <div class="trigger-radio-option">
                        <input type="radio" name="trigger_logic" id="trigger_logic_any" value="any" <?php checked($trigger_logic, 'any'); ?>>
                        <label for="trigger_logic_any" style="font-weight: normal; margin: 0;">
                            <?php _e('ANY condition is met', 'clear-pop'); ?>
                        </label>
                    </div>
                    <small style="margin-left: 28px; margin-top: -8px;"><?php _e('Popup shows as soon as any one trigger fires', 'clear-pop'); ?></small>

                    <div class="trigger-radio-option">
                        <input type="radio" name="trigger_logic" id="trigger_logic_all" value="all" <?php checked($trigger_logic, 'all'); ?>>
                        <label for="trigger_logic_all" style="font-weight: normal; margin: 0;">
                            <?php _e('ALL conditions are met', 'clear-pop'); ?>
                        </label>
                    </div>
                    <small style="margin-left: 28px; margin-top: -8px;"><?php _e('Popup waits until all enabled triggers have fired', 'clear-pop'); ?></small>
                </div>
            </div>
        </div>

        <div class="trigger-section-divider"></div>

        <!-- Cookie & Frequency -->
        <div class="trigger-setting-field">
            <label><?php _e('Cookie & Frequency', 'clear-pop'); ?></label>
            <small style="margin-top: -4px; margin-bottom: 8px;"><?php _e('Show again after user closes popup:', 'clear-pop'); ?></small>
            <div class="trigger-radio-group">
                <div class="trigger-radio-option">
                    <input type="radio" name="cookie_duration" id="cookie_never" value="never" <?php checked($cookie_duration, 'never'); ?>>
                    <label for="cookie_never" style="font-weight: normal; margin: 0;">
                        <?php _e('Never (user will never see it again)', 'clear-pop'); ?>
                    </label>
                </div>

                <div class="trigger-radio-option">
                    <input type="radio" name="cookie_duration" id="cookie_session" value="session" <?php checked($cookie_duration, 'session'); ?>>
                    <label for="cookie_session" style="font-weight: normal; margin: 0;">
                        <?php _e('Same session (show again on next visit)', 'clear-pop'); ?>
                    </label>
                </div>

                <div class="trigger-radio-option">
                    <input type="radio" name="cookie_duration" id="cookie_1hour" value="1hour" <?php checked($cookie_duration, '1hour'); ?>>
                    <label for="cookie_1hour" style="font-weight: normal; margin: 0;">
                        <?php _e('After 1 hour', 'clear-pop'); ?>
                    </label>
                </div>

                <div class="trigger-radio-option">
                    <input type="radio" name="cookie_duration" id="cookie_24hours" value="24hours" <?php checked($cookie_duration, '24hours'); ?>>
                    <label for="cookie_24hours" style="font-weight: normal; margin: 0;">
                        <?php _e('After 24 hours', 'clear-pop'); ?>
                    </label>
                </div>

                <div class="trigger-radio-option">
                    <input type="radio" name="cookie_duration" id="cookie_7days" value="7days" <?php checked($cookie_duration, '7days'); ?>>
                    <label for="cookie_7days" style="font-weight: normal; margin: 0;">
                        <?php _e('After 7 days', 'clear-pop'); ?>
                    </label>
                </div>

                <div class="trigger-radio-option">
                    <input type="radio" name="cookie_duration" id="cookie_30days" value="30days" <?php checked($cookie_duration, '30days'); ?>>
                    <label for="cookie_30days" style="font-weight: normal; margin: 0;">
                        <?php _e('After 30 days', 'clear-pop'); ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="trigger-section-divider"></div>

        <!-- Trigger Preview -->
        <div class="trigger-setting-field">
            <label><?php _e('Active Triggers Summary', 'clear-pop'); ?></label>
            <div id="trigger-preview" style="padding: 12px; background: #f9f9f9; border-radius: 4px; font-size: 13px; line-height: 1.6;">
                <em><?php _e('Enable triggers above to see summary...', 'clear-pop'); ?></em>
            </div>
        </div>

        <!-- Clear Cookie Button (only show if popup is published) -->
        <?php if ($post->post_status === 'publish') : ?>
        <div class="trigger-section-divider"></div>
        <div class="trigger-setting-field">
            <label><?php _e('Testing Tools', 'clear-pop'); ?></label>
            <button type="button" id="clear_popup_cookie" class="button" style="margin-top: 8px;">
                <?php _e('Clear Cookie for Testing', 'clear-pop'); ?>
            </button>
            <small><?php _e('Clears the cookie so you can test this popup again immediately.', 'clear-pop'); ?></small>
            <div id="clear_cookie_message" style="margin-top: 8px; padding: 8px; display: none; border-radius: 4px;"></div>
        </div>
        <?php endif; ?>

        <script>
            (function() {
                // Handle time delay checkbox
                const timeCheckbox = document.getElementById('enable_time_delay');
                const timeInput = document.getElementById('trigger_time_delay');

                function updateTimeDelay() {
                    if (!timeCheckbox.checked) {
                        const currentValue = timeInput.value;
                        if (currentValue && currentValue !== '0') {
                            timeInput.setAttribute('data-prev-value', currentValue);
                        }
                        timeInput.value = '0';
                    } else {
                        const prevValue = timeInput.getAttribute('data-prev-value') || timeInput.getAttribute('data-default') || '5';
                        timeInput.value = prevValue;
                    }
                    updateLogicVisibility();
                }

                if (timeCheckbox) {
                    timeCheckbox.addEventListener('change', updateTimeDelay);
                }

                // Handle scroll depth checkbox
                const scrollCheckbox = document.getElementById('enable_scroll_depth');
                const scrollInput = document.getElementById('trigger_scroll_depth');

                function updateScrollDepth() {
                    if (!scrollCheckbox.checked) {
                        const currentValue = scrollInput.value;
                        if (currentValue && currentValue !== '0') {
                            scrollInput.setAttribute('data-prev-value', currentValue);
                        }
                        scrollInput.value = '0';
                    } else {
                        const prevValue = scrollInput.getAttribute('data-prev-value') || scrollInput.getAttribute('data-default') || '50';
                        scrollInput.value = prevValue;
                    }
                    updateLogicVisibility();
                }

                if (scrollCheckbox) {
                    scrollCheckbox.addEventListener('change', updateScrollDepth);
                }

                // Update logic section visibility based on enabled triggers
                function updateLogicVisibility() {
                    let enabledCount = 0;

                    if (timeCheckbox && timeCheckbox.checked && parseInt(timeInput.value) > 0) enabledCount++;
                    if (scrollCheckbox && scrollCheckbox.checked && parseInt(scrollInput.value) > 0) enabledCount++;

                    const firstVisit = document.getElementById('trigger_first_visit');
                    if (firstVisit && firstVisit.checked) enabledCount++;

                    const exitIntent = document.getElementById('trigger_exit_intent');
                    if (exitIntent && exitIntent.checked) enabledCount++;

                    const logicSection = document.getElementById('trigger_logic_section');
                    if (logicSection) {
                        logicSection.style.display = enabledCount >= 2 ? 'block' : 'none';
                    }
                }

                // Attach change listeners to all trigger checkboxes
                const firstVisit = document.getElementById('trigger_first_visit');
                const exitIntent = document.getElementById('trigger_exit_intent');

                if (firstVisit) firstVisit.addEventListener('change', updateLogicVisibility);
                if (exitIntent) exitIntent.addEventListener('change', updateLogicVisibility);

                // Initialize on page load
                updateLogicVisibility();

                // Update trigger preview
                function updateTriggerPreview() {
                    const preview = document.getElementById('trigger-preview');
                    if (!preview) return;

                    const triggers = [];

                    if (timeCheckbox && timeCheckbox.checked && parseInt(timeInput.value) > 0) {
                        triggers.push('After <strong>' + timeInput.value + ' seconds</strong> on page');
                    }

                    if (scrollCheckbox && scrollCheckbox.checked && parseInt(scrollInput.value) > 0) {
                        triggers.push('After scrolling <strong>' + scrollInput.value + '%</strong> down page');
                    }

                    const firstVisit = document.getElementById('trigger_first_visit');
                    if (firstVisit && firstVisit.checked) {
                        triggers.push('On <strong>first visit only</strong>');
                    }

                    const exitIntent = document.getElementById('trigger_exit_intent');
                    if (exitIntent && exitIntent.checked) {
                        triggers.push('When cursor moves toward <strong>browser top</strong> (desktop)');
                    }

                    if (triggers.length === 0) {
                        preview.innerHTML = '<em>This popup uses manual triggers only (click to open).</em>';
                        return;
                    }

                    // Get logic
                    const logicAny = document.getElementById('trigger_logic_any');
                    const logic = (logicAny && logicAny.checked) ? 'ANY' : 'ALL';
                    const logicText = triggers.length > 1
                        ? '<strong>' + logic + '</strong> of the following:<br>'
                        : '';

                    preview.innerHTML = 'This popup will automatically show when ' + logicText +
                        '<ul style="margin: 8px 0 0 20px;">' +
                        triggers.map(t => '<li>' + t + '</li>').join('') +
                        '</ul>';
                }

                // Attach listeners for preview updates
                if (timeCheckbox) timeCheckbox.addEventListener('change', updateTriggerPreview);
                if (timeInput) timeInput.addEventListener('input', updateTriggerPreview);
                if (scrollCheckbox) scrollCheckbox.addEventListener('change', updateTriggerPreview);
                if (scrollInput) scrollInput.addEventListener('input', updateTriggerPreview);
                const firstVisit = document.getElementById('trigger_first_visit');
                if (firstVisit) firstVisit.addEventListener('change', updateTriggerPreview);
                const exitIntent = document.getElementById('trigger_exit_intent');
                if (exitIntent) exitIntent.addEventListener('change', updateTriggerPreview);
                const logicRadios = document.querySelectorAll('input[name="trigger_logic"]');
                logicRadios.forEach(radio => radio.addEventListener('change', updateTriggerPreview));

                // Initialize preview
                updateTriggerPreview();

                // Clear cookie button handler
                const clearCookieBtn = document.getElementById('clear_popup_cookie');
                if (clearCookieBtn) {
                    clearCookieBtn.addEventListener('click', function() {
                        const postId = <?php echo absint($post->ID); ?>;
                        const messageDiv = document.getElementById('clear_cookie_message');

                        clearCookieBtn.disabled = true;
                        clearCookieBtn.textContent = 'Clearing...';

                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=clear_pop_cookie&popup_id=' + postId + '&nonce=<?php echo wp_create_nonce('clear_pop_cookie_' . $post->ID); ?>'
                        })
                        .then(response => response.json())
                        .then(data => {
                            clearCookieBtn.disabled = false;
                            clearCookieBtn.textContent = 'Clear Cookie for Testing';

                            if (data.success) {
                                messageDiv.style.display = 'block';
                                messageDiv.style.background = '#d4edda';
                                messageDiv.style.color = '#155724';
                                messageDiv.style.border = '1px solid #c3e6cb';
                                messageDiv.textContent = 'Cookie cleared successfully! You can now test this popup again.';

                                setTimeout(() => {
                                    messageDiv.style.display = 'none';
                                }, 5000);
                            } else {
                                messageDiv.style.display = 'block';
                                messageDiv.style.background = '#f8d7da';
                                messageDiv.style.color = '#721c24';
                                messageDiv.style.border = '1px solid #f5c6cb';
                                messageDiv.textContent = 'Error: ' + (data.data || 'Failed to clear cookie');

                                setTimeout(() => {
                                    messageDiv.style.display = 'none';
                                }, 5000);
                            }
                        })
                        .catch(error => {
                            clearCookieBtn.disabled = false;
                            clearCookieBtn.textContent = 'Clear Cookie for Testing';

                            messageDiv.style.display = 'block';
                            messageDiv.style.background = '#f8d7da';
                            messageDiv.style.color = '#721c24';
                            messageDiv.style.border = '1px solid #f5c6cb';
                            messageDiv.textContent = 'Error: ' + error.message;
                        });
                    });
                }
            })();
        </script>
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

        // Save popup settings fields
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

        // Save trigger settings
        // Time Delay (0 = disabled)
        if (isset($_POST['trigger_time_delay'])) {
            $time_delay = absint($_POST['trigger_time_delay']);
            if ($time_delay > 120) {
                $time_delay = 120;
            }
            update_post_meta($post_id, '_trigger_time_delay', $time_delay);
        }

        // Scroll Depth (0 = disabled)
        if (isset($_POST['trigger_scroll_depth'])) {
            $scroll_depth = absint($_POST['trigger_scroll_depth']);
            if ($scroll_depth > 100) {
                $scroll_depth = 100;
            }
            update_post_meta($post_id, '_trigger_scroll_depth', $scroll_depth);
        }

        // First Visit (checkbox)
        $first_visit = isset($_POST['trigger_first_visit']) ? '1' : '';
        update_post_meta($post_id, '_trigger_first_visit', $first_visit);

        // Exit Intent (checkbox)
        $exit_intent = isset($_POST['trigger_exit_intent']) ? '1' : '';
        update_post_meta($post_id, '_trigger_exit_intent', $exit_intent);

        // Trigger Logic
        if (isset($_POST['trigger_logic'])) {
            $trigger_logic = sanitize_text_field($_POST['trigger_logic']);
            $allowed_logic = array('any', 'all');
            if (in_array($trigger_logic, $allowed_logic, true)) {
                update_post_meta($post_id, '_trigger_logic', $trigger_logic);
            }
        }

        // Cookie Duration
        if (isset($_POST['cookie_duration'])) {
            $cookie_duration = sanitize_text_field($_POST['cookie_duration']);
            $allowed_durations = array('never', 'session', '1hour', '24hours', '7days', '30days');
            if (in_array($cookie_duration, $allowed_durations, true)) {
                update_post_meta($post_id, '_cookie_duration', $cookie_duration);
            }
        }
    }
}
