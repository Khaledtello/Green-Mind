<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlantRequest extends FormRequest
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
            'crop_id'       => 'sometimes|exists:crops,id',
            'name'          => 'sometimes|string|max:255',
            'planting_date' => 'sometimes|date|before_or_equal:today',
            'harvest_date'  => 'nullable|date|after:planting_date|before_or_equal:today',
            'quantity'      => 'sometimes|integer|min:1',
            'disease_name'  => 'sometimes|nullable|string',
            'notes'         => 'nullable|string',
        ];
    }
}
