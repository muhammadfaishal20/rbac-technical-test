<template>
    <form @submit.prevent="handleSubmit">
        <div class="field mb-4">
            <label for="files" class="block text-900 font-medium mb-2">Select Files *</label>
            <FileUpload
                mode="basic"
                :multiple="true"
                :auto="false"
                accept="image/jpeg,image/jpg,image/png,video/mp4"
                :maxFileSize="104857600"
                :chooseLabel="'Choose Files'"
                @select="onFileSelect"
                :disabled="loading"
                class="w-full"
            />
            <small class="text-secondary mt-2 block">
                Allowed types: JPG, JPEG, PNG, MP4. Maximum size: 100 MB per file.
            </small>
            <small v-if="errors.files" class="p-error block mt-1">{{ errors.files }}</small>
        </div>

        <div v-if="selectedFiles.length > 0" class="field mb-4">
            <label class="block text-900 font-medium mb-2">
                Selected Files ({{ selectedFiles.length }})
            </label>
            <div class="flex flex-column gap-3">
                <div
                    v-for="(file, index) in selectedFiles"
                    :key="index"
                    class="border-round surface-border border-1 p-3"
                >
                    <div class="flex align-items-start gap-3">
                        <!-- Preview -->
                        <div class="file-preview-container">
                            <img
                                v-if="file.type.startsWith('image/')"
                                :src="getFilePreviewUrl(file)"
                                :alt="file.name"
                                class="file-preview-image"
                            />
                            <video
                                v-else-if="file.type.startsWith('video/')"
                                :src="getFilePreviewUrl(file)"
                                class="file-preview-video"
                                controls
                            />
                            <div
                                v-else
                                class="file-preview-placeholder"
                            >
                                <i :class="getFileIcon(file.type)" class="text-4xl"></i>
                            </div>
                        </div>

                        <!-- File Info -->
                        <div class="flex flex-column gap-1 flex-1">
                            <div class="flex align-items-center justify-content-between">
                                <div class="flex flex-column">
                                    <span class="font-medium">{{ file.name }}</span>
                                    <span class="text-sm text-secondary">{{ formatFileSize(file.size) }}</span>
                                </div>
                                <Button
                                    icon="pi pi-times"
                                    class="p-button-rounded p-button-text p-button-danger"
                                    @click="removeFile(index)"
                                    :disabled="loading"
                                    v-tooltip.top="'Remove'"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="uploadErrors.length > 0" class="field mb-4">
            <Message severity="error" :closable="false">
                <ul class="m-0 pl-3">
                    <li v-for="(error, index) in uploadErrors" :key="index">{{ error }}</li>
                </ul>
            </Message>
        </div>

        <div class="flex justify-content-end gap-2 mt-4">
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="handleCancel"
                :disabled="loading"
            />
            <Button
                type="submit"
                label="Upload Files"
                icon="pi pi-upload"
                :loading="loading"
                :disabled="selectedFiles.length === 0"
            />
        </div>
    </form>
</template>

<script setup>
import { ref, reactive, onBeforeUnmount } from 'vue';
import { useToast } from 'primevue/usetoast';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import Message from 'primevue/message';
import fileService from '@/services/files.js';

const emit = defineEmits(['save', 'cancel']);

const toast = useToast();

const selectedFiles = ref([]);
const uploadErrors = ref([]);
const loading = ref(false);
const filePreviewUrls = ref(new Map());

const errors = reactive({
    files: null,
});

const allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'video/mp4', 'video/x-mp4'];
const maxFileSize = 104857600; // 100 MB in bytes

const onFileSelect = (event) => {
    const newFiles = Array.from(event.files);
    uploadErrors.value = [];
    errors.files = null;

    // Validate files
    newFiles.forEach((file) => {
        // Check file type
        if (!allowedMimeTypes.includes(file.type)) {
            uploadErrors.value.push(`${file.name}: Invalid file type. Only jpg, jpeg, png, and mp4 are allowed.`);
            return;
        }

        // Check file size
        if (file.size > maxFileSize) {
            uploadErrors.value.push(`${file.name}: File size exceeds 100 MB limit.`);
            return;
        }

        // Check if file already selected
        const isDuplicate = selectedFiles.value.some(
            existingFile => existingFile.name === file.name && existingFile.size === file.size
        );

        if (!isDuplicate) {
            selectedFiles.value.push(file);
            // Create preview URL for image and video
            if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                const previewUrl = URL.createObjectURL(file);
                filePreviewUrls.value.set(file, previewUrl);
            }
        }
    });

    if (uploadErrors.value.length > 0) {
        errors.files = 'Some files have validation errors. Please check the error messages below.';
    }
};

