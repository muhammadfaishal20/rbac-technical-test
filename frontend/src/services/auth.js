/**
 * Authentication API Service
 */
import http, { getCsrfCookie } from './http.js';

const authService = {
    /**
     * Register new user
     */
    async register(userData) {
        await getCsrfCookie();
        const response = await http.post('/auth/register', userData);
        return response;
    },

    /**
     * Login user
     */
    async login(credentials) {
        await getCsrfCookie();
        const response = await http.post('/auth/login', credentials);
        
        // Store token and user data
        if (response.data && response.data.token) {
            localStorage.setItem('auth_token', response.data.token);
            localStorage.setItem('auth_user', JSON.stringify(response.data.user));
        }
        
        return response;
    },

    /**
     * Logout user
     */
    async logout() {
        try {
            await http.post('/auth/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            // Clear local storage regardless of API response
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
        }
    },

    /**
     * Logout from all devices
     */
    async logoutAll() {
        try {
            await http.post('/auth/logout-all');
        } catch (error) {
            console.error('Logout all error:', error);
        } finally {
            // Clear local storage regardless of API response
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
        }
    },

    /**
     * Get current authenticated user
     */
    async me() {
        const response = await http.get('/auth/me');
        
        // Update stored user data
        if (response.data) {
            localStorage.setItem('auth_user', JSON.stringify(response.data));
        }
        
        return response;
    },

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!localStorage.getItem('auth_token');
    },

    /**
     * Get stored user data
     */
    getStoredUser() {
        const userStr = localStorage.getItem('auth_user');
        return userStr ? JSON.parse(userStr) : null;
    },

    /**
     * Get stored token
     */
    getToken() {
        return localStorage.getItem('auth_token');
    },
};

export default authService;

