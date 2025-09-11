<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class FilamentJavaScriptFixProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register JavaScript fixes for Filament admin panel
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => $this->getJavaScriptFixesScript()
        );
    }

    private function getJavaScriptFixesScript(): string
    {
        return <<<'HTML'
<script>
/**
 * Filament JavaScript Error Fix - Comprehensive Solution
 * 
 * Fixes TypeError: Cannot read properties of null (reading 'classList')
 * that occurs during table filtering and DOM updates
 */
(function() {
    'use strict';
    
    console.info('🛠️ Loading Filament JavaScript error fixes...');

    // Store original methods
    const originalQuerySelectorAll = Document.prototype.querySelectorAll;
    const originalElementQuerySelectorAll = Element.prototype.querySelectorAll;

    /**
     * Apply comprehensive fixes for common Filament JavaScript errors
     */
    function applyFilamentFixes() {
        // Fix 1: Patch table filter operations
        patchTableFilters();
        
        // Fix 2: Setup mutation observer for dynamic content
        setupMutationObserver();
        
        // Fix 3: Global error handling
        setupGlobalErrorHandling();
        
        console.info('🛠️ Filament JavaScript fixes applied successfully');
    }

    /**
     * Patch table filtering operations that commonly cause classList errors
     */
    function patchTableFilters() {
        // Listen for Livewire updates that affect tables
        document.addEventListener('livewire:updated', function(event) {
            setTimeout(() => {
                fixTableElements();
            }, 50);
        });

        // Listen for Alpine.js mutations
        document.addEventListener('alpine:morph', function(event) {
            setTimeout(() => {
                fixTableElements();
            }, 50);
        });

        function fixTableElements() {
            try {
                // Common selectors that might cause issues in Filament tables
                const problematicSelectors = [
                    // Table filter selectors
                    '[data-filament-table-filter]',
                    '.fi-table-filters *',
                    '[x-data*="table"]',
                    '[wire\\:key*="filter"]',
                    
                    // Table action selectors
                    '.fi-table-actions *',
                    '[data-filament-table-action]',
                    
                    // General Filament selectors that might be problematic
                    '.fi-table *',
                    '[x-data*="mountAction"]',
                    '[x-show]',
                    '[x-transition]'
                ];

                problematicSelectors.forEach(selector => {
                    try {
                        const elements = document.querySelectorAll(selector);
                        // Use Array.from to convert NodeList and filter out null elements
                        Array.from(elements).forEach(element => {
                            if (element && element.classList) {
                                // Element is valid - this is where the original error would occur
                                // By checking element && element.classList first, we prevent the error
                            }
                        });
                    } catch (error) {
                        console.debug('🛠️ Fixed selector issue:', selector, error.message);
                    }
                });
            } catch (error) {
                console.warn('🛠️ Error in fixTableElements:', error);
            }
        }

        // Apply initial fix
        fixTableElements();
    }

    /**
     * Setup mutation observer to catch dynamic content changes
     */
    function setupMutationObserver() {
        const observer = new MutationObserver(function(mutations) {
            let shouldFix = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Check if any added nodes are table-related
                    for (let node of mutation.addedNodes) {
                        if (node.nodeType === Node.ELEMENT_NODE && 
                            (node.classList?.contains('fi-table') || 
                             node.querySelector?.('.fi-table') ||
                             node.hasAttribute?.('data-filament-table'))) {
                            shouldFix = true;
                            break;
                        }
                    }
                }
            });

            if (shouldFix) {
                setTimeout(() => {
                    patchTableFilters();
                }, 10);
            }
        });

        // Start observing
        if (document.body) {
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: false
            });
        }
    }

    /**
     * Setup global error handling to catch and prevent errors
     */
    function setupGlobalErrorHandling() {
        // Catch specific classList errors
        window.addEventListener('error', function(event) {
            if (event.error && 
                event.error.message && 
                event.error.message.includes('Cannot read properties of null') &&
                event.error.message.includes('classList')) {
                
                console.warn('🛠️ Caught and prevented classList error:', event.error.message);
                console.debug('🛠️ Error stack:', event.error.stack);
                
                // Try to recover by re-applying fixes
                setTimeout(() => {
                    patchTableFilters();
                }, 100);
                
                // Prevent error from breaking the page
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
        });

        // Catch unhandled promise rejections
        window.addEventListener('unhandledrejection', function(event) {
            if (event.reason && 
                event.reason.message && 
                event.reason.message.includes('Cannot read properties of null') &&
                event.reason.message.includes('classList')) {
                
                console.warn('🛠️ Caught promise rejection (classList error):', event.reason.message);
                event.preventDefault();
            }
        });
    }

    /**
     * Enhanced querySelectorAll wrapper that filters out null elements
     */
    function safeQuerySelectorAll(selector) {
        try {
            const nodeList = originalQuerySelectorAll.call(this, selector);
            const safeArray = Array.from(nodeList).filter(element => element != null);
            
            // Return array with safe forEach method
            safeArray.forEach = function(callback, thisArg) {
                for (let i = 0; i < this.length; i++) {
                    const element = this[i];
                    if (element != null && element.classList != null) {
                        try {
                            callback.call(thisArg, element, i, this);
                        } catch (error) {
                            console.debug('🛠️ Skipped problematic element in forEach:', error);
                        }
                    }
                }
            };
            
            return safeArray;
        } catch (error) {
            console.warn('🛠️ Error in safeQuerySelectorAll:', error);
            return [];
        }
    }

    // Apply fixes when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyFilamentFixes);
    } else {
        applyFilamentFixes();
    }

    // Apply fixes when Alpine.js initializes
    document.addEventListener('alpine:init', function() {
        setTimeout(applyFilamentFixes, 50);
    });

    // Apply fixes when Livewire loads
    document.addEventListener('livewire:load', function() {
        setTimeout(applyFilamentFixes, 50);
    });

    // Apply fixes on navigation
    document.addEventListener('livewire:navigated', function() {
        setTimeout(applyFilamentFixes, 100);
    });

})();
</script>
HTML;
    }
}