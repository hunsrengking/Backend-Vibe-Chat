<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterGuestRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|min:3|max:20|unique:guests,username',
            'password' => 'required|string|min:6',
            'nickname' => 'required|string|min:3|max:20',
            'avatar_url' => 'nullable|string',
        ];
    }
}
