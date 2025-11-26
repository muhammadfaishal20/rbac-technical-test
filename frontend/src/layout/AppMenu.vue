<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/store/authStore.js';
import { hasPermission } from '@/utils/permission.js';
import AppMenuItem from './AppMenuItem.vue';

const authStore = useAuthStore();

// Menu items based on permissions
const menuItems = computed(() => {
    const user = authStore.user;
    const items = [];

    // Dashboard - accessible to all authenticated users
    items.push({
        label: 'Home',
        items: [
            { 
                label: 'Dashboard', 
                icon: 'pi pi-fw pi-home', 
                to: '/',
                visible: true
            }
        ]
    });

    // Role Management - requires manage-roles permission
    if (user && hasPermission(user, 'manage-roles')) {
        items.push({
            label: 'Role Management',
            items: [
                { 
                    label: 'Roles', 
                    icon: 'pi pi-fw pi-shield', 
                    to: '/roles',
                    visible: true
                }
            ]
        });
    }

    // User Management - requires manage-users permission
    if (user && hasPermission(user, 'manage-users')) {
        items.push({
            label: 'User Management',
            items: [
                { 
                    label: 'Users', 
                    icon: 'pi pi-fw pi-users', 
                    to: '/users',
                    visible: true
                }
            ]
        });
    }

    // File Management - requires manage-files permission
    if (user && hasPermission(user, 'manage-files')) {
        items.push({
            label: 'File Management',
            items: [
                { 
                    label: 'Files', 
                    icon: 'pi pi-fw pi-file', 
                    to: '/files',
                    visible: true
                }
            ]
        });
    }

    return items;
});
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in menuItems" :key="item.label">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
