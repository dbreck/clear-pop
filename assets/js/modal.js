/**
 * Clear Pop - Modal JavaScript
 */

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        // Attach click handlers to triggers (both old and new class patterns).
        // May be empty: auto-triggered popups (time delay/scroll/exit-intent) are
        // opened by triggers.js and have no on-page trigger element — but the
        // close/overlay/ESC handlers below must still be bound.
        const triggers = document.querySelectorAll('.hsp-popup-trigger, [class*="hsp-popup-trigger-"]');
        triggers.forEach(function(trigger) {
            trigger.addEventListener('click', handleTriggerClick);
        });
        
        // Attach close handlers
        const closeButtons = document.querySelectorAll('.hsp-popup-close');
        closeButtons.forEach(function(btn) {
            btn.addEventListener('click', handleCloseClick);
        });
        
        // Close on overlay click
        const overlays = document.querySelectorAll('.hsp-popup-overlay');
        overlays.forEach(function(overlay) {
            overlay.addEventListener('click', handleOverlayClick);
        });
        
        // Close on ESC key
        document.addEventListener('keydown', handleEscKey);
    }
    
    /**
     * Handle trigger click
     */
    function handleTriggerClick(e) {
        e.preventDefault();

        // First try data-popup-id (backward compatibility)
        let popupId = this.getAttribute('data-popup-id');
        let popup = null;

        // If not found, parse from class name (hsp-popup-trigger-123 or hsp-popup-trigger-slug)
        if (!popupId) {
            const classes = this.className.split(' ');
            for (let i = 0; i < classes.length; i++) {
                // Try numeric ID first (hsp-popup-trigger-123)
                const numericMatch = classes[i].match(/^hsp-popup-trigger-(\d+)$/);
                if (numericMatch) {
                    popupId = numericMatch[1];
                    popup = document.getElementById('hsp-popup-' + popupId);
                    if (popup) {
                        break;
                    }
                }

                // Try slug-based (hsp-popup-trigger-my-popup-name)
                const slugMatch = classes[i].match(/^hsp-popup-trigger-([a-z0-9-]+)$/i);
                if (slugMatch && !popup) {
                    const slug = slugMatch[1];
                    // Skip if it's just a number (already handled above)
                    if (!/^\d+$/.test(slug)) {
                        // Find popup by data-popup-slug attribute
                        popup = document.querySelector('.hsp-popup-overlay[data-popup-slug="' + slug + '"]');
                        if (popup) {
                            popupId = popup.getAttribute('data-popup-id');
                            break;
                        }
                    }
                }
            }
        } else {
            popup = document.getElementById('hsp-popup-' + popupId);
        }

        if (!popup) {
            return;
        }

        // Check if a specific tab should be opened via class (e.g., hsp-tab-1, hsp-tab-2)
        let tabIndex = null;
        const tabClasses = this.className.split(' ');
        for (let i = 0; i < tabClasses.length; i++) {
            const tabMatch = tabClasses[i].match(/^hsp-tab-(\d+)$/);
            if (tabMatch) {
                tabIndex = parseInt(tabMatch[1], 10);
                break;
            }
        }

        openPopup(popup, tabIndex);
    }
    
    /**
     * Handle close button click
     */
    function handleCloseClick(e) {
        e.preventDefault();
        const popup = this.closest('.hsp-popup-overlay');
        if (popup) {
            closePopup(popup);
        }
    }
    
    /**
     * Handle overlay click (close when clicking outside container)
     */
    function handleOverlayClick(e) {
        if (e.target === this) {
            closePopup(this);
        }
    }
    
    /**
     * Handle ESC key press
     */
    function handleEscKey(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            const activePopup = document.querySelector('.hsp-popup-overlay.hsp-active');
            if (activePopup) {
                closePopup(activePopup);
            }
        }
    }
    
    /**
     * Open popup
     */
    function openPopup(popup, tabIndex) {
        // Close any other open popups first
        const openPopups = document.querySelectorAll('.hsp-popup-overlay.hsp-active');
        openPopups.forEach(function(p) {
            if (p !== popup) {
                closePopup(p);
            }
        });

        // Remember the trigger so focus can return to it on close.
        hspPrevFocus = document.activeElement;

        popup.classList.add('hsp-active');
        document.body.classList.add('hsp-popup-open');

        // Dialog focus management: isolate the background and trap Tab.
        hspBackgroundInert(popup, true);
        document.addEventListener('keydown', hspTrapKeydown, true);

        // Lazy-load the body on first open (spinner shows until it arrives), then
        // wire up tabs, move focus into the dialog, and fire the open event.
        // Already-loaded / non-lazy popups resolve immediately.
        ensureContentLoaded(popup).then(function() {
            // Initialize WPBakery tabs if present (with optional specific tab index)
            initWPBakeryTabs(popup, tabIndex);

            // Move focus into the dialog once its content exists.
            window.requestAnimationFrame(function() {
                if (!popup.classList.contains('hsp-active')) { return; }
                // Focus the labeled dialog container (role="dialog", tabindex="-1"),
                // NOT the close button. Programmatically focusing the close button
                // makes the browser paint its :focus-visible ring after the lazy-load
                // layout shift — it looks like a stray border on the close button when
                // the popup is opened by mouse. Focusing the dialog is the recommended
                // ARIA pattern (screen readers announce it via aria-label) and leaves
                // the close button's keyboard focus ring fully intact — that ring now
                // only appears when a keyboard user actually Tabs to the button.
                var dialog = popup.querySelector('[role="dialog"]') || popup;
                dialog.focus();
            });

            // Trigger custom event
            const event = new CustomEvent('hspPopupOpen', {
                detail: { popup: popup, tabIndex: tabIndex }
            });
            document.dispatchEvent(event);
        });
    }

    // ---- Dialog focus management ------------------------------------------------
    var hspPrevFocus = null;
    var HSP_FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    function hspFocusable(container) {
        return Array.prototype.filter.call(
            container.querySelectorAll(HSP_FOCUSABLE),
            function(el) { return el.offsetWidth > 0 || el.offsetHeight > 0; }
        );
    }

    // Trap Tab/Shift+Tab inside the active popup (capture phase).
    function hspTrapKeydown(e) {
        if (e.key !== 'Tab') { return; }
        var popup = document.querySelector('.hsp-popup-overlay.hsp-active');
        if (!popup) { return; }
        var f = hspFocusable(popup);
        if (!f.length) { e.preventDefault(); return; }
        var first = f[0], last = f[f.length - 1];
        if (!popup.contains(document.activeElement)) { e.preventDefault(); first.focus(); return; }
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }

    // Make everything in <body> except the overlay inert + aria-hidden while open.
    function hspBackgroundInert(popup, on) {
        var kids = document.body.children;
        for (var i = 0; i < kids.length; i++) {
            var el = kids[i];
            if (el === popup || el.tagName === 'SCRIPT' || el.tagName === 'STYLE' || el.tagName === 'LINK') { continue; }
            if (on) {
                if (el.hasAttribute('data-hsp-inert')) { continue; }
                if (el.hasAttribute('aria-hidden')) { el.setAttribute('data-hsp-kept-ah', '1'); }
                el.setAttribute('aria-hidden', 'true');
                el.setAttribute('inert', '');
                el.setAttribute('data-hsp-inert', '1');
            } else if (el.getAttribute('data-hsp-inert') === '1') {
                el.removeAttribute('inert');
                if (el.getAttribute('data-hsp-kept-ah') === '1') { el.removeAttribute('data-hsp-kept-ah'); }
                else { el.removeAttribute('aria-hidden'); }
                el.removeAttribute('data-hsp-inert');
            }
        }
    }

    /**
     * Lazy-load a popup's body the first time it opens.
     *
     * The body is rendered server-side via admin-ajax (clearpop_content) and
     * injected here, so heavy markup — and any embedded Gravity Form whose
     * element IDs would otherwise duplicate an inline copy of the same form —
     * stays out of the initial page DOM until needed. Returns a Promise that
     * resolves once the content is in place (or immediately when there is
     * nothing to load).
     */
    function ensureContentLoaded(popup) {
        const inner = popup.querySelector('.hsp-popup-content-inner');

        // Non-lazy or already loaded: nothing to do.
        if (!inner || inner.getAttribute('data-lazy') !== '1' || inner.getAttribute('data-loaded') === '1') {
            return Promise.resolve();
        }
        // A load is already in flight (or done) for this popup.
        if (inner._loadingPromise) {
            return inner._loadingPromise;
        }

        const cfg = window.clearPopAjax || {};
        const popupId = inner.getAttribute('data-popup-id');
        if (!cfg.ajax_url || !cfg.nonce || !popupId) {
            // Misconfigured — fail soft so the popup still opens (just empty).
            inner.setAttribute('data-loaded', '1');
            inner.innerHTML = '';
            return Promise.resolve();
        }

        const url = cfg.ajax_url +
            '?action=clearpop_content&popup_id=' + encodeURIComponent(popupId) +
            '&nonce=' + encodeURIComponent(cfg.nonce);

        inner._loadingPromise = fetch(url, { credentials: 'same-origin' })
            .then(function(res) {
                if (!res.ok) { throw new Error('HTTP ' + res.status); }
                return res.text();
            })
            .then(function(html) {
                setHtmlWithScripts(inner, html);
                inner.setAttribute('data-loaded', '1');
                reinitGravityForms(inner);
                // Nudge any layout-sensitive embeds (sliders, maps, GF) to recalc.
                if (window.jQuery) { jQuery(window).trigger('resize'); }
            })
            .catch(function(err) {
                if (window.console) { console.error('[clear-pop] lazy load failed:', err); }
                inner.innerHTML = '<div class="hsp-popup-error" role="alert">Sorry — this content could not be loaded. Please refresh and try again.</div>';
            });

        return inner._loadingPromise;
    }

    /**
     * Replace a container's HTML and execute any <script> tags it contains.
     * innerHTML alone does not run injected scripts, so we recreate them.
     */
    function setHtmlWithScripts(container, html) {
        container.innerHTML = html;
        const scripts = container.querySelectorAll('script');
        scripts.forEach(function(oldScript) {
            const newScript = document.createElement('script');
            if (oldScript.src) {
                newScript.src = oldScript.src;
            } else {
                newScript.textContent = oldScript.textContent;
            }
            if (oldScript.type) { newScript.type = oldScript.type; }
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    /**
     * Re-initialize any Gravity Forms that arrived in the injected content.
     * Gravity Forms' JS framework is already enqueued on the page (the footer
     * render primes it); here we just (re)fire its post-render hook so field
     * styling, masking and conditional logic bind to the now-live form.
     */
    function reinitGravityForms(container) {
        if (!window.jQuery) { return; }
        const forms = container.querySelectorAll('form[id^="gform_"]');
        forms.forEach(function(form) {
            const match = form.id.match(/gform_(\d+)/);
            if (match) {
                try {
                    jQuery(document).trigger('gform_post_render', [parseInt(match[1], 10), 1]);
                } catch (e) { /* non-fatal */ }
            }
        });
    }

    /**
     * Initialize WPBakery/Salient tabs inside popup
     */
    function initWPBakeryTabs(popup, tabIndex) {
        // Find all tabbed containers in the popup
        const tabbedContainers = popup.querySelectorAll('.tabbed');

        if (!tabbedContainers.length) {
            return;
        }

        // Default to first tab (index 1) if not specified
        if (!tabIndex || tabIndex < 1) {
            tabIndex = 1;
        }

        // Wait a tick for the popup to be fully visible in the DOM
        setTimeout(function() {
            tabbedContainers.forEach(function(tabbedContainer) {

                // Find all tab panels and hide them first
                const tabPanels = tabbedContainer.querySelectorAll('.wpb_tab');
                tabPanels.forEach(function(panel) {
                    panel.style.visibility = 'hidden';
                    panel.style.position = 'absolute';
                    panel.style.opacity = '0';
                    panel.style.left = '-9999px';
                    panel.style.display = 'none';
                    panel.classList.remove('visible-tab');
                });

                // Find the tab nav and update active states
                const tabNav = tabbedContainer.querySelector('.wpb_tabs_nav');
                if (tabNav) {
                    // Remove all active-tab classes
                    const allTabLinks = tabNav.querySelectorAll('li, a');
                    allTabLinks.forEach(function(el) {
                        el.classList.remove('active-tab');
                    });

                    // Add active-tab class to the selected tab (nth-child is 1-based)
                    const selectedTabLi = tabNav.querySelector('li:nth-child(' + tabIndex + ')');
                    const selectedTabLink = tabNav.querySelector('li:nth-child(' + tabIndex + ') a');
                    if (selectedTabLi) selectedTabLi.classList.add('active-tab');
                    if (selectedTabLink) selectedTabLink.classList.add('active-tab');
                }

                // Find the specified tab panel and make it visible
                // Use nth-of-type to only count .wpb_tab divs, not all children
                const selectedTabPanel = tabbedContainer.querySelector('.wpb_tab:nth-of-type(' + tabIndex + ')');
                if (selectedTabPanel) {
                    selectedTabPanel.style.visibility = 'visible';
                    selectedTabPanel.style.position = 'relative';
                    selectedTabPanel.style.opacity = '1';
                    selectedTabPanel.style.left = '0';
                    selectedTabPanel.style.display = 'block';
                    selectedTabPanel.classList.add('visible-tab');
                }

                // Trigger window resize to ensure any content inside recalculates
                setTimeout(function() {
                    if (window.jQuery) {
                        jQuery(window).trigger('resize');
                    }
                }, 50);
            });
        }, 10);
    }
    
    /**
     * Close popup
     */
    function closePopup(popup) {
        popup.classList.remove('hsp-active');

        // Remove body lock if no other popups are open
        const stillOpen = document.querySelectorAll('.hsp-popup-overlay.hsp-active');
        if (!stillOpen.length) {
            document.body.classList.remove('hsp-popup-open');
        }

        // Tear down focus management: stop trapping, restore the background, and
        // return focus to whatever opened the popup.
        document.removeEventListener('keydown', hspTrapKeydown, true);
        hspBackgroundInert(popup, false);
        if (hspPrevFocus && typeof hspPrevFocus.focus === 'function') {
            hspPrevFocus.focus();
        }
        hspPrevFocus = null;

        // Trigger custom event
        const event = new CustomEvent('hspPopupClose', {
            detail: { popup: popup }
        });
        document.dispatchEvent(event);
    }
    
    // Expose API for programmatic control
    window.hspPopup = {
        open: function(popupId) {
            const popup = document.getElementById('hsp-popup-' + popupId);
            if (popup) {
                openPopup(popup);
            }
        },
        close: function(popupId) {
            const popup = document.getElementById('hsp-popup-' + popupId);
            if (popup) {
                closePopup(popup);
            }
        },
        closeAll: function() {
            const popups = document.querySelectorAll('.hsp-popup-overlay.hsp-active');
            popups.forEach(function(popup) {
                closePopup(popup);
            });
        }
    };
    
})();
