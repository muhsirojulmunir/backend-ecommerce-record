<?php

namespace App\Http\Requests\Admin\WebsiteSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebsiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => 'nullable|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'store_address' => 'nullable|string',
            'store_city_id' => 'nullable|integer',
            'couriers' => 'nullable|array',
            'couriers.*' => 'string|in:jne,pos,tiki,jnt,sicepat,anteraja',
        ];
    }
}
