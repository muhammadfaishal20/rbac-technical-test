# API Examples - Quick Reference

Contoh data siap pakai untuk testing API endpoints.

## 🔐 Authentication

### Login
```json
POST /api/auth/login
{
  "email": "admin@example.com",
  "password": "password"
}
```

### Register
```json
POST /api/auth/register
{
  "name": "New User",
  "email": "newuser@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Note:** User yang register otomatis mendapat role `management-file`.

---

## 👥 Create User Examples

**Endpoint:** `POST /api/rbac/users`  
**Access:** Requires `manage-users` permission (admin only)

### User 1: Admin (Single Role)
```json
{
  "name": "Admin User",
  "email": "admin@example.com",
  "password": "Admin@123",
  "password_confirmation": "Admin@123",
  "roles": [1]
}
```

### User 2: Management User (Single Role)
```json
{
  "name": "Management User",
  "email": "management.user@example.com",
  "password": "Mgmt@123",
  "password_confirmation": "Mgmt@123",
  "roles": [2]
}
```

### User 3: Management File (Single Role)
```json
{
  "name": "Management File",
  "email": "management.file@example.com",
  "password": "File@123",
  "password_confirmation": "File@123",
  "roles": [3]
}
```

### User 4: Multiple Roles (Admin + Management User)
```json
{
  "name": "Multi Role User 1",
  "email": "multi1@example.com",
  "password": "Multi@123",
  "password_confirmation": "Multi@123",
  "roles": [1, 2]
}
```

### User 5: Multiple Roles (Admin + Management File)
```json
{
  "name": "Multi Role User 2",
  "email": "multi2@example.com",
  "password": "Multi@123",
  "password_confirmation": "Multi@123",
  "roles": [1, 3]
}
```

### User 6: Multiple Roles (Management User + Management File)
```json
{
  "name": "Multi Role User 3",
  "email": "multi3@example.com",
  "password": "Multi@123",
  "password_confirmation": "Multi@123",
  "roles": [2, 3]
}
```

### User 7: Multiple Roles (All Roles)
```json
{
  "name": "Multi Role User 4",
  "email": "multi4@example.com",
  "password": "Multi@123",
  "password_confirmation": "Multi@123",
  "roles": [1, 2, 3]
}
```

**Note:** Role IDs (1, 2, 3) harus disesuaikan dengan IDs yang ada di database. Cek dengan:
```
GET /api/rbac/users/roles
```

**Default Role IDs (After Seeder):**
- ID 1: `admin`
- ID 2: `management-user`
- ID 3: `management-file`

---

## 🎭 Create Role Examples

**Endpoint:** `POST /api/rbac/roles`  
**Access:** Requires `manage-roles` permission (admin, management-user)

### Role 1: Custom Role with manage-roles
```json
{
  "name": "role-manager",
  "permissions": [1]
}
```

### Role 2: Custom Role with manage-files
```json
{
  "name": "file-manager",
  "permissions": [3]
}
```

### Role 3: Custom Role with Multiple Permissions
```json
{
  "name": "super-manager",
  "permissions": [1, 2, 3]
}
```

### Role 4: Role without Permissions
```json
{
  "name": "guest"
}
```

**Note:** Permission IDs (1, 2, 3) harus disesuaikan dengan IDs yang ada di database. Cek dengan:
```
GET /api/rbac/roles/permissions
```

**Default Permission IDs (After Seeder):**
- ID 1: `manage-roles`
- ID 2: `manage-users`
- ID 3: `manage-files`

**Protected Roles:** Cannot create/update/delete:
- `admin`
- `management-user`
- `management-file`

---

## 🔑 Permission Examples (Via Tinker)

Permission biasanya dibuat via seeder, tapi jika ingin membuat manual:

```bash
php artisan tinker
```

```php
use Spatie\Permission\Models\Permission;

