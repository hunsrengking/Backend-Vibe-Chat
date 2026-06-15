<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateChatMessageRequest extends FormRequest
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
            'content' => 'required_without:media|nullable|string|min:1|max:500',
            'media' => 'nullable|file|max:20480',
            'media_type' => 'nullable|string|in:voice,image,video',
            'group_chat_id' => 'nullable|integer',
        ];
    }
}
