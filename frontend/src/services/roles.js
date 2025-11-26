/**
 * Role Management API Service
 */
import http from './http.js';

const roleService = {
    /**
     * Get list of roles
     * @param {Object} params - Query parameters (search, per_page, etc.)
     */
    async getRoles(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/rbac/roles?${queryString}` : '/rbac/roles';
        const response = await http.get(endpoint);
        return response;
    },

    /**
     * Get single role by ID
     * @param {number|string} roleId - Role ID
     */
    async getRole(roleId) {
        const response = await http.get(`/rbac/roles/${roleId}`);
        return response;
    },

    /**
     * Create new role
     * @param {Object} roleData - Role data (name, permissions)
     */
    async createRole(roleData) {
        const response = await http.post('/rbac/roles', roleData);
        return response;
    },

    /**
     * Update role
     * @param {number|string} roleId - Role ID
     * @param {Object} roleData - Role data to update
     */
    async updateRole(roleId, roleData) {
        const response = await http.put(`/rbac/roles/${roleId}`, roleData);
        return response;
    },

    /**
     * Delete role
     * @param {number|string} roleId - Role ID
     */
    async deleteRole(roleId) {
        const response = await http.delete(`/rbac/roles/${roleId}`);
        return response;
    },

    /**
     * Get all available permissions
     */
    async getPermissions() {
        const response = await http.get('/rbac/roles/permissions');
        return response;
    },
};

export default roleService;

