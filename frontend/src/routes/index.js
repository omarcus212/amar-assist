import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import ProductsView from '@/views/ProductView.vue'
import UserView from '@/views/UserView.vue'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: LoginView,
            meta: { requiresGuest: true }
        },
        {
            path: '/',
            redirect: '/login'
        },
        {
            path: '/products',
            name: 'products',
            component: ProductsView,
            meta: { requiresAuth: true }
        },
        {
            path: '/users',
            name: 'users',
            component: UserView,
            meta: { requiresAuth: true }
        }
    ]
})

// Guard de navegação - rotas
router.beforeEach((to) => {
    const token = localStorage.getItem('token')

    if (to.meta.requiresAuth && !token) {
        return { name: 'login' }
    }

    if (to.meta.requiresGuest && token) {
        return { name: 'products' }
    }
})

export default router