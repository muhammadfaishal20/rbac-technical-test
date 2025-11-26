<template>
    <div class="card">
        <div class="flex justify-content-between align-items-center mb-4">
            <h1 class="text-3xl font-bold">User Management</h1>
            <Button
                label="Add User"
                icon="pi pi-plus"
                @click="showUserDialog = true"
            />
        </div>

        <DataTable
            :value="users"
            :loading="loading"
            paginator
            :rows="perPage"
            :totalRecords="totalRecords"
            @page="onPageChange"
            :rowsPerPageOptions="[10, 20, 50]"
            filterDisplay="row"
            :globalFilterFields="['name', 'email']"
        >
            <template #header>
                <div class="flex justify-content-between align-items-center">
                    <span class="text-xl font-semibold">Users</span>
                    <span class="p-input-icon-left">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="filters.global"
                            placeholder="Search users..."
                        />
                    </span>
                </div>
            </template>

            <Column field="id" header="ID" sortable style="min-width: 5rem" />
            <Column field="name" header="Name" sortable style="min-width: 12rem" />
            <Column field="email" header="Email" sortable style="min-width: 15rem" />
            
            <Column header="Roles" style="min-width: 15rem">
                <template #body="slotProps">
                    <Tag
                        v-for="role in slotProps.data.roles"
                        :key="role.id"
                        :value="role.name"
                        class="mr-2"
                    />
                </template>
            </Column>

            <Column header="Actions" style="min-width: 10rem">
                <template #body="slotProps">
                    <Button
                        icon="pi pi-pencil"
                        class="p-button-rounded p-button-text mr-2"
                        @click="editUser(slotProps.data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        class="p-button-rounded p-button-text p-button-danger"
                        @click="confirmDelete(slotProps.data)"
                    />
                </template>
            </Column>
        </DataTable>

        <!-- User Dialog -->
        <Dialog
            v-model:visible="showUserDialog"
            :header="editingUser ? 'Edit User' : 'Add User'"
            :modal="true"
            :style="{ width: '50rem' }"
        >
            <UserForm
                v-if="showUserDialog"
                :user="editingUser"
                @save="handleSave"
                @cancel="showUserDialog = false"
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
import userService from '@/services/users.js';
import UserForm from '@/components/UserForm.vue';

const confirm = useConfirm();
const toast = useToast();

const users = ref([]);
const loading = ref(false);
const showUserDialog = ref(false);
const editingUser = ref(null);
const perPage = ref(15);
const totalRecords = ref(0);
const filters = ref({
    global: null,
});

const fetchUsers = async (page = 1) => {
    loading.value = true;
    try {
        const response = await userService.getUsers({
            page,
            per_page: perPage.value,
        });

        if (response.success && response.data) {
            users.value = response.data.data || [];
            totalRecords.value = response.data.total || 0;
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to fetch users',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const onPageChange = (event) => {
    fetchUsers(event.page + 1);
};

const editUser = (user) => {
    editingUser.value = user;
    showUserDialog.value = true;
};

const confirmDelete = (user) => {
    confirm.require({
        message: `Are you sure you want to delete user "${user.name}"?`,
        header: 'Delete Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            deleteUser(user.id);
        },
    });
};

const deleteUser = async (userId) => {
    loading.value = true;
    try {
        await userService.deleteUser(userId);
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'User deleted successfully',
            life: 3000,
        });
        fetchUsers();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to delete user',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const handleSave = async () => {
    showUserDialog.value = false;
    editingUser.value = null;
    await fetchUsers();
};

onMounted(() => {
    fetchUsers();
});
</script>

