# RBAC Module Documentation

Modul Role-Based Access Control (RBAC) menggunakan Spatie Laravel Permission.

## Struktur Folder

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── RBAC/
│   │   │       ├── RoleController.php
│   │   │       └── UserController.php
│   │   ├── Middleware/
│   │   │   └── CheckPermission.php
│   │   └── Requests/
│   │       └── RBAC/
│   │           ├── RoleRequest.php
│   │           └── UserRequest.php
│   └── Models/
│       └── User.php (updated with HasRoles trait)
├── database/
│   └── seeders/
│       └── RolePermissionSeeder.php
└── routes/
    ├── api.php
    └── rbac.php
```

## Setup

### 1. Install Dependencies
Package sudah terinstall: `spatie/laravel-permission`

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Seed Roles dan Permissions
```bash
php artisan db:seed --class=RolePermissionSeeder
```

## Roles & Permissions

### Default Roles
- **admin**: Memiliki semua permissions
- **user**: Memiliki permission `view-dashboard`

### Default Permissions
- `view-dashboard`: Akses ke halaman dashboard
- `manage-roles`: Mengelola roles
- `manage-users`: Mengelola users

## API Endpoints

### Role Endpoints

#### List Roles
```
GET /api/rbac/roles
Query Parameters:
  - search: string (optional)
  - per_page: integer (optional, default: 15)
```

#### Get Role
```
GET /api/rbac/roles/{role}
```

#### Create Role
```
POST /api/rbac/roles
Body:
{
  "name": "role_name",
  "permissions": [1, 2, 3] // array of permission IDs
}
```

#### Update Role
```
PUT/PATCH /api/rbac/roles/{role}
Body:
{
  "name": "role_name",
  "permissions": [1, 2, 3] // array of permission IDs
}
```

#### Delete Role
```
DELETE /api/rbac/roles/{role}
Note: Cannot delete default roles (admin, user)
```

#### Get All Permissions
```
GET /api/rbac/roles/permissions
```

### User Endpoints

#### List Users
```
GET /api/rbac/users
Query Parameters:
  - search: string (optional)
  - role: string (optional) - filter by role name
  - per_page: integer (optional, default: 15)
```

#### Get User
```
GET /api/rbac/users/{user}
```

#### Create User
```
POST /api/rbac/users
Body:
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "password",
  "password_confirmation": "password",
  "roles": [1, 2] // array of role IDs
}
```

#### Update User
```
PUT/PATCH /api/rbac/users/{user}
Body:
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "password", // optional
  "password_confirmation": "password", // required if password provided
  "roles": [1, 2] // array of role IDs
}
```

#### Delete User
```
DELETE /api/rbac/users/{user}
Note: Cannot delete own account
```

#### Get All Roles
```
GET /api/rbac/users/roles
```

## Middleware

### CheckPermission Middleware

Middleware untuk mengecek permission user sebelum mengakses route.

**Usage:**
```php
Route::middleware(['auth:sanctum', 'permission:view-dashboard'])->get('/dashboard', function () {
    // Route logic
});
```

**Example Route:**
```php
// routes/api.php
Route::middleware(['auth:sanctum', 'permission:view-dashboard'])->get('/dashboard', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Welcome to Dashboard!',
        'user' => $request->user(),
    ]);
});
```

## Validation

### RoleRequest
- `name`: required, string, max:255, unique
- `permissions`: nullable, array
- `permissions.*`: exists in permissions table

### UserRequest
- `name`: required, string, max:255
- `email`: required, string, email, max:255, unique
- `password`: required (on create), nullable (on update), string, min:8, confirmed
- `roles`: nullable, array
- `roles.*`: exists in roles table

## Features

1. **CRUD Roles**: Create, Read, Update, Delete roles
2. **Assign Permissions**: Assign multiple permissions to roles
3. **CRUD Users**: Create, Read, Update, Delete users
4. **Assign Multiple Roles**: Assign multiple roles to users
5. **Search & Filter**: Search roles/users and filter by role
6. **Pagination**: Paginated results for lists
7. **Permission Middleware**: Protect routes with permission check
8. **Validation**: Comprehensive request validation

## Security Features

- Cannot delete default roles (admin, user)
- Cannot delete own account
- Password hashing
- Permission-based access control
- Authentication required for all RBAC endpoints

## Next Steps

1. Run migrations: `php artisan migrate`
2. Seed roles and permissions: `php artisan db:seed --class=RolePermissionSeeder`
3. Create admin user and assign admin role
4. Test API endpoints using Postman or similar tools

