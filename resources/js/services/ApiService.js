/**
 * API Service for handling all API calls
 * Centralized service for consistent API communication
 */

class ApiService {
    constructor() {
        this.baseURL = '/api';
        this.token = localStorage.getItem('auth_token');
        this.userType = localStorage.getItem('user_type'); // 'admin' or 'employee'
    }

    /**
     * Set authentication token
     */
    setToken(token, userType) {
        this.token = token;
        this.userType = userType;
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user_type', userType);
    }

    /**
     * Clear authentication token
     */
    clearToken() {
        this.token = null;
        this.userType = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_type');
    }

    /**
     * Get headers for API requests
     */
    getHeaders(includeAuth = true) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        if (includeAuth && this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        return headers;
    }

    /**
     * Make API request
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: this.getHeaders(),
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Authentication methods
    async loginAdmin(credentials) {
        const response = await this.request('/auth/admin/login', {
            method: 'POST',
            body: JSON.stringify(credentials)
        });

        if (response.success) {
            this.setToken(response.data.token, 'admin');
        }

        return response;
    }

    async loginEmployee(credentials) {
        const response = await this.request('/auth/employee/login', {
            method: 'POST',
            body: JSON.stringify(credentials)
        });

        if (response.success) {
            this.setToken(response.data.token, 'employee');
        }

        return response;
    }

    async logout() {
        try {
            const endpoint = this.userType === 'admin' ? '/auth/admin/logout' : '/auth/employee/logout';
            await this.request(endpoint, { method: 'POST' });
        } finally {
            this.clearToken();
        }
    }

    async getCurrentUser() {
        const endpoint = this.userType === 'admin' ? '/auth/admin/me' : '/auth/employee/me';
        return await this.request(endpoint);
    }

    async refreshToken() {
        const endpoint = this.userType === 'admin' ? '/auth/admin/refresh' : '/auth/employee/refresh';
        const response = await this.request(endpoint, { method: 'POST' });
        
        if (response.success) {
            this.setToken(response.data.token, this.userType);
        }

        return response;
    }

    // Dashboard methods
    async getDashboard() {
        const endpoint = this.userType === 'admin' ? '/admin/dashboard' : '/employee/dashboard';
        return await this.request(endpoint);
    }

    async getStats() {
        const endpoint = this.userType === 'admin' ? '/admin/stats' : '/employee/stats';
        return await this.request(endpoint);
    }

    // Activities methods
    async getActivities() {
        const endpoint = this.userType === 'admin' ? '/admin/activities' : '/employee/activities';
        return await this.request(endpoint);
    }

    // Users/Employees methods (admin only)
    async getUsers() {
        if (this.userType !== 'admin') {
            throw new Error('Access denied. Admin privileges required.');
        }
        return await this.request('/admin/users');
    }

    async getEmployees() {
        if (this.userType !== 'admin') {
            throw new Error('Access denied. Admin privileges required.');
        }
        return await this.request('/admin/employees');
    }

    // Penalties methods
    async getPenalties() {
        const endpoint = this.userType === 'admin' ? '/admin/penalties' : '/employee/penalties';
        return await this.request(endpoint);
    }

    // Settings methods
    async getSettings() {
        const endpoint = this.userType === 'admin' ? '/admin/settings' : '/employee/settings';
        return await this.request(endpoint);
    }

    async updateSettings(settings) {
        const endpoint = this.userType === 'admin' ? '/admin/settings' : '/employee/settings';
        return await this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(settings)
        });
    }

    // Idle monitoring
    async handleIdleWarning(data) {
        return await this.request('/idle-monitoring/handle-warning', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    // Health check
    async healthCheck() {
        return await this.request('/health');
    }
}

// Create singleton instance
const apiService = new ApiService();

export default apiService;
