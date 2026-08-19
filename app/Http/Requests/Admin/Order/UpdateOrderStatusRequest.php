<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|in:pending,processing,shipped,completed,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid,failed,refunded',
            'tracking_number' => 'nullable|string|max:100',
        ];
    }
}
