# CORS & Sanctum Setup Guide

## Masalah: Tidak Bisa Mengakses Menu Setelah Login

Jika `laravel_session` dan `xsrf-token` sudah tersimpan di cookie tapi tidak bisa mengakses menu, kemungkinan masalahnya ada di konfigurasi CORS dan Sanctum.

## Solusi

### 1. Update `.env` Backend

Pastikan file `.env` di backend memiliki konfigurasi berikut:

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

# Sanctum Stateful Domains
# Tambahkan semua URL frontend yang akan digunakan
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,localhost:5173,127.0.0.1:3000,127.0.0.1:5173,127.0.0.1:8000,::1
```

**Catatan:**
- Ganti `localhost:5173` dengan port frontend Anda (bisa 3000, 5173, atau port lainnya)
- Jika frontend dan backend di domain yang sama, tambahkan domain tersebut

### 2. Update `.env` Frontend

Pastikan file `.env` di frontend memiliki:

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

### 3. Clear Cache Backend

Setelah update `.env`, jalankan:

```bash
cd backend
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 4. Restart Server

Restart Laravel server:

```bash
php artisan serve
```

### 5. Verifikasi CORS

File `config/cors.php` sudah diupdate untuk:
- Allow specific origins (tidak menggunakan `*`)
- Support credentials
- Include frontend URL dari env

### 6. Test Authentication Flow

1. Buka browser DevTools (F12)
2. Buka tab **Application** > **Cookies**
3. Login melalui frontend
4. Pastikan cookies berikut ada:
   - `laravel_session`
   - `XSRF-TOKEN`
   - `auth_token` (di localStorage, bukan cookie)

5. Setelah login, cek Network tab:
   - Request ke `/api/auth/me` harus return 200 dengan user data
   - Request harus include `Authorization: Bearer {token}` header
   - Request harus include cookies (credentials: include)

### 7. Troubleshooting

#### Masalah: 401 Unauthorized pada `/api/auth/me`

**Solusi:**
- Pastikan token ada di localStorage
- Pastikan token dikirim di header `Authorization: Bearer {token}`
- Pastikan cookies dikirim (credentials: include)
- Cek apakah token masih valid

#### Masalah: CORS Error

**Solusi:**
- Pastikan frontend URL ada di `SANCTUM_STATEFUL_DOMAINS`
- Pastikan frontend URL ada di `allowed_origins` di `config/cors.php`
- Pastikan `supports_credentials => true` di `config/cors.php`
- Clear cache: `php artisan config:clear`

#### Masalah: CSRF Token Mismatch

**Solusi:**
- Pastikan `getCsrfCookie()` dipanggil sebelum login
- Pastikan cookies dikirim dengan `credentials: 'include'`
- Pastikan frontend URL ada di `SANCTUM_STATEFUL_DOMAINS`

#### Masalah: Router Guard Redirect ke Login

**Solusi:**
- Cek apakah token ada di localStorage
- Cek apakah `authStore.fetchUser()` berhasil
- Cek console untuk error messages
- Pastikan response dari `/api/auth/me` memiliki struktur `{ success: true, data: {...} }`

### 8. Debug Steps

1. **Cek Token:**
```javascript
// Di browser console
localStorage.getItem('auth_token')
```

2. **Cek User Data:**
```javascript
// Di browser console
localStorage.getItem('auth_user')
```

3. **Test API Call:**
```javascript
// Di browser console
fetch('http://localhost:8000/api/auth/me', {
    headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
        'Accept': 'application/json'
    },
    credentials: 'include'
}).then(r => r.json()).then(console.log)
```

4. **Cek Cookies:**
- Buka DevTools > Application > Cookies
- Pastikan `laravel_session` dan `XSRF-TOKEN` ada
- Pastikan cookies tidak expired

## Konfigurasi yang Sudah Diperbaiki

1. ✅ CORS config - allowed_origins sekarang spesifik (tidak menggunakan `*`)
2. ✅ Router guard - improved error handling dan user fetch
3. ✅ AuthStore - improved fetchUser dengan better error handling
4. ✅ HTTP client - sudah menggunakan `credentials: 'include'`

## Catatan Penting

- **Jangan gunakan `allowed_origins: ['*']` jika `supports_credentials: true`**
- **Frontend URL harus ada di `SANCTUM_STATEFUL_DOMAINS`**
- **Pastikan frontend dan backend menggunakan protocol yang sama (http atau https)**
- **Jika menggunakan HTTPS, pastikan semua URL menggunakan HTTPS**

