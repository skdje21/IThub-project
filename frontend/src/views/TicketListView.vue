<template>
  <div>
    <div class="page-header">
      <h1>Заявки</h1>
      <button class="btn btn-primary" @click="showCreate = true">+ Новая заявка</button>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <select v-model="filters.status" @change="loadTickets">
        <option value="">Все статусы</option>
        <option value="draft">Черновик</option>
        <option value="pending">Ожидает</option>
        <option value="approved">Одобрена</option>
        <option value="in_progress">В работе</option>
        <option value="done">Выполнена</option>
        <option value="rejected">Отклонена</option>
      </select>
      <select v-model="filters.priority" @change="loadTickets">
        <option value="">Все приоритеты</option>
        <option value="low">Низкий</option>
        <option value="medium">Средний</option>
        <option value="high">Высокий</option>
      </select>
      <select v-model="filters.category_id" @change="loadTickets">
        <option value="">Все категории</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
      <div v-if="loading" class="loading">Загрузка...</div>
      <template v-else>
        <table v-if="tickets.length > 0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Заявка</th>
              <th>Категория</th>
              <th>Приоритет</th>
              <th>Статус</th>
              <th>Автор</th>
              <th>Дата</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ticket in tickets" :key="ticket.id" class="clickable"
                @click="$router.push(`/tickets/${ticket.id}`)">
              <td><span class="ticket-id">#{{ ticket.id }}</span></td>
              <td style="max-width: 280px;">
                <div style="font-weight: 500;">{{ ticket.title }}</div>
              </td>
              <td>{{ ticket.category?.name }}</td>
              <td><span class="badge" :class="'badge-' + ticket.priority">{{ priorityLabel(ticket.priority) }}</span></td>
              <td><span class="badge" :class="'badge-' + ticket.status">{{ statusLabel(ticket.status) }}</span></td>
              <td>{{ ticket.author?.name }}</td>
              <td style="white-space: nowrap; color: #6b7280; font-size: 13px;">{{ formatDate(ticket.created_at) }}</td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-state">
          <div class="icon">📋</div>
          <p>Заявок не найдено</p>
        </div>

        <!-- Pagination -->
        <div v-if="meta.last_page > 1" class="pagination">
          <button :disabled="meta.current_page <= 1" @click="goToPage(meta.current_page - 1)">&larr;</button>
          <template v-for="p in paginationPages" :key="p">
            <button v-if="p === '...'" disabled>...</button>
            <button v-else :class="{ active: p === meta.current_page }" @click="goToPage(p)">{{ p }}</button>
          </template>
          <button :disabled="meta.current_page >= meta.last_page" @click="goToPage(meta.current_page + 1)">&rarr;</button>
          <span class="info">{{ meta.total }} заявок</span>
        </div>
      </template>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreate" class="modal-overlay" @click.self="showCreate = false">
      <div class="modal">
        <div class="modal-header">
          <h2>Новая заявка</h2>
          <button class="modal-close" @click="showCreate = false">&times;</button>
        </div>
        <form @submit.prevent="createTicket">
          <div class="modal-body">
            <div class="form-group">
              <label>Заголовок *</label>
              <input v-model="form.title" required placeholder="Кратко опишите проблему" />
            </div>
            <div class="form-group">
              <label>Описание *</label>
              <textarea v-model="form.description" required placeholder="Подробное описание запроса..."></textarea>
            </div>
            <div class="form-group">
              <label>Категория *</label>
              <select v-model="form.category_id" required>
                <option value="">Выберите категорию</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>Приоритет</label>
              <select v-model="form.priority">
                <option value="low">Низкий</option>
                <option value="medium">Средний</option>
                <option value="high">Высокий</option>
              </select>
            </div>
            <div v-if="createError" class="form-error">{{ createError }}</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showCreate = false">Отмена</button>
            <button type="submit" class="btn btn-primary" :disabled="creating">
              {{ creating ? 'Создание...' : 'Создать заявку' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import api from '../api/axios'

const tickets = ref([])
const categories = ref([])
const loading = ref(true)
const meta = ref({})
const showCreate = ref(false)
const creating = ref(false)
const createError = ref('')

const filters = reactive({ status: '', priority: '', category_id: '' })
const form = reactive({ title: '', description: '', category_id: '', priority: 'medium' })

const statusLabels = { draft: 'Черновик', pending: 'Ожидает', approved: 'Одобрена', in_progress: 'В работе', done: 'Выполнена', rejected: 'Отклонена' }
const priorityLabels = { low: 'Низкий', medium: 'Средний', high: 'Высокий' }
const statusLabel = (s) => statusLabels[s] || s
const priorityLabel = (p) => priorityLabels[p] || p

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const paginationPages = computed(() => {
  const total = meta.value.last_page || 1
  const current = meta.value.current_page || 1
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = [1]
  if (current > 3) pages.push('...')
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i)
  if (current < total - 2) pages.push('...')
  pages.push(total)
  return pages
})

async function loadTickets(page = 1) {
  loading.value = true
  try {
    const params = { page: typeof page === 'number' ? page : 1 }
    if (filters.status) params.status = filters.status
    if (filters.priority) params.priority = filters.priority
    if (filters.category_id) params.category_id = filters.category_id
    const { data } = await api.get('/tickets', { params })
    tickets.value = data.data
    meta.value = data.meta
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function goToPage(p) {
  loadTickets(p)
}

async function createTicket() {
  creating.value = true
  createError.value = ''
  try {
    await api.post('/tickets', form)
    showCreate.value = false
    form.title = ''
    form.description = ''
    form.category_id = ''
    form.priority = 'medium'
    loadTickets()
  } catch (e) {
    createError.value = e.response?.data?.message || 'Ошибка создания заявки'
  } finally {
    creating.value = false
  }
}

onMounted(async () => {
  const { data } = await api.get('/categories')
  categories.value = data.data
  loadTickets()
})
</script>
