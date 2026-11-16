import { defineStore } from 'pinia'
import api from '../services/api'

export const useCatalogStore = defineStore('catalog', {
  state: () => ({
    products: [],
    categories: [],
    stats: null,
    loading: false,
    error: null,
  }),
  actions: {
    async loadHome() {
      if (this.stats && this.products.length) return
      this.loading = true
      this.error = null
      try {
        const [products, stats] = await Promise.all([
          api.get('/products', { params: { region: 'other' } }),
          api.get('/stats'),
        ])
        this.products = products.data
        this.stats = stats.data
      } catch (error) {
        try {
          const response = await fetch('/catalog-snapshot.json')
          if (!response.ok) throw error
          const snapshot = await response.json()
          this.products = snapshot.products
          this.stats = snapshot.stats
        } catch {
          this.error = error.message
        }
      } finally {
        this.loading = false
      }
    },
    async loadCategories() {
      if (this.categories.length) return
      const response = await api.get('/categories')
      this.categories = response.data
    },
    async fetchProducts(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/products', { params })
        this.products = response.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },
    async fetchProduct(slug) {
      const response = await api.get(`/products/${slug}`)
      return response.data
    },
  },
})