// Create permissions
Permission::create(['name' => 'manage-roles']);
Permission::create(['name' => 'manage-users']);
Permission::create(['name' => 'manage-files']);
```

---

## 📝 Update Examples

### Update Role (Assign Permissions)
**Endpoint:** `PUT /api/rbac/roles/{id}`  
**Access:** Requires `manage-roles` permission
```json
{
  "name": "custom-role",
  "permissions": [1, 3]
}
```

### Update User (Assign Roles)
**Endpoint:** `PUT /api/rbac/users/{id}`  
**Access:** Requires `manage-users` permission (admin only)
```json
{
  "name": "Updated Name",
  "email": "updated@example.com",
  "roles": [1, 2]
}
```

### Update User Password
**Endpoint:** `PUT /api/rbac/users/{id}`  
**Access:** Requires `manage-users` permission (admin only)
```json
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "NewPassword123",
  "password_confirmation": "NewPassword123"
}
```

---

## 🔍 Get Data Examples

### Get All Permissions
```
GET /api/rbac/roles/permissions
Headers: Authorization: Bearer {token}
```

### Get All Roles
```
GET /api/rbac/users/roles
Headers: Authorization: Bearer {token}
```

### Get All Users
```
GET /api/rbac/users
Headers: Authorization: Bearer {token}
Access: Requires manage-users permission (admin only)
```

### Get All Roles (for role management)
```
GET /api/rbac/roles
Headers: Authorization: Bearer {token}
Access: Requires manage-roles permission
```

### Get Single User
```
GET /api/rbac/users/{id}
Headers: Authorization: Bearer {token}
Access: Requires manage-users permission (admin only)
```

### Get Single Role
```
GET /api/rbac/roles/{id}
Headers: Authorization: Bearer {token}
Access: Requires manage-roles permission
```

---

## 📋 Complete Testing Workflow

### Step 1: Login
```json
POST /api/auth/login
{
  "email": "admin@example.com",
  "password": "password"
}
```
**Save the token from response!**

### Step 2: Get Available Permissions
```
GET /api/rbac/roles/permissions
Authorization: Bearer {token}
```

### Step 3: Create New Role
```json
POST /api/rbac/roles
Authorization: Bearer {token}
{
  "name": "custom-role",
  "permissions": [1, 3]
}
```

### Step 4: Get Available Roles
```
GET /api/rbac/users/roles
Authorization: Bearer {token}
```

### Step 5: Create New User
```json
POST /api/rbac/users
Authorization: Bearer {token}
{
  "name": "New User",
  "email": "newuser@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "roles": [4]
}
```

---

## 🧪 Postman Collection JSON

```json
{
  "info": {
    "name": "RBAC API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Login",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"email\": \"admin@example.com\",\n  \"password\": \"password\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/auth/login",
          "host": ["{{base_url}}"],
          "path": ["auth", "login"]
        }
      }
    },
    {
      "name": "Create Role",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          },
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"name\": \"custom-role\",\n  \"permissions\": [1, 3]\n}"
        },
        "url": {
          "raw": "{{base_url}}/rbac/roles",
          "host": ["{{base_url}}"],
          "path": ["rbac", "roles"]
        }
      }
    },
    {
      "name": "Create User",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          },
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"name\": \"John Doe\",\n  \"email\": \"john@example.com\",\n  \"password\": \"password123\",\n  \"password_confirmation\": \"password123\",\n  \"roles\": [1, 2]\n}"
        },
        "url": {
          "raw": "{{base_url}}/rbac/users",
          "host": ["{{base_url}}"],
          "path": ["rbac", "users"]
        }
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api"
    },
    {
      "key": "token",
      "value": ""
    }
  ]
}
```

---

## 🔐 Access Control Summary

### RBAC Routes (`/api/rbac/*`)
- **Role Routes:** Requires `manage-roles` permission
  - ✅ admin
  - ✅ management-user
  - ❌ management-file

- **User Routes:** Requires `manage-users` permission
  - ✅ admin
  - ❌ management-user
  - ❌ management-file

### File Routes (`/api/files/*`)
- Requires `manage-files` permission
  - ✅ admin
  - ❌ management-user
  - ✅ management-file

---

## 💡 Tips

1. **Always check IDs first**: Sebelum create user/role dengan permissions/roles, selalu cek IDs yang tersedia dengan GET endpoints.

2. **Use Postman/Insomnia**: Lebih mudah untuk testing dengan tools seperti Postman atau Insomnia.

3. **Save token**: Setelah login, simpan token untuk digunakan di request berikutnya.

4. **Check responses**: Perhatikan response untuk melihat struktur data yang dikembalikan.

5. **Error handling**: Perhatikan error messages untuk debugging.

6. **Access Control**: Pastikan user yang digunakan memiliki permission yang sesuai untuk mengakses endpoint.

---

## 🚨 Common Errors

### Invalid Permission ID
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "permissions.0": ["The selected permissions.0 is invalid."]
  }
}
```
**Solution:** Cek permission IDs dengan `GET /api/rbac/roles/permissions`

### Invalid Role ID
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "roles.0": ["The selected roles.0 is invalid."]
  }
}
```
**Solution:** Cek role IDs dengan `GET /api/rbac/users/roles`

### Duplicate Email
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```
**Solution:** Gunakan email yang berbeda

### Duplicate Role Name
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["Role name already exists."]
  }
}
```
**Solution:** Gunakan nama role yang berbeda

### Access Denied
```json
{
  "message": "Access denied. You do not have the required permission: manage-users"
}
```
**Solution:** Login dengan user yang memiliki permission yang sesuai (admin untuk manage-users)

### Cannot Delete Default Role
```json
{
  "success": false,
  "message": "Cannot delete default roles."
}
```
**Solution:** Default roles (admin, management-user, management-file) tidak bisa dihapus

---

## 📊 Default Data After Seeder

### Permissions
| ID | Name |
|----|------|
| 1 | manage-roles |
| 2 | manage-users |
| 3 | manage-files |

### Roles
| ID | Name | Permissions |
|----|------|-------------|
| 1 | admin | 1, 2, 3 |
| 2 | management-user | 1 |
| 3 | management-file | 3 |

### Users (From UserSeeder)
| Email | Password | Roles |
|-------|----------|-------|
| admin@example.com | password | admin |
| management.user@example.com | password | management-user |
| management.file@example.com | password | management-file |
| multi1@example.com | password | admin, management-user |
| multi2@example.com | password | admin, management-file |
| multi3@example.com | password | management-user, management-file |
| multi4@example.com | password | admin, management-user, management-file |
