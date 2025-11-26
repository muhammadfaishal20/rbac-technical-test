/**
 * File Upload API Service
 */
import http, { getCsrfCookie } from './http.js';

const fileService = {
    /**
     * Upload multiple files
     * @param {File[]} files - Array of files to upload
     * @returns {Promise}
     */
    async uploadFiles(files) {
        // Get CSRF cookie first (required for Sanctum)
        await getCsrfCookie();
        
        const formData = new FormData();
        
        // Add files to FormData with files[] array format
        // Ensure we're working with valid File objects
        Array.from(files).forEach((file, index) => {
            if (file instanceof File) {
                // Verify file is still valid before appending
                if (file.size > 0 && file.name) {
                    formData.append('files[]', file, file.name);
                } else {
                    console.warn(`File at index ${index} is invalid (size: ${file.size}, name: ${file.name}):`, file);
                }
            } else {
                console.warn(`File at index ${index} is not a valid File object:`, file);
            }
        });
        
        // Verify FormData has files
        if (formData.getAll('files[]').length === 0) {
            throw new Error('No valid files to upload');
        }
        
        // Don't set Content-Type header - let browser set it with boundary for FormData
        const response = await http.post('/files/upload', formData);
        
        return response;
    },

    /**
     * Get list of files
     * @param {Object} params - Query parameters (search, mime, per_page)
     * @returns {Promise}
     */
    async getFiles(params = {}) {
        const queryParams = new URLSearchParams();
        
        if (params.search) {
            queryParams.append('search', params.search);
        }
        if (params.mime) {
            queryParams.append('mime', params.mime);
        }
        if (params.per_page) {
            queryParams.append('per_page', params.per_page);
        }
        
        const queryString = queryParams.toString();
        const endpoint = queryString ? `/files?${queryString}` : '/files';
        
        const response = await http.get(endpoint);
        return response;
    },

    /**
     * Get file details
     * @param {number} fileId - File ID
     * @returns {Promise}
     */
    async getFile(fileId) {
        const response = await http.get(`/files/${fileId}`);
        return response;
    },

    /**
     * Download file
     * @param {number} fileId - File ID
     * @param {string} filename - Optional filename for download
     * @returns {Promise}
     */
    async downloadFile(fileId, filename = null) {
        const response = await http.get(`/files/${fileId}/download`, {
            responseType: 'blob',
        });
        
        // Create blob URL and trigger download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename || `file-${fileId}`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        
        return { success: true };
    },

    /**
     * Delete file
     * @param {number} fileId - File ID
     * @returns {Promise}
     */
    async deleteFile(fileId) {
        const response = await http.delete(`/files/${fileId}`);
        return response;
    },
};

export default fileService;

