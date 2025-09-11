<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clipboard API Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .test-container {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background: #f9f9f9;
        }
        button {
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #005a87;
        }
        .status {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        .warning { background: #fff3cd; color: #856404; }
        
        .invoice-simulation {
            display: inline-block;
            padding: 8px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 5px 0;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .invoice-simulation:hover {
            background: #f0f0f0;
        }
        .copy-icon {
            margin-left: 8px;
            opacity: 0.6;
        }
        .copy-icon:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <h1>🧪 Clipboard API Test Suite</h1>
    <p>This page tests clipboard functionality in both secure (HTTPS/localhost) and non-secure (HTTP) contexts.</p>
    
    <div class="test-container">
        <h2>📋 Environment Information</h2>
        <div id="env-info"></div>
    </div>

    <div class="test-container">
        <h2>🧪 Basic Clipboard Tests</h2>
        <button onclick="testBasicCopy()">Test Basic Copy</button>
        <button onclick="testLongTextCopy()">Test Long Text Copy</button>
        <button onclick="testSpecialCharsCopy()">Test Special Characters</button>
        <div id="basic-test-results"></div>
    </div>

    <div class="test-container">
        <h2>💰 Invoice Number Simulation</h2>
        <p>These simulate the actual Filament table copy functionality:</p>
        
        <div class="invoice-simulation" onclick="simulateInvoiceCopy('INV-PEND-000258')">
            INV-PEND-000258 <span class="copy-icon">📋</span>
        </div>
        
        <div class="invoice-simulation" onclick="simulateInvoiceCopy('INV-PAID-000157')">
            INV-PAID-000157 <span class="copy-icon">📋</span>
        </div>
        
        <div class="invoice-simulation" onclick="simulateInvoiceCopy('INV-OVER-000089')">
            INV-OVER-000089 <span class="copy-icon">📋</span>
        </div>
        
        <div id="invoice-test-results"></div>
    </div>

    <div class="test-container">
        <h2>⚙️ Fallback Mechanism Tests</h2>
        <button onclick="testFallbackDirectly()">Test Fallback Function</button>
        <button onclick="testErrorHandling()">Test Error Handling</button>
        <div id="fallback-test-results"></div>
    </div>

    <div class="test-container">
        <h2>📊 Test Results Summary</h2>
        <div id="summary-results"></div>
    </div>

    @include('filament.clipboard-fix')

    <script>
        let testResults = {
            passed: 0,
            failed: 0,
            total: 0
        };

        // Environment detection
        function displayEnvironmentInfo() {
            const info = document.getElementById('env-info');
            const isSecure = location.protocol === 'https:' || location.hostname === 'localhost';
            const hasClipboardAPI = typeof navigator.clipboard !== 'undefined';
            
            info.innerHTML = `
                <div class="status info">
                    <strong>Protocol:</strong> ${location.protocol}<br>
                    <strong>Host:</strong> ${location.hostname}<br>
                    <strong>Is Secure Context:</strong> ${isSecure ? '✅ Yes' : '❌ No'}<br>
                    <strong>Navigator.clipboard Available:</strong> ${hasClipboardAPI ? '✅ Yes' : '❌ No (using fallback)'}<br>
                    <strong>User Agent:</strong> ${navigator.userAgent}
                </div>
            `;
        }

        // Test basic copy functionality
        async function testBasicCopy() {
            const testText = 'Hello, World!';
            try {
                await navigator.clipboard.writeText(testText);
                showTestResult('basic-test-results', `✅ Basic copy test passed: "${testText}"`, 'success');
                updateTestResults(true);
            } catch (error) {
                showTestResult('basic-test-results', `❌ Basic copy test failed: ${error.message}`, 'error');
                updateTestResults(false);
            }
        }

        // Test long text copy
        async function testLongTextCopy() {
            const longText = 'This is a longer text with multiple words and special characters: !@#$%^&*()_+-=[]{}|;:,.<>?';
            try {
                await navigator.clipboard.writeText(longText);
                showTestResult('basic-test-results', `✅ Long text copy test passed`, 'success');
                updateTestResults(true);
            } catch (error) {
                showTestResult('basic-test-results', `❌ Long text copy test failed: ${error.message}`, 'error');
                updateTestResults(false);
            }
        }

        // Test special characters
        async function testSpecialCharsCopy() {
            const specialText = '™®©€£¥¢₹₽₨Ωαβγδεζηθικλμνξοπρστυφχψω';
            try {
                await navigator.clipboard.writeText(specialText);
                showTestResult('basic-test-results', `✅ Special characters test passed`, 'success');
                updateTestResults(true);
            } catch (error) {
                showTestResult('basic-test-results', `❌ Special characters test failed: ${error.message}`, 'error');
                updateTestResults(false);
            }
        }

        // Simulate actual invoice copy
        async function simulateInvoiceCopy(invoiceNumber) {
            try {
                await navigator.clipboard.writeText(invoiceNumber);
                showTestResult('invoice-test-results', `✅ Invoice ${invoiceNumber} copied successfully`, 'success');
                updateTestResults(true);
            } catch (error) {
                showTestResult('invoice-test-results', `❌ Failed to copy ${invoiceNumber}: ${error.message}`, 'error');
                updateTestResults(false);
            }
        }

        // Test fallback function directly
        function testFallbackDirectly() {
            const testText = 'Fallback test text';
            
            // Create a temporary textarea to test the fallback
            const textarea = document.createElement('textarea');
            textarea.value = testText;
            textarea.style.cssText = 'position:fixed;left:-9999px;top:-9999px;opacity:0;';
            
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                const success = document.execCommand('copy');
                document.body.removeChild(textarea);
                
                if (success) {
                    showTestResult('fallback-test-results', `✅ Fallback mechanism works correctly`, 'success');
                    updateTestResults(true);
                } else {
                    showTestResult('fallback-test-results', `❌ Fallback mechanism failed`, 'error');
                    updateTestResults(false);
                }
            } catch (error) {
                document.body.removeChild(textarea);
                showTestResult('fallback-test-results', `❌ Fallback error: ${error.message}`, 'error');
                updateTestResults(false);
            }
        }

        // Test error handling
        async function testErrorHandling() {
            try {
                // Test with null/undefined
                await navigator.clipboard.writeText(null);
                showTestResult('fallback-test-results', `✅ Error handling test passed (null handling)`, 'success');
                updateTestResults(true);
            } catch (error) {
                showTestResult('fallback-test-results', `ℹ️ Error handling test: ${error.message}`, 'info');
                updateTestResults(true); // Expected behavior
            }
        }

        // Utility functions
        function showTestResult(containerId, message, type) {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = `status ${type}`;
            div.textContent = message;
            container.appendChild(div);
        }

        function updateTestResults(passed) {
            testResults.total++;
            if (passed) {
                testResults.passed++;
            } else {
                testResults.failed++;
            }
            updateSummary();
        }

        function updateSummary() {
            const summary = document.getElementById('summary-results');
            const passRate = testResults.total > 0 ? Math.round((testResults.passed / testResults.total) * 100) : 0;
            
            summary.innerHTML = `
                <div class="status ${passRate >= 80 ? 'success' : passRate >= 50 ? 'warning' : 'error'}">
                    <strong>Test Summary:</strong><br>
                    Total Tests: ${testResults.total}<br>
                    Passed: ${testResults.passed}<br>
                    Failed: ${testResults.failed}<br>
                    Pass Rate: ${passRate}%
                </div>
            `;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            displayEnvironmentInfo();
            updateSummary();
            
            // Auto-run a basic test
            setTimeout(() => {
                testBasicCopy();
            }, 1000);
        });
    </script>
</body>
</html>