const removeFile = (index) => {
    const file = selectedFiles.value[index];
    // Revoke preview URL to free memory
    if (filePreviewUrls.value.has(file)) {
        URL.revokeObjectURL(filePreviewUrls.value.get(file));
        filePreviewUrls.value.delete(file);
    }
    selectedFiles.value.splice(index, 1);
    uploadErrors.value = [];
    errors.files = null;
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const getFileIcon = (mimeType) => {
    if (mimeType.startsWith('image/')) {
        return 'pi pi-image text-primary';
    } else if (mimeType.startsWith('video/')) {
        return 'pi pi-video text-primary';
    }
    return 'pi pi-file text-secondary';
};

const getFilePreviewUrl = (file) => {
    return filePreviewUrls.value.get(file) || '';
};

const handleCancel = () => {
    // Revoke all preview URLs to free memory
    filePreviewUrls.value.forEach(url => {
        URL.revokeObjectURL(url);
    });
    filePreviewUrls.value.clear();
    
    // Clear selected files
    selectedFiles.value = [];
    uploadErrors.value = [];
    errors.files = null;
    
    emit('cancel');
};

const handleSubmit = async () => {
    if (selectedFiles.value.length === 0) {
        errors.files = 'Please select at least one file to upload.';
        return;
    }

    loading.value = true;
    uploadErrors.value = [];
    errors.files = null;

    try {
        // Ensure files are valid File objects
        const filesToUpload = selectedFiles.value.filter(file => {
            if (!(file instanceof File)) {
                return false;
            }
            // Verify file is still valid
            if (file.size === 0) {
                return false;
            }
            return true;
        });
        
        if (filesToUpload.length === 0) {
            errors.files = 'No valid files to upload.';
            return;
        }

        if (filesToUpload.length !== selectedFiles.value.length) {
            errors.files = 'Some files are invalid and cannot be uploaded.';
        }

        const response = await fileService.uploadFiles(filesToUpload);

        if (response.success) {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: response.message || `${selectedFiles.value.length} file(s) uploaded successfully`,
                life: 3000,
            });

            // Revoke all preview URLs
            filePreviewUrls.value.forEach(url => {
                URL.revokeObjectURL(url);
            });
            filePreviewUrls.value.clear();

            // Reset form
            selectedFiles.value = [];
            uploadErrors.value = [];
            errors.files = null;

            emit('save');
        }
    } catch (error) {
        // Handle validation errors
        if (error.data && error.data.errors) {
            const errorData = error.data.errors;
            Object.keys(errorData).forEach(key => {
                if (Array.isArray(errorData[key])) {
                    errorData[key].forEach(msg => {
                        uploadErrors.value.push(msg);
                    });
                } else {
                    uploadErrors.value.push(errorData[key]);
                }
            });
            errors.files = 'Some files failed validation. Please check the error messages below.';
        } else {
            const errorMessage = error.message || 'Failed to upload files';
            uploadErrors.value.push(errorMessage);
            errors.files = errorMessage;
        }

        toast.add({
            severity: 'error',
            summary: 'Upload Failed',
            detail: error.message || 'Failed to upload files',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

// Cleanup preview URLs when component is unmounted
onBeforeUnmount(() => {
    filePreviewUrls.value.forEach(url => {
        URL.revokeObjectURL(url);
    });
    filePreviewUrls.value.clear();
});
</script>

<style scoped>
.file-preview-container {
    width: 120px;
    height: 120px;
    flex-shrink: 0;
    border-radius: 4px;
    overflow: hidden;
    background-color: var(--surface-ground);
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-preview-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-preview-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--surface-100);
}
</style>

