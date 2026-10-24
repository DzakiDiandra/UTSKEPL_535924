import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.path !== from.path) return { top: 0 }
    return false
  },
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { title: 'Masuk - LokaHP', public: true },
    },
    {
      path: '/',
      name: 'beranda',
      component: () => import('@/views/HomeView.vue'),
      meta: { title: 'LokaHP - HP pilihan, stok jelas', requiresAuth: true },
    },
    {
      path: '/produk',
      name: 'katalog',
      component: () => import('@/views/KatalogView.vue'),
      meta: { title: 'Katalog HP - LokaHP', requiresAuth: true },
    },
    {
      path: '/produk/:id',
      name: 'produk-detail',
      component: () => import('@/views/ProdukDetailView.vue'),
      meta: { title: 'Detail HP - LokaHP', requiresAuth: true },
    },
    {
      path: '/admin/produk',
      name: 'admin-produk',
      component: () => import('@/views/admin/AdminProdukView.vue'),
      meta: {
        title: 'Daftar Produk - LokaHP',
        admin: true,
        requiresAuth: true,
        requiresAdmin: true,
      },
    },
    {
      path: '/admin/produk/baru',
      name: 'admin-produk-baru',
      component: () => import('@/views/admin/AdminProdukFormView.vue'),
      meta: {
        title: 'Tambah Produk - LokaHP',
        admin: true,
        requiresAuth: true,
        requiresAdmin: true,
      },
    },
    {
      path: '/admin/produk/:id/edit',
      name: 'admin-produk-edit',
      component: () => import('@/views/admin/AdminProdukFormView.vue'),
      meta: {
        title: 'Edit Produk - LokaHP',
        admin: true,
        requiresAuth: true,
        requiresAdmin: true,
      },
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'tidak-ditemukan',
      component: () => import('@/views/NotFoundView.vue'),
      meta: { title: 'Halaman Tidak Ditemukan - LokaHP', requiresAuth: true },
    },
  ],
})

router.beforeEach((to) => {
  const { isAuthenticated, isAdmin, clearSession } = useAuth()

  if (to.meta.public) {
    if (isAuthenticated.value) {
      return isAdmin.value ? { name: 'admin-produk' } : { name: 'katalog' }
    }

    return true
  }

  if (!isAuthenticated.value) {
    return {
      name: 'login',
      query: { redirect: to.fullPath },
    }
  }

  if (to.meta.requiresAdmin && !isAdmin.value) {
    clearSession()
    const { tampilkanToast } = useToast()
    tampilkanToast('Akun ini bukan admin. Silakan masuk dengan akun admin.', 'danger')

    return { name: 'login' }
  }

  return true
})

router.afterEach((to) => {
  document.title = to.meta.title ?? 'LokaHP'
})

export default router
