# Penjelasan Alur RBAC dengan Spatie Laravel Permission

## Konsep Dasar

Spatie Laravel Permission menggunakan **Polymorphic Many-to-Many Relationship** untuk menghubungkan users dengan roles dan permissions. Ini berbeda dengan pendekatan tradisional yang menggunakan `role_id` langsung di table `users`.

## Mengapa Tidak Ada `role_id` di Table Users?

### Pendekatan Tradisional (Tidak Digunakan)
```
users table:
- id
- name
- email
- role_id  ❌ (hanya bisa 1 role per user)
```

**Masalah:**
- User hanya bisa punya 1 role
- Tidak fleksibel untuk multiple roles
- Sulit untuk assign permissions langsung ke user

### Pendekatan Spatie (Yang Digunakan)
```
users table:
- id
- name
- email
✅ Tidak ada role_id

model_has_roles table (pivot table):
- role_id
- model_type (contoh: "App\Models\User")
- model_id (user_id)
```

**Keuntungan:**
- User bisa punya **multiple roles**
- User bisa punya **permissions langsung** (tanpa melalui role)
- Fleksibel dan scalable

## Struktur Database

### 1. Table `permissions`
Menyimpan daftar semua permissions yang tersedia.

```sql
permissions:
- id
- name (contoh: "view-dashboard", "manage-users")
- guard_name (default: "web")
- timestamps
```

**Contoh Data:**
```
id | name            | guard_name
1  | view-dashboard  | web
2  | manage-roles    | web
3  | manage-users    | web
```

### 2. Table `roles`
Menyimpan daftar semua roles yang tersedia.

```sql
roles:
- id
- name (contoh: "admin", "user")
- guard_name (default: "web")
- timestamps
```

**Contoh Data:**
```
id | name  | guard_name
1  | admin | web
2  | user  | web
```

### 3. Table `role_has_permissions` (Pivot)
Menghubungkan roles dengan permissions (Many-to-Many).

```sql
role_has_permissions:
- permission_id (FK ke permissions.id)
- role_id (FK ke roles.id)
```

**Contoh Data:**
```
permission_id | role_id
1             | 1      (admin punya view-dashboard)
2             | 1      (admin punya manage-roles)
3             | 1      (admin punya manage-users)
1             | 2      (user punya view-dashboard)
```

**Penjelasan:**
- Role "admin" memiliki semua permissions (1, 2, 3)
- Role "user" hanya memiliki permission "view-dashboard" (1)

### 4. Table `model_has_roles` (Polymorphic Pivot)
Menghubungkan users (atau model lain) dengan roles.

```sql
model_has_roles:
- role_id (FK ke roles.id)
- model_type (contoh: "App\Models\User")
- model_id (FK ke users.id)
```

**Contoh Data:**
```
role_id | model_type        | model_id
1       | App\Models\User   | 1      (User ID 1 punya role admin)
2       | App\Models\User   | 2      (User ID 2 punya role user)
1       | App\Models\User   | 3      (User ID 3 punya role admin)
2       | App\Models\User   | 3      (User ID 3 punya role user juga!)
```

**Penjelasan:**
- User ID 1: hanya role "admin"
- User ID 2: hanya role "user"
- User ID 3: punya **multiple roles** (admin + user)

### 5. Table `model_has_permissions` (Polymorphic Pivot)
Menghubungkan users (atau model lain) dengan permissions secara langsung (tanpa melalui role).

```sql
model_has_permissions:
- permission_id (FK ke permissions.id)
- model_type (contoh: "App\Models\User")
- model_id (FK ke users.id)
```

**Contoh Data:**
```
permission_id | model_type        | model_id
1             | App\Models\User   | 4      (User ID 4 punya permission view-dashboard langsung)
```

**Penjelasan:**
- User ID 4 punya permission "view-dashboard" **langsung**, tanpa melalui role
- Berguna untuk kasus khusus di mana user perlu permission spesifik tanpa role tertentu

## Alur Kerja (Flow)

### 1. Setup Awal (Seeder)

```php
// 1. Create Permissions
Permission::create(['name' => 'view-dashboard']);
Permission::create(['name' => 'manage-roles']);
Permission::create(['name' => 'manage-users']);

// 2. Create Roles
$adminRole = Role::create(['name' => 'admin']);
$userRole = Role::create(['name' => 'user']);

// 3. Assign Permissions to Roles
$adminRole->givePermissionTo(['view-dashboard', 'manage-roles', 'manage-users']);
$userRole->givePermissionTo(['view-dashboard']);
```

**Hasil di Database:**
- Table `permissions`: 3 records
- Table `roles`: 2 records
- Table `role_has_permissions`: 4 records (admin: 3 permissions, user: 1 permission)

### 2. Assign Role ke User

```php
$user = User::find(1);
$user->assignRole('admin');
```

**Yang Terjadi:**
- Insert ke table `model_has_roles`:
  ```
  role_id: 1 (admin)
  model_type: "App\Models\User"
  model_id: 1
  ```

### 3. Assign Multiple Roles ke User

```php
$user = User::find(3);
$user->assignRole(['admin', 'user']);
// atau
$user->syncRoles(['admin', 'user']);
```

