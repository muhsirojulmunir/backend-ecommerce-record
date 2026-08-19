<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => 'required|array',
            'shipping_address.recipient_name' => 'required|string|max:100',
            'shipping_address.phone' => 'required|string|max:20',
            'shipping_address.address_line' => 'required|string',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.province' => 'required|string|max:100',
            'shipping_address.postal_code' => 'required|string|max:10',
            'shipping_cost' => 'required|numeric|min:0',
            'courier' => 'required|string|max:50',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address.required' => 'Alamat pengiriman wajib diisi.',
            'shipping_cost.required' => 'Ongkos kirim wajib diisi.',
            'courier.required' => 'Kurir pengiriman wajib dipilih.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ];
    }
}
