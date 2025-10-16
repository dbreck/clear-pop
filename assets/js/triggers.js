/**
 * Clear Pop - Automatic Trigger System
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    /**
     * Initialize automatic triggers
     */
    function init() {
        // Ensure trigger data is available
        if (typeof window.clearPopTriggers === 'undefined') {
            return;
        }

        // Process each popup with automatic triggers
        Object.keys(window.clearPopTriggers).forEach(function(popupId) {
            const config = window.clearPopTriggers[popupId];
            initPopupTriggers(popupId, config);
        });

        // Set up close tracking for all popups
        setupCloseTracking();
    }

    /**
     * Initialize triggers for a specific popup
     */
    function initPopupTriggers(popupId, config) {
        // Check cookie - skip if already shown and still within duration
        if (!shouldShowPopup(popupId, config.cookie_duration)) {
            return;
        }

        // First visit check - if enabled and not first visit, skip entirely
        if (config.first_visit && !isFirstVisit()) {
            return;
        }

        // Track which triggers have fired
        const triggerState = {
            time_delay: false,
            scroll_depth: false,
            first_visit: false,
            exit_intent: false
        };

        // Track which triggers are enabled
        const enabledTriggers = [];
        if (config.time_delay > 0) enabledTriggers.push('time_delay');
        if (config.scroll_depth > 0) enabledTriggers.push('scroll_depth');
        if (config.first_visit) enabledTriggers.push('first_visit');
        if (config.exit_intent) enabledTriggers.push('exit_intent');

        // If no triggers enabled, nothing to do
        if (enabledTriggers.length === 0) {
            return;
        }

        // Mark first_visit as automatically fired if enabled (it's a condition, not a trigger)
        if (config.first_visit) {
            triggerState.first_visit = true;
        }

        /**
         * Check if popup should open based on logic
         */
        function evaluateLogic() {
            if (config.logic === 'all') {
                // ALL: Every enabled trigger must have fired
                const allFired = enabledTriggers.every(function(trigger) {
                    return triggerState[trigger] === true;
                });
                if (allFired) {
                    openPopup(popupId);
                }
            } else {
                // ANY: At least one trigger has fired
                const anyFired = enabledTriggers.some(function(trigger) {
                    return triggerState[trigger] === true;
                });
                if (anyFired) {
                    openPopup(popupId);
                }
            }
        }

        /**
         * Mark a trigger as fired and evaluate logic
         */
        function triggerFired(triggerName) {
            if (triggerState[triggerName] !== undefined) {
                triggerState[triggerName] = true;
                evaluateLogic();
            }
        }

        // Initialize each enabled trigger
        if (config.time_delay > 0) {
            initTimeDelay(config.time_delay, triggerFired);
        }

        if (config.scroll_depth > 0) {
            initScrollDepth(config.scroll_depth, triggerFired);
        }

        if (config.exit_intent) {
            initExitIntent(triggerFired);
        }
    }

    /**
     * Time Delay Trigger
     */
    function initTimeDelay(delaySeconds, callback) {
        setTimeout(function() {
            callback('time_delay');
        }, delaySeconds * 1000);
    }

    /**
     * Scroll Depth Trigger
     */
    function initScrollDepth(targetPercent, callback) {
        let fired = false;

        const scrollHandler = throttle(function() {
            if (fired) return;

            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const docHeight = document.documentElement.scrollHeight;
            const winHeight = window.innerHeight;
            const scrollableHeight = docHeight - winHeight;

            // Prevent division by zero
            if (scrollableHeight <= 0) {
                return;
            }

            const scrollPercent = (scrollTop / scrollableHeight) * 100;

            if (scrollPercent >= targetPercent) {
                fired = true;
                window.removeEventListener('scroll', scrollHandler);
                callback('scroll_depth');
            }
        }, 100);

        window.addEventListener('scroll', scrollHandler);
    }

    /**
     * Exit Intent Trigger (desktop only)
     */
    function initExitIntent(callback) {
        // Only on desktop
        if (window.innerWidth <= 768) {
            return;
        }

        let fired = false;
        let mouseMoveCount = 0;

        function exitHandler(e) {
            if (fired) return;

            // Ignore until mouse has moved a few times (avoid false positives)
            mouseMoveCount++;
            if (mouseMoveCount < 3) return;

            // Cursor near top of viewport
            if (e.clientY < 10) {
                fired = true;
                document.removeEventListener('mousemove', exitHandler);
                callback('exit_intent');
            }
        }

        document.addEventListener('mousemove', exitHandler);
    }

    /**
     * Check if this is user's first visit
     */
    function isFirstVisit() {
        const cookieName = 'clear_pop_first_visit';

        if (getCookie(cookieName)) {
            // Not first visit
            return false;
        }

        // This IS first visit - set long-term cookie
        setCookie(cookieName, '1', 365);
        return true;
    }

    /**
     * Check if popup should be shown based on cookie
     */
    function shouldShowPopup(popupId, duration) {
        const cookieName = 'clear_pop_' + popupId;
        const cookieValue = getCookie(cookieName);

        if (!cookieValue) {
            // No cookie = safe to show
            return true;
        }

        try {
            const data = JSON.parse(cookieValue);

            // Check based on duration setting
            if (duration === 'never') {
                // Cookie exists and set to never = don't show
                return false;
            }

            if (duration === 'session') {
                // Session cookies expire on browser close, so if exists, don't show
                return false;
            }

            // For time-based durations, check timestamp
            if (!data.last_shown) {
                return true;
            }

            const now = Date.now();
            const lastShown = parseInt(data.last_shown, 10);
            const durations = {
                '1hour': 60 * 60 * 1000,
                '24hours': 24 * 60 * 60 * 1000,
                '7days': 7 * 24 * 60 * 60 * 1000,
                '30days': 30 * 24 * 60 * 60 * 1000
            };

            if (durations[duration]) {
                const elapsed = now - lastShown;
                // If enough time has passed, show popup
                return elapsed >= durations[duration];
            }

            return true;
        } catch (e) {
            // Invalid cookie data = safe to show
            return true;
        }
    }

    /**
     * Open popup programmatically
     */
    function openPopup(popupId) {
        if (window.hspPopup && window.hspPopup.open) {
            window.hspPopup.open(popupId);

            // Record that popup was shown (client-side only for now)
            // Server-side recording happens on close via AJAX
            recordPopupShown(popupId);
        }
    }

    /**
     * Record popup shown (set cookie)
     */
    function recordPopupShown(popupId) {
        const cookieName = 'clear_pop_' + popupId;
        const data = {
            popup_id: popupId,
            shown_count: 1,
            last_shown: Date.now(),
            closed_method: null
        };

        // Check if cookie exists and increment count
        const existing = getCookie(cookieName);
        if (existing) {
            try {
                const existingData = JSON.parse(existing);
                data.shown_count = (existingData.shown_count || 0) + 1;
            } catch (e) {
                // Invalid JSON, use default
            }
        }

        // Don't set cookie yet - wait for close event
        // Store in memory for now
        window.clearPopShownData = window.clearPopShownData || {};
        window.clearPopShownData[popupId] = data;
    }

    /**
     * Set up close tracking for all popups
     */
    function setupCloseTracking() {
        // Listen for the hspPopupClose event from modal.js
        document.addEventListener('hspPopupClose', function(e) {
            if (!e.detail || !e.detail.popup) {
                return;
            }

            const popup = e.detail.popup;
            const popupId = popup.id.replace('hsp-popup-', '');

            if (!popupId) {
                return;
            }

            // Determine close method
            let closeMethod = 'unknown';
            if (e.detail.method) {
                closeMethod = e.detail.method;
            }

            // Send AJAX request to record close
            sendCloseTracking(popupId, closeMethod);
        });

        // Also hook into existing close mechanisms to detect method
        const closeButtons = document.querySelectorAll('.hsp-popup-close');
        closeButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const popup = this.closest('.hsp-popup-overlay');
                if (popup) {
                    const popupId = popup.id.replace('hsp-popup-', '');
                    sendCloseTracking(popupId, 'close_button');
                }
            });
        });

        // ESC key tracking
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                const activePopup = document.querySelector('.hsp-popup-overlay.hsp-active');
                if (activePopup) {
                    const popupId = activePopup.id.replace('hsp-popup-', '');
                    sendCloseTracking(popupId, 'escape');
                }
            }
        });

        // Overlay click tracking
        const overlays = document.querySelectorAll('.hsp-popup-overlay');
        overlays.forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    const popupId = this.id.replace('hsp-popup-', '');
                    sendCloseTracking(popupId, 'overlay');
                }
            });
        });
    }

    /**
     * Send AJAX request to record popup close
     */
    function sendCloseTracking(popupId, closeMethod) {
        // Get trigger config for this popup
        const config = window.clearPopTriggers && window.clearPopTriggers[popupId];
        if (!config) {
            return;
        }

        // Get AJAX URL
        const ajaxUrl = window.clearPopAjax && window.clearPopAjax.ajax_url;
        if (!ajaxUrl) {
            return;
        }

        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'clear_pop_close');
        formData.append('popup_id', popupId);
        formData.append('close_method', closeMethod);
        formData.append('cookie_duration', config.cookie_duration || 'never');

        // Send AJAX request
        fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        }).catch(function(error) {
            console.error('Clear Pop: Failed to record close event', error);
        });
    }

    /**
     * Get cookie value by name
     */
    function getCookie(name) {
        const nameEQ = name + '=';
        const cookies = document.cookie.split(';');

        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i];
            while (cookie.charAt(0) === ' ') {
                cookie = cookie.substring(1);
            }
            if (cookie.indexOf(nameEQ) === 0) {
                return decodeURIComponent(cookie.substring(nameEQ.length));
            }
        }

        return null;
    }

    /**
     * Set cookie
     */
    function setCookie(name, value, days) {
        let expires = '';
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
    }

    /**
     * Throttle function execution
     */
    function throttle(func, wait) {
        let timeout = null;
        let lastRan = 0;

        return function() {
            const context = this;
            const args = arguments;
            const now = Date.now();

            if (!lastRan) {
                func.apply(context, args);
                lastRan = now;
            } else {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    if ((now - lastRan) >= wait) {
                        func.apply(context, args);
                        lastRan = now;
                    }
                }, wait - (now - lastRan));
            }
        };
    }

})();
