import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF token handling (Laravel built-in)
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('Security token missing. Please refresh the page and try again.');
}

// Add CSRF token to all requests automatically
window.axios.interceptors.request.use(function (config) {
    // Always include CSRF token for non-GET requests
    if (config.method !== 'get' && token) {
        config.headers['X-CSRF-TOKEN'] = token.content;
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});

// Handle CSRF token mismatch errors
window.axios.interceptors.response.use(
    function (response) {
        return response;
    },
    async function (error) {
        if (error.response?.status === 419) {
            console.error('CSRF token expired. Attempting to refresh...');
            
            try {
                // Try to get a fresh CSRF token
                const response = await axios.get('/csrf-token');
                if (response.data.csrf_token) {
                    // Update the token in the meta tag
                    const metaTag = document.head.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.setAttribute('content', response.data.csrf_token);
                    }
                    
                    // Update axios default header
                    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrf_token;
                    
                    console.log('CSRF token refreshed successfully, retrying original request...');
                    
                    // Retry the original request with the new token
                    const originalRequest = error.config;
                    originalRequest.headers['X-CSRF-TOKEN'] = response.data.csrf_token;
                    
                    return axios(originalRequest);
                }
            } catch (refreshError) {
                console.error('Failed to refresh CSRF token:', refreshError);
            }
            
            // If refresh fails, reload the page
            window.location.reload();
        }
        return Promise.reject(error);
    }
);
