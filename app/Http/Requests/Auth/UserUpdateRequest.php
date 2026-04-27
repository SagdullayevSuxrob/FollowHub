<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserUpdateRequest extends FormRequest
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
            "username" => "sometimes|string|alpha_dash|max:16|unique:users,username," . Auth::id(),
            "name" => "sometimes|string|max:255",
            "email" => "sometimes|email|unique:users,email," . Auth::id(),
            "type" => "sometimes|string|in:public,private",
            "password" => "sometimes|string|min:8|confirmed",
        ];
    }
}
