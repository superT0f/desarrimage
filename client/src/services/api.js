import axios from 'axios'

const http = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
  timeout: 10000,
})

http.interceptors.response.use(
  (response) => response.data,
  (error) => {
    const message = error.response?.data?.error || 'Impossible de joindre le catalogue.'
    return Promise.reject(new Error(message))
  },
)

export default http
