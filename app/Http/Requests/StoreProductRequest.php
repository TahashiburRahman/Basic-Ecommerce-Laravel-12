<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'featured' => ['boolean'],
            'image' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.config('image-optimization.max_input_kilobytes', 20480),
            ],
            'remove_image' => ['sometimes', 'boolean'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,slug,'.$productId,
            ],
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
