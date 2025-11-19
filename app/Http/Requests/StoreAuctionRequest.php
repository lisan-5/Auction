<?php

namespace App\Http\Requests;

use App\Enums\AuctionCondition;
use App\Enums\AuctionStatus;
use App\Enums\AuctionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy will handle create; keep this true.
        return true;
    }

    public function rules(): array
    {
        return [
            // artist_id will be set automatically from authenticated user
            "title" => ["required", "string", "max:255"],
            "description" => ["nullable", "string"],
            "type" => ["required", new Enum(AuctionType::class)],
            "status" => ["required", new Enum(AuctionStatus::class)],
            "start_time" => ["required", "date", "after:2 hours"],
            "end_time" => ["required", "date", "after:start_time"],
            "starting_price" => ["required", "numeric", "min:0"],
            "reserve_price" => ["sometimes", "numeric", "min:0"],

            "year_created" => ["sometimes", "string", "max:10"],
            "dimensions" => ["sometimes", "string", "max:50"],
            "province" => ["sometimes", "string", "max:60"],
            "condition" => ["sometimes", new Enum(AuctionCondition::class)],

            "category_id" => ["nullable", "integer", "exists:categories,id"],

            // Images: optional multiple
            "images" => ["sometimes", "array"],
            "images.*" => [
                "file",
                "image",
                "mimes:jpg,jpeg,png,webp",
                "max:5120",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.after' => 'The auction start time must be at least 2 hours from now to ensure proper scheduling.',
        ];
    }
}
