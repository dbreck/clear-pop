<?php
/**
 * Cookie Manager
 *
 * Handles cookie-based popup view tracking and frequency control
 *
 * @package Clear_Pop
 * @since 1.2.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cookie Manager Class
 */
class Clear_Pop_Cookie_Manager {

    private static $instance = null;

    /**
     * Cookie prefix for all popup cookies
     */
    const COOKIE_PREFIX = 'clear_pop_';

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Private constructor for singleton
    }

    /**
     * Check if popup should be shown based on cookie and duration setting
     *
     * @param int    $popup_id         Popup post ID
     * @param string $duration_setting Duration setting (never, session, 1hour, 24hours, 7days, 30days)
     * @return bool True if popup should be shown, false if suppressed by cookie
     */
    public function should_show_popup($popup_id, $duration_setting) {
        $cookie_name = self::COOKIE_PREFIX . $popup_id;

        // No cookie set = show popup
        if (!isset($_COOKIE[$cookie_name])) {
            return true;
        }

        // Parse cookie data
        $cookie_data = json_decode(stripslashes($_COOKIE[$cookie_name]), true);

        // Invalid cookie format = show popup (cookie corrupted or tampered)
        if (!is_array($cookie_data) || !isset($cookie_data['last_shown'])) {
            return true;
        }

        // Check duration logic
        switch ($duration_setting) {
            case 'never':
                // Never show again once cookie is set
                return false;

            case 'session':
                // Session cookies cleared on browser close, so if cookie exists = same session
                return false;

            case '1hour':
                return $this->check_time_elapsed($cookie_data['last_shown'], HOUR_IN_SECONDS);

            case '24hours':
                return $this->check_time_elapsed($cookie_data['last_shown'], DAY_IN_SECONDS);

            case '7days':
                return $this->check_time_elapsed($cookie_data['last_shown'], 7 * DAY_IN_SECONDS);

            case '30days':
                return $this->check_time_elapsed($cookie_data['last_shown'], 30 * DAY_IN_SECONDS);

            default:
                // Unknown duration = show popup (fail safe)
                return true;
        }
    }

    /**
     * Check if enough time has elapsed since last shown
     *
     * @param int $last_shown Timestamp of last shown
     * @param int $duration   Required duration in seconds
     * @return bool True if duration has elapsed
     */
    private function check_time_elapsed($last_shown, $duration) {
        $current_time = time();
        $elapsed = $current_time - $last_shown;

        return $elapsed >= $duration;
    }

    /**
     * Record popup as shown
     *
     * @param int $popup_id Popup post ID
     */
    public function record_popup_shown($popup_id) {
        $cookie_name = self::COOKIE_PREFIX . $popup_id;

        // Get existing cookie data or create new
        $cookie_data = $this->get_cookie_data($popup_id);

        // Update shown count and timestamp
        $cookie_data['shown_count'] = isset($cookie_data['shown_count']) ? $cookie_data['shown_count'] + 1 : 1;
        $cookie_data['last_shown'] = time();

        // Store as JSON
        $cookie_value = json_encode($cookie_data);

        // Set cookie (1 year expiry for persistent tracking)
        setcookie(
            $cookie_name,
            $cookie_value,
            time() + (365 * DAY_IN_SECONDS),
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true // HTTP only
        );

        // Update cookie superglobal for same-request access
        $_COOKIE[$cookie_name] = $cookie_value;
    }

    /**
     * Record popup close event with method
     *
     * @param int    $popup_id Popup post ID
     * @param string $method   Close method (x_button, overlay, escape)
     */
    public function record_popup_closed($popup_id, $method) {
        $cookie_name = self::COOKIE_PREFIX . $popup_id;
        $cookie_data = $this->get_cookie_data($popup_id);

        // Update close information
        $cookie_data['closed_method'] = sanitize_text_field($method);
        $cookie_data['last_closed'] = time();

        // Get duration setting from post meta
        $duration_setting = get_post_meta($popup_id, '_cookie_duration', true);
        if (empty($duration_setting)) {
            $duration_setting = 'never'; // Default
        }

        // Calculate cookie expiry based on duration
        $expiry = $this->get_cookie_expiry($duration_setting);

        // Store as JSON
        $cookie_value = json_encode($cookie_data);

        // Set cookie with appropriate expiry
        setcookie(
            $cookie_name,
            $cookie_value,
            $expiry,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true // HTTP only
        );

        // Update cookie superglobal for same-request access
        $_COOKIE[$cookie_name] = $cookie_value;
    }

    /**
     * Get cookie expiry timestamp based on duration setting
     *
     * @param string $duration_setting Duration setting
     * @return int Timestamp for cookie expiry (0 for session)
     */
    private function get_cookie_expiry($duration_setting) {
        switch ($duration_setting) {
            case 'never':
                // 10 years
                return time() + (10 * 365 * DAY_IN_SECONDS);

            case 'session':
                // Session cookie (expires when browser closes)
                return 0;

            case '1hour':
                return time() + HOUR_IN_SECONDS;

            case '24hours':
                return time() + DAY_IN_SECONDS;

            case '7days':
                return time() + (7 * DAY_IN_SECONDS);

            case '30days':
                return time() + (30 * DAY_IN_SECONDS);

            default:
                // Default to 1 year for unknown settings
                return time() + (365 * DAY_IN_SECONDS);
        }
    }

    /**
     * Get existing cookie data or empty array
     *
     * @param int $popup_id Popup post ID
     * @return array Cookie data
     */
    private function get_cookie_data($popup_id) {
        $cookie_name = self::COOKIE_PREFIX . $popup_id;

        if (!isset($_COOKIE[$cookie_name])) {
            return array();
        }

        $cookie_data = json_decode(stripslashes($_COOKIE[$cookie_name]), true);

        return is_array($cookie_data) ? $cookie_data : array();
    }

    /**
     * Clear popup cookie (for testing)
     *
     * @param int $popup_id Popup post ID
     */
    public function clear_popup_cookie($popup_id) {
        $cookie_name = self::COOKIE_PREFIX . $popup_id;

        // Delete cookie by setting expiry in the past
        setcookie(
            $cookie_name,
            '',
            time() - 3600,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );

        // Remove from superglobal
        unset($_COOKIE[$cookie_name]);
    }
}
