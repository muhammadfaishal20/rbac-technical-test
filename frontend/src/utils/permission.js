/**
 * RBAC Permission Helper Utilities
 */

/**
 * Check if user has permission
 * @param {Object|Ref} user - User object with permissions (can be direct or via roles), or Vue ref
 * @param {string} permission - Permission name to check
 * @returns {boolean}
 */
export function hasPermission(user, permission) {
    if (!user) {
        return false;
    }

    // Handle Vue ref - extract actual value
    const userObj = user?.value !== undefined ? user.value : user;
    
    if (!userObj) {
        return false;
    }

    // Check direct permissions first (if exists)
    if (userObj.permissions && Array.isArray(userObj.permissions)) {
        const hasDirectPermission = userObj.permissions.some(p => {
            const permName = typeof p === 'string' ? p : p.name;
            return permName === permission;
        });
        if (hasDirectPermission) {
            return true;
        }
    }

    // Check permissions via roles
    if (userObj.roles && Array.isArray(userObj.roles)) {
        for (const role of userObj.roles) {
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
}

/**
 * Check if user has role
 * @param {Object} user - User object with roles
 * @param {string} role - Role name to check
 * @returns {boolean}
 */
export function hasRole(user, role) {
    if (!user || !user.roles) {
        return false;
    }

    const roles = Array.isArray(user.roles) 
        ? user.roles 
        : [];

    return roles.some(r => {
        const roleName = typeof r === 'string' ? r : r.name;
        return roleName === role;
    });
}

/**
 * Check if user has any of the given roles
 * @param {Object} user - User object with roles
 * @param {string[]} roles - Array of role names to check
 * @returns {boolean}
 */
export function hasAnyRole(user, roles) {
    if (!user || !user.roles || !Array.isArray(roles)) {
        return false;
    }

    const userRoles = user.roles.map(r => typeof r === 'string' ? r : r.name);
    return roles.some(role => userRoles.includes(role));
}

/**
 * Check if user has all of the given roles
 * @param {Object} user - User object with roles
 * @param {string[]} roles - Array of role names to check
 * @returns {boolean}
 */
export function hasAllRoles(user, roles) {
    if (!user || !user.roles || !Array.isArray(roles)) {
        return false;
    }

    const userRoles = user.roles.map(r => typeof r === 'string' ? r : r.name);
    return roles.every(role => userRoles.includes(role));
}

/**
 * Check if user has any of the given permissions
 * @param {Object} user - User object with permissions
 * @param {string[]} permissions - Array of permission names to check
 * @returns {boolean}
 */
export function hasAnyPermission(user, permissions) {
    if (!user || !user.permissions || !Array.isArray(permissions)) {
        return false;
    }

    const userPermissions = user.permissions.map(p => typeof p === 'string' ? p : p.name);
    return permissions.some(permission => userPermissions.includes(permission));
}

/**
 * Check if user has all of the given permissions
 * @param {Object} user - User object with permissions
 * @param {string[]} permissions - Array of permission names to check
 * @returns {boolean}
 */
export function hasAllPermissions(user, permissions) {
    if (!user || !user.permissions || !Array.isArray(permissions)) {
        return false;
    }

    const userPermissions = user.permissions.map(p => typeof p === 'string' ? p : p.name);
    return permissions.every(permission => userPermissions.includes(permission));
}

/**
 * Get user's role names
 * @param {Object} user - User object with roles
 * @returns {string[]}
 */
export function getUserRoles(user) {
    if (!user || !user.roles) {
        return [];
    }

    return user.roles.map(r => typeof r === 'string' ? r : r.name);
}

/**
 * Get user's permission names
 * @param {Object} user - User object with permissions
 * @returns {string[]}
 */
export function getUserPermissions(user) {
    if (!user || !user.permissions) {
        return [];
    }

    return user.permissions.map(p => typeof p === 'string' ? p : p.name);
}

/**
 * Check if user is admin
 * @param {Object} user - User object with roles
 * @returns {boolean}
 */
export function isAdmin(user) {
    return hasRole(user, 'admin');
}

/**
 * Check if user is management-user
 * @param {Object} user - User object with roles
 * @returns {boolean}
 */
export function isManagementUser(user) {
    return hasRole(user, 'management-user');
}

/**
 * Check if user is management-file
 * @param {Object} user - User object with roles
 * @returns {boolean}
 */
export function isManagementFile(user) {
    return hasRole(user, 'management-file');
}

