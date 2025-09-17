import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Download, Printer, CreditCard, Calendar, DollarSign, FileText, MapPin, Building, Phone, Mail, CheckCircle, Clock, AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface Customer {
    id: number;
    name: string;
    email: string;
    organization: string;
}

interface Company {
    name: string;
    address: string;
    phone: string;
    email: string;
}

interface LineItem {
    type: string;
    description: string;
    quantity: number;
    unit_price: string;
    total: string;
}

interface Payment {
    id: number;
    amount: string;
    payment_date: string;
    payment_method: string;
    reference_number?: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    invoice_date: string;
    due_date: string;
    status: string;
    is_overdue: boolean;
    description?: string;
    notes?: string;
    line_items: LineItem[];
    subtotal: string;
    tax_rate: number;
    tax_amount: string;
    total_amount: string;
    amount_paid: string;
    balance_due: string;
    billing_address: string;
    billing_city: string;
    billing_parish: string;
    billing_postal_code: string;
    company: Company;
    payments: Payment[];
}

interface Props {
    customer: Customer;
    invoice: Invoice;
}

export default function InvoiceDetail({ customer, invoice }: Props) {
    const handleDownload = () => {
        // This will be implemented to call the PDF download endpoint
        window.location.href = `/api/customer/invoices/${invoice.id}/download`;
    };

    const handlePrint = () => {
        window.print();
    };

    const handlePayNow = () => {
        // This will be implemented to redirect to payment gateway
        router.visit(`/customer/invoices/${invoice.id}/pay`);
    };

    const getStatusBadge = (status: string, isOverdue: boolean) => {
        if (isOverdue && status !== 'paid') {
            return <Badge variant="destructive">Overdue</Badge>;
        }

        const statusConfig: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline' | 'success'; label: string }> = {
            draft: { variant: 'secondary', label: 'Draft' },
            sent: { variant: 'default', label: 'Sent' },
            viewed: { variant: 'outline', label: 'Viewed' },
            partially_paid: { variant: 'default', label: 'Partially Paid' },
            paid: { variant: 'success', label: 'Paid' },
            overdue: { variant: 'destructive', label: 'Overdue' },
            cancelled: { variant: 'secondary', label: 'Cancelled' },
            refunded: { variant: 'outline', label: 'Refunded' },
            written_off: { variant: 'secondary', label: 'Written Off' },
        };

        const config = statusConfig[status] || { variant: 'default', label: status };
        return <Badge variant={config.variant}>{config.label}</Badge>;
    };

    const canPay = parseFloat(invoice.balance_due.replace(',', '')) > 0 &&
                   !['paid', 'cancelled', 'refunded', 'written_off'].includes(invoice.status);

    return (
        <>
            <Head title={`Invoice #${invoice.invoice_number}`} />

            <div className="min-h-screen bg-gray-50 print:bg-white">
                {/* Header */}
                <div className="bg-white border-b print:hidden">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex items-center justify-between h-16">
                            <div className="flex items-center">
                                <Link href="/customer/invoices" className="mr-4">
                                    <Button variant="ghost" size="sm">
                                        <ArrowLeft className="h-4 w-4 mr-2" />
                                        Back to Invoices
                                    </Button>
                                </Link>
                                <h1 className="text-xl font-semibold">Invoice #{invoice.invoice_number}</h1>
                            </div>
                            <div className="flex items-center space-x-2">
                                <Button variant="outline" size="sm" onClick={handlePrint}>
                                    <Printer className="h-4 w-4 mr-2" />
                                    Print
                                </Button>
                                <Button variant="outline" size="sm" onClick={handleDownload}>
                                    <Download className="h-4 w-4 mr-2" />
                                    Download PDF
                                </Button>
                                {canPay && (
                                    <Button variant="default" size="sm" onClick={handlePayNow}>
                                        <CreditCard className="h-4 w-4 mr-2" />
                                        Pay Now
                                    </Button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Invoice Content */}
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Status Alert */}
                    {invoice.is_overdue && canPay && (
                        <Alert variant="destructive" className="mb-6 print:hidden">
                            <AlertCircle className="h-4 w-4" />
                            <AlertDescription>
                                This invoice is overdue. Please make payment as soon as possible to avoid service interruption.
                            </AlertDescription>
                        </Alert>
                    )}

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Main Invoice Card */}
                        <div className="lg:col-span-2">
                            <Card className="print:shadow-none print:border-0">
                                <CardHeader>
                                    <div className="flex justify-between items-start">
                                        <div>
                                            <CardTitle className="text-2xl">Invoice</CardTitle>
                                            <CardDescription className="mt-2">
                                                #{invoice.invoice_number}
                                            </CardDescription>
                                        </div>
                                        <div className="text-right">
                                            {getStatusBadge(invoice.status, invoice.is_overdue)}
                                            <div className="mt-2 text-3xl font-bold">
                                                ${invoice.total_amount}
                                            </div>
                                        </div>
                                    </div>
                                </CardHeader>

                                <CardContent className="space-y-6">
                                    {/* Company and Customer Info */}
                                    <div className="grid grid-cols-2 gap-6">
                                        <div>
                                            <h3 className="font-semibold mb-2">From</h3>
                                            <div className="text-sm text-muted-foreground space-y-1">
                                                <div className="flex items-center">
                                                    <Building className="h-4 w-4 mr-2" />
                                                    {invoice.company.name}
                                                </div>
                                                {invoice.company.address && (
                                                    <div className="flex items-center">
                                                        <MapPin className="h-4 w-4 mr-2" />
                                                        {invoice.company.address}
                                                    </div>
                                                )}
                                                {invoice.company.phone && (
                                                    <div className="flex items-center">
                                                        <Phone className="h-4 w-4 mr-2" />
                                                        {invoice.company.phone}
                                                    </div>
                                                )}
                                                {invoice.company.email && (
                                                    <div className="flex items-center">
                                                        <Mail className="h-4 w-4 mr-2" />
                                                        {invoice.company.email}
                                                    </div>
                                                )}
                                            </div>
                                        </div>

                                        <div>
                                            <h3 className="font-semibold mb-2">Bill To</h3>
                                            <div className="text-sm text-muted-foreground space-y-1">
                                                <div>{customer.organization || customer.name}</div>
                                                {invoice.billing_address && (
                                                    <div>{invoice.billing_address}</div>
                                                )}
                                                {(invoice.billing_city || invoice.billing_parish || invoice.billing_postal_code) && (
                                                    <div>
                                                        {[invoice.billing_city, invoice.billing_parish, invoice.billing_postal_code]
                                                            .filter(Boolean)
                                                            .join(', ')}
                                                    </div>
                                                )}
                                                <div>{customer.email}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <Separator />

                                    {/* Invoice Dates */}
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="flex items-center text-sm">
                                            <Calendar className="h-4 w-4 mr-2 text-muted-foreground" />
                                            <span className="text-muted-foreground">Invoice Date:</span>
                                            <span className="ml-2 font-medium">{invoice.invoice_date}</span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <Calendar className="h-4 w-4 mr-2 text-muted-foreground" />
                                            <span className="text-muted-foreground">Due Date:</span>
                                            <span className="ml-2 font-medium">{invoice.due_date}</span>
                                        </div>
                                    </div>

                                    {invoice.description && (
                                        <>
                                            <Separator />
                                            <div>
                                                <h3 className="font-semibold mb-2">Description</h3>
                                                <p className="text-sm text-muted-foreground">{invoice.description}</p>
                                            </div>
                                        </>
                                    )}

                                    <Separator />

                                    {/* Line Items */}
                                    <div>
                                        <h3 className="font-semibold mb-4">Items</h3>
                                        <div className="overflow-x-auto">
                                            <table className="w-full">
                                                <thead>
                                                    <tr className="border-b">
                                                        <th className="text-left py-2 text-sm font-medium">Description</th>
                                                        <th className="text-center py-2 text-sm font-medium">Qty</th>
                                                        <th className="text-right py-2 text-sm font-medium">Unit Price</th>
                                                        <th className="text-right py-2 text-sm font-medium">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {invoice.line_items.length > 0 ? (
                                                        invoice.line_items.map((item, index) => (
                                                            <tr key={index} className="border-b">
                                                                <td className="py-3">
                                                                    <div className="text-sm font-medium">{item.description}</div>
                                                                    {item.type && (
                                                                        <div className="text-xs text-muted-foreground capitalize">
                                                                            {item.type.replace('_', ' ')}
                                                                        </div>
                                                                    )}
                                                                </td>
                                                                <td className="text-center py-3 text-sm">{item.quantity}</td>
                                                                <td className="text-right py-3 text-sm">${item.unit_price}</td>
                                                                <td className="text-right py-3 text-sm font-medium">${item.total}</td>
                                                            </tr>
                                                        ))
                                                    ) : (
                                                        <tr>
                                                            <td colSpan={4} className="py-4 text-center text-muted-foreground text-sm">
                                                                No line items available
                                                            </td>
                                                        </tr>
                                                    )}
                                                </tbody>
                                            </table>
                                        </div>

                                        {/* Totals */}
                                        <div className="mt-6 space-y-2">
                                            <div className="flex justify-between text-sm">
                                                <span className="text-muted-foreground">Subtotal</span>
                                                <span>${invoice.subtotal}</span>
                                            </div>
                                            {invoice.tax_rate > 0 && (
                                                <div className="flex justify-between text-sm">
                                                    <span className="text-muted-foreground">Tax ({invoice.tax_rate}%)</span>
                                                    <span>${invoice.tax_amount}</span>
                                                </div>
                                            )}
                                            <Separator />
                                            <div className="flex justify-between font-semibold">
                                                <span>Total</span>
                                                <span className="text-lg">${invoice.total_amount}</span>
                                            </div>
                                            {parseFloat(invoice.amount_paid.replace(',', '')) > 0 && (
                                                <>
                                                    <div className="flex justify-between text-sm">
                                                        <span className="text-muted-foreground">Amount Paid</span>
                                                        <span className="text-green-600">-${invoice.amount_paid}</span>
                                                    </div>
                                                    <Separator />
                                                    <div className="flex justify-between font-semibold">
                                                        <span>Balance Due</span>
                                                        <span className="text-lg">${invoice.balance_due}</span>
                                                    </div>
                                                </>
                                            )}
                                        </div>
                                    </div>

                                    {invoice.notes && (
                                        <>
                                            <Separator />
                                            <div>
                                                <h3 className="font-semibold mb-2">Notes</h3>
                                                <p className="text-sm text-muted-foreground whitespace-pre-wrap">{invoice.notes}</p>
                                            </div>
                                        </>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Payment History */}
                            {invoice.payments.length > 0 && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="text-lg">Payment History</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-3">
                                            {invoice.payments.map((payment) => (
                                                <div key={payment.id} className="border-l-2 border-green-500 pl-3">
                                                    <div className="flex justify-between items-start">
                                                        <div>
                                                            <div className="text-sm font-medium">${payment.amount}</div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {payment.payment_date}
                                                            </div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {payment.payment_method}
                                                            </div>
                                                        </div>
                                                        {payment.reference_number && (
                                                            <div className="text-xs text-muted-foreground">
                                                                Ref: {payment.reference_number}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Quick Actions */}
                            <Card className="print:hidden">
                                <CardHeader>
                                    <CardTitle className="text-lg">Quick Actions</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2">
                                        {canPay && (
                                            <Button className="w-full" onClick={handlePayNow}>
                                                <CreditCard className="h-4 w-4 mr-2" />
                                                Pay Now
                                            </Button>
                                        )}
                                        <Button variant="outline" className="w-full" onClick={handleDownload}>
                                            <Download className="h-4 w-4 mr-2" />
                                            Download PDF
                                        </Button>
                                        <Button variant="outline" className="w-full" onClick={handlePrint}>
                                            <Printer className="h-4 w-4 mr-2" />
                                            Print Invoice
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Status Info */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg">Invoice Status</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm text-muted-foreground">Status</span>
                                            {getStatusBadge(invoice.status, invoice.is_overdue)}
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm text-muted-foreground">Total</span>
                                            <span className="font-medium">${invoice.total_amount}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm text-muted-foreground">Paid</span>
                                            <span className="font-medium text-green-600">${invoice.amount_paid}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm text-muted-foreground">Balance</span>
                                            <span className="font-semibold text-lg">${invoice.balance_due}</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>

            {/* Print Styles */}
            <style jsx>{`
                @media print {
                    @page {
                        margin: 1cm;
                    }
                    .print\\:hidden {
                        display: none !important;
                    }
                    .print\\:shadow-none {
                        box-shadow: none !important;
                    }
                    .print\\:border-0 {
                        border: 0 !important;
                    }
                    .print\\:bg-white {
                        background-color: white !important;
                    }
                }
            `}</style>
        </>
    );
}