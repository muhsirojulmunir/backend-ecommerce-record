<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $method             = strtoupper($this->input('payment_method', ''));
                $activeMethods      = array_map('strtoupper', config('duitku.active_methods', ['MANUAL_BCA', 'COD']));
                $maintenanceMethods = array_map('strtoupper', config('duitku.maintenance_methods', []));

                // Blokir jika metode ini sedang dalam maintenance
                if (in_array($method, $maintenanceMethods, true) && ! in_array($method, $activeMethods, true)) {
                    $validator->errors()->add(
                        'payment_method',
                        'Metode pembayaran ini sedang dalam pemeliharaan. Silakan pilih metode lain yang tersedia.'
                    );
                }
            },
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
