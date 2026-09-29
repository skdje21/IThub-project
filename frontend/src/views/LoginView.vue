<template>
  <div style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f1f5f9;">
    <div style="background: #fff; padding: 40px; border-radius: 8px; width: 400px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
      <h1 style="margin-bottom: 24px;">IT Hub — Вход</h1>
      <form @submit.prevent="handleLogin">
        <div style="margin-bottom: 16px;">
          <label>Email</label>
          <input v-model="email" type="email" required style="width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;" />
        </div>
        <div style="margin-bottom: 16px;">
          <label>Пароль</label>
          <input v-model="password" type="password" required style="width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;" />
        </div>
        <p v-if="error" style="color: red;">{{ error }}</p>
        <button type="submit" style="width: 100%; padding: 10px; background: #3b82f6; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
          Войти
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
const router = useRouter()
const auth = useAuthStore()

async function handleLogin() {
  try {
    error.value = ''
    await auth.login(email.value, password.value)
    router.push('/')
  } catch (e) {
    console.error('Login error:', e.response?.data || e.message || e)
    error.value = e.response?.data?.message || 'Неверный email или пароль'
  }
}
</script>
