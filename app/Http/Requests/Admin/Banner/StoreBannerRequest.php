<?php

namespace App\Http\Requests\Admin\Banner;

use App\Models\Banner;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            // Banner boleh berupa foto maupun video (rasio 3:1 / 1200 x 400 px)
            'uploaded_image' => 'required|file|mimes:jpeg,png,jpg,webp,mp4,mov,webm,ogg|max:30720',
            'link' => 'nullable|string|max:255',
            'position' => 'nullable|in:hero,promo,sidebar',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ];
    }

    /**
     * Batas maksimal 3 banner hero, sama dengan aturan di panel admin web.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('position', 'hero') !== 'hero') {
                return;
            }

            if (Banner::where('position', 'hero')->count() >= 3) {
                $validator->errors()->add('position', 'Batas maksimal banner Hero adalah 3 banner.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul banner wajib diisi.',
            'uploaded_image.required' => 'Foto atau video banner wajib diunggah.',
            'uploaded_image.mimes' => 'Format banner harus JPG, PNG, WEBP, MP4, MOV, WEBM, atau OGG.',
            'uploaded_image.max' => 'Ukuran file banner maksimal 30MB.',
        ];
    }
}
