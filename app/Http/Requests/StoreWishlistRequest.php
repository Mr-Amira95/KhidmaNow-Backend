<?php

namespace App\Http\Requests;

use App\Models\Wishlist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreWishlistRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'item_type' => ['required', Rule::in(array_keys(Wishlist::TYPE_MODELS))],
            'item_id'   => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $table = Wishlist::TYPE_TABLES[$this->item_type] ?? null;
                    if ($table && !DB::table($table)->where('id', $value)->exists()) {
                        $fail('The selected item does not exist.');
                    }
                },
            ],
        ];
    }
}
