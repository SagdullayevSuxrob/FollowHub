<?php

namespace App\Http\Requests\Posts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('post')->user_id;
    }


    public function rules(): array
    {
        return [
            'caption' => 'nullable|string',
            'media' => 'nullable|array|max:10',
            'media.*' => 'required|file|mimetypes:image/jpg,image/jpeg,image/png,application/pdf,video/mp4|max:102400',
            'delete_media_ids' => 'nullable',
        ];
    }
}
