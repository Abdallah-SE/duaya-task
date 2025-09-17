import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

export function useCsrfToken() {
  const csrfToken = ref(null)

  const getCsrfToken = () => {
    const token = document.head.querySelector('meta[name="csrf-token"]')
    return token ? token.content : null
  }

  const refreshCsrfToken = async () => {
    try {
      const response = await fetch('/csrf-token', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        if (data.csrf_token) {
          // Update the meta tag
          const metaTag = document.head.querySelector('meta[name="csrf-token"]')
          if (metaTag) {
            metaTag.setAttribute('content', data.csrf_token)
          }
          
          // Update Inertia.js router headers
          router.defaults.headers.common['X-CSRF-TOKEN'] = data.csrf_token
          
          csrfToken.value = data.csrf_token
          return data.csrf_token
        }
      }
    } catch (error) {
      console.error('Failed to refresh CSRF token:', error)
    }
    
    return null
  }

  const ensureCsrfToken = async () => {
    let token = getCsrfToken()
    
    if (!token) {
      token = await refreshCsrfToken()
    }
    
    if (token) {
      router.defaults.headers.common['X-CSRF-TOKEN'] = token
      csrfToken.value = token
    }
    
    return token
  }

  onMounted(() => {
    csrfToken.value = getCsrfToken()
    if (csrfToken.value) {
      router.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.value
    }
  })

  return {
    csrfToken,
    getCsrfToken,
    refreshCsrfToken,
    ensureCsrfToken
  }
}




