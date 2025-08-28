<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->subject }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 40px;
            margin: 20px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #5C2C86;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .content {
            margin: 30px 0;
        }
        .message {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #5C2C86;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #A06CD5;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #5C2C86;
            padding: 15px;
            margin: 20px 0;
        }
        .category-badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #E2CFEA;
            color: #5C2C86;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            @if($notification->company && $notification->company->name)
                <h1 style="color: #5C2C86; margin: 0;">{{ $notification->company->name }}</h1>
            @else
                <h1 style="color: #5C2C86; margin: 0;">Admin Service Core</h1>
            @endif
        </div>

        <div class="content">
            <span class="category-badge">{{ $notification->category->label() }}</span>
            
            <div class="message">{{ $message }}</div>

            @if($notification->data && isset($notification->data['action_url']))
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $notification->data['action_url'] }}" class="button">
                        {{ $notification->data['action_text'] ?? 'View Details' }}
                    </a>
                </div>
            @endif

            @if($notification->data && isset($notification->data['additional_info']))
                <div class="info-box">
                    <strong>Additional Information:</strong><br>
                    {{ $notification->data['additional_info'] }}
                </div>
            @endif
        </div>

        <div class="footer">
            <p>This is an automated notification from your service management system.</p>
            <p>
                @if($notification->category === App\Enums\NotificationCategory::MARKETING)
                    <a href="{{ url('/unsubscribe/' . encrypt($notification->recipient_id)) }}" style="color: #666; text-decoration: underline;">
                        Unsubscribe from marketing emails
                    </a><br>
                @endif
                © {{ date('Y') }} {{ $notification->company->name ?? 'Admin Service Core' }}. All rights reserved.
            </p>
            <p style="font-size: 12px; color: #999;">
                Notification ID: #{{ $notification->id }}
            </p>
        </div>
    </div>
</body>
</html>