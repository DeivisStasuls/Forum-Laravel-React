<?php

namespace App\Http\Requests;

use App\Models\Subforum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubforumRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $subforum = Subforum::query()
            ->where('slug', (string) $this->route('slug'))
            ->first();

        return $subforum && ($this->user()?->can('update', $subforum) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $subforum = Subforum::query()
            ->where('slug', (string) $this->route('slug'))
            ->first();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subforums', 'name')->ignore($subforum?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'restricted_thread_creation' => ['nullable', 'boolean'],
        ];
    }
}


