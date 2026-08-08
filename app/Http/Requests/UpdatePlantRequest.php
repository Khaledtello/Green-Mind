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
            'name'                 => 'string|max:255',
            'quantity'             => 'integer|min:1',
            'base_irrigation_days' => 'nullable|integer|min:1',
            'disease_id'           => 'nullable|exists:diseases,id',
            'notes'                => 'nullable|string',
        ];
    }
}
