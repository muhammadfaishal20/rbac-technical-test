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
            <div class="flex flex-column gap-2">
                <div
                    v-for="(file, index) in selectedFiles"
                    :key="index"
                    class="flex align-items-center justify-content-between p-3 border-round surface-border border-1"
                >
                    <div class="flex align-items-center gap-3">
                        <i
                            :class="getFileIcon(file.type)"
                            class="text-3xl"
                        />
                        <div class="flex flex-column">
                            <span class="font-medium">{{ file.name }}</span>
                            <span class="text-sm text-secondary">{{ formatFileSize(file.size) }}</span>
                        </div>
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
                @click="$emit('cancel')"
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
import { ref, reactive } from 'vue';
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

const errors = reactive({
    files: null,
});

const allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'video/mp4'];
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
        }
    });

    if (uploadErrors.value.length > 0) {
        errors.files = 'Some files have validation errors. Please check the error messages below.';
    }
};

const removeFile = (index) => {
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

const handleSubmit = async () => {
    if (selectedFiles.value.length === 0) {
        errors.files = 'Please select at least one file to upload.';
        return;
    }

    loading.value = true;
    uploadErrors.value = [];
    errors.files = null;

    try {
        const response = await fileService.uploadFiles(selectedFiles.value);

        if (response.success) {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: response.message || `${selectedFiles.value.length} file(s) uploaded successfully`,
                life: 3000,
            });

            // Reset form
            selectedFiles.value = [];
            uploadErrors.value = [];
            errors.files = null;

            emit('save');
        }
    } catch (error) {
        // Handle validation errors
        if (error.data && error.data.errors) {
            const errors = error.data.errors;
            Object.keys(errors).forEach(key => {
                errors[key].forEach(msg => {
                    uploadErrors.value.push(msg);
                });
            });
            errors.files = 'Some files failed validation. Please check the error messages below.';
        } else {
            uploadErrors.value.push(error.message || 'Failed to upload files');
            errors.files = error.message || 'Failed to upload files';
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
</script>

