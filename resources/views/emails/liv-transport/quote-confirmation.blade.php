<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Request Received - LIV Transport</title>
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
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .thank-you-box {
            background: #e8f4f8;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        .thank-you-box h2 {
            color: #1e3c72;
            margin: 0 0 10px 0;
        }
        .quote-details {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            color: #333;
            text-align: right;
        }
        .next-steps {
            background: #fff5e6;
            border-left: 4px solid #ff9800;
            padding: 20px;
            margin: 30px 0;
        }
        .next-steps h3 {
            color: #e67e00;
            margin: 0 0 15px 0;
        }
        .next-steps ul {
            margin: 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin-bottom: 10px;
        }
        .contact-info {
            background: #f0f8ff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .contact-info h3 {
            color: #1e3c72;
            margin: 0 0 15px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 25px;
            text-align: center;
            font-size: 13px;
            color: #666;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            color: #1e3c72;
            text-decoration: none;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>LIV Transport LLC</h1>
            <p>Professional Transportation Services</p>
        </div>

        <div class="content">
            <div class="thank-you-box">
                <h2>Thank You for Your Quote Request!</h2>
                <p>We've received your request and will contact you within 24 hours.</p>
            </div>

            <p>Dear {{ $quote->name }},</p>
            
            <p>Thank you for considering LIV Transport for your transportation needs. We've successfully received your quote request and our team is already reviewing the details.</p>

            <div class="quote-details">
                <h3 style="color: #1e3c72; margin-top: 0;">Your Quote Reference</h3>
                <div class="detail-row">
                    <span class="detail-label">Quote Number:</span>
                    <span class="detail-value" style="font-weight: bold; color: #1e3c72;">{{ $quote->quote_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Project Type:</span>
                    <span class="detail-value">{{ $quote->project_type }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">{{ $quote->location }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Start Date:</span>
                    <span class="detail-value">{{ $quote->start_date->format('F j, Y') }}</span>
                </div>
                @if($quote->duration)
                <div class="detail-row">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value">{{ $quote->duration }}</span>
                </div>
                @endif
            </div>

            <div class="next-steps">
                <h3>What Happens Next?</h3>
                <ul>
                    <li>Our team will review your requirements in detail</li>
                    <li>We'll prepare a comprehensive quote tailored to your needs</li>
                    <li>A representative will contact you within 24 hours</li>
                    <li>We'll discuss any additional details or special requirements</li>
                </ul>
            </div>

            <div class="contact-info">
                <h3>Need Immediate Assistance?</h3>
                <p>If you have urgent requirements or questions, please don't hesitate to contact us:</p>
                <p style="margin: 10px 0;">
                    <strong>Phone:</strong> <a href="tel:225-555-0123" style="color: #1e3c72; text-decoration: none;">(225) 555-0123</a><br>
                    <strong>Email:</strong> <a href="mailto:livtransportllc@gmail.com" style="color: #1e3c72; text-decoration: none;">livtransportllc@gmail.com</a>
                </p>
            </div>

            <p>We appreciate your interest in LIV Transport and look forward to serving your transportation needs. Our commitment is to provide reliable, efficient, and professional services that exceed your expectations.</p>

            <p style="margin-top: 30px;">Best regards,</p>
            <p>
                <strong>The LIV Transport Team</strong><br>
                Professional Transportation Services
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 10px 0;">
                <strong>LIV Transport LLC</strong><br>
                Baton Rouge, Louisiana
            </p>
            <div class="social-links">
                <a href="http://liv-transport.test">Visit Our Website</a>
            </div>
            <p style="margin: 15px 0 0 0; font-size: 11px; color: #999;">
                This email was sent to {{ $quote->email }} regarding quote request #{{ $quote->quote_number }}.<br>
                If you did not submit this request, please contact us immediately.
            </p>
        </div>
    </div>
</body>
</html>