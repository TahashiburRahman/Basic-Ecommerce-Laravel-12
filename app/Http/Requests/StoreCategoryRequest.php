<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'image' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.config('image-optimization.max_input_kilobytes', 20480),
            ],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug,'.$categoryId],
        ];
    }

    public function messages(): array
    {
        return [
            'image.image' => 'The image must be a valid JPEG, PNG, or WebP image.',
            'image.mimes' => 'The image must be a JPEG, JPG, PNG, or WebP file.',
            'image.max' => 'The image may not be larger than 20 MB.',
        ];
    }
}
