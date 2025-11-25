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

        // Add URL to each file
        $files->getCollection()->transform(function ($file) {
            $file->url = $file->url;
            $file->formatted_size = $file->formatted_size;
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

        foreach ($request->file('files') as $file) {
            try {
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
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        if (count($errors) > 0 && count($uploadedFiles) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload files.',
                'errors' => $errors,
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

