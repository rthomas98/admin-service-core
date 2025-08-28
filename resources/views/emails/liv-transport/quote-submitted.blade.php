<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New LIV Transport Quote Request</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-section h2 {
            color: #1e3c72;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            min-width: 150px;
        }
        .info-value {
            color: #333;
        }
        .message-box {
            background: #f8f9fa;
            border-left: 4px solid #1e3c72;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .btn {
            display: inline-block;
            background: #1e3c72;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New LIV Transport Quote Request</h1>
            <p style="margin: 5px 0;">Quote #{{ $quote->quote_number }}</p>
        </div>

        <div class="content">
            <div class="info-section">
                <h2>Customer Information</h2>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $quote->name }}</span>
                </div>
                @if($quote->company)
                <div class="info-row">
                    <span class="info-label">Company:</span>
                    <span class="info-value">{{ $quote->company }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">
                        <a href="mailto:{{ $quote->email }}">{{ $quote->email }}</a>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">
                        <a href="tel:{{ $quote->phone }}">{{ $quote->phone }}</a>
                    </span>
                </div>
            </div>

            <div class="info-section">
                <h2>Project Details</h2>
                <div class="info-row">
                    <span class="info-label">Project Type:</span>
                    <span class="info-value">{{ $quote->project_type }}</span>
                </div>
                @if($quote->services)
                <div class="info-row">
                    <span class="info-label">Services:</span>
                    <span class="info-value">{{ implode(', ', $quote->services) }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Location:</span>
                    <span class="info-value">{{ $quote->location }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Start Date:</span>
                    <span class="info-value">{{ $quote->start_date->format('F j, Y') }}</span>
                </div>
                @if($quote->duration)
                <div class="info-row">
                    <span class="info-label">Duration:</span>
                    <span class="info-value">{{ $quote->duration }}</span>
                </div>
                @endif
            </div>

            @if($quote->message)
            <div class="message-box">
                <strong>Additional Information:</strong><br>
                {!! nl2br(e($quote->message)) !!}
            </div>
            @endif

            <div style="text-align: center;">
                <a href="http://admin-service-core.test/admin/3/quotes/{{ $quote->id }}/edit" class="btn">
                    View Quote in Admin Panel
                </a>
            </div>
        </div>

        <div class="footer">
            <p>This email was sent from the LIV Transport quote submission system.</p>
            <p>{{ now()->format('F j, Y g:i A') }}</p>
        </div>
    </div>
</body>
</html>