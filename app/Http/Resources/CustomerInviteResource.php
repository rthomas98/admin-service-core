<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerInviteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'status' => $this->expiration_status,
            'expires_at' => $this->expires_at->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Relationships
            'customer' => $this->when($this->relationLoaded('customer'), function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->full_name,
                    'email' => $this->customer->getNotificationEmail(),
                    'phone' => $this->customer->display_phone,
                    'customer_number' => $this->customer->customer_number,
                ];
            }),

            'invited_by' => $this->when($this->relationLoaded('invitedBy'), function () {
                return [
                    'id' => $this->invitedBy->id,
                    'name' => $this->invitedBy->name,
                    'email' => $this->invitedBy->email,
                ];
            }),

            'company' => $this->when($this->relationLoaded('company'), function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),

            // Computed attributes
            'is_expired' => $this->isExpired(),
            'is_accepted' => $this->isAccepted(),
            'is_valid' => $this->isValid(),
            'expires_in_hours' => $this->when(! $this->isExpired() && ! $this->isAccepted(), function () {
                return max(0, round($this->expires_at->diffInHours(now(), false) * -1));
            }),

            // URLs (only for admin users)
            'resend_url' => $this->when($request->user() && ! $this->isAccepted(), function () {
                return route('api.customer-invites.resend', $this->id);
            }),
            'cancel_url' => $this->when($request->user() && ! $this->isAccepted(), function () {
                return route('api.customer-invites.destroy', $this->id);
            }),
        ];
    }
}
