<template>
    <div class="card">
        <div class="flex justify-content-between align-items-center mb-4">
            <h1 class="text-3xl font-bold">Role Management</h1>
            <Button
                label="Add Role"
                icon="pi pi-plus"
                @click="showRoleDialog = true"
            />
        </div>

        <DataTable
            :value="roles"
            :loading="loading"
            paginator
            :rows="perPage"
            :totalRecords="totalRecords"
            @page="onPageChange"
            :rowsPerPageOptions="[10, 20, 50]"
            filterDisplay="row"
            :globalFilterFields="['name']"
        >
            <template #header>
                <div class="flex justify-content-between align-items-center">
                    <span class="text-xl font-semibold">Roles</span>
                    <span class="p-input-icon-left">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="filters.global"
                            placeholder="Search roles..."
                        />
                    </span>
                </div>
            </template>

            <Column field="id" header="ID" sortable style="min-width: 5rem" />
            <Column field="name" header="Name" sortable style="min-width: 12rem" />
            
            <Column header="Permissions" style="min-width: 20rem">
                <template #body="slotProps">
                    <Tag
                        v-for="permission in slotProps.data.permissions"
                        :key="permission.id"
                        :value="permission.name"
                        class="mr-2 mb-2"
                    />
                </template>
            </Column>

            <Column header="Actions" style="min-width: 10rem">
                <template #body="slotProps">
                    <Button
                        icon="pi pi-pencil"
                        class="p-button-rounded p-button-text mr-2"
                        @click="editRole(slotProps.data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        class="p-button-rounded p-button-text p-button-danger"
                        @click="confirmDelete(slotProps.data)"
                        :disabled="isDefaultRole(slotProps.data.name)"
                    />
                </template>
            </Column>
        </DataTable>

        <!-- Role Dialog -->
        <Dialog
            v-model:visible="showRoleDialog"
            :header="editingRole ? 'Edit Role' : 'Add Role'"
            :modal="true"
            :style="{ width: '50rem' }"
        >
            <RoleForm
                v-if="showRoleDialog"
                :role="editingRole"
                @save="handleSave"
                @cancel="showRoleDialog = false"
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
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import ConfirmDialog from 'primevue/confirmdialog';
import roleService from '@/services/roles.js';
import RoleForm from '@/components/RoleForm.vue';

const confirm = useConfirm();
const toast = useToast();

const roles = ref([]);
const loading = ref(false);
const showRoleDialog = ref(false);
const editingRole = ref(null);
const perPage = ref(15);
const totalRecords = ref(0);
const filters = ref({
    global: null,
});

const defaultRoles = ['admin', 'management-user', 'management-file'];

const isDefaultRole = (roleName) => {
    return defaultRoles.includes(roleName);
};

const fetchRoles = async (page = 1) => {
    loading.value = true;
    try {
        const response = await roleService.getRoles({
            page,
            per_page: perPage.value,
        });

        if (response.success && response.data) {
            roles.value = response.data.data || [];
            totalRecords.value = response.data.total || 0;
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to fetch roles',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const onPageChange = (event) => {
    fetchRoles(event.page + 1);
};

const editRole = (role) => {
    editingRole.value = role;
    showRoleDialog.value = true;
};

const confirmDelete = (role) => {
    if (isDefaultRole(role.name)) {
        toast.add({
            severity: 'warn',
            summary: 'Warning',
            detail: 'Default roles cannot be deleted',
            life: 3000,
        });
        return;
    }

    confirm.require({
        message: `Are you sure you want to delete role "${role.name}"?`,
        header: 'Delete Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            deleteRole(role.id);
        },
    });
};

const deleteRole = async (roleId) => {
    loading.value = true;
    try {
        await roleService.deleteRole(roleId);
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Role deleted successfully',
            life: 3000,
        });
        fetchRoles();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to delete role',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const handleSave = async () => {
    showRoleDialog.value = false;
    editingRole.value = null;
    await fetchRoles();
};

onMounted(() => {
    fetchRoles();
});
</script>

