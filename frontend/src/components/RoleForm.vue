<template>
    <form @submit.prevent="handleSubmit">
        <div class="field mb-4">
            <label for="name" class="block text-900 font-medium mb-2">Name *</label>
            <InputText
                id="name"
                v-model="form.name"
                class="w-full"
                placeholder="Enter role name"
                :class="{ 'p-invalid': errors.name }"
                :disabled="isDefaultRole"
                required
            />
            <small v-if="errors.name" class="p-error">{{ errors.name }}</small>
            <small v-if="isDefaultRole" class="text-600">Default roles cannot be renamed</small>
        </div>

        <div class="field mb-4">
            <label for="permissions" class="block text-900 font-medium mb-2">Permissions *</label>
            <MultiSelect
                id="permissions"
                v-model="form.permissions"
                :options="availablePermissions"
                optionLabel="name"
                optionValue="id"
                class="w-full"
                placeholder="Select permissions"
                :class="{ 'p-invalid': errors.permissions }"
                required
            />
            <small v-if="errors.permissions" class="p-error">{{ errors.permissions }}</small>
        </div>

        <div class="flex justify-content-end gap-2 mt-4">
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="$emit('cancel')"
            />
            <Button
                type="submit"
                label="Save"
                :loading="loading"
            />
        </div>
    </form>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import Button from 'primevue/button';
import roleService from '@/services/roles.js';

const props = defineProps({
    role: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['save', 'cancel']);

const toast = useToast();

const form = reactive({
    name: '',
    permissions: [],
});

const errors = reactive({
    name: null,
    permissions: null,
});

const loading = ref(false);
const availablePermissions = ref([]);

const defaultRoles = ['admin', 'management-user', 'management-file'];

const isDefaultRole = computed(() => {
    return props.role && defaultRoles.includes(props.role.name);
});

const validateForm = () => {
    errors.name = null;
    errors.permissions = null;
    let isValid = true;

    if (!form.name) {
        errors.name = 'Name is required';
        isValid = false;
    }

    if (!form.permissions || form.permissions.length === 0) {
        errors.permissions = 'At least one permission is required';
        isValid = false;
    }

    return isValid;
};

const handleSubmit = async () => {
    if (!validateForm()) {
        return;
    }

    loading.value = true;

    try {
        const roleData = {
            name: form.name,
            permissions: form.permissions,
        };

        if (props.role) {
            await roleService.updateRole(props.role.id, roleData);
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Role updated successfully',
                life: 3000,
            });
        } else {
            await roleService.createRole(roleData);
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Role created successfully',
                life: 3000,
            });
        }

        emit('save');
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to save role',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const fetchAvailablePermissions = async () => {
    try {
        const response = await roleService.getPermissions();
        if (response.success && response.data) {
            availablePermissions.value = response.data;
        }
    } catch (error) {
        console.error('Failed to fetch permissions:', error);
    }
};

onMounted(async () => {
    await fetchAvailablePermissions();

    if (props.role) {
        form.name = props.role.name || '';
        form.permissions = props.role.permissions ? props.role.permissions.map(p => p.id) : [];
    }
});
</script>

