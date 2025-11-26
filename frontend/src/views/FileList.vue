<template>
    <div class="card">
        <div class="flex justify-content-between align-items-center mb-4">
            <h1 class="text-3xl font-bold">File Management</h1>
            <Button
                label="Upload Files"
                icon="pi pi-upload"
                @click="showUploadDialog = true"
            />
        </div>

        <!-- Search and Filter -->
        <div class="flex gap-2 mb-4">
            <span class="p-input-icon-left flex-1">
                <i class="pi pi-search" />
                <InputText
                    v-model="searchQuery"
                    placeholder="Search files..."
                    @input="debouncedSearch"
                />
            </span>
            <Select
                v-model="mimeFilter"
                :options="mimeOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Filter by type"
                class="w-12rem"
                @change="fetchFiles"
            />
        </div>

        <!-- Files Table -->
        <DataTable
            :value="files"
            :loading="loading"
            paginator
            :rows="perPage"
            :totalRecords="totalRecords"
            @page="onPageChange"
            :rowsPerPageOptions="[10, 20, 50]"
            emptyMessage="No files found"
        >
            <Column header="Preview" style="min-width: 8rem">
                <template #body="slotProps">
                    <div class="flex align-items-center">
                        <img
                            v-if="slotProps.data.mime.startsWith('image/')"
                            :src="slotProps.data.url"
                            :alt="slotProps.data.name"
                            class="file-preview-image"
                        />
                        <i
                            v-else-if="slotProps.data.mime.startsWith('video/')"
                            class="pi pi-video text-4xl text-primary"
                        />
                        <i
                            v-else
                            class="pi pi-file text-4xl text-secondary"
                        />
                    </div>
                </template>
            </Column>

            <Column field="name" header="File Name" sortable style="min-width: 15rem">
                <template #body="slotProps">
                    <div class="flex flex-column">
                        <span class="font-semibold">{{ slotProps.data.name }}</span>
                        <span class="text-sm text-secondary">{{ slotProps.data.formatted_size }}</span>
                    </div>
                </template>
            </Column>

            <Column field="mime" header="Type" sortable style="min-width: 10rem">
                <template #body="slotProps">
                    <Tag
                        :value="slotProps.data.mime"
                        :severity="getMimeSeverity(slotProps.data.mime)"
                    />
                </template>
            </Column>

            <Column header="Uploaded By" style="min-width: 12rem">
                <template #body="slotProps">
                    <span v-if="slotProps.data.user">
                        {{ slotProps.data.user.name }}
                    </span>
                    <span v-else class="text-secondary">Unknown</span>
                </template>
            </Column>

            <Column field="created_at" header="Uploaded At" sortable style="min-width: 12rem">
                <template #body="slotProps">
                    {{ formatDate(slotProps.data.created_at) }}
                </template>
            </Column>

            <Column header="Actions" style="min-width: 10rem">
                <template #body="slotProps">
                    <Button
                        icon="pi pi-download"
                        class="p-button-rounded p-button-text mr-2"
                        v-tooltip.top="'Download'"
                        @click="downloadFile(slotProps.data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        class="p-button-rounded p-button-text p-button-danger"
                        v-tooltip.top="'Delete'"
                        @click="confirmDelete(slotProps.data)"
                    />
                </template>
            </Column>
        </DataTable>

        <!-- Upload Dialog -->
        <Dialog
            v-model:visible="showUploadDialog"
            header="Upload Files"
            :modal="true"
            :style="{ width: '50rem' }"
        >
            <FileUploadForm
                v-if="showUploadDialog"
                @save="handleUploadSave"
                @cancel="showUploadDialog = false"
            />
        </Dialog>

        <!-- Delete Confirmation -->
        <ConfirmDialog />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import ConfirmDialog from 'primevue/confirmdialog';
import FileUploadForm from '@/components/FileUploadForm.vue';
import fileService from '@/services/files.js';

const confirm = useConfirm();
const toast = useToast();

const files = ref([]);
const loading = ref(false);
const showUploadDialog = ref(false);
const perPage = ref(15);
const totalRecords = ref(0);
const currentPage = ref(1);
const searchQuery = ref('');
const mimeFilter = ref(null);
let searchTimeout = null;

const mimeOptions = [
    { label: 'All Types', value: null },
    { label: 'Images', value: 'image' },
    { label: 'Videos', value: 'video' },
];

const fetchFiles = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            per_page: perPage.value,
        };

        if (searchQuery.value) {
            params.search = searchQuery.value;
        }

        if (mimeFilter.value) {
            params.mime = mimeFilter.value;
        }

        const response = await fileService.getFiles(params);

        if (response.success && response.data) {
            files.value = response.data.data || [];
            totalRecords.value = response.data.total || 0;
            currentPage.value = page;
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to fetch files',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const onPageChange = (event) => {
    fetchFiles(event.page + 1);
};

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchFiles(1);
    }, 500);
};

const handleUploadSave = async () => {
    showUploadDialog.value = false;
    await fetchFiles(currentPage.value);
};

const downloadFile = async (file) => {
    try {
        await fileService.downloadFile(file.id, file.name);
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'File download started',
            life: 2000,
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to download file',
            life: 3000,
        });
    }
};

const confirmDelete = (file) => {
    confirm.require({
        message: `Are you sure you want to delete "${file.name}"?`,
        header: 'Delete Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            deleteFile(file.id);
        },
    });
};

const deleteFile = async (fileId) => {
    loading.value = true;
    try {
        await fileService.deleteFile(fileId);
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'File deleted successfully',
            life: 3000,
        });
        await fetchFiles(currentPage.value);
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to delete file',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getMimeSeverity = (mime) => {
    if (mime.startsWith('image/')) {
        return 'success';
    } else if (mime.startsWith('video/')) {
        return 'info';
    }
    return 'secondary';
};

onMounted(() => {
    fetchFiles();
});
</script>

<style scoped>
.file-preview-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}
</style>

