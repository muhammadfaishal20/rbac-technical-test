<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'required',
                'file',
                'max:102400', // 100 MB in KB (100 * 1024)
                'mimes:jpg,jpeg,png,mp4',
            ],
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
            'files.required' => 'At least one file is required.',
            'files.array' => 'Files must be an array.',
            'files.min' => 'At least one file is required.',
            'files.*.required' => 'Each file is required.',
            'files.*.file' => 'Each item must be a valid file.',
            'files.*.max' => 'Each file must not be larger than 100 MB.',
            'files.*.mimes' => 'Each file must be one of: jpg, jpeg, png, mp4.',
        ];
    }
}

