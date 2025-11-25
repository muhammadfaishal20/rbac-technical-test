# Authentication API Documentation

Dokumentasi lengkap untuk fitur authentication menggunakan Laravel Sanctum.

## Setup

### 1. Run Migrations
Pastikan migration untuk `personal_access_tokens` sudah dijalankan:
```bash
php artisan migrate
```

### 2. CORS Configuration
CORS sudah dikonfigurasi untuk frontend terpisah. Pastikan di `.env`:
```env
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1:8000
```

## API Endpoints

### 1. Register
Mendaftarkan user baru.

**Endpoint:** `POST /api/auth/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response Success (201):**
```json
{
  "success": true,
  "message": "User registered successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": "2024-01-01T00:00:00.000000Z",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z",
      "roles": [
        {
          "id": 2,
          "name": "user",
          "guard_name": "web"
        }
      ],
      "permissions": [
        {
          "id": 1,
          "name": "view-dashboard",
          "guard_name": "web"
        }
      ]
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

**Response Error (422):**
```json
{
  "message": "The email has already been taken. (and 1 more error)",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password confirmation does not match."
    ]
  }
}
```

### 2. Login
Login user dan mendapatkan access token.

**Endpoint:** `POST /api/auth/login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": "2024-01-01T00:00:00.000000Z",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z",
      "roles": [
        {
          "id": 1,
          "name": "admin",
          "guard_name": "web"
        }
      ],
      "permissions": [
        {
          "id": 1,
          "name": "view-dashboard",
          "guard_name": "web"
        },
        {
          "id": 2,
          "name": "manage-roles",
          "guard_name": "web"
        },
        {
          "id": 3,
          "name": "manage-users",
          "guard_name": "web"
        }
      ]
    },
    "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

**Response Error (422):**
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": [
      "The provided credentials are incorrect."
    ]
  }
}
```

### 3. Get Authenticated User
Mendapatkan informasi user yang sedang login.

**Endpoint:** `GET /api/auth/me`

**Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": "2024-01-01T00:00:00.000000Z",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "roles": [...],
    "permissions": [...]
  }
}
```

**Response Error (401):**
```json
{
  "message": "Unauthenticated."
}
```

### 4. Logout
Logout dari device saat ini (revoke token yang digunakan).

**Endpoint:** `POST /api/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Logged out successfully."
}
```

### 5. Logout All Devices
Logout dari semua devices (revoke semua tokens).

**Endpoint:** `POST /api/auth/logout-all`

**Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Logged out from all devices successfully."
}
```

## Usage Examples

### JavaScript/Fetch API

#### Register
```javascript
const register = async (name, email, password) => {
  const response = await fetch('http://localhost:8000/api/auth/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      name,
      email,
      password,
      password_confirmation: password,
    }),
  });

  const data = await response.json();
  
  if (data.success) {
    // Save token to localStorage
    localStorage.setItem('token', data.data.token);
    return data.data.user;
  } else {
    throw new Error(data.message);
  }
};
```

#### Login
```javascript
const login = async (email, password) => {
  const response = await fetch('http://localhost:8000/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      email,
      password,
    }),
  });

  const data = await response.json();
  
  if (data.success) {
    // Save token to localStorage
    localStorage.setItem('token', data.data.token);
    return data.data.user;
  } else {
    throw new Error(data.message);
  }
};
```

#### Get Authenticated User
```javascript
const getMe = async () => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://localhost:8000/api/auth/me', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    },
  });

  const data = await response.json();
  
  if (data.success) {
    return data.data;
  } else {
    throw new Error(data.message);
  }
};
```

#### Logout
```javascript
const logout = async () => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://localhost:8000/api/auth/logout', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    },
  });

  const data = await response.json();
  
  if (data.success) {
    // Remove token from localStorage
    localStorage.removeItem('token');
    return true;
  } else {
    throw new Error(data.message);
  }
};
```

### Axios Example

```javascript
import axios from 'axios';

// Setup axios instance
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 errors (unauthorized)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      // Redirect to login
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Auth functions
export const authAPI = {
  register: (data) => api.post('/auth/register', data),
  login: (data) => api.post('/auth/login', data),
  me: () => api.get('/auth/me'),
  logout: () => api.post('/auth/logout'),
  logoutAll: () => api.post('/auth/logout-all'),
};
```

### Vue.js Example

```vue
<template>
  <div>
    <form @submit.prevent="handleLogin">
      <input v-model="email" type="email" placeholder="Email" />
      <input v-model="password" type="password" placeholder="Password" />
      <button type="submit">Login</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const email = ref('');
const password = ref('');

const handleLogin = async () => {
  try {
    const response = await axios.post('http://localhost:8000/api/auth/login', {
      email: email.value,
      password: password.value,
    });

    if (response.data.success) {
      localStorage.setItem('token', response.data.data.token);
      // Redirect or update state
      console.log('Logged in:', response.data.data.user);
    }
  } catch (error) {
    console.error('Login failed:', error.response?.data);
  }
};
</script>
```

## Validation Rules

### Register
- `name`: required, string, max:255
- `email`: required, string, email, max:255, unique
- `password`: required, string, min:8, confirmed

### Login
- `email`: required, string, email
- `password`: required, string

## Security Features

1. **Password Hashing**: Password di-hash menggunakan bcrypt
2. **Token-based Authentication**: Menggunakan Laravel Sanctum tokens
3. **CORS Protection**: CORS dikonfigurasi untuk frontend terpisah
4. **Token Revocation**: Token bisa di-revoke saat logout
5. **Multiple Devices**: User bisa login dari multiple devices
6. **Logout All**: User bisa logout dari semua devices sekaligus

## Default Role Assignment

Saat register, user baru otomatis mendapat role `user` dengan permission `view-dashboard`.

## Testing dengan Postman

1. **Register:**
   - Method: POST
   - URL: `http://localhost:8000/api/auth/register`
   - Body (raw JSON):
     ```json
     {
       "name": "Test User",
       "email": "test@example.com",
       "password": "password123",
       "password_confirmation": "password123"
     }
     ```

2. **Login:**
   - Method: POST
   - URL: `http://localhost:8000/api/auth/login`
   - Body (raw JSON):
     ```json
     {
       "email": "test@example.com",
       "password": "password123"
     }
     ```

3. **Get Me:**
   - Method: GET
   - URL: `http://localhost:8000/api/auth/me`
   - Headers:
     - `Authorization: Bearer {token}`

4. **Logout:**
   - Method: POST
   - URL: `http://localhost:8000/api/auth/logout`
   - Headers:
     - `Authorization: Bearer {token}`

## Notes

- Token tidak memiliki expiration (bisa diubah di `config/sanctum.php`)
- User yang register otomatis mendapat role `user`
- Password minimum 8 karakter
- Email harus unique
- Semua responses menggunakan format JSON dengan struktur `{ success, message, data }`

