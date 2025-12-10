<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThreadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $thread = $this->route('thread');
        // Only the thread author can update it
        return $thread && $this->user()->id === $thread->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'subforum_id' => ['required', 'exists:subforums,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The thread title is required.',
            'title.max' => 'The thread title may not be greater than 255 characters.',
            'body.required' => 'The thread content is required.',
            'body.min' => 'The thread content must be at least 10 characters.',
            'subforum_id.required' => 'Please select a subforum.',
            'subforum_id.exists' => 'The selected subforum does not exist.',
        ];
    }
}


