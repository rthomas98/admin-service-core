<?php

namespace App\Observers;

use App\Enums\WorkOrderStatus;
use App\Jobs\GenerateInvoiceFromWorkOrder;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Log;

class WorkOrderObserver
{
    /**
     * Handle the WorkOrder "creating" event.
     */
    public function creating(WorkOrder $workOrder): void
    {
        // Auto-generate ticket number if not provided
        if (! $workOrder->ticket_number) {
            $workOrder->ticket_number = $this->generateTicketNumber($workOrder->company_id);
        }

        // Set default status if not provided
        if (! $workOrder->status) {
            $workOrder->status = WorkOrderStatus::DRAFT;
        }
    }

    /**
     * Handle the WorkOrder "created" event.
     */
    public function created(WorkOrder $workOrder): void
    {
        Log::info('Work Order created', [
            'ticket_number' => $workOrder->ticket_number,
            'company_id' => $workOrder->company_id,
            'status' => $workOrder->status->value,
        ]);
    }

    /**
     * Handle the WorkOrder "updated" event.
     */
    public function updated(WorkOrder $workOrder): void
    {
        // Auto-set completed_at timestamp when status changes to completed
        if ($workOrder->isDirty('status') && $workOrder->status === WorkOrderStatus::COMPLETED) {
            $workOrder->completed_at = now();
            $workOrder->saveQuietly();

            // Automatically generate invoice for completed work orders
            // Only for RAW Disposal company (company_id = 2)
            if ($workOrder->company_id === 2 && ! $workOrder->invoice_id) {
                GenerateInvoiceFromWorkOrder::dispatch($workOrder, true);
                Log::info('Invoice generation job dispatched for completed work order', [
                    'ticket_number' => $workOrder->ticket_number,
                ]);
            }
        }

        // Log status changes
        if ($workOrder->isDirty('status')) {
            Log::info('Work Order status changed', [
                'ticket_number' => $workOrder->ticket_number,
                'old_status' => $workOrder->getOriginal('status')?->value,
                'new_status' => $workOrder->status->value,
            ]);
        }
    }

    /**
     * Handle the WorkOrder "deleted" event.
     */
    public function deleted(WorkOrder $workOrder): void
    {
        Log::info('Work Order deleted', [
            'ticket_number' => $workOrder->ticket_number,
            'company_id' => $workOrder->company_id,
        ]);
    }

    /**
     * Handle the WorkOrder "restored" event.
     */
    public function restored(WorkOrder $workOrder): void
    {
        Log::info('Work Order restored', [
            'ticket_number' => $workOrder->ticket_number,
            'company_id' => $workOrder->company_id,
        ]);
    }

    /**
     * Handle the WorkOrder "force deleted" event.
     */
    public function forceDeleted(WorkOrder $workOrder): void
    {
        Log::warning('Work Order permanently deleted', [
            'ticket_number' => $workOrder->ticket_number,
            'company_id' => $workOrder->company_id,
        ]);
    }

    /**
     * Generate a unique ticket number for the company.
     */
    protected function generateTicketNumber(int $companyId): string
    {
        $lastTicket = WorkOrder::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastTicket ? intval(substr($lastTicket->ticket_number, -5)) + 1 : 15150;

        return str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
