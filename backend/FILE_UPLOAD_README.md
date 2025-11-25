# File Upload Module Documentation

Dokumentasi lengkap untuk modul upload file dengan multiple file support.

## Features

- ✅ Multiple file upload
- ✅ Validasi: max 100 MB per file
- ✅ Validasi: extension (jpg, jpeg, png, mp4)
- ✅ Simpan ke `storage/app/public/uploads`
- ✅ Simpan metadata: nama file, path, mime, size, user_id
- ✅ Role-based access control
- ✅ List files dengan pagination
- ✅ Download files
- ✅ Delete files

## Setup

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Create Storage Link
```bash
php artisan storage:link
```
Ini akan membuat symbolic link dari `storage/app/public` ke `public/storage` sehingga file bisa diakses via URL.

### 3. Seed Roles and Permissions
```bash
php artisan db:seed --class=RolePermissionSeeder
```

**Roles & Permissions:**
- **admin**: manage-files, manage-roles, manage-users, view-dashboard
- **user**: manage-files

## API Endpoints

### 1. Upload Files (Multiple)
**Endpoint:** `POST /api/files/upload`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request:**
```
files[]: [file1, file2, file3, ...]
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/files/upload \
  -H "Authorization: Bearer {token}" \
  -F "files[]=@/path/to/image1.jpg" \
  -F "files[]=@/path/to/image2.png" \
  -F "files[]=@/path/to/video.mp4"
```

**Response Success (201):**
```json
{
  "success": true,
  "message": "3 file(s) uploaded successfully.",
  "data": [
    {
      "id": 1,
      "name": "image1.jpg",
      "path": "uploads/1234567890_abc123.jpg",
      "mime": "image/jpeg",
      "size": 1024000,
      "formatted_size": "1000 KB",
      "user_id": 1,
      "url": "http://localhost:8000/storage/uploads/1234567890_abc123.jpg",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    },
    ...
  ],
  "errors": null
}
```

**Response Error (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "files.0": [
      "Each file must not be larger than 100 MB."
    ],
    "files.1": [
      "Each file must be one of: jpg, jpeg, png, mp4."
    ]
  }
}
```

### 2. List Files
**Endpoint:** `GET /api/files`

**Query Parameters:**
- `search`: string (optional) - Search by file name
- `mime`: string (optional) - Filter by MIME type (e.g., "image/jpeg", "video/mp4")
- `per_page`: integer (optional, default: 15) - Items per page

**Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "image1.jpg",
        "path": "uploads/1234567890_abc123.jpg",
        "mime": "image/jpeg",
        "size": 1024000,
        "formatted_size": "1000 KB",
        "user_id": 1,
        "url": "http://localhost:8000/storage/uploads/1234567890_abc123.jpg",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        }
      }
    ],
    "total": 10,
    "per_page": 15,
    "last_page": 1
  }
}
```

**Note:**
- Regular users can only see their own files
- Admin users can see all files

### 3. Get File Details
**Endpoint:** `GET /api/files/{id}`

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
    "name": "image1.jpg",
    "path": "uploads/1234567890_abc123.jpg",
    "mime": "image/jpeg",
    "size": 1024000,
    "formatted_size": "1000 KB",
    "user_id": 1,
    "url": "http://localhost:8000/storage/uploads/1234567890_abc123.jpg",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Response Error (403):**
```json
{
  "success": false,
  "message": "Access denied."
}
```

### 4. Download File
**Endpoint:** `GET /api/files/{id}/download`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** File download

**Response Error (403):**
```json
{
  "success": false,
  "message": "Access denied."
}
```

**Response Error (404):**
```json
{
  "success": false,
  "message": "File not found."
}
```

### 5. Delete File
**Endpoint:** `DELETE /api/files/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "File deleted successfully."
}
```

**Response Error (403):**
```json
{
  "success": false,
  "message": "Access denied."
}
```

## Validation Rules

### Upload Files
- `files`: required, array, min:1
- `files.*`: required, file, max:102400 KB (100 MB), mimes:jpg,jpeg,png,mp4

## Access Control

### Role-Based Access
- **Admin**: Can access all files (upload, view, download, delete)
- **User**: Can only access their own files (upload, view, download, delete)

### Permission Required
- All file endpoints require `manage-files` permission
- Middleware: `permission:manage-files`

## File Storage

### Storage Location
Files are stored in: `storage/app/public/uploads/`

### File Naming
Files are renamed to prevent conflicts:
- Format: `{timestamp}_{unique_id}.{extension}`
- Example: `1234567890_abc123def456.jpg`

### Access URL
Files can be accessed via:
```
http://localhost:8000/storage/uploads/{filename}
```

## Usage Examples

### JavaScript/Fetch API

