import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('../layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'Dashboard', component: () => import('../views/DashboardView.vue') },
      { path: 'tickets', name: 'Tickets', component: () => import('../views/TicketListView.vue') },
      { path: 'tickets/:id', name: 'TicketDetail', component: () => import('../views/TicketDetailView.vue') },
      { path: 'equipment', name: 'Equipment', component: () => import('../views/EquipmentListView.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    return { name: 'Login' }
  }
  if (to.meta.guest && token) {
    return { name: 'Dashboard' }
  }
})

export default router
