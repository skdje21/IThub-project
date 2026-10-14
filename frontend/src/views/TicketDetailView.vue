<template>
  <div>
    <div class="page-header">
      <div style="display: flex; align-items: center; gap: 12px;">
        <button class="btn btn-secondary btn-sm" @click="$router.push('/tickets')">&larr; Назад</button>
        <h1 v-if="ticket">Заявка #{{ ticket.id }}</h1>
      </div>
    </div>

    <div v-if="loading" class="loading">Загрузка...</div>

    <template v-else-if="ticket">
      <div class="detail-grid">
        <!-- Left column -->
        <div>
          <div class="card" style="margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 16px;">{{ ticket.title }}</h2>
            <p style="color: #374151; line-height: 1.7; white-space: pre-wrap;">{{ ticket.description }}</p>
          </div>

          <!-- Comments -->
          <div class="card">
            <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 16px;">
              Комментарии ({{ comments.length }})
            </h3>

            <div v-if="comments.length === 0" style="color: #9ca3af; font-size: 14px; padding: 12px 0;">
              Комментариев пока нет
            </div>

            <div v-for="comment in comments" :key="comment.id" class="comment">
              <div class="comment-header">
                <span class="comment-author">{{ comment.user?.name }}</span>
                <span class="comment-date">{{ formatDateTime(comment.created_at) }}</span>
              </div>
              <div class="comment-body">{{ comment.body }}</div>
            </div>

            <!-- Add comment form -->
            <form @submit.prevent="addComment" style="margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 16px;">
              <div class="form-group" style="margin-bottom: 8px;">
                <textarea v-model="newComment" placeholder="Написать комментарий..." style="min-height: 70px;"></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-sm" :disabled="!newComment.trim() || commenting">
                {{ commenting ? 'Отправка...' : 'Отправить' }}
              </button>
            </form>
          </div>
        </div>

        <!-- Right column — sidebar info -->
        <div>
          <div class="card" style="margin-bottom: 16px;">
            <div class="detail-field">
              <div class="label">Статус</div>
              <div class="value">
                <span class="badge" :class="'badge-' + ticket.status">{{ statusLabel(ticket.status) }}</span>
              </div>
            </div>

            <div class="detail-field">
              <div class="label">Приоритет</div>
              <div class="value">
                <span class="badge" :class="'badge-' + ticket.priority">{{ priorityLabel(ticket.priority) }}</span>
              </div>
            </div>

            <div class="detail-field">
              <div class="label">Категория</div>
              <div class="value">{{ ticket.category?.name }}</div>
            </div>

            <div class="detail-field">
              <div class="label">Автор</div>
              <div class="value">{{ ticket.author?.name }}</div>
            </div>

            <div class="detail-field">
              <div class="label">Исполнитель</div>
              <div class="value">{{ ticket.assignee?.name || '—' }}</div>
            </div>

            <div class="detail-field">
              <div class="label">Отдел</div>
              <div class="value">{{ ticket.department?.name }}</div>
            </div>

            <div class="detail-field">
              <div class="label">Создана</div>
              <div class="value">{{ formatDateTime(ticket.created_at) }}</div>
            </div>

            <div v-if="ticket.resolved_at" class="detail-field">
              <div class="label">Решена</div>
              <div class="value">{{ formatDateTime(ticket.resolved_at) }}</div>
            </div>
          </div>

          <!-- Status actions -->
          <div class="card" v-if="nextStatuses.length > 0">
            <div class="label" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 10px;">
              Сменить статус
            </div>
            <div class="status-actions">
              <button v-for="s in nextStatuses" :key="s"
                      class="btn btn-sm"
                      :class="statusBtnClass(s)"
                      :disabled="changingStatus"
                      @click="changeStatus(s)">
                {{ statusLabel(s) }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api/axios'

const route = useRoute()
const ticket = ref(null)
const comments = ref([])
const loading = ref(true)
const newComment = ref('')
const commenting = ref(false)
const changingStatus = ref(false)

const statusLabels = { draft: 'Черновик', pending: 'Ожидает', approved: 'Одобрена', in_progress: 'В работе', done: 'Выполнена', rejected: 'Отклонена' }
const priorityLabels = { low: 'Низкий', medium: 'Средний', high: 'Высокий' }
const statusLabel = (s) => statusLabels[s] || s
const priorityLabel = (p) => priorityLabels[p] || p

const transitions = {
  draft: ['pending'],
  pending: ['approved', 'rejected'],
  approved: ['in_progress', 'rejected'],
  in_progress: ['done'],
  rejected: ['pending'],
  done: [],
}

const nextStatuses = computed(() => {
  if (!ticket.value) return []
  return transitions[ticket.value.status] || []
})

function statusBtnClass(s) {
  if (s === 'done') return 'btn-success'
  if (s === 'rejected') return 'btn-danger'
  return 'btn-secondary'
}

function formatDateTime(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function loadTicket() {
  const { data } = await api.get(`/tickets/${route.params.id}`)
  ticket.value = data.data
}

async function loadComments() {
  const { data } = await api.get(`/tickets/${route.params.id}/comments`)
  comments.value = data.data
}

async function addComment() {
  if (!newComment.value.trim()) return
  commenting.value = true
  try {
    await api.post(`/tickets/${route.params.id}/comments`, { body: newComment.value })
    newComment.value = ''
    await loadComments()
  } catch (e) {
    console.error(e)
  } finally {
    commenting.value = false
  }
}

async function changeStatus(status) {
  changingStatus.value = true
  try {
    await api.patch(`/tickets/${route.params.id}/status`, { status })
    await loadTicket()
  } catch (e) {
    alert(e.response?.data?.message || 'Ошибка смены статуса')
  } finally {
    changingStatus.value = false
  }
}

onMounted(async () => {
  try {
    await Promise.all([loadTicket(), loadComments()])
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>
