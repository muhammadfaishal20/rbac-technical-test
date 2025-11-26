/**
 * HTTP Client using native fetch API
 * Base configuration for API calls
 */

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

/**
 * Get authentication token from localStorage
 */
const getToken = () => {
    return localStorage.getItem('auth_token');
};

/**
 * Get CSRF cookie (for Sanctum)
 */
const getCsrfCookie = async () => {
    try {
        await fetch(`${API_BASE_URL.replace('/api', '')}/sanctum/csrf-cookie`, {
            method: 'GET',
            credentials: 'include',
        });
    } catch (error) {
        console.error('Failed to get CSRF cookie:', error);
    }
};

/**
 * HTTP Client class using native fetch
 */
class HttpClient {
    constructor(baseURL = API_BASE_URL) {
        this.baseURL = baseURL;
    }

    /**
     * Build full URL
     */
    buildURL(endpoint) {
        if (endpoint.startsWith('http')) {
            return endpoint;
        }
        return `${this.baseURL}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`;
    }

    /**
     * Build headers
     */
    async buildHeaders(customHeaders = {}) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...customHeaders,
        };

        const token = getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        return headers;
    }

    /**
     * Handle response
     */
    async handleResponse(response, options = {}) {
        // Handle blob response
        if (options.responseType === 'blob') {
            if (!response.ok) {
                // Try to parse error as JSON if possible
                try {
                    const errorData = await response.json();
                    throw {
                        message: errorData.message || errorData.error || 'An error occurred',
                        status: response.status,
                        data: errorData,
                    };
                } catch (e) {
                    // If not JSON, throw generic error
                    if (e.status) throw e;
                    throw {
                        message: 'An error occurred',
                        status: response.status,
                        data: null,
                    };
                }
            }
            return response;
        }

        const contentType = response.headers.get('content-type');
        const isJson = contentType && contentType.includes('application/json');

        let data;
        if (isJson) {
            data = await response.json();
        } else {
            data = await response.text();
        }

        if (!response.ok) {
            const error = {
                message: data.message || data.error || 'An error occurred',
                status: response.status,
                data: data,
            };

            // Handle 401 Unauthorized - clear token
            // Don't redirect here, let router guard handle it
            if (response.status === 401) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
            }

            throw error;
        }

        return data;
    }

    /**
     * GET request
     */
    async get(endpoint, options = {}) {
        const url = this.buildURL(endpoint);
        const headers = await this.buildHeaders(options.headers);

        const response = await fetch(url, {
            method: 'GET',
            headers,
            credentials: 'include',
            redirect: 'manual', // Prevent automatic redirect
            ...options,
        });

        // Handle redirect manually
        if (response.type === 'opaqueredirect' || response.status === 0) {
            throw {
                message: 'Request was redirected. Please check authentication.',
                status: 401,
                data: null,
            };
        }

        return this.handleResponse(response, options);
    }

    /**
     * POST request
     */
    async post(endpoint, data = null, options = {}) {
        const url = this.buildURL(endpoint);
        const headers = await this.buildHeaders(options.headers);

        // Remove Content-Type for FormData (let browser set it with boundary)
        if (data instanceof FormData) {
            delete headers['Content-Type'];
        }

        const response = await fetch(url, {
            method: 'POST',
            headers,
            credentials: 'include',
            redirect: 'manual', // Prevent automatic redirect
            body: data instanceof FormData ? data : JSON.stringify(data),
            ...options,
        });

        // Handle redirect manually (status 301, 302, 303, 307, 308)
        if (response.status >= 300 && response.status < 400) {
            const location = response.headers.get('Location');
            // If redirecting to login, it means authentication failed
            if (location && location.includes('/login')) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
                throw {
                    message: 'Authentication required. Please login again.',
                    status: 401,
                    data: null,
                };
            }
            throw {
                message: 'Request was redirected. Please check authentication.',
                status: 401,
                data: null,
            };
        }

        return this.handleResponse(response);
    }

    /**
     * PUT request
     */
    async put(endpoint, data = null, options = {}) {
        const url = this.buildURL(endpoint);
        const headers = await this.buildHeaders(options.headers);

        // Remove Content-Type for FormData
        if (data instanceof FormData) {
            delete headers['Content-Type'];
        }

        const response = await fetch(url, {
            method: 'PUT',
            headers,
            credentials: 'include',
            body: data instanceof FormData ? data : JSON.stringify(data),
            ...options,
        });

        return this.handleResponse(response);
    }

    /**
     * PATCH request
     */
    async patch(endpoint, data = null, options = {}) {
        const url = this.buildURL(endpoint);
        const headers = await this.buildHeaders(options.headers);

        // Remove Content-Type for FormData
        if (data instanceof FormData) {
            delete headers['Content-Type'];
        }

        const response = await fetch(url, {
            method: 'PATCH',
            headers,
            credentials: 'include',
            body: data instanceof FormData ? data : JSON.stringify(data),
            ...options,
        });

        return this.handleResponse(response);
    }

    /**
     * DELETE request
     */
    async delete(endpoint, options = {}) {
        const url = this.buildURL(endpoint);
        const headers = await this.buildHeaders(options.headers);

        const response = await fetch(url, {
            method: 'DELETE',
            headers,
            credentials: 'include',
            ...options,
        });

        return this.handleResponse(response);
    }
}

// Create and export default instance
const http = new HttpClient();

// Export class and instance
export default http;
export { HttpClient, getCsrfCookie };

