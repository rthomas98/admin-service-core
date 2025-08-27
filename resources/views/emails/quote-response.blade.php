<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote #{{ $quote->quote_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .quote-info {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: normal;
        }
        .items-table td {
            border-bottom: 1px solid #e0e0e0;
            padding: 12px;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .total-section {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .total-row.grand-total {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #e0e0e0;
            padding-top: 10px;
            margin-top: 10px;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .terms {
            background: #fff5f5;
            border: 1px solid #ffc0c0;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>RAW Disposal</h1>
            <p style="margin: 5px 0;">Professional Waste Management Services</p>
        </div>

        <div class="content">
            <h2>Quote #{{ $quote->quote_number }}</h2>
            
            <p>Dear {{ $quote->name }},</p>
            
            <p>Thank you for your interest in RAW Disposal services. We're pleased to provide you with a detailed quote for your {{ strtolower($quote->project_type) }} project.</p>

            <div class="quote-info">
                <div class="info-row">
                    <span class="info-label">Customer:</span>
                    <span class="info-value">{{ $quote->name }}</span>
                </div>
                @if($quote->company)
                <div class="info-row">
                    <span class="info-label">Company:</span>
                    <span class="info-value">{{ $quote->company }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Project Type:</span>
                    <span class="info-value">{{ $quote->project_type }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Project Start Date:</span>
                    <span class="info-value">{{ $quote->start_date->format('F j, Y') }}</span>
                </div>
                @if($quote->duration)
                <div class="info-row">
                    <span class="info-label">Duration:</span>
                    <span class="info-value">{{ $quote->duration }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Location:</span>
                    <span class="info-value">{{ $quote->location }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Quote Valid Until:</span>
                    <span class="info-value">{{ $quote->valid_until->format('F j, Y') }}</span>
                </div>
            </div>

            @if($quote->description)
            <div style="margin: 20px 0;">
                <h3>Project Description</h3>
                <p>{{ $quote->description }}</p>
            </div>
            @endif

            @if($quote->items && count($quote->items) > 0)
            <h3>Services & Equipment</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Unit Price</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quote->items as $item)
                    <tr>
                        <td>{{ ucfirst($item['type'] ?? 'Service') }}</td>
                        <td>{{ $item['description'] }}</td>
                        <td style="text-align: center;">{{ $item['quantity'] }}</td>
                        <td style="text-align: right;">${{ number_format($item['unit_price'], 2) }}</td>
                        <td style="text-align: right;">${{ number_format($item['quantity'] * $item['unit_price'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>${{ number_format($quote->subtotal ?? 0, 2) }}</span>
                </div>
                @if($quote->discount_amount > 0)
                <div class="total-row">
                    <span>Discount:</span>
                    <span>-${{ number_format($quote->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="total-row">
                    <span>Tax ({{ $quote->tax_rate ?? 8.25 }}%):</span>
                    <span>${{ number_format($quote->tax_amount ?? 0, 2) }}</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total Amount:</span>
                    <span>${{ number_format($quote->total_amount ?? 0, 2) }}</span>
                </div>
            </div>

            @if($quote->delivery_address || $quote->requested_delivery_date)
            <div class="quote-info">
                <h3 style="margin-top: 0;">Delivery Information</h3>
                @if($quote->delivery_address)
                <div class="info-row">
                    <span class="info-label">Delivery Address:</span>
                    <span class="info-value">
                        {{ $quote->delivery_address }}<br>
                        {{ $quote->delivery_city }}, {{ $quote->delivery_parish }} {{ $quote->delivery_postal_code }}
                    </span>
                </div>
                @endif
                @if($quote->requested_delivery_date)
                <div class="info-row">
                    <span class="info-label">Delivery Date:</span>
                    <span class="info-value">{{ $quote->requested_delivery_date->format('F j, Y') }}</span>
                </div>
                @endif
                @if($quote->requested_pickup_date)
                <div class="info-row">
                    <span class="info-label">Pickup Date:</span>
                    <span class="info-value">{{ $quote->requested_pickup_date->format('F j, Y') }}</span>
                </div>
                @endif
            </div>
            @endif

            @if($quote->terms_conditions)
            <div class="terms">
                <h3 style="margin-top: 0;">Terms & Conditions</h3>
                <p style="white-space: pre-line;">{{ $quote->terms_conditions }}</p>
            </div>
            @endif

            <div style="text-align: center; margin: 30px 0;">
                <p><strong>Ready to proceed?</strong></p>
                <p>To accept this quote and schedule your service, please contact us at:</p>
                <p>
                    Phone: (504) 555-0123<br>
                    Email: sales@rawdisposal.com
                </p>
            </div>
        </div>

        <div class="footer">
            <p>This quote is valid until {{ $quote->valid_until->format('F j, Y') }}</p>
            <p>Thank you for choosing RAW Disposal!</p>
            <p style="margin-top: 10px; font-size: 12px;">
                RAW Disposal | New Orleans, LA<br>
                Professional Waste Management Services
            </p>
        </div>
    </div>
</body>
</html>