#### Upload Multiple Files
```javascript
const uploadFiles = async (files) => {
  const token = localStorage.getItem('token');
  const formData = new FormData();
  
  // Add files to FormData
  Array.from(files).forEach(file => {
    formData.append('files[]', file);
  });
  
  const response = await fetch('http://localhost:8000/api/files/upload', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
    body: formData,
  });
  
  return await response.json();
};

// Usage
const fileInput = document.querySelector('input[type="file"]');
fileInput.addEventListener('change', async (e) => {
  const result = await uploadFiles(e.target.files);
  console.log(result);
});
```

#### List Files
```javascript
const listFiles = async (search = '', mime = '') => {
  const token = localStorage.getItem('token');
  const params = new URLSearchParams();
  
  if (search) params.append('search', search);
  if (mime) params.append('mime', mime);
  
  const response = await fetch(`http://localhost:8000/api/files?${params}`, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  return await response.json();
};
```

#### Download File
```javascript
const downloadFile = async (fileId) => {
  const token = localStorage.getItem('token');
  
  const response = await fetch(`http://localhost:8000/api/files/${fileId}/download`, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  if (response.ok) {
    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'filename';
    a.click();
  }
};
```

#### Delete File
```javascript
const deleteFile = async (fileId) => {
  const token = localStorage.getItem('token');
  
  const response = await fetch(`http://localhost:8000/api/files/${fileId}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  return await response.json();
};
```

### Vue.js Example

```vue
<template>
  <div>
    <input 
      type="file" 
      multiple 
      accept="image/jpeg,image/png,video/mp4"
      @change="handleFileUpload"
    />
    <button @click="uploadFiles" :disabled="uploading">
      {{ uploading ? 'Uploading...' : 'Upload Files' }}
    </button>
    
    <div v-for="file in files" :key="file.id">
      <img v-if="file.mime.startsWith('image/')" :src="file.url" :alt="file.name" />
      <video v-else-if="file.mime.startsWith('video/')" :src="file.url" controls></video>
      <p>{{ file.name }} - {{ file.formatted_size }}</p>
      <button @click="downloadFile(file.id)">Download</button>
      <button @click="deleteFile(file.id)">Delete</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const files = ref([]);
const selectedFiles = ref([]);
const uploading = ref(false);

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`,
  },
});

const handleFileUpload = (event) => {
  selectedFiles.value = Array.from(event.target.files);
};

const uploadFiles = async () => {
  if (selectedFiles.value.length === 0) return;
  
  uploading.value = true;
  const formData = new FormData();
  
  selectedFiles.value.forEach(file => {
    formData.append('files[]', file);
  });
  
  try {
    const response = await api.post('/files/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    
    if (response.data.success) {
      files.value.push(...response.data.data);
      selectedFiles.value = [];
    }
  } catch (error) {
    console.error('Upload failed:', error.response?.data);
  } finally {
    uploading.value = false;
  }
};

const downloadFile = async (fileId) => {
  try {
    const response = await api.get(`/files/${fileId}/download`, {
      responseType: 'blob',
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'file');
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Download failed:', error);
  }
};

const deleteFile = async (fileId) => {
  try {
    await api.delete(`/files/${fileId}`);
    files.value = files.value.filter(f => f.id !== fileId);
  } catch (error) {
    console.error('Delete failed:', error);
  }
};

// Load files on mount
api.get('/files').then(response => {
  files.value = response.data.data.data;
});
</script>
```

## Database Schema

### files table
```sql
- id (bigint, primary key)
- name (string) - Original file name
- path (string) - Storage path
- mime (string) - MIME type
- size (bigint) - File size in bytes
- user_id (bigint, foreign key to users.id)
- created_at (timestamp)
- updated_at (timestamp)
```

## Notes

1. **Storage Link**: Pastikan sudah menjalankan `php artisan storage:link` agar file bisa diakses via URL
2. **File Size**: Maximum 100 MB per file
3. **File Types**: Hanya jpg, jpeg, png, mp4 yang diizinkan
4. **Access Control**: User hanya bisa melihat/mengakses file mereka sendiri, kecuali admin
5. **File Naming**: File otomatis di-rename untuk mencegah konflik nama
6. **Multiple Upload**: Bisa upload multiple files sekaligus dalam satu request

## Troubleshooting

### File tidak bisa diakses via URL
**Solution:** Pastikan sudah menjalankan `php artisan storage:link`

### Upload gagal dengan error "The files field is required"
**Solution:** Pastikan menggunakan `files[]` (array) sebagai field name

### File terlalu besar
**Solution:** Pastikan file tidak melebihi 100 MB

### File type tidak didukung
**Solution:** Hanya jpg, jpeg, png, mp4 yang diizinkan

