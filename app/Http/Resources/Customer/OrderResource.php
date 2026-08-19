<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_price' => (float) $this->total_price,
            'shipping_cost' => (float) $this->shipping_cost,
            'grand_total' => (float) $this->grand_total,
            'status' => $this->status,
            'shipping_address' => $this->shipping_address,
            'courier' => $this->courier,
            'tracking_number' => $this->tracking_number,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'variant_info' => $item->variant_info,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => $item->subtotal,
                ];
            }),
            'return_request' => $this->returnRequest ? [
                'id' => $this->returnRequest->id,
                'type' => $this->returnRequest->type,
                'reason' => $this->returnRequest->reason,
                'status' => $this->returnRequest->status,
                'admin_notes' => $this->returnRequest->admin_notes,
                'resolved_at' => $this->returnRequest->resolved_at?->toDateTimeString(),
            ] : null,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
