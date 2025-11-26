import AppLayout from '@/layout/AppLayout.vue';
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/store/authStore.js';
import { hasPermission } from '@/utils/permission.js';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: AppLayout,
            meta: { requiresAuth: true },
            children: [
                {
                    path: '/',
                    name: 'dashboard',
                    component: () => import('@/views/Dashboard.vue')
                },
                {
                    path: '/users',
                    name: 'users',
                    component: () => import('@/views/UserList.vue'),
                    meta: { requiresPermission: 'manage-users' }
                },
                {
                    path: '/roles',
                    name: 'roles',
                    component: () => import('@/views/RoleList.vue'),
                    meta: { requiresPermission: 'manage-roles' }
                },
                {
                    path: '/files',
                    name: 'files',
                    component: () => import('@/views/FileList.vue'),
                    meta: { requiresPermission: 'manage-files' }
                },
                {
                    path: '/pages/empty',
                    name: 'empty',
                    component: () => import('@/views/pages/Empty.vue')
                }
            ]
        },
        {
            path: '/pages/notfound',
            name: 'notfound',
            component: () => import('@/views/pages/NotFound.vue')
        },
        {
            path: '/auth/login',
            name: 'login',
            component: () => import('@/views/Login.vue'),
            meta: { requiresGuest: true }
        },
        {
            path: '/auth/access',
            name: 'accessDenied',
            component: () => import('@/views/pages/auth/Access.vue')
        },
        {
            path: '/auth/error',
            name: 'error',
            component: () => import('@/views/pages/auth/Error.vue')
        }
    ]
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    
    // Check if route requires authentication
    if (to.meta.requiresAuth) {
        const token = localStorage.getItem('auth_token');
        
        if (!token) {
            next({ name: 'login', query: { redirect: to.fullPath } });
            return;
        }

        // Ensure user is loaded in store
        // First, try to use stored user data immediately (non-blocking)
        if (!authStore.user) {
            const storedUser = localStorage.getItem('auth_user');
            
            if (storedUser) {
                try {
                    const userData = JSON.parse(storedUser);
                    authStore.updateUser(userData);
                } catch (e) {
                    // Invalid stored data, clear it and redirect to login
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('auth_user');
                    next({ name: 'login', query: { redirect: to.fullPath } });
                    return;
                }
            }
        }

        // If still no user after using stored data, try to fetch from API
        // But don't block navigation - use stored data if fetch fails
        if (!authStore.user) {
            try {
                // Add timeout to prevent infinite loading
                const fetchPromise = authStore.fetchUser();
                const timeoutPromise = new Promise((_, reject) => 
                    setTimeout(() => reject(new Error('Request timeout')), 3000)
                );
                
                await Promise.race([fetchPromise, timeoutPromise]);
            } catch (error) {
                // If fetch fails, check if we have stored user
                const storedUser = localStorage.getItem('auth_user');
                if (!storedUser) {
                    // No stored user and fetch failed, redirect to login
                    next({ name: 'login', query: { redirect: to.fullPath } });
                    return;
                }
                // Otherwise, continue with stored user (already loaded above)
            }
        }
        
        // Final check - if still no user, redirect to login
        if (!authStore.user) {
            next({ name: 'login', query: { redirect: to.fullPath } });
            return;
        }

        // Check if route requires specific permission
        if (to.meta.requiresPermission) {
            const user = authStore.user;
            
            if (!user || !hasPermission(user, to.meta.requiresPermission)) {
                next({ name: 'accessDenied' });
                return;
            }
        }

        // Check if route requires specific role
        if (to.meta.requiresRole) {
            const requiredRoles = Array.isArray(to.meta.requiresRole) 
                ? to.meta.requiresRole 
                : [to.meta.requiresRole];
            
            const hasRequiredRole = requiredRoles.some(role => 
                authStore.hasRole(role)
            );

            if (!authStore.user || !hasRequiredRole) {
                next({ name: 'accessDenied' });
                return;
            }
        }
    }

    // Check if route requires guest (not authenticated)
    if (to.meta.requiresGuest) {
        const token = localStorage.getItem('auth_token');
        if (token && authStore.isAuthenticated) {
            next({ name: 'dashboard' });
            return;
        }
    }

    next();
});

export default router;
