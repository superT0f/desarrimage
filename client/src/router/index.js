import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { setSeo } from '../services/seo'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/catalogue',
      name: 'catalog',
      component: () => import('../views/CatalogView.vue'),
    },
    {
      path: '/produit/:slug/imprimer',
      name: 'print-guide',
      component: () => import('../views/PrintGuideView.vue'),
    },
    {
      path: '/produit/:slug',
      name: 'product',
      component: () => import('../views/ProductView.vue'),
    },
    {
      path: '/contribuer',
      name: 'contribute',
      component: () => import('../views/ContributeView.vue'),
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('../views/NotFoundView.vue'),
    },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.hash) return { el: to.hash, behavior: 'smooth' }
    return { top: 0 }
  },
})

router.afterEach((to) => {
  const titles = {
    home: 'Désarrimage — Changez de cap, pas d’habitudes',
    catalog: 'Explorer le catalogue — Désarrimage',
    contribute: 'Contribuer — Désarrimage',
    'print-guide': 'Guide Google Drive à imprimer — Désarrimage',
    'not-found': 'Page introuvable — Désarrimage',
  }
  setSeo({
    title: titles[to.name] || 'Fiche de migration — Désarrimage',
    description: to.meta.description,
    path: to.fullPath,
  })
})

export default router
