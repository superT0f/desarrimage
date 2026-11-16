import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: { Accept: 'application/json' },
  timeout: 10000,
})

const mirror = axios.create({ baseURL: '/mirror/api', timeout: 10000 })

http.interceptors.response.use(
  (response) => response.data,
  async (error) => {
    if (error.config) {
      try {
        const path = error.config.url === '/products' ? '/products.json' : error.config.url
        const fallback = await mirror.get(path, { params: error.config.params })
        return fallback.data
      } catch {}
    }
    const message = error.response?.data?.error || 'Impossible de joindre le catalogue.'
    return Promise.reject(new Error(message))
  },
)

export default http
