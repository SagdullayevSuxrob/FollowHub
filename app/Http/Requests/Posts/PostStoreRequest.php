<?php

namespace App\Http\Requests\Posts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PostStoreRequest extends FormRequest
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
            'caption' => 'nullable|string',
            'media' => 'required|array|min:1|max:10',
            'media.*' => 'required|file|mimetypes:image/jpg,image/png,image/jpeg,application/pdf,video/mp4|max:102400',
        ];
    }
}
