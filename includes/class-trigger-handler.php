<?php
/**
 * Trigger Handler - Manages automatic display triggers
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Clear_Pop_Trigger_Handler {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // AJAX handlers for close tracking
        add_action('wp_ajax_clear_pop_close', array($this, 'handle_popup_close'));
        add_action('wp_ajax_nopriv_clear_pop_close', array($this, 'handle_popup_close'));

        // AJAX handler for clearing cookies (admin only)
        add_action('wp_ajax_clear_pop_cookie', array($this, 'handle_clear_cookie'));
    }

    /**
     * Get localized trigger data for JavaScript
     *
     * @return array
     */
    public function get_localized_data() {
        $trigger_data = array();

        $popups = get_posts(array(
            'post_type'      => 'hsp_popup',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));

        if (empty($popups)) {
            return $trigger_data;
        }

        foreach ($popups as $popup_id) {
            $time_delay = absint(get_post_meta($popup_id, '_trigger_time_delay', true));
            $scroll_depth = absint(get_post_meta($popup_id, '_trigger_scroll_depth', true));
            $first_visit = get_post_meta($popup_id, '_trigger_first_visit', true);
            $exit_intent = get_post_meta($popup_id, '_trigger_exit_intent', true);
            $trigger_logic = get_post_meta($popup_id, '_trigger_logic', true) ?: 'any';
            $cookie_duration = get_post_meta($popup_id, '_cookie_duration', true) ?: 'never';

            // Only add to localized data if at least one trigger is enabled
            if ($time_delay > 0 || $scroll_depth > 0 || $first_visit === '1' || $exit_intent === '1') {
                $trigger_data[$popup_id] = array(
                    'time_delay'      => $time_delay,
                    'scroll_depth'    => $scroll_depth,
                    'first_visit'     => $first_visit === '1',
                    'exit_intent'     => $exit_intent === '1',
                    'logic'           => $trigger_logic,
                    'cookie_duration' => $cookie_duration,
                );
            }
        }

        return $trigger_data;
    }

    /**
     * Handle AJAX request when popup is closed
     */
    public function handle_popup_close() {
        // No nonce check - this is a fire-and-forget tracking endpoint
        // Worst case: someone sets a cookie on their own browser

        $popup_id = isset($_POST['popup_id']) ? absint($_POST['popup_id']) : 0;
        $close_method = isset($_POST['close_method']) ? sanitize_text_field($_POST['close_method']) : 'unknown';
        $cookie_duration = isset($_POST['cookie_duration']) ? sanitize_text_field($_POST['cookie_duration']) : 'never';

        if (!$popup_id) {
            wp_send_json_error('Invalid popup ID');
            return;
        }

        // Validate cookie duration
        $allowed_durations = array('never', 'session', '1hour', '24hours', '7days', '30days');
        if (!in_array($cookie_duration, $allowed_durations, true)) {
            $cookie_duration = 'never';
        }

        // Get cookie manager
        $cookie_manager = Clear_Pop_Cookie_Manager::get_instance();

        // Record popup closed
        $cookie_manager->record_popup_closed($popup_id, $close_method, $cookie_duration);

        wp_send_json_success(array(
            'popup_id'     => $popup_id,
            'close_method' => $close_method,
            'duration'     => $cookie_duration,
        ));
    }

    /**
     * Check if popup should be rendered based on cookies
     *
     * @param int $popup_id
     * @return bool
     */
    public function should_render_popup($popup_id) {
        $cookie_manager = Clear_Pop_Cookie_Manager::get_instance();
        $cookie_duration = get_post_meta($popup_id, '_cookie_duration', true) ?: 'never';

        return $cookie_manager->should_show_popup($popup_id, $cookie_duration);
    }

    /**
     * Handle AJAX request to clear popup cookie (admin only)
     */
    public function handle_clear_cookie() {
        // Verify nonce
        $popup_id = isset($_POST['popup_id']) ? absint($_POST['popup_id']) : 0;

        if (!$popup_id) {
            wp_send_json_error('Invalid popup ID');
            return;
        }

        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (!wp_verify_nonce($nonce, 'clear_pop_cookie_' . $popup_id)) {
            wp_send_json_error('Security check failed');
            return;
        }

        // Check user capability
        if (!current_user_can('edit_post', $popup_id)) {
            wp_send_json_error('Permission denied');
            return;
        }

        // Clear the cookie
        $cookie_manager = Clear_Pop_Cookie_Manager::get_instance();
        $cookie_manager->clear_popup_cookie($popup_id);

        wp_send_json_success(array(
            'message' => 'Cookie cleared successfully',
            'popup_id' => $popup_id,
        ));
    }
}
