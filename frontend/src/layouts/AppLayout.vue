<template>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h1>IT Hub</h1>
        <span>Система IT-заявок</span>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/" active-class="" exact-active-class="" :class="{ active: $route.path === '/' }">
          <span class="icon">📊</span> Dashboard
        </router-link>
        <router-link to="/tickets" active-class="" exact-active-class="" :class="{ active: $route.path.startsWith('/tickets') }">
          <span class="icon">📋</span> Заявки
        </router-link>
        <router-link to="/equipment" active-class="" exact-active-class="" :class="{ active: $route.path === '/equipment' }">
          <span class="icon">🖥️</span> Оборудование
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="sidebar-user" v-if="auth.user">
          <div class="avatar">{{ auth.user.name?.charAt(0) }}</div>
          <div class="info">
            <div class="name">{{ auth.user.name }}</div>
            <div class="role">{{ roleLabel(auth.user.role) }}</div>
          </div>
        </div>
        <button class="btn-logout" @click="handleLogout">Выйти</button>
      </div>
    </aside>

    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const roleLabels = { admin: 'Администратор', manager: 'Менеджер', employee: 'Сотрудник' }
const roleLabel = (role) => roleLabels[role] || role

onMounted(async () => {
  if (auth.token && !auth.user) {
    try {
      await auth.fetchUser()
    } catch {
      await auth.logout()
      router.push('/login')
    }
  }
})

async function handleLogout() {
  try { await auth.logout() } catch {}
  router.push('/login')
}
</script>
