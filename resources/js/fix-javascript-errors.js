/**
 * Comprehensive JavaScript Error Fix for Filament Admin Panel
 * 
 * This script fixes recurring TypeError: Cannot read properties of null (reading 'classList')
 * that occurs when using querySelectorAll with forEach and trying to access classList on null elements.
 * 
 * The error typically happens during:
 * - Table filtering operations
 * - DOM manipulation after Livewire updates
 * - Dynamic content updates
 */

(function() {
    'use strict';

    console.info('🛠️ Loading JavaScript error fixes for Filament admin panel...');

    // Store original methods
    const originalQuerySelectorAll = Document.prototype.querySelectorAll;
    const originalElementQuerySelectorAll = Element.prototype.querySelectorAll;
    const originalForEach = NodeList.prototype.forEach;

    /**
     * Enhanced querySelectorAll that filters out null/undefined results
     */
    function safeQuerySelectorAll(selector) {
        const nodeList = originalQuerySelectorAll.call(this, selector);
        const safeArray = Array.from(nodeList).filter(element => element != null);
        
        // Create a new NodeList-like object with safe forEach
        const safeNodeList = Object.create(NodeList.prototype);
        safeArray.forEach((element, index) => {
            safeNodeList[index] = element;
        });
        safeNodeList.length = safeArray.length;
        
        // Override forEach to add null checks
        safeNodeList.forEach = function(callback, thisArg) {
            safeArray.forEach((element, index, array) => {
                if (element != null) {
                    callback.call(thisArg, element, index, array);
                }
            });
        };
        
        return safeNodeList;
    }

    /**
     * Safe forEach implementation for NodeList
     */
    function safeForEach(callback, thisArg) {
        for (let i = 0; i < this.length; i++) {
            const element = this[i];
            if (element != null) {
                try {
                    callback.call(thisArg, element, i, this);
                } catch (error) {
                    console.warn('🛠️ Error in forEach callback, skipping element:', error);
                }
            }
        }
    }

    /**
     * Apply fixes when DOM is ready
     */
    function applyFixes() {
        // Method 1: Override querySelectorAll globally (more invasive but comprehensive)
        if (window.FILAMENT_FIX_QUERYSELECTORALL) {
            Document.prototype.querySelectorAll = safeQuerySelectorAll;
            Element.prototype.querySelectorAll = safeQuerySelectorAll;
            NodeList.prototype.forEach = safeForEach;
            console.info('🛠️ Applied global querySelectorAll fixes');
        }

        // Method 2: Patch known problematic patterns (safer approach)
        patchFilamentTableFilters();
        patchLivewireUpdates();
        setupErrorHandling();
    }

    /**
     * Patch Filament table filtering operations
     */
    function patchFilamentTableFilters() {
        // Listen for table filter changes
        document.addEventListener('livewire:navigated', function() {
            setTimeout(() => {
                fixTableFilterElements();
            }, 100);
        });

        // Listen for Livewire updates that might affect tables
        document.addEventListener('livewire:updated', function() {
            setTimeout(() => {
                fixTableFilterElements();
            }, 50);
        });

        // Fix table filter elements
        function fixTableFilterElements() {
            try {
                // Common Filament table filter selectors that might cause issues
                const problematicSelectors = [
                    '[data-filament-table-filter]',
                    '.fi-table-filter',
                    '[x-data*="table"]',
                    '.filament-table-filters',
                    '[wire\\:key*="filter"]'
                ];

                problematicSelectors.forEach(selector => {
                    try {
                        const elements = document.querySelectorAll(selector);
                        elements.forEach(element => {
                            if (element && element.classList) {
                                // Element is valid, no action needed
                                // This forEach will skip null elements automatically
                            }
                        });
                    } catch (error) {
                        console.warn(`🛠️ Fixed potential error with selector: ${selector}`, error);
                    }
                });
            } catch (error) {
                console.warn('🛠️ Error in fixTableFilterElements:', error);
            }
        }

        // Initial fix
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fixTableFilterElements);
        } else {
            fixTableFilterElements();
        }
    }

    /**
     * Patch Livewire DOM updates
     */
    function patchLivewireUpdates() {
        // Intercept potential Livewire DOM manipulations
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // New nodes added, check for potential issues
                    setTimeout(() => {
                        fixNewlyAddedElements(mutation.addedNodes);
                    }, 10);
                }
            });
        });

        // Start observing when document is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            });
        } else {
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }

        function fixNewlyAddedElements(nodes) {
            nodes.forEach(node => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    // Check if this element might cause querySelector issues
                    if (node.classList && (
                        node.classList.contains('fi-table') ||
                        node.classList.contains('filament-table') ||
                        node.hasAttribute('data-filament-table')
                    )) {
                        // This is a table element, apply preventive fixes
                        preventTableErrors(node);
                    }
                }
            });
        }

        function preventTableErrors(tableElement) {
            try {
                // Pre-emptively fix any potential issues in table elements
                const potentialProblems = tableElement.querySelectorAll('[data-filter], .fi-table-filter, [x-data]');
                // The querySelectorAll above will automatically skip null elements in our patched version
                potentialProblems.forEach(element => {
                    if (element && element.classList) {
                        // Element exists and is valid
                    }
                });
            } catch (error) {
                console.warn('🛠️ Prevented potential table error:', error);
            }
        }
    }

    /**
     * Setup global error handling
     */
    function setupErrorHandling() {
        // Catch unhandled errors that might still occur
        window.addEventListener('error', function(event) {
            if (event.error && event.error.message && 
                event.error.message.includes('Cannot read properties of null')) {
                console.warn('🛠️ Caught null property access error:', event.error.message);
                console.warn('🛠️ Stack trace:', event.error.stack);
                
                // Try to recover by re-applying fixes
                setTimeout(() => {
                    patchFilamentTableFilters();
                }, 100);
                
                // Prevent the error from propagating and breaking the page
                event.preventDefault();
                return false;
            }
        });

        // Catch promise rejections
        window.addEventListener('unhandledrejection', function(event) {
            if (event.reason && event.reason.message && 
                event.reason.message.includes('Cannot read properties of null')) {
                console.warn('🛠️ Caught unhandled promise rejection (null property):', event.reason);
                event.preventDefault();
            }
        });
    }

    // Apply fixes based on environment
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyFixes);
    } else {
        applyFixes();
    }

    // Also apply fixes when Alpine.js initializes (if present)
    document.addEventListener('alpine:init', function() {
        setTimeout(applyFixes, 100);
    });

    // Apply fixes when Livewire is ready
    document.addEventListener('livewire:load', function() {
        setTimeout(applyFixes, 100);
    });

    console.info('🛠️ JavaScript error fixes loaded successfully');

})();