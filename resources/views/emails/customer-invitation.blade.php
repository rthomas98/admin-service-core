<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal Invitation</title>
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1f2937;
        }
        
        .message {
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.7;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(5, 150, 105, 0.3);
        }
        
        .features {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 24px;
            margin: 30px 0;
        }
        
        .features h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #1f2937;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 8px 0;
            padding-left: 24px;
            position: relative;
        }
        
        .feature-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #059669;
            font-weight: bold;
        }
        
        .expiry-notice {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            margin: 30px 0;
            border-radius: 4px;
        }
        
        .expiry-notice p {
            margin: 0;
            color: #92400e;
            font-weight: 500;
        }
        
        .support {
            background-color: #f1f5f9;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
        }
        
        .support h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1f2937;
        }
        
        .support p {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }
        
        .footer {
            background-color: #1f2937;
            padding: 30px;
            text-align: center;
            color: #9ca3af;
        }
        
        .footer p {
            margin: 8px 0;
            font-size: 14px;
        }
        
        .footer a {
            color: #10b981;
            text-decoration: none;
        }
        
        /* Responsive design */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            
            .header, .content, .footer {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .cta-button {
                display: block;
                text-align: center;
                padding: 14px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $companyName }}</h1>
            <p>Customer Portal Invitation</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hello {{ $customerName }},
            </div>
            
            <div class="message">
                <p>We're excited to invite you to access your new customer portal! This secure platform will give you 24/7 access to manage your account, view services, and stay connected with us.</p>
            </div>
            
            <!-- CTA Button -->
            <div style="text-align: center;">
                <a href="{{ $registrationUrl }}" class="cta-button">
                    Complete Your Registration
                </a>
            </div>
            
            <!-- Features -->
            <div class="features">
                <h3>What you can do in your portal:</h3>
                <ul class="feature-list">
                    <li>View and manage your service requests</li>
                    <li>Track order status and delivery schedules</li>
                    <li>Access invoices and payment history</li>
                    <li>Update your contact information</li>
                    <li>Request new services or quotes</li>
                    <li>Communicate directly with our team</li>
                </ul>
            </div>
            
            <!-- Expiry Notice -->
            <div class="expiry-notice">
                <p><strong>Important:</strong> This invitation expires on {{ $expiresAt->format('F j, Y \a\t g:i A') }}. Please complete your registration before this date.</p>
            </div>
            
            <!-- Support -->
            <div class="support">
                <h4>Need help getting started?</h4>
                <p>Our team is here to assist you. Contact us if you have any questions about accessing your portal.</p>
            </div>
            
            <div class="message">
                <p>Thank you for being a valued customer. We look forward to serving you better through this new platform!</p>
                
                <p style="margin-top: 20px;">
                    Best regards,<br>
                    <strong>The {{ $companyName }} Team</strong>
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</p>
            <p>
                If you have any questions, please contact us at 
                <a href="mailto:support@{{ strtolower(str_replace(' ', '', $companyName)) }}.com">
                    support@{{ strtolower(str_replace(' ', '', $companyName)) }}.com
                </a>
            </p>
            <p style="font-size: 12px; margin-top: 16px;">
                This invitation was sent to {{ $invite->email }}. 
                If you believe you received this email in error, please ignore it.
            </p>
        </div>
    </div>
</body>
</html>