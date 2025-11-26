<?php

namespace App\Http\Controllers\File;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\UploadFileRequest;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Get PHP upload configuration (for debugging)
     */
    public function getUploadConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'max_file_uploads' => ini_get('max_file_uploads'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'max_input_time' => ini_get('max_input_time'),
                'file_uploads' => ini_get('file_uploads') ? 'enabled' : 'disabled',
                'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
                'php_version' => PHP_VERSION,
                'recommended' => [
                    'upload_max_filesize' => '100M',
                    'post_max_size' => '100M',
                    'max_file_uploads' => '20',
                    'memory_limit' => '256M',
                ],
            ],
        ]);
    }

    /**
     * Display a listing of the files.
     */
    public function index(Request $request): JsonResponse
    {
        $query = File::with('user');

        // Filter by authenticated user (non-admin can only see their own files)
        // Admin can see all files, management-file can only see their own files
        if (!$request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by mime type
        if ($request->has('mime')) {
            $query->where('mime', $request->get('mime'));
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $files = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Add URL and formatted size to each file
        $files->getCollection()->transform(function ($file) {
            $file->url = $file->url;
            $file->formatted_size = $file->formatted_size;
            // Ensure path is included in response
            $file->path = $file->path;
            return $file;
        });

        return response()->json([
            'success' => true,
            'data' => $files,
        ]);
    }

    /**
     * Store multiple uploaded files.
     */
    public function store(UploadFileRequest $request): JsonResponse
    {
        $uploadedFiles = [];
        $errors = [];

        $files = $request->file('files');
        
        if (!$files || !is_array($files)) {
            return response()->json([
                'success' => false,
                'message' => 'No files received.',
                'errors' => ['files' => ['No files were uploaded.']],
            ], 422);
        }
        
        // Log for debugging
        \Log::info('File upload request received', [
            'file_count' => count($files),
            'files' => array_map(function($file) {
                return [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                    'is_valid' => $file->isValid(),
                    'error' => $file->isValid() ? null : $file->getError(),
                ];
            }, $files),
        ]);

        foreach ($files as $index => $file) {
            try {
                // Check if file is valid
                if (!$file->isValid()) {
                    $errorMessage = $file->getErrorMessage();
                    // Check for common upload errors
                    $uploadMaxFilesize = ini_get('upload_max_filesize');
                    $postMaxSize = ini_get('post_max_size');
                    
                    if ($file->getError() === UPLOAD_ERR_INI_SIZE) {
                        $errorMessage = "File size exceeds PHP upload_max_filesize limit ({$uploadMaxFilesize}). Please increase 'upload_max_filesize' in php.ini to at least 100M.";
                    } elseif ($file->getError() === UPLOAD_ERR_FORM_SIZE) {
                        $errorMessage = "File size exceeds PHP post_max_size limit ({$postMaxSize}). Please increase 'post_max_size' in php.ini to at least 100M.";
                    } elseif ($file->getError() === UPLOAD_ERR_PARTIAL) {
                        $errorMessage = 'File was only partially uploaded.';
                    } elseif ($file->getError() === UPLOAD_ERR_NO_FILE) {
                        $errorMessage = 'No file was uploaded.';
                    } elseif ($file->getError() === UPLOAD_ERR_NO_TMP_DIR) {
                        $errorMessage = 'Missing temporary folder for file upload.';
                    } elseif ($file->getError() === UPLOAD_ERR_CANT_WRITE) {
                        $errorMessage = 'Failed to write file to disk.';
                    } elseif ($file->getError() === UPLOAD_ERR_EXTENSION) {
                        $errorMessage = 'File upload stopped by extension.';
                    }
                    
                    $errors[] = [
                        'file' => $file->getClientOriginalName() ?? "File {$index}",
                        'error' => $errorMessage,
                    ];
                    continue;
                }

                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;

                // Store file
                $path = $file->storeAs('uploads', $filename, 'public');

                // Save metadata to database
                $fileModel = File::create([
                    'name' => $originalName,
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'user_id' => $request->user()->id,
                ]);

                $fileModel->url = $fileModel->url;
                $fileModel->formatted_size = $fileModel->formatted_size;
                $fileModel->load('user');

                $uploadedFiles[] = $fileModel;
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName() ?? "File {$index}",
                    'error' => $e->getMessage(),
                ];
            }
        }

        if (count($errors) > 0 && count($uploadedFiles) === 0) {
            // Format errors for Laravel validation format
            $formattedErrors = [];
            foreach ($errors as $index => $error) {
                $formattedErrors["files.{$index}"] = [$error['error']];
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload files.',
                'errors' => $formattedErrors,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedFiles) . ' file(s) uploaded successfully.',
            'data' => $uploadedFiles,
            'errors' => count($errors) > 0 ? $errors : null,
        ], 201);
    }

    /**
     * Display the specified file.
     */
    public function show(File $file, Request $request): JsonResponse
    {
        // Check if user can access this file
        if (!$request->user()->hasRole('admin') && $file->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        $file->url = $file->url;
        $file->formatted_size = $file->formatted_size;
        $file->load('user');

        return response()->json([
            'success' => true,
            'data' => $file,
        ]);
    }

    /**
     * Download the specified file.
     */
    public function download(File $file, Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
    {
        // Check if user can access this file
        if (!$request->user()->hasRole('admin') && $file->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        if (!Storage::disk('public')->exists($file->path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.',
            ], 404);
        }

        return Storage::disk('public')->download($file->path, $file->name);
    }

    /**
     * Remove the specified file from storage.
     */
    public function destroy(File $file, Request $request): JsonResponse
    {
        // Check if user can delete this file
        if (!$request->user()->hasRole('admin') && $file->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($file->path)) {
                Storage::disk('public')->delete($file->path);
            }

            // Delete record from database
            $file->delete();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

