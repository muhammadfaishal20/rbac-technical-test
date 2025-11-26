<template>
    <form @submit.prevent="handleSubmit">
        <div class="field mb-4">
            <label for="name" class="block text-900 font-medium mb-2">Name *</label>
            <InputText
                id="name"
                v-model="form.name"
                class="w-full"
                placeholder="Enter user name"
                :class="{ 'p-invalid': errors.name }"
                required
            />
            <small v-if="errors.name" class="p-error">{{ errors.name }}</small>
        </div>

        <div class="field mb-4">
            <label for="email" class="block text-900 font-medium mb-2">Email *</label>
            <InputText
                id="email"
                v-model="form.email"
                type="email"
                class="w-full"
                placeholder="Enter email"
                :class="{ 'p-invalid': errors.email }"
                :disabled="!!user"
                required
            />
            <small v-if="errors.email" class="p-error">{{ errors.email }}</small>
        </div>

        <div class="field mb-4">
            <label for="password" class="block text-900 font-medium mb-2">
                Password {{ user ? '(leave empty to keep current)' : '*' }}
            </label>
            <Password
                id="password"
                v-model="form.password"
                class="w-full"
                placeholder="Enter password"
                :feedback="false"
                toggleMask
                :class="{ 'p-invalid': errors.password }"
                :required="!user"
                inputStyle="width: 100%"
                :inputProps="{ autocomplete: 'new-password' }"
            />
            <small v-if="errors.password" class="p-error">{{ errors.password }}</small>
        </div>

        <div class="field mb-4">
            <label for="roles" class="block text-900 font-medium mb-2">Roles *</label>
            <MultiSelect
                id="roles"
                v-model="form.roles"
                :options="availableRoles"
                optionLabel="name"
                optionValue="id"
                class="w-full"
                placeholder="Select roles"
                :class="{ 'p-invalid': errors.roles }"
                required
            />
            <small v-if="errors.roles" class="p-error">{{ errors.roles }}</small>
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
import { ref, reactive, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import MultiSelect from 'primevue/multiselect';
import Button from 'primevue/button';
import userService from '@/services/users.js';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['save', 'cancel']);

const toast = useToast();

const form = reactive({
    name: '',
    email: '',
    password: '',
    roles: [],
});

const errors = reactive({
    name: null,
    email: null,
    password: null,
    roles: null,
});

const loading = ref(false);
const availableRoles = ref([]);

const validateForm = () => {
    errors.name = null;
    errors.email = null;
    errors.password = null;
    errors.roles = null;
    let isValid = true;

    if (!form.name) {
        errors.name = 'Name is required';
        isValid = false;
    }

    if (!form.email) {
        errors.email = 'Email is required';
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Please enter a valid email';
        isValid = false;
    }

    if (!props.user && !form.password) {
        errors.password = 'Password is required';
        isValid = false;
    } else if (form.password && form.password.length < 6) {
        errors.password = 'Password must be at least 6 characters';
        isValid = false;
    }

    if (!form.roles || form.roles.length === 0) {
        errors.roles = 'At least one role is required';
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
        const userData = {
            name: form.name,
            email: form.email,
            roles: form.roles,
        };

        if (form.password) {
            userData.password = form.password;
        }

        if (props.user) {
            await userService.updateUser(props.user.id, userData);
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'User updated successfully',
                life: 3000,
            });
        } else {
            await userService.createUser(userData);
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'User created successfully',
                life: 3000,
            });
        }

        emit('save');
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to save user',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const fetchAvailableRoles = async () => {
    try {
        const response = await userService.getAvailableRoles();
        if (response.success && response.data) {
            availableRoles.value = response.data;
        }
    } catch (error) {
        console.error('Failed to fetch roles:', error);
    }
};

onMounted(async () => {
    await fetchAvailableRoles();

    if (props.user) {
        form.name = props.user.name || '';
        form.email = props.user.email || '';
        form.roles = props.user.roles ? props.user.roles.map(r => r.id) : [];
    }
});
</script>

