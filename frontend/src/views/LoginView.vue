<template>
  <div class="login-page">
    <div class="login-card">
      <h1>IT Hub</h1>
      <p class="subtitle">Войдите в систему управления заявками</p>

      <div v-if="error" class="login-error">{{ error }}</div>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>Email</label>
          <input v-model="email" type="email" placeholder="admin@ithub.test" required />
        </div>
        <div class="form-group">
          <label>Пароль</label>
          <input v-model="password" type="password" placeholder="password" required />
        </div>
        <button type="submit" class="btn btn-primary" :disabled="loading">
          {{ loading ? 'Вход...' : 'Войти' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const router = useRouter()
const auth = useAuthStore()

async function handleLogin() {
  try {
    error.value = ''
    loading.value = true
    await auth.login(email.value, password.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Неверный email или пароль'
  } finally {
    loading.value = false
  }
}
</script>
