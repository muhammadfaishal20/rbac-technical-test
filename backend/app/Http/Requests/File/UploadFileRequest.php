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
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $files = $this->file('files');
            
            if (!$files || !is_array($files)) {
                return;
            }
            
            foreach ($files as $index => $file) {
                if (!$file) {
                    $validator->errors()->add("files.{$index}", 'No file was uploaded.');
                    continue;
                }
                
                if (!$file->isValid()) {
                    $errorCode = $file->getError();
                    $uploadMaxFilesize = ini_get('upload_max_filesize');
                    $postMaxSize = ini_get('post_max_size');
                    $maxFileUploads = ini_get('max_file_uploads');
                    
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => "File size exceeds PHP upload_max_filesize limit ({$uploadMaxFilesize}). Please increase 'upload_max_filesize' in php.ini to at least 100M.",
                        UPLOAD_ERR_FORM_SIZE => "File size exceeds PHP post_max_size limit ({$postMaxSize}). Please increase 'post_max_size' in php.ini to at least 100M.",
                        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded. Please try again.',
                        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder for file upload. Please check PHP configuration.',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk. Please check file permissions.',
                        UPLOAD_ERR_EXTENSION => 'File upload stopped by PHP extension. Please check PHP configuration.',
                    ];
                    
                    $errorMessage = $errorMessages[$errorCode] ?? $file->getErrorMessage();
                    
                    // Add PHP configuration info for size-related errors
                    if (in_array($errorCode, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE])) {
                        $errorMessage .= " Current PHP settings: upload_max_filesize={$uploadMaxFilesize}, post_max_size={$postMaxSize}, max_file_uploads={$maxFileUploads}";
                    }
                    
                    $validator->errors()->add(
                        "files.{$index}",
                        $errorMessage
                    );
                    continue;
                }
                
                // Check file extension (more reliable than MIME type)
                $extension = strtolower($file->getClientOriginalExtension());
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'mp4'];
                
                if (!in_array($extension, $allowedExtensions)) {
                    $validator->errors()->add(
                        "files.{$index}",
                        "File extension '{$extension}' is not allowed. Allowed extensions: " . implode(', ', $allowedExtensions) . '.'
                    );
                    continue;
                }
                
                // For MP4 files, skip strict MIME type validation
                // Different browsers/systems may send different MIME types for MP4
                if ($extension === 'mp4') {
                    continue; // Accept MP4 based on extension only
                }
                
                // For image files, validate MIME type
                $mimeType = $file->getMimeType();
                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                ];
                
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    $validator->errors()->add(
                        "files.{$index}",
                        "File MIME type '{$mimeType}' is not allowed for extension '{$extension}'."
                    );
                }
            }
        });
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
            'files.*.file' => 'The file upload failed. Please check if the file is valid and not corrupted.',
            'files.*.max' => 'Each file must not be larger than 100 MB.',
        ];
    }
}

