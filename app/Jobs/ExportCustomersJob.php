<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ExportCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $companyId;

    protected $userEmail;

    protected $filters;

    /**
     * Create a new job instance.
     */
    public function __construct($companyId, $userEmail, array $filters = [])
    {
        $this->companyId = $companyId;
        $this->userEmail = $userEmail;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = Customer::where('company_id', $this->companyId);

        // Apply filters
        if (isset($this->filters['customer_type'])) {
            $query->whereIn('business_type', (array) $this->filters['customer_type']);
        }

        if (isset($this->filters['state'])) {
            $query->where('state', $this->filters['state']);
        }

        if (isset($this->filters['has_balance']) && $this->filters['has_balance']) {
            $query->whereHas('invoices', function ($q) {
                $q->whereIn('status', ['pending', 'overdue']);
            });
        }

        if (isset($this->filters['active']) && $this->filters['active']) {
            $query->whereHas('serviceOrders', function ($q) {
                $q->where('service_date', '>=', now()->subMonths(3));
            });
        }

        $customers = $query->get();

        // Create CSV
        $csv = Writer::createFromString();

        // Add headers
        $csv->insertOne([
            'Customer Number',
            'Organization',
            'Contact Name',
            'Email',
            'Phone',
            'Secondary Phone',
            'Address',
            'City',
            'State',
            'ZIP',
            'County',
            'Customer Type',
            'Customer Since',
            'Portal Access',
            'Outstanding Balance',
            'Total Orders',
            'Last Order Date',
            'Internal Memo',
            'External Message',
        ]);

        // Add data rows
        foreach ($customers as $customer) {
            $balance = $customer->invoices()
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('total');

            $lastOrder = $customer->serviceOrders()
                ->latest('service_date')
                ->first();

            $csv->insertOne([
                $customer->customer_number,
                $customer->organization,
                $customer->full_name,
                is_array($customer->emails) ? implode(', ', $customer->emails) : $customer->emails,
                $customer->phone,
                $customer->secondary_phone,
                $customer->address,
                $customer->city,
                $customer->state,
                $customer->zip,
                $customer->county,
                $customer->business_type,
                $customer->customer_since?->format('Y-m-d'),
                $customer->portal_access ? 'Yes' : 'No',
                $balance > 0 ? number_format($balance, 2) : '0.00',
                $customer->serviceOrders()->count(),
                $lastOrder?->service_date?->format('Y-m-d'),
                strip_tags($customer->internal_memo ?? ''),
                strip_tags($customer->external_message ?? ''),
            ]);
        }

        // Save file
        $filename = 'customers_export_'.now()->format('Y-m-d_His').'.csv';
        Storage::disk('local')->put('exports/'.$filename, $csv->toString());

        // Send email notification with download link
        \Mail::to($this->userEmail)->send(new \App\Mail\CustomerExportReady($filename, $customers->count()));
    }
}
