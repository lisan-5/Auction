<?php

namespace App\Http\Requests;

use App\Enums\AuctionCondition;
use App\Enums\AuctionStatus;
use App\Enums\AuctionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy handles update; keep true here.
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', new Enum(AuctionType::class)],
            'status' => ['sometimes', new Enum(AuctionStatus::class)],
            'start_time' => ['sometimes', 'date', 'after:2 hours'],
            'end_time' => ['sometimes', 'date', 'after:start_time'],
            'starting_price' => ['sometimes', 'numeric', 'min:0'],
            'reserve_price' => ['sometimes', 'numeric', 'min:0'],

            'year_created' => ['sometimes', 'string', 'max:10'],
            'dimensions' => ['sometimes', 'string', 'max:50'],
            'province' => ['sometimes', 'string', 'max:60'],
            'condition' => ['sometimes', new Enum(AuctionCondition::class)],

            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],

            'images' => ['sometimes', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.after' => 'The auction start time must be at least 2 hours from now to ensure proper scheduling.',
        ];
    }
}
