{{-- 
    Clipboard Fix for Filament Admin Panel
    This script fixes clipboard functionality in non-secure contexts (HTTP)
--}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Only apply fix if clipboard API is not available
        if (typeof window.navigator.clipboard === 'undefined' || !window.navigator.clipboard) {
            console.info('📋 Applying clipboard fallback for HTTP context...');
            
            // Create fallback clipboard object
            window.navigator.clipboard = {
                writeText: function(text) {
                    return new Promise(function(resolve, reject) {
                        try {
                            const textarea = document.createElement('textarea');
                            textarea.value = text;
                            textarea.style.cssText = 'position:fixed;left:-9999px;top:-9999px;opacity:0;';
                            textarea.setAttribute('readonly', '');
                            
                            document.body.appendChild(textarea);
                            textarea.select();
                            textarea.setSelectionRange(0, 99999);
                            
                            const success = document.execCommand('copy');
                            document.body.removeChild(textarea);
                            
                            if (success) {
                                console.info('📋 Text copied successfully:', text);
                                
                                // Show success notification
                                showClipboardNotification('Copied: ' + text, 'success');
                                resolve();
                            } else {
                                reject(new Error('Copy command failed'));
                            }
                        } catch (error) {
                            console.error('📋 Clipboard fallback error:', error);
                            reject(error);
                        }
                    });
                }
            };

            // Function to show notification
            function showClipboardNotification(message, type) {
                // Remove any existing notifications
                const existing = document.querySelector('.clipboard-notification');
                if (existing) {
                    existing.remove();
                }

                const notification = document.createElement('div');
                notification.className = 'clipboard-notification';
                notification.innerHTML = message;
                notification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 12px 20px;
                    background: ${type === 'success' ? '#10b981' : '#ef4444'};
                    color: white;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 500;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                    z-index: 10000;
                    transition: all 0.3s ease;
                    opacity: 0;
                    transform: translateY(-20px);
                `;
                
                document.body.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.style.opacity = '1';
                    notification.style.transform = 'translateY(0)';
                }, 10);
                
                // Remove after 3 seconds
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateY(-20px)';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }

            console.info('📋 Clipboard fallback initialized successfully!');
        }
    });
</script>