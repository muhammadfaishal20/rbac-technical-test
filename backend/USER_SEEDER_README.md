# User Seeder Documentation

Seeder untuk membuat user dengan berbagai skenario RBAC untuk testing dan development.

## Skenario

### Skenario 1: User dengan 1 Role

User yang hanya memiliki 1 role saja.

**Users:**
- **john@example.com** (Password: `password`)
  - Role: `admin`
  - Permissions: `view-dashboard`, `manage-roles`, `manage-users` (dari role admin)

- **jane@example.com** (Password: `password`)
  - Role: `user`
  - Permissions: `view-dashboard` (dari role user)

**Cara Test:**
```php
$user = User::where('email', 'john@example.com')->first();
$user->hasRole('admin'); // true
$user->hasRole('user'); // false
$user->can('view-dashboard'); // true
$user->can('manage-users'); // true
```

### Skenario 2: User dengan Multiple Roles

User yang memiliki lebih dari 1 role.

**Users:**
- **bob@example.com** (Password: `password`)
  - Roles: `admin`, `user`
  - Permissions: `view-dashboard`, `manage-roles`, `manage-users` (dari role admin)

- **alice@example.com** (Password: `password`)
  - Roles: `admin`, `user`
  - Permissions: `view-dashboard`, `manage-roles`, `manage-users` (dari role admin)

**Cara Test:**
```php
$user = User::where('email', 'bob@example.com')->first();
$user->hasRole('admin'); // true
$user->hasRole('user'); // true
$user->hasAnyRole(['admin', 'user']); // true
$user->can('view-dashboard'); // true (dari admin atau user)
$user->can('manage-users'); // true (dari admin)
```

### Skenario 3: User dengan Permission Langsung

User yang memiliki permission langsung tanpa melalui role, atau kombinasi role + permission langsung.

**Users:**
- **charlie@example.com** (Password: `password`)
  - Roles: (tidak ada)
  - Direct Permissions: `view-dashboard`
  - Total Permissions: `view-dashboard`

- **diana@example.com** (Password: `password`)
  - Roles: (tidak ada)
  - Direct Permissions: `view-dashboard`, `manage-users`
  - Total Permissions: `view-dashboard`, `manage-users`

- **edward@example.com** (Password: `password`)
  - Roles: `user` (punya permission `view-dashboard`)
  - Direct Permissions: `manage-roles`
  - Total Permissions: `view-dashboard` (dari role), `manage-roles` (direct)

**Cara Test:**
```php
// User tanpa role, hanya direct permission
$user = User::where('email', 'charlie@example.com')->first();
$user->roles; // empty collection
$user->can('view-dashboard'); // true (dari direct permission)
$user->can('manage-users'); // false

// User dengan role + direct permission
$user = User::where('email', 'edward@example.com')->first();
$user->hasRole('user'); // true
$user->can('view-dashboard'); // true (dari role user)
$user->can('manage-roles'); // true (dari direct permission)
```

## Cara Menjalankan Seeder

### 1. Run Semua Seeders (Recommended)
```bash
php artisan db:seed
```
Ini akan menjalankan:
1. RolePermissionSeeder (membuat roles dan permissions)
2. UserSeeder (membuat users dengan berbagai skenario)

### 2. Run Seeder Spesifik
```bash
# Pastikan RolePermissionSeeder sudah dijalankan terlebih dahulu
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=UserSeeder
```

### 3. Fresh Migration + Seed
```bash
php artisan migrate:fresh --seed
```

## Daftar Semua Users

| Email | Password | Roles | Direct Permissions | Total Permissions |
|-------|----------|-------|-------------------|-------------------|
| john@example.com | password | admin | - | view-dashboard, manage-roles, manage-users |
| jane@example.com | password | user | - | view-dashboard |
| bob@example.com | password | admin, user | - | view-dashboard, manage-roles, manage-users |
| alice@example.com | password | admin, user | - | view-dashboard, manage-roles, manage-users |
| charlie@example.com | password | - | view-dashboard | view-dashboard |
| diana@example.com | password | - | view-dashboard, manage-users | view-dashboard, manage-users |
| edward@example.com | password | user | manage-roles | view-dashboard, manage-roles |

## Testing dengan API

### Login sebagai User
```bash
# Login sebagai admin (john@example.com)
POST /api/auth/login
{
  "email": "john@example.com",
  "password": "password"
}

# Login sebagai user biasa (jane@example.com)
POST /api/auth/login
{
  "email": "jane@example.com",
  "password": "password"
}
```

### Test Permission Middleware
```bash
# Test dashboard route (requires view-dashboard permission)
GET /api/dashboard
Headers: Authorization: Bearer {token}

# Semua user di atas bisa akses karena punya view-dashboard
```

### Test RBAC Endpoints
```bash
# List users (requires manage-users permission)
GET /api/rbac/users
Headers: Authorization: Bearer {token}

# Hanya user dengan permission manage-users yang bisa akses:
# - john@example.com (admin)
# - bob@example.com (admin)
# - alice@example.com (admin)
# - diana@example.com (direct permission)
# - edward@example.com (direct permission)
```

## Query Examples

### Get User dengan Roles dan Permissions
```php
$user = User::with('roles', 'permissions')->where('email', 'bob@example.com')->first();

// Get all roles
$user->roles; // Collection of Role models

// Get all permissions (from roles + direct)
$user->permissions; // Collection of Permission models

// Get direct permissions only
$user->getDirectPermissions(); // Collection

// Get permissions from roles only
$user->getPermissionsViaRoles(); // Collection
```

### Check User Capabilities
```php
$user = User::where('email', 'edward@example.com')->first();

// Check role
$user->hasRole('user'); // true
$user->hasAnyRole(['admin', 'user']); // true
$user->hasAllRoles(['admin', 'user']); // false

// Check permission
$user->can('view-dashboard'); // true
$user->can('manage-roles'); // true
$user->can('manage-users'); // false
$user->hasPermissionTo('view-dashboard'); // true
$user->hasAnyPermission(['view-dashboard', 'manage-users']); // true
$user->hasAllPermissions(['view-dashboard', 'manage-roles']); // true
```

## Notes

- Semua users menggunakan password: `password`
- Semua users sudah verified (`email_verified_at` sudah di-set)
- Pastikan `RolePermissionSeeder` dijalankan sebelum `UserSeeder`
- Seeder ini aman untuk dijalankan multiple times (akan create user baru jika email belum ada)

