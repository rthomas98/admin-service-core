<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class CustomerInvoiceController extends Controller
{
    /**
     * Display a listing of customer invoices
     */
    public function index(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        $query = Invoice::where('customer_id', $customer->id)
            ->with(['serviceOrder', 'payments']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from') && ! empty($request->date_from)) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && ! empty($request->date_to)) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'invoice_date');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['invoice_date', 'due_date', 'total_amount', 'balance_due', 'status'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest('invoice_date');
        }

        $perPage = min($request->get('per_page', 15), 50); // Max 50 items per page
        $invoices = $query->paginate($perPage);

        return response()->json([
            'invoices' => $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                    'formatted_invoice_date' => $invoice->invoice_date->format('M j, Y'),
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'formatted_due_date' => $invoice->due_date->format('M j, Y'),
                    'subtotal' => number_format($invoice->subtotal, 2),
                    'tax_amount' => number_format($invoice->tax_amount, 2),
                    'total_amount' => number_format($invoice->total_amount, 2),
                    'amount_paid' => number_format($invoice->amount_paid, 2),
                    'balance_due' => number_format($invoice->balance_due, 2),
                    'status' => $invoice->status,
                    'status_label' => ucfirst($invoice->status),
                    'is_overdue' => $invoice->isOverdue(),
                    'is_paid' => $invoice->isPaid(),
                    'line_items' => $invoice->line_items,
                    'notes' => $invoice->notes,
                    'service_order' => $invoice->serviceOrder ? [
                        'id' => $invoice->serviceOrder->id,
                        'order_number' => $invoice->serviceOrder->order_number ?? null,
                    ] : null,
                    'payment_count' => $invoice->payments->count(),
                    'last_payment_date' => $invoice->payments->max('payment_date')?->format('M j, Y'),
                ];
            }),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
                'from' => $invoices->firstItem(),
                'to' => $invoices->lastItem(),
            ],
            'filters' => [
                'status' => $request->get('status', 'all'),
                'search' => $request->get('search', ''),
                'date_from' => $request->get('date_from', ''),
                'date_to' => $request->get('date_to', ''),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    /**
     * Display the specified invoice
     */
    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the invoice belongs to the authenticated customer
        if ($invoice->customer_id !== $customer->id) {
            abort(404);
        }

        $invoice->load(['serviceOrder', 'payments', 'company']);

        return response()->json([
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'formatted_invoice_date' => $invoice->invoice_date->format('F j, Y'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'formatted_due_date' => $invoice->due_date->format('F j, Y'),
                'subtotal' => number_format($invoice->subtotal, 2),
                'tax_rate' => $invoice->tax_rate,
                'tax_amount' => number_format($invoice->tax_amount, 2),
                'discount_amount' => number_format($invoice->discount_amount ?? 0, 2),
                'total_amount' => number_format($invoice->total_amount, 2),
                'amount_paid' => number_format($invoice->amount_paid, 2),
                'balance_due' => number_format($invoice->balance_due, 2),
                'status' => $invoice->status,
                'status_label' => ucfirst($invoice->status),
                'is_overdue' => $invoice->isOverdue(),
                'is_paid' => $invoice->isPaid(),
                'line_items' => $invoice->line_items ?? [],
                'notes' => $invoice->notes,
                'terms_conditions' => $invoice->terms_conditions,
                'billing_address' => [
                    'address' => $invoice->billing_address,
                    'city' => $invoice->billing_city,
                    'parish' => $invoice->billing_parish,
                    'postal_code' => $invoice->billing_postal_code,
                    'full_address' => $invoice->full_billing_address,
                ],
                'service_order' => $invoice->serviceOrder ? [
                    'id' => $invoice->serviceOrder->id,
                    'order_number' => $invoice->serviceOrder->order_number ?? null,
                    'service_date' => $invoice->serviceOrder->service_date?->format('M j, Y'),
                ] : null,
                'company' => [
                    'name' => $invoice->company->name,
                    'address' => $invoice->company->address,
                    'city' => $invoice->company->city,
                    'state' => $invoice->company->state,
                    'zip' => $invoice->company->zip,
                    'phone' => $invoice->company->phone,
                    'email' => $invoice->company->email,
                ],
                'sent_date' => $invoice->sent_date?->format('M j, Y'),
                'paid_date' => $invoice->paid_date?->format('M j, Y'),
            ],
        ]);
    }

    /**
     * Get payment history for an invoice
     */
    public function payments(Request $request, Invoice $invoice): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the invoice belongs to the authenticated customer
        if ($invoice->customer_id !== $customer->id) {
            abort(404);
        }

        $payments = $invoice->payments()
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'formatted_payment_date' => $payment->payment_date->format('M j, Y'),
                    'amount' => number_format($payment->amount, 2),
                    'payment_method' => $payment->payment_method,
                    'reference_number' => $payment->reference_number,
                    'notes' => $payment->notes,
                ];
            });

        return response()->json([
            'payments' => $payments,
            'total_payments' => $payments->count(),
            'total_amount_paid' => number_format($invoice->amount_paid, 2),
            'remaining_balance' => number_format($invoice->balance_due, 2),
        ]);
    }

    /**
     * Download invoice PDF
     */
    public function download(Request $request, Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the invoice belongs to the authenticated customer
        if ($invoice->customer_id !== $customer->id) {
            abort(404);
        }

        // Load related data for the PDF
        $invoice->load(['customer', 'company', 'serviceOrder', 'workOrder', 'payments']);

        // Generate the PDF
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));

        // Return the PDF download
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
