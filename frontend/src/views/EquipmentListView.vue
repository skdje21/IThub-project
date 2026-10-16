<template>
  <div>
    <div class="page-header">
      <h1>Оборудование</h1>
      <button v-if="isAdmin" class="btn btn-primary" @click="showCreate = true">+ Добавить</button>
    </div>

    <div class="table-wrapper">
      <div v-if="loading" class="loading">Загрузка...</div>
      <template v-else>
        <table v-if="items.length > 0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Название</th>
              <th>Тип</th>
              <th>Серийный номер</th>
              <th>Статус</th>
              <th>Назначен</th>
              <th v-if="isAdmin"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td style="font-weight: 600; color: #6b7280;">#{{ item.id }}</td>
              <td style="font-weight: 500;">{{ item.name }}</td>
              <td>{{ item.type }}</td>
              <td style="font-family: monospace; font-size: 13px; color: #6b7280;">{{ item.serial_number }}</td>
              <td><span class="badge" :class="'badge-' + item.status">{{ equipStatusLabel(item.status) }}</span></td>
              <td>{{ item.assigned_to?.name || '—' }}</td>
              <td v-if="isAdmin" style="text-align: right;">
                <button class="btn btn-danger btn-sm" @click.stop="openDeleteModal(item)">Удалить</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-state">
          <div class="icon">🖥️</div>
          <p>Оборудование не найдено</p>
        </div>
      </template>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreate" class="modal-overlay" @click.self="showCreate = false">
      <div class="modal">
        <div class="modal-header">
          <h2>Добавить оборудование</h2>
          <button class="modal-close" @click="showCreate = false">&times;</button>
        </div>
        <form @submit.prevent="createEquipment">
          <div class="modal-body">
            <div class="form-group">
              <label>Название *</label>
              <input v-model="form.name" required placeholder="MacBook Pro 14&quot;" />
            </div>
            <div class="form-group">
              <label>Тип *</label>
              <input v-model="form.type" required placeholder="Ноутбук" />
            </div>
            <div class="form-group">
              <label>Серийный номер *</label>
              <input v-model="form.serial_number" required placeholder="SN-0001-0001" />
            </div>
            <div v-if="createError" class="form-error">{{ createError }}</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showCreate = false">Отмена</button>
            <button type="submit" class="btn btn-primary" :disabled="creating">
              {{ creating ? 'Добавление...' : 'Добавить' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal" style="width: 420px;">
        <div class="modal-header">
          <h2>Удаление оборудования</h2>
          <button class="modal-close" @click="deleteTarget = null">&times;</button>
        </div>
        <div class="modal-body">
          <p style="margin-bottom: 12px;">Вы уверены, что хотите удалить это оборудование?</p>
          <div style="background: #f9fafb; border-radius: 8px; padding: 14px; border: 1px solid #e5e7eb;">
            <div style="font-weight: 600; margin-bottom: 4px;">{{ deleteTarget.name }}</div>
            <div style="font-size: 13px; color: #6b7280;">
              {{ deleteTarget.type }} &middot; {{ deleteTarget.serial_number }}
            </div>
          </div>
          <p style="margin-top: 12px; font-size: 13px; color: #dc2626;">Это действие нельзя отменить.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="deleteTarget = null">Отмена</button>
          <button class="btn btn-danger" :disabled="deleting" @click="deleteEquipment">
            {{ deleting ? 'Удаление...' : 'Удалить' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import api from '../api/axios'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const items = ref([])
const loading = ref(true)
const showCreate = ref(false)
const creating = ref(false)
const createError = ref('')
const deleteTarget = ref(null)
const deleting = ref(false)

const form = reactive({ name: '', type: '', serial_number: '' })

const isAdmin = computed(() => auth.user?.role === 'admin')

const equipStatusLabels = { available: 'Доступно', assigned: 'Назначено', repair: 'Ремонт', decommissioned: 'Списано' }
const equipStatusLabel = (s) => equipStatusLabels[s] || s

async function loadEquipment() {
  loading.value = true
  try {
    const { data } = await api.get('/equipment')
    items.value = data.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openDeleteModal(item) {
  deleteTarget.value = item
}

async function deleteEquipment() {
  deleting.value = true
  try {
    await api.delete(`/equipment/${deleteTarget.value.id}`)
    deleteTarget.value = null
    loadEquipment()
  } catch (e) {
    alert(e.response?.data?.message || 'Ошибка удаления')
  } finally {
    deleting.value = false
  }
}

async function createEquipment() {
  creating.value = true
  createError.value = ''
  try {
    await api.post('/equipment', form)
    showCreate.value = false
    form.name = ''
    form.type = ''
    form.serial_number = ''
    loadEquipment()
  } catch (e) {
    createError.value = e.response?.data?.message || 'Ошибка добавления'
  } finally {
    creating.value = false
  }
}

onMounted(loadEquipment)
</script>
