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
        // Get all trigger elements (both old and new class patterns)
        const triggers = document.querySelectorAll('.hsp-popup-trigger, [class*="hsp-popup-trigger-"]');
        
        if (!triggers.length) {
            return;
        }
        
        // Attach click handlers to triggers
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

        // If not found, parse from class name (hsp-popup-trigger-123)
        if (!popupId) {
            const classes = this.className.split(' ');
            for (let i = 0; i < classes.length; i++) {
                const match = classes[i].match(/^hsp-popup-trigger-(\d+)$/);
                if (match) {
                    popupId = match[1];
                    break;
                }
            }
        }

        if (!popupId) {
            return;
        }

        const popup = document.getElementById('hsp-popup-' + popupId);
        if (!popup) {
            return;
        }

        // Check if a specific tab should be opened via class (e.g., hsp-tab-1, hsp-tab-2)
        let tabIndex = null;
        const classes = this.className.split(' ');
        for (let i = 0; i < classes.length; i++) {
            const match = classes[i].match(/^hsp-tab-(\d+)$/);
            if (match) {
                tabIndex = parseInt(match[1], 10);
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

        popup.classList.add('hsp-active');
        document.body.classList.add('hsp-popup-open');

        // Initialize WPBakery tabs if present (with optional specific tab index)
        initWPBakeryTabs(popup, tabIndex);

        // Trigger custom event
        const event = new CustomEvent('hspPopupOpen', {
            detail: { popup: popup, tabIndex: tabIndex }
        });
        document.dispatchEvent(event);
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
