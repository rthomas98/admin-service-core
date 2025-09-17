/**
 * Filament Notification Animation Patch
 * Fixes: "Failed to execute 'animate' on 'Element': Easing may not be set to a list of values"
 *
 * This patch intercepts Element.animate() calls and fixes invalid easing values
 * that may be passed as arrays instead of strings.
 */

(function() {
    'use strict';

    // Only run if Element.animate exists (Web Animations API support)
    if (!Element.prototype.animate) {
        console.warn('Web Animations API not supported in this browser');
        return;
    }

    // Store original animate method
    const originalAnimate = Element.prototype.animate;

    // Override Element.animate to fix easing issues
    Element.prototype.animate = function(keyframes, options) {
        try {
            // Fix options if they exist
            if (options) {
                // Handle easing that might be an array
                if (options.easing) {
                    if (Array.isArray(options.easing)) {
                        console.warn('Animation easing was an array, converting to string:', options.easing);
                        // Use the first valid easing or default to ease-in-out
                        options.easing = options.easing[0] || 'cubic-bezier(0.4, 0, 0.2, 1)';
                    }

                    // Validate easing string format
                    if (typeof options.easing === 'string') {
                        // Check for invalid formats and replace with safe default
                        const validEasings = [
                            'linear', 'ease', 'ease-in', 'ease-out', 'ease-in-out',
                            'cubic-bezier', 'steps'
                        ];

                        const hasValidEasing = validEasings.some(valid =>
                            options.easing.includes(valid)
                        );

                        if (!hasValidEasing) {
                            console.warn('Invalid easing detected:', options.easing, 'Using default');
                            options.easing = 'cubic-bezier(0.4, 0, 0.2, 1)';
                        }
                    }
                }

                // Fix duration if it's not a number
                if (options.duration && typeof options.duration !== 'number') {
                    options.duration = parseFloat(options.duration) || 300;
                }

                // Fix iterations if invalid
                if (options.iterations && typeof options.iterations !== 'number') {
                    options.iterations = parseFloat(options.iterations) || 1;
                }
            }

            // Fix keyframes if they have easing properties
            if (Array.isArray(keyframes)) {
                keyframes = keyframes.map(frame => {
                    if (frame.easing) {
                        if (Array.isArray(frame.easing)) {
                            frame.easing = frame.easing[0] || 'ease-in-out';
                        }
                    }
                    return frame;
                });
            }

            // Call original animate with fixed parameters
            return originalAnimate.call(this, keyframes, options);

        } catch (error) {
            console.error('Animation error caught and handled:', error);

            // Fallback: Try animation without easing
            if (options && options.easing) {
                delete options.easing;
                try {
                    return originalAnimate.call(this, keyframes, options);
                } catch (fallbackError) {
                    console.error('Animation fallback also failed:', fallbackError);
                }
            }

            // Last resort: Return a mock animation object
            return {
                finished: Promise.resolve(),
                ready: Promise.resolve(),
                cancel: () => {},
                finish: () => {},
                pause: () => {},
                play: () => {},
                reverse: () => {},
                currentTime: 0,
                playbackRate: 1,
                playState: 'finished'
            };
        }
    };

    // Also patch Alpine's transition system if it exists
    document.addEventListener('alpine:init', () => {
        if (window.Alpine && window.Alpine.transition) {
            const originalTransition = window.Alpine.transition;

            window.Alpine.transition = function(el, setFunction, options = {}) {
                // Fix transition options
                if (options.duration && Array.isArray(options.duration)) {
                    options.duration = options.duration[0];
                }

                if (options.easing && Array.isArray(options.easing)) {
                    options.easing = options.easing[0] || 'ease-in-out';
                }

                return originalTransition.call(this, el, setFunction, options);
            };
        }
    });

    // Log that the patch is active
    console.info('Filament Animation Patch: Active - Animation errors will be intercepted and fixed');

})();