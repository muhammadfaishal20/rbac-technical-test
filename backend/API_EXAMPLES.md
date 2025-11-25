# API Examples - Request Data

Dokumentasi contoh data untuk testing API endpoints (Create User, Role, Permission).

## Table of Contents
1. [Create Permission](#create-permission)
2. [Create Role](#create-role)
3. [Create User](#create-user)
4. [Update Role (Assign Permissions)](#update-role-assign-permissions)
5. [Update User (Assign Roles)](#update-user-assign-roles)

---

## Create Permission

**Note:** Permission biasanya dibuat via seeder, tapi jika ingin membuat via API, bisa menggunakan Spatie Permission model langsung atau membuat endpoint khusus.

### Via Tinker (Recommended)
```php
php artisan tinker

use Spatie\Permission\Models\Permission;

Permission::create(['name' => 'manage-roles']);
Permission::create(['name' => 'manage-users']);
Permission::create(['name' => 'manage-files']);
```

### Example Permissions Data
```json
{
  "name": "manage-roles"
}
```

```json
{
  "name": "manage-users"
}
```

```json
{
  "name": "manage-files"
}
```

---

## Create Role

**Endpoint:** `POST /api/rbac/roles`

**Access:** Requires `manage-roles` permission (admin, management-user)

### Example 1: Create Role without Permissions
```json
{
  "name": "custom-role"
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/rbac/roles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "name": "custom-role"
  }'
```

### Example 2: Create Role with Permissions
```json
{
  "name": "custom-role",
  "permissions": [1, 2]
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/rbac/roles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "name": "custom-role",
    "permissions": [1, 2]
  }'
```

### Default Roles (After Seeder)
- **ID 1:** admin (permissions: manage-roles, manage-users, manage-files)
- **ID 2:** management-user (permissions: manage-roles)
- **ID 3:** management-file (permissions: manage-files)

**Note:** Permission IDs (1, 2, 3) harus sudah ada di database. Cek permission IDs dengan:
```bash
GET /api/rbac/roles/permissions
```

### More Role Examples
```json
// Role with manage-roles permission
{
  "name": "role-manager",
  "permissions": [1]
}

// Role with manage-files permission
{
  "name": "file-manager",
  "permissions": [3]
}

// Role with multiple permissions
{
  "name": "super-manager",
  "permissions": [1, 2, 3]
}
```

---

## Create User

**Endpoint:** `POST /api/rbac/users`

**Access:** Requires `manage-users` permission (admin only)

### Example 1: Create User without Roles
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/rbac/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Example 2: Create User with Single Role
```json
{
  "name": "Jane Smith",
  "email": "jane.smith@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "roles": [1]
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/rbac/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "name": "Jane Smith",
    "email": "jane.smith@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "roles": [1]
  }'
```

### Example 3: Create User with Multiple Roles
```json
{
  "name": "Bob Johnson",
  "email": "bob.johnson@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "roles": [1, 2]
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/rbac/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "name": "Bob Johnson",
    "email": "bob.johnson@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "roles": [1, 2]
  }'
```

### More User Examples

#### Admin User
```json
{
  "name": "Admin User",
  "email": "admin@example.com",
  "password": "Admin@123",
  "password_confirmation": "Admin@123",
  "roles": [1]
}
```

#### Management User
```json
{
  "name": "Management User",
  "email": "management.user@example.com",
  "password": "Mgmt@123",
  "password_confirmation": "Mgmt@123",
  "roles": [2]
}
```

#### Management File User
```json
{
  "name": "Management File User",
  "email": "management.file@example.com",
  "password": "File@123",
  "password_confirmation": "File@123",
  "roles": [3]
}
```

#### User with Multiple Roles (Admin + Management User)
```json
{
  "name": "Multi Role User",
  "email": "multi@example.com",
  "password": "Multi@123",
  "password_confirmation": "Multi@123",
  "roles": [1, 2]
}
```

#### User with Multiple Roles (All Roles)
```json
{
  "name": "Super User",
  "email": "super@example.com",
  "password": "Super@123",
  "password_confirmation": "Super@123",
  "roles": [1, 2, 3]
}
```

**Note:** Role IDs (1, 2, 3) harus sudah ada di database. Cek role IDs dengan:
```bash
GET /api/rbac/users/roles
```

---

## Update Role (Assign Permissions)

**Endpoint:** `PUT /api/rbac/roles/{id}` atau `PATCH /api/rbac/roles/{id}`

**Access:** Requires `manage-roles` permission (admin, management-user)

### Example 1: Update Role Name Only
```json
{
  "name": "updated-role-name"
}
```

### Example 2: Update Role with Permissions
```json
{
  "name": "management-user",
  "permissions": [1, 3]
}
```

**cURL:**
```bash
curl -X PUT http://localhost:8000/api/rbac/roles/2 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "name": "management-user",
    "permissions": [1, 3]
  }'
```

### Example 3: Remove All Permissions from Role
```json
{
  "name": "custom-role",
  "permissions": []
}
```

### Example 4: Replace Permissions
```json
{
  "name": "custom-role",
  "permissions": [1, 3]
}
```

**Note:** `permissions` array akan mengganti semua permissions yang ada (sync).

**Protected Roles:** Cannot update or delete default roles:
- admin
- management-user
- management-file

---

## Update User (Assign Roles)

**Endpoint:** `PUT /api/rbac/users/{id}` atau `PATCH /api/rbac/users/{id}`

**Access:** Requires `manage-users` permission (admin only)

### Example 1: Update User Info Only
```json
{
  "name": "Updated Name",
  "email": "updated@example.com"
}
```

### Example 2: Update User with Roles
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "roles": [1, 2]
}
```

**cURL:**
```bash
curl -X PUT http://localhost:8000/api/rbac/users/5 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "roles": [1, 2]
  }'
```

### Example 3: Update User Password
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

### Example 4: Update User with Roles and Password
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123",
  "roles": [1]
}
```

### Example 5: Remove All Roles from User
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "roles": []
}
```

**Note:** `roles` array akan mengganti semua roles yang ada (sync).

---

## Complete Workflow Example

### Step 1: Login
```bash
POST /api/auth/login
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

### Step 2: Get Available Permissions
```bash
GET /api/rbac/roles/permissions
Headers: Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "manage-roles",
      "guard_name": "web"
    },
    {
      "id": 2,
      "name": "manage-users",
      "guard_name": "web"
    },
    {
      "id": 3,
      "name": "manage-files",
      "guard_name": "web"
    }
  ]
}
```

### Step 3: Create Role with Permissions
```bash
POST /api/rbac/roles
Headers: Authorization: Bearer {token}
{
  "name": "custom-role",
  "permissions": [1, 3]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Role created successfully.",
  "data": {
    "id": 4,
    "name": "custom-role",
    "guard_name": "web",
    "permissions": [
      {
        "id": 1,
        "name": "manage-roles"
      },
      {
        "id": 3,
        "name": "manage-files"
      }
    ]
  }
}
```

### Step 4: Get Available Roles
```bash
GET /api/rbac/users/roles
Headers: Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "admin",
      "guard_name": "web"
    },
    {
      "id": 2,
      "name": "management-user",
      "guard_name": "web"
    },
    {
      "id": 3,
      "name": "management-file",
      "guard_name": "web"
    },
    {
      "id": 4,
      "name": "custom-role",
      "guard_name": "web"
    }
  ]
}
```

### Step 5: Create User with Role
```bash
POST /api/rbac/users
Headers: Authorization: Bearer {token}
{
  "name": "New User",
  "email": "newuser@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "roles": [4]
}
```

**Response:**
```json
{
  "success": true,
  "message": "User created successfully.",
  "data": {
    "id": 8,
    "name": "New User",
    "email": "newuser@example.com",
    "roles": [
      {
        "id": 4,
        "name": "custom-role"
      }
    ],
    "permissions": [
      {
        "id": 1,
        "name": "manage-roles"
      },
      {
        "id": 3,
        "name": "manage-files"
      }
    ]
  }
}
```

---

## Postman Collection Examples

### Environment Variables
```
base_url: http://localhost:8000/api
token: (set after login)
```

### Create Role
```
POST {{base_url}}/rbac/roles
Headers:
  Authorization: Bearer {{token}}
  Content-Type: application/json

Body (raw JSON):
{
  "name": "custom-role",
  "permissions": [1, 3]
}
```

### Create User
```
POST {{base_url}}/rbac/users
Headers:
  Authorization: Bearer {{token}}
  Content-Type: application/json

Body (raw JSON):
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "roles": [1, 2]
}
```

---

## JavaScript/Fetch Examples

### Create Role
```javascript
const createRole = async (name, permissionIds = []) => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://localhost:8000/api/rbac/roles', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    body: JSON.stringify({
      name,
      permissions: permissionIds,
    }),
  });

  return await response.json();
};

// Usage
createRole('custom-role', [1, 3]);
```

### Create User
```javascript
const createUser = async (name, email, password, roleIds = []) => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://localhost:8000/api/rbac/users', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    body: JSON.stringify({
      name,
      email,
      password,
      password_confirmation: password,
      roles: roleIds,
    }),
  });

  return await response.json();
};

// Usage
createUser('John Doe', 'john@example.com', 'password123', [1, 2]);
```

---

## Common Default IDs (After Seeder)

Setelah menjalankan seeder, biasanya IDs seperti ini:

### Permissions
- ID 1: `manage-roles`
- ID 2: `manage-users`
- ID 3: `manage-files`

### Roles
- ID 1: `admin` (permissions: 1, 2, 3)
- ID 2: `management-user` (permissions: 1)
- ID 3: `management-file` (permissions: 3)

**Note:** IDs bisa berbeda tergantung urutan seeder atau data yang sudah ada. Selalu gunakan endpoint untuk mendapatkan IDs yang benar:
- `GET /api/rbac/roles/permissions` - Get all permissions
- `GET /api/rbac/users/roles` - Get all roles

---

## Access Control

### RBAC Routes (`/api/rbac/*`)
**Required Permission:** `manage-roles`
**Access:**
- ✅ admin
- ✅ management-user
- ❌ management-file

### User Management Routes (`/api/rbac/users/*`)
**Required Permission:** `manage-users`
**Access:**
- ✅ admin
- ❌ management-user
- ❌ management-file

### File Routes (`/api/files/*`)
**Required Permission:** `manage-files`
**Access:**
- ✅ admin
- ❌ management-user
- ✅ management-file

---

## Validation Rules

### Role
- `name`: required, string, max:255, unique
- `permissions`: nullable, array
- `permissions.*`: exists in permissions table

### User
- `name`: required, string, max:255
- `email`: required, string, email, max:255, unique
- `password`: required (on create), nullable (on update), string, min:8, confirmed
- `roles`: nullable, array
- `roles.*`: exists in roles table

---

## Error Examples

### Invalid Permission ID
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "permissions.0": [
      "The selected permissions.0 is invalid."
    ]
  }
}
```

### Duplicate Role Name
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "Role name already exists."
    ]
  }
}
```

### Invalid Role ID
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "roles.0": [
      "The selected roles.0 is invalid."
    ]
  }
}
```

### Access Denied
```json
{
  "message": "Access denied. You do not have the required permission: manage-users"
}
```
