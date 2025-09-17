<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $appName }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #5C2C86 0%, #A06CD5 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 20px;
            color: #5C2C86;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .credentials-box {
            background: #f8f3ff;
            border: 2px solid #e8d9ff;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
        }
        .credential-item {
            margin: 15px 0;
            display: flex;
            align-items: flex-start;
        }
        .credential-label {
            font-weight: 600;
            color: #5C2C86;
            min-width: 120px;
            margin-right: 15px;
        }
        .credential-value {
            color: #333;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
            word-break: break-all;
            flex: 1;
        }
        .password-value {
            background: #fff3cd;
            border-color: #ffc107;
            font-weight: 600;
        }
        .action-button {
            display: inline-block;
            background: #5C2C86;
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 30px 0;
            transition: background 0.3s;
        }
        .action-button:hover {
            background: #4a2370;
        }
        .security-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .security-notice h3 {
            color: #856404;
            margin: 0 0 10px 0;
            font-size: 16px;
            display: flex;
            align-items: center;
        }
        .security-notice ul {
            margin: 10px 0;
            padding-left: 20px;
            color: #856404;
        }
        .role-badge {
            display: inline-block;
            background: #A06CD5;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin: 5px 5px 5px 0;
            font-weight: 500;
        }
        .personal-message {
            background: #f0f8ff;
            border-left: 4px solid #4299e1;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
            color: #2c5282;
        }
        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
        }
        .footer a {
            color: #5C2C86;
            text-decoration: none;
        }
        .warning-icon {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{ $appName }}!</h1>
        </div>

        <div class="content">
            <div class="greeting">Hello {{ $userName }},</div>

            <p>Your account has been successfully created. Below are your login credentials:</p>

            <div class="credentials-box">
                <h3 style="margin-top: 0; color: #5C2C86;">Account Details</h3>

                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">{{ $userEmail }}</span>
                </div>

                <div class="credential-item">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value password-value">{{ $temporaryPassword }}</span>
                </div>

                @if($userRoles)
                <div class="credential-item">
                    <span class="credential-label">Role(s):</span>
                    <div>
                        @foreach(explode(', ', $userRoles) as $role)
                            <span class="role-badge">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            @if($personalMessage)
            <div class="personal-message">
                <strong>Message from your administrator:</strong><br>
                {{ $personalMessage }}
            </div>
            @endif

            <div class="security-notice">
                <h3>
                    <svg class="warning-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Important Security Notice
                </h3>
                <ul>
                    <li><strong>Change your password immediately</strong> upon first login</li>
                    <li>Use a strong password with at least 8 characters</li>
                    <li>Include uppercase, lowercase, numbers, and special characters</li>
                    <li>Never share your credentials with anyone</li>
                    <li>This temporary password will expire in 7 days</li>
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" class="action-button">Login to Your Account</a>
            </div>

            <div style="margin-top: 30px;">
                <h3 style="color: #5C2C86;">Getting Started</h3>
                <ol style="color: #666;">
                    <li>Click the button above or visit <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></li>
                    <li>Enter your email address and temporary password</li>
                    <li>You'll be prompted to create a new password</li>
                    <li>Complete your profile setup</li>
                    <li>Start exploring your dashboard and features</li>
                </ol>
            </div>

            <p style="margin-top: 30px; color: #666;">
                If you have any questions or need assistance, please don't hesitate to contact your system administrator or reply to this email.
            </p>
        </div>

        <div class="footer">
            <p>This is an automated message from {{ $appName }}.</p>
            <p>© {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
            <p style="margin-top: 15px;">
                <a href="{{ url('/privacy') }}">Privacy Policy</a> |
                <a href="{{ url('/terms') }}">Terms of Service</a>
            </p>
        </div>
    </div>
</body>
</html>