<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'no' => $this->no,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'expires_at' => $this->expiry_date,
            'paid_at' => $this->paid_at,
            'cancelled_at' => $this->cancelled_at,
            'refunded_at' => $this->refunded_at,
            'payment_method' => $this->payment_method,

            // Customer
            'customer' => [
                'id' => $this->portal_user_id,
                'email' => $this->portalUser?->email,
                'name' => $this->portalUser?->name,
            ],

            // Event
            'event' => [
                'id' => $this->event_id,
                'name' => $this->event?->name,
                'code' => $this->event?->code,
            ],

            // Pricing
            'pricing' => [
                'subtotal' => (float) $this->subtotal_amount,
                'discount' => (float) $this->discount_amount,
                'promo_code' => $this->promoCode?->code,
                'total' => (float) $this->total_amount,
                'currency' => $this->currency ?? 'VND',
            ],

            // Payment
            'payment' => [
                'status' => $this->status,
                'payment_method' => $this->payment_method,
                'attempts_count' => $this->paymentAttempts_count ?? $this->paymentAttempts()->count(),
            ],
        ];
    }
}
