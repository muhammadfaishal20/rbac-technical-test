# RBAC Update Summary

Dokumentasi perubahan RBAC system sesuai requirement baru.

## Perubahan Struktur

### Roles (3 Role Baru)

1. **admin**
   - Permissions: `manage-roles`, `manage-users`, `manage-files`
   - Akses: Semua fitur (role management, user management, file management)

2. **management-user**
   - Permissions: `manage-roles`
   - Akses: Hanya role & permission management

3. **management-file**
   - Permissions: `manage-files`
   - Akses: Hanya file management

### Permissions

- `manage-roles` - Manage roles and permissions
- `manage-users` - Manage users
- `manage-files` - Manage files

**Note:** Permission `view-dashboard` sudah dihapus karena tidak diperlukan.

## Skenario User

### Skenario 1: Single Role
User dengan 1 role saja:
- admin@example.com (admin)
- management.user@example.com (management-user)
- management.file@example.com (management-file)

### Skenario 2: Multiple Roles
User dengan multiple roles:
- multi1@example.com (admin, management-user)
- multi2@example.com (admin, management-file)
- multi3@example.com (management-user, management-file)
- multi4@example.com (admin, management-user, management-file)

**Note:** Skenario direct permission sudah dihapus. Semua user harus memiliki minimal 1 role.

## Route Access Control

### RBAC Routes (`/api/rbac/*`)
**Middleware:** `auth:sanctum`, `permission:manage-roles`
**Akses:**
- ✅ admin (punya permission manage-roles)
- ✅ management-user (punya permission manage-roles)
- ❌ management-file (tidak punya permission manage-roles)

**Routes:**
- `GET /api/rbac/roles` - List roles
- `POST /api/rbac/roles` - Create role
- `GET /api/rbac/roles/{id}` - Get role
- `PUT /api/rbac/roles/{id}` - Update role
- `DELETE /api/rbac/roles/{id}` - Delete role
- `GET /api/rbac/roles/permissions` - Get all permissions

### User Management Routes (`/api/rbac/users/*`)
**Middleware:** `auth:sanctum`, `permission:manage-roles`, `permission:manage-users`
**Akses:**
- ✅ admin (punya permission manage-users)
- ❌ management-user (tidak punya permission manage-users)
- ❌ management-file (tidak punya permission manage-users)

**Routes:**
- `GET /api/rbac/users` - List users
- `POST /api/rbac/users` - Create user
- `GET /api/rbac/users/{id}` - Get user
- `PUT /api/rbac/users/{id}` - Update user
- `DELETE /api/rbac/users/{id}` - Delete user
- `GET /api/rbac/users/roles` - Get all roles

### File Management Routes (`/api/files/*`)
**Middleware:** `auth:sanctum`, `permission:manage-files`
**Akses:**
- ✅ admin (punya permission manage-files)
- ❌ management-user (tidak punya permission manage-files)
- ✅ management-file (punya permission manage-files)

**Routes:**
- `POST /api/files/upload` - Upload files
- `GET /api/files` - List files
- `GET /api/files/{id}` - Get file details
- `GET /api/files/{id}/download` - Download file
- `DELETE /api/files/{id}` - Delete file

**File Access Rules:**
- Admin: Bisa lihat semua file dari semua user
- Management-file: Hanya bisa lihat file mereka sendiri

## Default Role Assignment

Saat user register, otomatis mendapat role `management-file` (bukan `user` lagi).

## Protected Default Roles

Default roles yang tidak bisa dihapus:
- `admin`
- `management-user`
- `management-file`

## Seeder

### RolePermissionSeeder
Membuat 3 permissions dan 3 roles dengan permission assignment.

### UserSeeder
Membuat users dengan 2 skenario:
1. Single role (3 users)
2. Multiple roles (4 users)

**Total:** 7 users untuk testing

## Testing

### Test Admin Access
```bash
# Login as admin
POST /api/auth/login
{
  "email": "admin@example.com",
  "password": "password"
}

# Admin bisa akses semua routes:
# - /api/rbac/roles (manage-roles)
# - /api/rbac/users (manage-users)
# - /api/files (manage-files)
```

### Test Management-User Access
```bash
# Login as management-user
POST /api/auth/login
{
  "email": "management.user@example.com",
  "password": "password"
}

# Management-user bisa akses:
# - /api/rbac/roles (manage-roles)
# - /api/rbac/users (manage-users) ❌ DENIED

# Management-user tidak bisa akses:
# - /api/files (manage-files) ❌ DENIED
```

### Test Management-File Access
```bash
# Login as management-file
POST /api/auth/login
{
  "email": "management.file@example.com",
  "password": "password"
}

# Management-file bisa akses:
# - /api/files (manage-files)

# Management-file tidak bisa akses:
# - /api/rbac/roles (manage-roles) ❌ DENIED
# - /api/rbac/users (manage-users) ❌ DENIED
```

### Test Multiple Roles
```bash
# Login as multi1 (admin + management-user)
POST /api/auth/login
{
  "email": "multi1@example.com",
  "password": "password"
}

# Multi1 bisa akses:
# - /api/rbac/roles (punya manage-roles dari management-user)
# - /api/rbac/users (punya manage-users dari admin)
# - /api/files (punya manage-files dari admin)
```

## Migration & Seeder Commands

```bash
# Fresh migration with seed
php artisan migrate:fresh --seed

# Or run seeders separately
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=UserSeeder
```

## Summary

✅ 3 roles baru sesuai requirement
✅ 2 skenario user (single role & multiple role)
✅ Direct permission skenario dihapus
✅ Route access control sesuai role
✅ Middleware protection sudah benar
✅ Default role assignment updated
✅ Protected default roles updated

