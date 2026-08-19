<?php

namespace App\Http\Requests\Admin\Banner;

use App\Models\Banner;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            // Banner boleh berupa foto maupun video (rasio 3:1 / 1200 x 400 px)
            'uploaded_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,mp4,mov,webm,ogg|max:30720',
            'link' => 'nullable|string|max:255',
            'position' => 'nullable|in:hero,promo,sidebar',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ];
    }

    /**
     * Batas maksimal 3 banner hero (tidak menghitung banner yang sedang diedit).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('position') !== 'hero') {
                return;
            }

            $currentId = $this->route('banner');
            $currentId = is_object($currentId) ? $currentId->id : $currentId;

            $count = Banner::where('position', 'hero')
                ->where('id', '!=', $currentId)
                ->count();

            if ($count >= 3) {
                $validator->errors()->add('position', 'Batas maksimal banner Hero adalah 3 banner.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'uploaded_image.mimes' => 'Format banner harus JPG, PNG, WEBP, MP4, MOV, WEBM, atau OGG.',
            'uploaded_image.max' => 'Ukuran file banner maksimal 30MB.',
        ];
    }
}
