# Technical Test - Laravel & Vue.js Application

Aplikasi full-stack dengan Laravel 10 backend dan Vue.js 3 frontend, dilengkapi dengan Role-Based Access Control (RBAC) dan File Upload Management.

## 📋 Requirements

### Backend
- PHP >= 8.1
- Composer
- MySQL >= 5.7 atau MariaDB >= 10.3
- Node.js & NPM (untuk asset compilation)

### Frontend
- Node.js >= 18.x
- NPM atau Yarn

## 🚀 Quick Start

### 1. Clone Repository

```bash
git clone <repository-url>
cd technical-test
```

### 2. Backend Setup (Laravel)

#### 2.1 Install Dependencies

```bash
cd backend
composer install
```

**Penjelasan:** Menginstall semua package PHP yang diperlukan (Laravel, Sanctum, Spatie Permission, dll).

#### 2.2 Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

**Penjelasan:** 
- Copy file `.env.example` menjadi `.env` untuk konfigurasi environment
- Generate application key untuk enkripsi Laravel

#### 2.3 Database Configuration

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=technical_test
DB_USERNAME=root
DB_PASSWORD=
```

**Penjelasan:** Sesuaikan dengan konfigurasi MySQL Anda.

#### 2.4 Run Migrations & Seeders

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=UserSeeder
```

**Penjelasan:**
- `migrate`: Membuat semua tabel database (users, roles, permissions, files, dll)
- `RolePermissionSeeder`: Membuat roles (admin, management-user, management-file) dan permissions
- `UserSeeder`: Membuat user contoh untuk testing

#### 2.5 Create Storage Link

```bash
php artisan storage:link
```

**Penjelasan:** Membuat symbolic link dari `storage/app/public` ke `public/storage` agar file upload bisa diakses via URL.

#### 2.6 Start Development Server

```bash
php artisan serve
```

**Penjelasan:** Menjalankan Laravel development server di `http://localhost:8000`

### 3. Frontend Setup (Vue.js)

#### 3.1 Install Dependencies

```bash
cd ../frontend
npm install
```

**Penjelasan:** Menginstall semua package JavaScript yang diperlukan (Vue, PrimeVue, Vue Router, dll).

#### 3.2 Environment Configuration

Edit file `.env` atau `.env.local` dan sesuaikan API URL:

```env
VITE_API_URL=http://localhost:8000/api
```

**Penjelasan:** URL backend API yang akan digunakan oleh frontend.

#### 3.3 Start Development Server

```bash
npm run dev
```

**Penjelasan:** Menjalankan Vite development server di `http://localhost:5173`

## 🔐 Default Login Credentials

Setelah menjalankan seeder, Anda bisa login dengan:

### Admin User
- **Email:** admin@example.com
- **Password:** password
- **Role:** admin (memiliki semua permissions)

### Management User
- **Email:** management.user@example.com
- **Password:** password
- **Role:** management-user (manage roles & permissions)

### Management File
- **Email:** management.file@example.com
- **Password:** password
- **Role:** management-file (manage files)

## 📁 Project Structure

```
technical-test/
├── backend/              # Laravel 10 Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── RBAC/      # Role & Permission controllers
│   │   │   │   └── File/      # File upload controllers
│   │   │   └── Requests/      # Form Request validations
│   │   └── Models/
│   ├── database/
│   │   ├── migrations/        # Database migrations
│   │   └── seeders/           # Database seeders
│   └── routes/
│       └── api.php            # API routes
│
└── frontend/            # Vue.js 3 Frontend
    ├── src/
    │   ├── components/        # Vue components
    │   ├── views/             # Page views
    │   ├── services/          # API services
    │   ├── store/             # State management
    │   └── router/            # Vue Router
    └── package.json
```

## 🛠️ Available Commands

### Backend

```bash
# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=UserSeeder

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Generate storage link
php artisan storage:link
```

### Frontend

```bash
# Development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint
```

## 🔧 Configuration

### Backend Configuration

#### File Upload Settings

Edit `backend/.env` untuk mengatur ukuran maksimal file upload:

```env
# PHP Configuration (edit php.ini)
# upload_max_filesize = 100M
# post_max_size = 200M
```

**Note:** Untuk upload file besar, pastikan juga konfigurasi web server (Nginx/Apache) sudah diset dengan benar. Lihat dokumentasi di `backend/app/Http/Controllers/File/FileController.php` method `getUploadConfig()` untuk detail.

#### CORS Configuration

File `backend/config/cors.php` sudah dikonfigurasi untuk frontend di `http://localhost:5173`.

### Frontend Configuration

#### API URL

Edit `frontend/.env` atau `frontend/.env.local`:

```env
VITE_API_URL=http://localhost:8000/api
```

## 📚 Features

- ✅ **Authentication** - Login/Logout dengan Laravel Sanctum
- ✅ **RBAC** - Role-Based Access Control dengan Spatie Laravel Permission
- ✅ **User Management** - CRUD users dengan role assignment
- ✅ **Role Management** - CRUD roles dengan permission assignment
- ✅ **File Upload** - Multiple file upload (JPG, PNG, MP4) dengan preview
- ✅ **File Management** - List, download, delete files dengan pagination

## 🐛 Troubleshooting

### Backend Issues

**Error: SQLSTATE[HY000] [1045] Access denied**
- Pastikan konfigurasi database di `.env` sudah benar
- Pastikan MySQL service sudah running

**Error: 500 Internal Server Error**
- Clear cache: `php artisan cache:clear && php artisan config:clear`
- Check log: `tail -f storage/logs/laravel.log`

**Error: Storage link not found**
- Run: `php artisan storage:link`

### Frontend Issues

**Error: Network Error atau CORS**
- Pastikan backend sudah running di `http://localhost:8000`
- Check konfigurasi CORS di `backend/config/cors.php`

**Error: 401 Unauthorized**
- Pastikan sudah login
- Check token di localStorage browser

### File Upload Issues

**Error: 413 Content Too Large**
- Pastikan konfigurasi PHP (`upload_max_filesize`, `post_max_size`) sudah benar
- Pastikan konfigurasi web server (Nginx `client_max_body_size` atau Apache `LimitRequestBody`) sudah benar
- Akses endpoint `/api/files/config/upload` untuk melihat konfigurasi saat ini

## 📝 API Documentation

### Authentication
- `POST /api/auth/register` - Register user
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user

### RBAC
- `GET /api/rbac/roles` - List roles
- `POST /api/rbac/roles` - Create role
- `PUT /api/rbac/roles/{id}` - Update role
- `DELETE /api/rbac/roles/{id}` - Delete role
- `GET /api/rbac/users` - List users
- `POST /api/rbac/users` - Create user
- `PUT /api/rbac/users/{id}` - Update user
- `DELETE /api/rbac/users/{id}` - Delete user

### Files
- `GET /api/files` - List files
- `POST /api/files/upload` - Upload files
- `GET /api/files/{id}` - Get file details
- `GET /api/files/{id}/download` - Download file
- `DELETE /api/files/{id}` - Delete file

## 📄 License

MIT License

## 👤 Author

Technical Test Project

