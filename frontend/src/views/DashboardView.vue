<template>
  <div>
    <div class="page-header">
      <h1>Dashboard</h1>
    </div>

    <div v-if="loading" class="loading">Загрузка...</div>

    <template v-else>
      <div class="stats-grid">
        <div class="stat-card accent-blue">
          <div class="label">Всего заявок</div>
          <div class="value">{{ stats.total_tickets }}</div>
        </div>
        <div class="stat-card accent-yellow">
          <div class="label">Ожидают</div>
          <div class="value">{{ stats.tickets_by_status?.pending || 0 }}</div>
          <div class="sub">На рассмотрении</div>
        </div>
        <div class="stat-card accent-purple">
          <div class="label">В работе</div>
          <div class="value">{{ stats.tickets_by_status?.in_progress || 0 }}</div>
          <div class="sub">Выполняются</div>
        </div>
        <div class="stat-card accent-green">
          <div class="label">Выполнено</div>
          <div class="value">{{ stats.tickets_by_status?.done || 0 }}</div>
        </div>
        <div class="stat-card accent-red">
          <div class="label">Отклонено</div>
          <div class="value">{{ stats.tickets_by_status?.rejected || 0 }}</div>
        </div>
      </div>

      <div class="stats-grid" style="grid-template-columns: 1fr 1fr;">
        <div class="card">
          <h3 style="margin-bottom: 16px; font-size: 15px; font-weight: 600;">Заявки по статусам</h3>
          <div v-for="(count, status) in stats.tickets_by_status" :key="status" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
            <span class="badge" :class="'badge-' + status">{{ statusLabel(status) }}</span>
            <span style="font-weight: 600;">{{ count }}</span>
          </div>
        </div>

        <div class="card">
          <h3 style="margin-bottom: 16px; font-size: 15px; font-weight: 600;">Последние заявки</h3>
          <div v-if="recentTickets.length === 0" class="empty-state" style="padding: 20px;">
            <p>Нет заявок</p>
          </div>
          <div v-for="ticket in recentTickets" :key="ticket.id"
               style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; cursor: pointer;"
               @click="$router.push(`/tickets/${ticket.id}`)">
            <div>
              <span class="ticket-id">#{{ ticket.id }}</span>
              <span style="margin-left: 8px;">{{ ticket.title }}</span>
            </div>
            <span class="badge" :class="'badge-' + ticket.status">{{ statusLabel(ticket.status) }}</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../api/axios'

const stats = ref({})
const recentTickets = ref([])
const loading = ref(true)

const statusLabels = {
  draft: 'Черновик',
  pending: 'Ожидает',
  approved: 'Одобрена',
  in_progress: 'В работе',
  done: 'Выполнена',
  rejected: 'Отклонена',
}
const statusLabel = (s) => statusLabels[s] || s

onMounted(async () => {
  try {
    const [statsRes, ticketsRes] = await Promise.all([
      api.get('/dashboard/stats'),
      api.get('/tickets', { params: { per_page: 5 } }),
    ])
    stats.value = statsRes.data
    recentTickets.value = ticketsRes.data.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>