**Yang Terjadi:**
- Insert ke table `model_has_roles`:
  ```
  role_id: 1, model_type: "App\Models\User", model_id: 3
  role_id: 2, model_type: "App\Models\User", model_id: 3
  ```

### 4. Check Permission User

```php
$user = User::find(1);
$user->hasPermissionTo('view-dashboard'); // true
$user->can('view-dashboard'); // true
```

**Alur Pengecekan:**
1. Cek apakah user punya permission **langsung** di `model_has_permissions`
2. Jika tidak, cek apakah user punya role yang memiliki permission tersebut:
   - Ambil semua roles user dari `model_has_roles`
   - Cek di `role_has_permissions` apakah role tersebut punya permission
3. Return true jika ditemukan, false jika tidak

**Contoh:**
- User ID 1 punya role "admin"
- Role "admin" punya permission "view-dashboard" (di `role_has_permissions`)
- Jadi `$user->can('view-dashboard')` = **true**

### 5. Middleware Check

```php
Route::middleware(['auth:sanctum', 'permission:view-dashboard'])->get('/dashboard', ...);
```

**Alur:**
1. Middleware `auth:sanctum` memastikan user sudah login
2. Middleware `permission:view-dashboard`:
   - Ambil user yang sedang login
   - Panggil `$user->can('view-dashboard')`
   - Jika false, return 403 Forbidden
   - Jika true, lanjutkan ke controller

## Diagram Relasi

```
┌─────────────┐
│   users     │
│─────────────│
│ id          │
│ name        │
│ email       │
└──────┬──────┘
       │
       │ (polymorphic many-to-many)
       │
       ▼
┌─────────────────────┐         ┌─────────────┐
│  model_has_roles    │────────▶│   roles     │
│─────────────────────│         │─────────────│
│ role_id             │         │ id          │
│ model_type          │         │ name        │
│ model_id (user_id)  │         └──────┬──────┘
└─────────────────────┘                │
                                       │ (many-to-many)
                                       │
                                       ▼
                              ┌──────────────────────┐         ┌──────────────┐
                              │ role_has_permissions │────────▶│ permissions  │
                              │──────────────────────│         │──────────────│
                              │ role_id              │         │ id           │
                              │ permission_id        │         │ name         │
                              └──────────────────────┘         └──────────────┘
                                       ▲
                                       │
                                       │ (polymorphic many-to-many - optional)
                                       │
┌─────────────────────┐                │
│model_has_permissions│────────────────┘
│─────────────────────│
│ permission_id       │
│ model_type          │
│ model_id (user_id)  │
└─────────────────────┘
```

## Contoh Skenario

### Skenario 1: User dengan 1 Role
```
User ID: 1
Name: John Doe
Email: john@example.com

Roles: admin
  └─ Permissions: view-dashboard, manage-roles, manage-users

Check: $user->can('view-dashboard') → true
Check: $user->can('manage-users') → true
Check: $user->can('delete-everything') → false
```

### Skenario 2: User dengan Multiple Roles
```
User ID: 3
Name: Jane Doe
Email: jane@example.com

Roles: admin, user
  └─ admin permissions: view-dashboard, manage-roles, manage-users
  └─ user permissions: view-dashboard

Check: $user->can('view-dashboard') → true (dari admin atau user)
Check: $user->can('manage-users') → true (dari admin)
```

### Skenario 3: User dengan Permission Langsung
```
User ID: 4
Name: Bob Smith
Email: bob@example.com

Roles: (tidak ada)

Direct Permissions: view-dashboard

Check: $user->can('view-dashboard') → true (dari direct permission)
Check: $user->can('manage-users') → false
```

## Keuntungan Pendekatan Ini

1. **Multiple Roles**: User bisa punya lebih dari 1 role
2. **Direct Permissions**: User bisa punya permission tanpa role
3. **Fleksibel**: Mudah menambah/mengurangi roles dan permissions
4. **Polymorphic**: Bisa digunakan untuk model lain (bukan hanya User)
5. **Scalable**: Tidak perlu mengubah struktur table users

## Cara Menggunakan di Code

### Assign Role
```php
$user->assignRole('admin');
$user->assignRole(['admin', 'user']); // multiple roles
```

### Remove Role
```php
$user->removeRole('admin');
```

### Sync Roles (Replace semua roles)
```php
$user->syncRoles(['admin', 'user']); // hanya punya 2 roles ini
```

### Assign Permission Langsung
```php
$user->givePermissionTo('view-dashboard');
```

### Check Permission
```php
$user->can('view-dashboard');
$user->hasPermissionTo('view-dashboard');
$user->hasAnyPermission(['view-dashboard', 'manage-users']);
$user->hasAllPermissions(['view-dashboard', 'manage-users']);
```

### Check Role
```php
$user->hasRole('admin');
$user->hasAnyRole(['admin', 'user']);
$user->hasAllRoles(['admin', 'user']);
```

## Kesimpulan

Spatie Laravel Permission menggunakan **polymorphic many-to-many relationship** yang memungkinkan:
- User punya **multiple roles**
- User punya **permissions langsung** (tanpa role)
- Struktur database yang **fleksibel dan scalable**
- Tidak perlu `role_id` di table users

Ini adalah best practice untuk sistem RBAC yang kompleks dan fleksibel.

