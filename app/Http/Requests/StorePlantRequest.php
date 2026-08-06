<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePlantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'crop_id'              => 'required|exists:crops,id',
            'name'                 => 'required|string|max:255',
            'planting_date'        => 'required|date|before_or_equal:today',
            'quantity'             => 'required|integer|min:1',
            'base_irrigation_days' => 'nullable|integer|min:1',
            'notes'                => 'nullable|string',
        ];
    }
}
