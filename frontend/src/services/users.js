/**
 * User Management API Service
 */
import http from './http.js';

const userService = {
    /**
     * Get list of users
     * @param {Object} params - Query parameters (search, per_page, role, etc.)
     */
    async getUsers(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/rbac/users?${queryString}` : '/rbac/users';
        const response = await http.get(endpoint);
        return response;
    },

    /**
     * Get single user by ID
     * @param {number|string} userId - User ID
     */
    async getUser(userId) {
        const response = await http.get(`/rbac/users/${userId}`);
        return response;
    },

    /**
     * Create new user
     * @param {Object} userData - User data (name, email, password, roles)
     */
    async createUser(userData) {
        const response = await http.post('/rbac/users', userData);
        return response;
    },

    /**
     * Update user
     * @param {number|string} userId - User ID
     * @param {Object} userData - User data to update
     */
    async updateUser(userId, userData) {
        const response = await http.put(`/rbac/users/${userId}`, userData);
        return response;
    },

    /**
     * Delete user
     * @param {number|string} userId - User ID
     */
    async deleteUser(userId) {
        const response = await http.delete(`/rbac/users/${userId}`);
        return response;
    },

    /**
     * Get all available roles for user assignment
     */
    async getAvailableRoles() {
        const response = await http.get('/rbac/users/roles');
        return response;
    },
};

export default userService;

