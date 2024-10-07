<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'email' => ['required', 'max:50', 'email', 'unique:users'],
            'province' => ['required', 'max:50'],
            'city' => ['required', 'max:50'],
            'barangay' => ['required', 'max:50'],
            'street_address' => ['required', 'max:50'],
            'contact_number' => ['required', 'digits:10'],
            'zip_code' => ['required', 'max:50'],
        ];
    }
}
