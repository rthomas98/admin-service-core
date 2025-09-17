<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Export Ready</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .stats {
            background-color: #f8f9fa;
            border-left: 4px solid #2563eb;
            padding: 15px;
            margin: 20px 0;
        }
        .stats p {
            margin: 5px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .notice {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 6px;
            padding: 12px;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Your Customer Export is Ready</h1>
        </div>

        <div class="content">
            <p>Hello,</p>

            <p>Your customer data export has been successfully generated and is ready for download.</p>

            <div class="stats">
                <p><strong>Export Summary:</strong></p>
                <p>📁 File: {{ $filename }}</p>
                <p>👥 Total Customers: {{ number_format($customerCount) }}</p>
                <p>📅 Generated: {{ now()->format('F j, Y at g:i A') }}</p>
            </div>

            <p>The export file is attached to this email. You can also download it using the button below:</p>

            <div style="text-align: center;">
                <a href="{{ $downloadUrl }}" class="button">Download Export</a>
            </div>

            <div class="notice">
                <strong>📌 Note:</strong> This download link will expire in 24 hours for security reasons. The CSV file can be opened with Excel, Google Sheets, or any spreadsheet application.
            </div>

            <p>The export includes the following information for each customer:</p>
            <ul>
                <li>Customer number and contact information</li>
                <li>Organization and address details</li>
                <li>Customer type and status</li>
                <li>Outstanding balance and order history</li>
                <li>Portal access status</li>
                <li>Internal notes and messages</li>
            </ul>
        </div>

        <div class="footer">
            <p>This is an automated message from your Admin Service Core system.</p>
            <p>If you didn't request this export, please contact your system administrator.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>