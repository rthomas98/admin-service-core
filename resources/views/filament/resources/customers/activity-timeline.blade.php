<div class="space-y-4">
    @php
        use App\Models\ServiceOrder;
        use App\Models\Invoice;
        use App\Models\Payment;
        use App\Models\Quote;
        use App\Models\Notification;
        use Illuminate\Support\Collection;

        // Collect all activities
        $activities = collect();

        // Add service orders
        $serviceOrders = ServiceOrder::where('customer_id', $record->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'service_order',
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'color' => 'primary',
                    'title' => 'Service Order #' . $order->order_number,
                    'description' => $order->description ?? 'Service order created',
                    'status' => $order->status,
                    'date' => $order->service_date ?? $order->created_at,
                    'meta' => [
                        'Status' => ucfirst(str_replace('_', ' ', $order->status)),
                        'Total' => '$' . number_format($order->total ?? 0, 2),
                    ],
                ];
            });
        $activities = $activities->merge($serviceOrders);

        // Add invoices
        $invoices = Invoice::where('customer_id', $record->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'type' => 'invoice',
                    'icon' => 'heroicon-o-document-text',
                    'color' => $invoice->status->value === 'paid' ? 'success' : ($invoice->status->value === 'overdue' ? 'danger' : 'warning'),
                    'title' => 'Invoice #' . $invoice->invoice_number,
                    'description' => 'Invoice ' . $invoice->status->getLabel(),
                    'status' => $invoice->status->value,
                    'date' => $invoice->invoice_date ?? $invoice->created_at,
                    'meta' => [
                        'Amount' => '$' . number_format($invoice->total, 2),
                        'Due Date' => $invoice->due_date?->format('M d, Y') ?? 'N/A',
                    ],
                ];
            });
        $activities = $activities->merge($invoices);

        // Add payments
        $payments = Payment::where('customer_id', $record->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'payment',
                    'icon' => 'heroicon-o-currency-dollar',
                    'color' => 'success',
                    'title' => 'Payment Received',
                    'description' => 'Payment via ' . ucfirst($payment->payment_method),
                    'status' => 'completed',
                    'date' => $payment->payment_date ?? $payment->created_at,
                    'meta' => [
                        'Amount' => '$' . number_format($payment->amount, 2),
                        'Reference' => $payment->reference_number ?? 'N/A',
                    ],
                ];
            });
        $activities = $activities->merge($payments);

        // Add quotes
        $quotes = Quote::where('customer_id', $record->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($quote) {
                return [
                    'type' => 'quote',
                    'icon' => 'heroicon-o-calculator',
                    'color' => $quote->status === 'accepted' ? 'success' : ($quote->status === 'rejected' ? 'danger' : 'info'),
                    'title' => 'Quote #' . $quote->quote_number,
                    'description' => 'Quote ' . $quote->status,
                    'status' => $quote->status,
                    'date' => $quote->created_at,
                    'meta' => [
                        'Amount' => '$' . number_format($quote->total ?? 0, 2),
                        'Valid Until' => $quote->valid_until?->format('M d, Y') ?? 'N/A',
                    ],
                ];
            });
        $activities = $activities->merge($quotes);

        // Sort all activities by date
        $activities = $activities->sortByDesc('date')->take(20);

        // Group by date
        $groupedActivities = $activities->groupBy(function ($activity) {
            $date = $activity['date'];
            if (is_string($date)) {
                $date = \Carbon\Carbon::parse($date);
            }

            if ($date->isToday()) {
                return 'Today';
            } elseif ($date->isYesterday()) {
                return 'Yesterday';
            } elseif ($date->isCurrentWeek()) {
                return 'This Week';
            } elseif ($date->isLastWeek()) {
                return 'Last Week';
            } elseif ($date->isCurrentMonth()) {
                return 'This Month';
            } elseif ($date->isLastMonth()) {
                return 'Last Month';
            } else {
                return $date->format('F Y');
            }
        });
    @endphp

    @if($activities->isEmpty())
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="mt-2 text-sm">No activity recorded yet</p>
        </div>
    @else
        @foreach($groupedActivities as $period => $periodActivities)
            <div class="relative">
                {{-- Period Label --}}
                <div class="sticky top-0 z-10 bg-white dark:bg-gray-900 py-2">
                    <div class="flex items-center">
                        <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
                        <span class="px-3 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $period }}</span>
                        <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                </div>

                {{-- Activities for this period --}}
                <div class="space-y-3">
                    @foreach($periodActivities as $activity)
                        <div class="relative flex gap-x-4">
                            {{-- Timeline Line --}}
                            @if(!$loop->last || !$loop->parent->last)
                                <div class="absolute left-5 top-8 -bottom-3 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                            @endif

                            {{-- Icon --}}
                            <div class="relative flex h-10 w-10 flex-none items-center justify-center rounded-full
                                        {{ $activity['color'] === 'success' ? 'bg-green-100 dark:bg-green-900' : '' }}
                                        {{ $activity['color'] === 'danger' ? 'bg-red-100 dark:bg-red-900' : '' }}
                                        {{ $activity['color'] === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900' : '' }}
                                        {{ $activity['color'] === 'info' ? 'bg-blue-100 dark:bg-blue-900' : '' }}
                                        {{ $activity['color'] === 'primary' ? 'bg-primary-100 dark:bg-primary-900' : '' }}">
                                <svg class="h-5 w-5
                                            {{ $activity['color'] === 'success' ? 'text-green-600 dark:text-green-400' : '' }}
                                            {{ $activity['color'] === 'danger' ? 'text-red-600 dark:text-red-400' : '' }}
                                            {{ $activity['color'] === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : '' }}
                                            {{ $activity['color'] === 'info' ? 'text-blue-600 dark:text-blue-400' : '' }}
                                            {{ $activity['color'] === 'primary' ? 'text-primary-600 dark:text-primary-400' : '' }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @switch($activity['type'])
                                        @case('service_order')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            @break
                                        @case('invoice')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            @break
                                        @case('payment')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            @break
                                        @case('quote')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            @break
                                        @default
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    @endswitch
                                </svg>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 py-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $activity['title'] }}
                                    </p>
                                    <time class="text-xs text-gray-500 dark:text-gray-400">
                                        @php
                                            $date = $activity['date'];
                                            if (is_string($date)) {
                                                $date = \Carbon\Carbon::parse($date);
                                            }
                                        @endphp
                                        {{ $date->format('g:i A') }}
                                    </time>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $activity['description'] }}</p>

                                @if(!empty($activity['meta']))
                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1">
                                        @foreach($activity['meta'] as $key => $value)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                <span class="font-medium">{{ $key }}:</span> {{ $value }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    {{-- Load More Button --}}
    @if($activities->count() >= 20)
        <div class="text-center pt-4">
            <button type="button"
                    class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                Load more activity
            </button>
        </div>
    @endif
</div>