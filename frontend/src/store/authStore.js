/**
 * Authentication Store (Composable)
 * Using Vue 3 Composition API for state management
 */
import { ref, computed } from 'vue';
import authService from '@/services/auth.js';

// Reactive state
const user = ref(null);
const token = ref(null);
const isAuthenticated = ref(false);
const loading = ref(false);
const error = ref(null);

// Initialize from localStorage
const initializeAuth = () => {
    const storedToken = authService.getToken();
    const storedUser = authService.getStoredUser();

    if (storedToken && storedUser) {
        token.value = storedToken;
        user.value = storedUser;
        isAuthenticated.value = true;
    }
};

// Initialize on module load
initializeAuth();

/**
 * Re-initialize auth from localStorage
 */
const reinitializeAuth = () => {
    const storedToken = authService.getToken();
    const storedUser = authService.getStoredUser();

    if (storedToken && storedUser) {
        token.value = storedToken;
        user.value = storedUser;
        isAuthenticated.value = true;
    } else {
        token.value = null;
        user.value = null;
        isAuthenticated.value = false;
    }
};

/**
 * Authentication Store Composable
 */
export function useAuthStore() {
    // Re-initialize on each use to ensure latest state
    reinitializeAuth();

    /**
     * Login user
     */
    const login = async (credentials) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await authService.login(credentials);
            
            if (response.data && response.data.token) {
                token.value = response.data.token;
                user.value = response.data.user;
                isAuthenticated.value = true;
            }

            return response;
        } catch (err) {
            error.value = err.message || 'Login failed';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Register new user
     */
    const register = async (userData) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await authService.register(userData);
            
            if (response.data && response.data.token) {
                token.value = response.data.token;
                user.value = response.data.user;
                isAuthenticated.value = true;
            }

            return response;
        } catch (err) {
            error.value = err.message || 'Registration failed';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Logout user
     */
    const logout = async () => {
        loading.value = true;
        error.value = null;

        try {
            await authService.logout();
        } catch (err) {
            console.error('Logout error:', err);
        } finally {
            // Clear state regardless of API response
            token.value = null;
            user.value = null;
            isAuthenticated.value = false;
            loading.value = false;
        }
    };

    /**
     * Logout from all devices
     */
    const logoutAll = async () => {
        loading.value = true;
        error.value = null;

        try {
            await authService.logoutAll();
        } catch (err) {
            console.error('Logout all error:', err);
        } finally {
            // Clear state regardless of API response
            token.value = null;
            user.value = null;
            isAuthenticated.value = false;
            loading.value = false;
        }
    };

    /**
     * Fetch current user data
     */
    const fetchUser = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await authService.me();
            
            if (response.data) {
                user.value = response.data;
                token.value = authService.getToken();
                isAuthenticated.value = true;
            } else {
                // If no data, user might not be authenticated
                throw new Error('No user data received');
            }

            return response;
        } catch (err) {
            error.value = err.message || 'Failed to fetch user';
            // If 401, clear auth state
            if (err.status === 401) {
                token.value = null;
                user.value = null;
                isAuthenticated.value = false;
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
            }
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Check if user has permission
     */
    const hasPermission = (permission) => {
        if (!user.value) {
            return false;
        }

        // Check direct permissions first (if exists)
        if (user.value.permissions && Array.isArray(user.value.permissions)) {
            const hasDirectPermission = user.value.permissions.some(p => {
                const permName = typeof p === 'string' ? p : p.name;
                return permName === permission;
            });
            if (hasDirectPermission) {
                return true;
            }
        }

        // Check permissions via roles
        if (user.value.roles && Array.isArray(user.value.roles)) {
            for (const role of user.value.roles) {
                if (role.permissions && Array.isArray(role.permissions)) {
                    const hasRolePermission = role.permissions.some(p => {
                        const permName = typeof p === 'string' ? p : p.name;
                        return permName === permission;
                    });
                    if (hasRolePermission) {
                        return true;
                    }
                }
            }
        }

        return false;
    };

    /**
     * Check if user has role
     */
    const hasRole = (role) => {
        if (!user.value || !user.value.roles) {
            return false;
        }

        const roles = user.value.roles || [];
        return roles.some(r => r.name === role);
    };

    /**
     * Check if user has any of the given roles
     */
    const hasAnyRole = (roles) => {
        if (!user.value || !user.value.roles) {
            return false;
        }

        const userRoles = user.value.roles.map(r => r.name);
        return roles.some(role => userRoles.includes(role));
    };

    /**
     * Check if user has any of the given permissions
     */
    const hasAnyPermission = (permissions) => {
        if (!user.value || !user.value.permissions) {
            return false;
        }

        const userPermissions = user.value.permissions.map(p => p.name);
        return permissions.some(permission => userPermissions.includes(permission));
    };

    /**
     * Clear error
     */
    const clearError = () => {
        error.value = null;
    };

    /**
     * Update user data manually (useful for syncing from localStorage)
     */
    const updateUser = (userData) => {
        if (userData) {
            user.value = userData;
            isAuthenticated.value = true;
        }
    };

    // Computed properties
    const userRoles = computed(() => {
        return user.value?.roles || [];
    });

    const userPermissions = computed(() => {
        return user.value?.permissions || [];
    });

    return {
        // State
        user,
        token,
        isAuthenticated,
        loading,
        error,

        // Computed
        userRoles,
        userPermissions,

        // Methods
        login,
        register,
        logout,
        logoutAll,
        fetchUser,
        updateUser,
        hasPermission,
        hasRole,
        hasAnyRole,
        hasAnyPermission,
        clearError,
    };
}

// Export default instance
export default useAuthStore;

