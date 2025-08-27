<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Development Setup - Herd Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-8">
                <h1 class="text-3xl font-bold text-white">📧 Herd Pro Mail Service</h1>
                <p class="mt-2 text-purple-100">Development Email Configuration</p>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Status Check -->
                <div class="border-l-4 border-green-500 pl-4">
                    <h2 class="text-lg font-semibold text-gray-900">✅ Mail Service Active</h2>
                    <p class="text-gray-600">Herd Pro is capturing all outgoing emails for development.</p>
                </div>

                <!-- Mail Viewer Access -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-blue-900 mb-3">Mail Viewer Access</h3>
                    <a href="http://localhost:8025" target="_blank" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Open Mail Viewer
                    </a>
                    <p class="mt-2 text-sm text-blue-700">
                        View all captured emails at: <code class="bg-blue-100 px-1 py-0.5 rounded">http://localhost:8025</code>
                    </p>
                </div>

                <!-- Configuration Details -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Current Configuration</h3>
                    <dl class="space-y-2">
                        <div class="flex">
                            <dt class="font-medium text-gray-600 w-32">SMTP Host:</dt>
                            <dd class="text-gray-900"><code class="bg-gray-200 px-1 py-0.5 rounded">localhost</code></dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-600 w-32">SMTP Port:</dt>
                            <dd class="text-gray-900"><code class="bg-gray-200 px-1 py-0.5 rounded">2525</code></dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-600 w-32">Mailer:</dt>
                            <dd class="text-gray-900"><code class="bg-gray-200 px-1 py-0.5 rounded">smtp</code></dd>
                        </div>
                    </dl>
                </div>

                <!-- Test Commands -->
                <div class="border rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Test Commands</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-1">Send Test Email:</p>
                            <code class="block bg-gray-900 text-green-400 p-3 rounded text-sm">
                                php artisan mail:test your-email@example.com
                            </code>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-1">Test Quote Submission:</p>
                            <code class="block bg-gray-900 text-green-400 p-3 rounded text-sm overflow-x-auto">
                                curl -X POST http://admin-service-core.test/api/quotes \<br>
                                &nbsp;&nbsp;-H "Content-Type: application/json" \<br>
                                &nbsp;&nbsp;-d '{"name": "Test", "email": "test@example.com", ...}'
                            </code>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="bg-green-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-green-900 mb-3">🎯 Development Features</h3>
                    <ul class="space-y-2 text-green-700">
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>All emails captured locally - nothing sent externally</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>View HTML and plain text versions of emails</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Search and filter captured emails</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Delete all emails with single click</span>
                        </li>
                    </ul>
                </div>

                <!-- Projects -->
                <div class="border-t pt-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Active Projects</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="http://raw-disposal.test" class="block p-4 border rounded-lg hover:shadow-md transition-shadow">
                            <h4 class="font-medium text-indigo-600">RAW Disposal</h4>
                            <p class="text-sm text-gray-600">Frontend application</p>
                        </a>
                        <a href="http://admin-service-core.test" class="block p-4 border rounded-lg hover:shadow-md transition-shadow">
                            <h4 class="font-medium text-indigo-600">Admin Service Core</h4>
                            <p class="text-sm text-gray-600">Backend administration</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>