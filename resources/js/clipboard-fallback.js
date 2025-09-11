/**
 * Clipboard Fallback Solution for Filament Admin Panel
 * 
 * This script provides a robust clipboard functionality that works in both
 * secure (HTTPS/localhost) and non-secure (HTTP) contexts by implementing
 * fallback mechanisms for environments where navigator.clipboard is not available.
 */

(function() {
    'use strict';

    // Check if we need to patch the clipboard functionality
    if (typeof window.navigator.clipboard === 'undefined' || !window.navigator.clipboard) {
        console.info('📋 Clipboard API not available. Initializing fallback solution...');
        
        // Create a fallback clipboard object
        window.navigator.clipboard = {
            writeText: function(text) {
                return new Promise(function(resolve, reject) {
                    try {
                        // Method 1: Try execCommand (legacy but works in HTTP)
                        if (copyToClipboardLegacy(text)) {
                            console.info('📋 Text copied using legacy method:', text);
                            resolve();
                            return;
                        }
                        
                        // Method 2: Create temporary textarea
                        if (copyToClipboardTextarea(text)) {
                            console.info('📋 Text copied using textarea method:', text);
                            resolve();
                            return;
                        }
                        
                        // If all methods fail, reject with a helpful message
                        reject(new Error('Clipboard operation failed. Text: ' + text));
                    } catch (error) {
                        console.error('📋 Clipboard fallback error:', error);
                        reject(error);
                    }
                });
            }
        };
    }

    /**
     * Legacy clipboard copy using execCommand
     * @param {string} text 
     * @returns {boolean} success
     */
    function copyToClipboardLegacy(text) {
        try {
            // Create a temporary textarea element
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '-9999px';
            textarea.style.opacity = '0';
            textarea.setAttribute('readonly', '');
            textarea.setAttribute('tabindex', '-1');
            
            // Add to DOM, select, copy, and remove
            document.body.appendChild(textarea);
            textarea.select();
            textarea.setSelectionRange(0, 99999); // For mobile devices
            
            const success = document.execCommand('copy');
            document.body.removeChild(textarea);
            
            return success;
        } catch (error) {
            console.error('📋 Legacy copy method failed:', error);
            return false;
        }
    }

    /**
     * Alternative textarea method for clipboard copy
     * @param {string} text 
     * @returns {boolean} success
     */
    function copyToClipboardTextarea(text) {
        try {
            // Create a more robust textarea element
            const textarea = document.createElement('textarea');
            textarea.value = text;
            
            // Style the textarea to be invisible but still functional
            Object.assign(textarea.style, {
                position: 'absolute',
                left: '-10000px',
                top: '-10000px',
                width: '1px',
                height: '1px',
                padding: '0',
                border: 'none',
                outline: 'none',
                boxShadow: 'none',
                background: 'transparent'
            });
            
            document.body.appendChild(textarea);
            
            // Focus and select the text
            textarea.focus();
            textarea.select();
            
            // Try to copy
            let success = false;
            try {
                success = document.execCommand('copy');
            } catch (err) {
                console.error('📋 execCommand failed:', err);
            }
            
            // Clean up
            document.body.removeChild(textarea);
            
            return success;
        } catch (error) {
            console.error('📋 Textarea copy method failed:', error);
            return false;
        }
    }

    /**
     * Enhanced clipboard functionality with user feedback
     */
    function enhanceClipboardFunctionality() {
        // Override Alpine.js clipboard functionality if present
        document.addEventListener('alpine:init', function() {
            if (window.Alpine) {
                // Add a global Alpine directive for clipboard
                Alpine.directive('clipboard', function(el, { expression }, { evaluate }) {
                    el.addEventListener('click', function() {
                        const text = evaluate(expression);
                        if (text) {
                            navigator.clipboard.writeText(text.toString())
                                .then(function() {
                                    showToast('Copied to clipboard: ' + text, 'success');
                                })
                                .catch(function(error) {
                                    console.error('📋 Clipboard error:', error);
                                    showToast('Failed to copy to clipboard', 'error');
                                });
                        }
                    });
                });
            }
        });

        // Listen for Filament table copy events
        document.addEventListener('click', function(event) {
            // Check if this is a Filament copyable column
            const copyButton = event.target.closest('[x-on\\:click*="clipboard.writeText"]');
            if (copyButton) {
                event.preventDefault();
                event.stopPropagation();
                
                // Extract the text from the Alpine.js expression
                const onClickAttr = copyButton.getAttribute('x-on:click');
                const match = onClickAttr.match(/clipboard\.writeText\('([^']+)'\)/);
                
                if (match && match[1]) {
                    const textToCopy = match[1];
                    navigator.clipboard.writeText(textToCopy)
                        .then(function() {
                            showToast('Copied: ' + textToCopy, 'success');
                        })
                        .catch(function(error) {
                            console.error('📋 Copy failed:', error);
                            showToast('Copy failed: ' + textToCopy, 'error');
                        });
                }
            }
        });
    }

    /**
     * Simple toast notification for user feedback
     * @param {string} message 
     * @param {string} type - 'success' or 'error'
     */
    function showToast(message, type = 'success') {
        // Create toast element
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateX(100%);
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        requestAnimationFrame(function() {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });
        
        // Remove after 3 seconds
        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(function() {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhanceClipboardFunctionality);
    } else {
        enhanceClipboardFunctionality();
    }

    console.info('📋 Clipboard fallback solution initialized successfully!');
})();