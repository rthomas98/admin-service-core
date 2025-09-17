<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #5C2C86;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #5C2C86;
            margin-bottom: 5px;
        }
        .invoice-title {
            text-align: right;
            margin-top: -80px;
        }
        .invoice-title h1 {
            color: #333;
            font-size: 32px;
            margin: 0;
        }
        .invoice-number {
            color: #666;
            font-size: 16px;
            margin-top: 5px;
        }
        .invoice-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .status-paid { background: #10B981; color: white; }
        .status-pending { background: #F59E0B; color: white; }
        .status-overdue { background: #EF4444; color: white; }
        .status-draft { background: #6B7280; color: white; }
        .status-sent { background: #3B82F6; color: white; }
        .row {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .col-right {
            text-align: right;
        }
        .info-block {
            margin-bottom: 20px;
        }
        .info-block h3 {
            color: #5C2C86;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .info-block p {
            margin: 0;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        th {
            background-color: #5C2C86;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        th.text-right,
        td.text-right {
            text-align: right;
        }
        th.text-center,
        td.text-center {
            text-align: center;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e5e5;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .totals {
            margin-top: 30px;
            text-align: right;
        }
        .totals-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8px;
        }
        .totals-label {
            padding-right: 20px;
            color: #666;
        }
        .totals-value {
            min-width: 100px;
            text-align: right;
            font-weight: 500;
        }
        .total-row {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #5C2C86;
            font-size: 18px;
            font-weight: bold;
        }
        .balance-due {
            color: #EF4444;
            font-size: 20px;
            font-weight: bold;
        }
        .notes {
            margin-top: 40px;
            padding: 20px;
            background-color: #f9f9f9;
            border-left: 4px solid #5C2C86;
        }
        .notes h3 {
            margin-top: 0;
            color: #5C2C86;
        }
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .payment-info {
            margin-top: 40px;
            padding: 20px;
            background-color: #f0fdf4;
            border: 2px solid #10B981;
            border-radius: 8px;
        }
        .payment-info h3 {
            margin-top: 0;
            color: #10B981;
        }
        @page {
            size: A4;
            margin: 0.5in;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ $invoice->company->name }}</div>
            @if($invoice->company->address)
                <div>{{ $invoice->company->address }}</div>
            @endif
            @if($invoice->company->phone)
                <div>Phone: {{ $invoice->company->phone }}</div>
            @endif
            @if($invoice->company->email)
                <div>Email: {{ $invoice->company->email }}</div>
            @endif
        </div>

        <div class="invoice-title">
            <h1>INVOICE</h1>
            <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
            @php
                $statusClass = 'status-' . str_replace('_', '-', $invoice->status->value);
            @endphp
            <div class="invoice-status {{ $statusClass }}">
                {{ $invoice->status->label() }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="info-block">
                <h3>Bill To</h3>
                <p>
                    <strong>{{ $invoice->customer->organization ?: $invoice->customer->full_name }}</strong><br>
                    @if($invoice->billing_address)
                        {{ $invoice->billing_address }}<br>
                    @endif
                    @if($invoice->billing_city || $invoice->billing_parish || $invoice->billing_postal_code)
                        {{ $invoice->billing_city }}{{ $invoice->billing_parish ? ', ' . $invoice->billing_parish : '' }}
                        {{ $invoice->billing_postal_code }}<br>
                    @endif
                    {{ $invoice->customer->getNotificationEmail() }}
                </p>
            </div>
        </div>

        <div class="col col-right">
            <div class="info-block">
                <h3>Invoice Details</h3>
                <p>
                    <strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('F j, Y') }}<br>
                    <strong>Due Date:</strong> {{ $invoice->due_date->format('F j, Y') }}<br>
                    @if($invoice->serviceOrder)
                        <strong>Service Order:</strong> #{{ $invoice->serviceOrder->order_number }}<br>
                    @endif
                    @if($invoice->workOrder)
                        <strong>Work Order:</strong> #{{ $invoice->workOrder->id }}<br>
                    @endif
                </p>
            </div>
        </div>
    </div>

    @if($invoice->description)
        <div class="info-block">
            <h3>Description</h3>
            <p>{{ $invoice->description }}</p>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="text-center" style="width: 15%;">Quantity</th>
                <th class="text-right" style="width: 15%;">Unit Price</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->line_items && count($invoice->line_items) > 0)
                @foreach($invoice->line_items as $item)
                    <tr>
                        <td>
                            {{ $item['description'] ?? '' }}
                            @if(isset($item['type']))
                                <br><small style="color: #666;">{{ ucfirst(str_replace('_', ' ', $item['type'])) }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                        <td class="text-right">${{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                        <td class="text-right">${{ number_format($item['total'] ?? ($item['quantity'] * $item['unit_price']), 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">No line items</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="totals">
        <table style="width: 300px; margin-left: auto;">
            <tr>
                <td style="text-align: right; padding: 5px 10px;">Subtotal:</td>
                <td style="text-align: right; padding: 5px 10px; width: 100px;">
                    ${{ number_format($invoice->subtotal, 2) }}
                </td>
            </tr>
            @if($invoice->tax_rate > 0)
                <tr>
                    <td style="text-align: right; padding: 5px 10px;">Tax ({{ $invoice->tax_rate }}%):</td>
                    <td style="text-align: right; padding: 5px 10px;">
                        ${{ number_format($invoice->tax_amount, 2) }}
                    </td>
                </tr>
            @endif
            <tr style="border-top: 2px solid #5C2C86;">
                <td style="text-align: right; padding: 10px 10px 5px; font-weight: bold; font-size: 16px;">Total:</td>
                <td style="text-align: right; padding: 10px 10px 5px; font-weight: bold; font-size: 16px;">
                    ${{ number_format($invoice->total_amount, 2) }}
                </td>
            </tr>
            @if($invoice->amount_paid > 0)
                <tr>
                    <td style="text-align: right; padding: 5px 10px; color: #10B981;">Amount Paid:</td>
                    <td style="text-align: right; padding: 5px 10px; color: #10B981;">
                        -${{ number_format($invoice->amount_paid, 2) }}
                    </td>
                </tr>
                <tr style="border-top: 1px solid #e5e5e5;">
                    <td style="text-align: right; padding: 10px 10px 5px; font-weight: bold; font-size: 18px; color: #EF4444;">
                        Balance Due:
                    </td>
                    <td style="text-align: right; padding: 10px 10px 5px; font-weight: bold; font-size: 18px; color: #EF4444;">
                        ${{ number_format($invoice->balance_due, 2) }}
                    </td>
                </tr>
            @endif
        </table>
    </div>

    @if($invoice->notes)
        <div class="notes">
            <h3>Notes</h3>
            <p>{!! nl2br(e($invoice->notes)) !!}</p>
        </div>
    @endif

    @if($invoice->balance_due > 0 && !in_array($invoice->status->value, ['paid', 'cancelled', 'refunded']))
        <div class="payment-info">
            <h3>Payment Information</h3>
            <p>
                Please remit payment by {{ $invoice->due_date->format('F j, Y') }} to avoid late fees.<br>
                <strong>Payment Options:</strong><br>
                • Online: Visit our customer portal<br>
                • Phone: Call {{ $invoice->company->phone ?? 'our office' }}<br>
                • Check: Make payable to {{ $invoice->company->name }}
            </p>
        </div>
    @endif

    <div class="footer">
        <p>
            Thank you for your business!<br>
            {{ $invoice->company->name }} • Generated on {{ now()->format('F j, Y') }}
        </p>
    </div>
</body>
</html>