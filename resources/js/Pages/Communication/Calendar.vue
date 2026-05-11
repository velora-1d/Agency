<template>
  <WorkspaceLayout title="Calendar" subtitle="Kelola agenda, task, meeting, dan jadwal kampanye dalam satu tampilan.">
    <template #actions>
      <div class="flex items-center gap-3">
        <!-- Filter Dropdown -->
        <div class="relative group">
          <button class="flex items-center gap-2 px-4 py-2 bg-white border border-stone-200 text-stone-700 text-sm font-semibold rounded-xl hover:bg-stone-50 transition-all active:scale-95">
            <Filter class="w-4 h-4 text-stone-400" />
            <span>Filter</span>
            <ChevronDown class="w-4 h-4 text-stone-400 group-hover:rotate-180 transition-transform" />
          </button>
          
          <div class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-2xl border border-stone-100 p-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-100 transform origin-top-right scale-95 group-hover:scale-100 pointer-events-none group-hover:pointer-events-auto">
            <button 
              v-for="type in eventTypes" 
              :key="type.id"
              @click="toggleFilter(type.id)"
              :class="[
                'w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-colors',
                activeFilters.includes(type.id) ? 'bg-stone-100 text-stone-900' : 'text-stone-500 hover:bg-stone-50'
              ]"
            >
              <div :class="['w-2 h-2 rounded-full', type.color]"></div>
              <span>{{ type.label }}</span>
              <Check v-if="activeFilters.includes(type.id)" class="w-3 h-3 ml-auto text-blue-600" />
            </button>
          </div>
        </div>

        <button 
          @click="showCreateModal = true"
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 hover:-translate-y-0.5 transition-all active:scale-95"
        >
          <Plus class="w-4 h-4" />
          <span>New Event</span>
        </button>
      </div>
    </template>

    <div class="h-[calc(100vh-160px)] min-h-[600px] animate-in fade-in slide-in-from-bottom-4 duration-700 relative">
      <CalendarGrid 
        :events="filteredEvents" 
        @range-change="handleRangeChange"
        @select-event="handleSelectEvent"
        @event-dropped="handleEventDropped"
      />

      <!-- Loading Overlay (Rule 13.3) -->
      <div v-if="isLoading" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-20 flex items-center justify-center transition-all animate-in fade-in duration-300 rounded-xl">
        <div class="flex flex-col items-center gap-3">
          <div class="relative">
            <div class="w-12 h-12 border-4 border-blue-100 rounded-full"></div>
            <Loader2 class="w-12 h-12 text-blue-600 animate-spin absolute inset-0" />
          </div>
          <span class="text-xs font-bold text-stone-500 uppercase tracking-widest animate-pulse">Syncing...</span>
        </div>
      </div>
    </div>

    <!-- Event Detail Modal -->
    <Transition name="modal">
      <div v-if="selectedEvent" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-md">
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-white/20">
          <div :class="['h-3 w-full', getEventBg(selectedEvent.color)]"></div>
          <div class="p-8">
            <div class="flex justify-between items-start mb-6">
              <div class="flex items-center gap-2 px-3 py-1.5 bg-stone-100 rounded-full">
                <component :is="getEventIcon(selectedEvent.type)" class="w-4 h-4 text-stone-600" />
                <span class="text-[10px] font-black text-stone-600 uppercase tracking-[0.2em]">{{ selectedEvent.type }}</span>
              </div>
              <button @click="selectedEvent = null" class="p-2 hover:bg-stone-100 rounded-full transition-all hover:rotate-90">
                <X class="w-5 h-5 text-stone-400" />
              </button>
            </div>
            
            <h3 class="text-2xl font-bold text-stone-950 mb-3 tracking-tight leading-tight">{{ selectedEvent.title }}</h3>
            <p v-if="selectedEvent.description" class="text-sm text-stone-500 mb-8 leading-relaxed">{{ selectedEvent.description }}</p>
            
            <div class="space-y-5 mb-10">
              <div class="flex items-center gap-4 group">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                  <CalendarIcon class="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Date</p>
                  <p class="text-sm font-semibold text-stone-900">{{ formatFullDate(selectedEvent.start) }}</p>
                </div>
              </div>
              <div class="flex items-center gap-4 group">
                <div class="w-10 h-10 rounded-2xl bg-purple-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                  <Clock class="w-5 h-5 text-purple-600" />
                </div>
                <div>
                  <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Time</p>
                  <p class="text-sm font-semibold text-stone-900">{{ formatTimeRange(selectedEvent.start, selectedEvent.end) }}</p>
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-3">
              <div class="grid grid-cols-2 gap-3">
                <button 
                  @click="editEvent(selectedEvent)"
                  class="flex items-center justify-center gap-2 px-6 py-3 border border-stone-200 text-stone-700 text-sm font-bold rounded-2xl hover:bg-stone-50 transition-all active:scale-95"
                >
                  <Edit3 class="w-4 h-4" />
                  <span>Edit</span>
                </button>
                <button 
                  v-if="selectedEvent.type === 'event'"
                  @click="deleteEvent(selectedEvent)"
                  class="flex items-center justify-center gap-2 px-6 py-3 border border-red-100 text-red-600 text-sm font-bold rounded-2xl hover:bg-red-50 transition-all active:scale-95"
                >
                  <Trash2 class="w-4 h-4" />
                  <span>Delete</span>
                </button>
                <button 
                  v-else
                  disabled
                  class="flex items-center justify-center gap-2 px-6 py-3 border border-stone-100 text-stone-300 text-sm font-bold rounded-2xl cursor-not-allowed"
                >
                  <Trash2 class="w-4 h-4" />
                  <span>Delete</span>
                </button>
              </div>
              <button 
                @click="openView(selectedEvent)"
                class="w-full py-4 bg-stone-950 text-white text-sm font-bold rounded-2xl hover:bg-stone-800 shadow-lg shadow-stone-900/10 transition-all active:scale-95"
              >
                Open View
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Create Event Modal -->
    <Transition name="modal">
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-md">
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-white/20">
          <div class="p-8">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-bold text-stone-900">{{ isEditing ? 'Edit Event' : 'Create New Event' }}</h3>
              <button @click="showCreateModal = false" class="p-2 hover:bg-stone-100 rounded-full transition-all">
                <X class="w-5 h-5 text-stone-400" />
              </button>
            </div>
            
            <form @submit.prevent="submit" class="space-y-4">
              <div>
                <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1.5">Event Title</label>
                <input 
                  v-model="form.title" 
                  type="text" 
                  placeholder="e.g. Design Sync"
                  class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none"
                  required
                />
                <p v-if="form.errors.title" class="text-[10px] text-red-500 mt-1">{{ form.errors.title }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1.5">Start Date</label>
                  <input 
                    v-model="form.start_at" 
                    type="datetime-local" 
                    class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none"
                    required
                  />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1.5">End Date</label>
                  <input 
                    v-model="form.end_at" 
                    type="datetime-local" 
                    class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none"
                  />
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1.5">Color Tag</label>
                <div class="flex gap-2">
                  <button 
                    v-for="color in ['blue', 'purple', 'emerald', 'amber', 'sky', 'rose']" 
                    :key="color"
                    type="button"
                    @click="form.color = color"
                    :class="[
                      'w-8 h-8 rounded-full border-2 transition-all',
                      form.color === color ? 'border-stone-900 scale-110 shadow-lg' : 'border-transparent',
                      getEventBg(color)
                    ]"
                  ></button>
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1.5">Description</label>
                <textarea 
                  v-model="form.description" 
                  rows="3"
                  placeholder="Add some details..."
                  class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none resize-none"
                ></textarea>
              </div>

              <button 
                type="submit" 
                :disabled="form.processing"
                class="w-full py-4 bg-blue-600 text-white text-sm font-bold rounded-2xl hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all active:scale-[0.98] disabled:opacity-50"
              >
                {{ isEditing ? (form.processing ? 'Updating...' : 'Update Event') : (form.processing ? 'Creating...' : 'Create Event') }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Toast Notification -->
    <div v-if="toast" class="fixed bottom-8 left-1/2 -translate-x-1/2 z-100 animate-in fade-in slide-in-from-bottom-8 duration-500">
      <div class="bg-stone-950 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3 border border-white/10 backdrop-blur-xl">
        <CheckCircle2 class="w-5 h-5 text-emerald-400" />
        <span class="text-sm font-medium">{{ toast }}</span>
      </div>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import CalendarGrid from '../../Components/domain/communication/CalendarGrid.vue'
import { 
  Plus, X, Calendar as CalendarIcon, Clock, 
  CheckCircle2, Video, FileText, Share2, Rocket, Info,
  Filter, ChevronDown, Check, Loader2, Trash2, Edit3
} from 'lucide-vue-next'

const props = defineProps({
  workspace: Object,
  initialEvents: Array,
  filters: Object
})

const allEvents = ref(props.initialEvents || [])
const selectedEvent = ref(null)
const showCreateModal = ref(false)
const isEditing = ref(false)
const editingEventId = ref(null)
const toast = ref('')
const isLoading = ref(false)
const activeFilters = ref(['task', 'meeting', 'invoice', 'social', 'campaign', 'event'])

const form = useForm({
  title: '',
  description: '',
  start_at: '',
  end_at: '',
  all_day: false,
  color: 'blue'
})

const resetForm = () => {
  form.reset()
  form.clearErrors()
  isEditing.value = false
  editingEventId.value = null
}

watch(
  () => props.initialEvents,
  (events) => {
    allEvents.value = events || []

    if (!selectedEvent.value) {
      return
    }

    const updatedSelectedEvent = allEvents.value.find((event) => event.id === selectedEvent.value.id)
    selectedEvent.value = updatedSelectedEvent || null
  },
  { deep: true }
)

const editEvent = (event) => {
  if (event.type !== 'event') {
    // For tasks/meetings, maybe redirect to their edit page
    openView(event)
    return
  }
  
  isEditing.value = true
  editingEventId.value = event.id
  form.title = event.title
  form.description = event.description
  form.start_at = new Date(event.start).toISOString().slice(0, 16)
  form.end_at = event.end ? new Date(event.end).toISOString().slice(0, 16) : ''
  form.all_day = event.all_day
  form.color = event.color
  
  selectedEvent.value = null
  showCreateModal.value = true
}

const openView = (event) => {
  let url = '#'
  const workspaceSlug = props.workspace.slug

  if (!event || !event.type) return

  switch(event.type) {
    case 'task':
      if (event.raw && event.raw.project_id) {
        url = `/w/${workspaceSlug}/projects/${event.raw.project_id}?task=${event.id}`
      }
      break
    case 'meeting':
      // Link to meeting detail if available, otherwise just stay on page or link to a general page
      url = '#' 
      break
    case 'invoice':
      // Link to invoice detail if available
      url = '#'
      break
    default:
      url = '#'
  }
  
  if (url !== '#') {
    router.get(url)
  } else {
    showToast('Detail view not available for this type yet')
  }
}

const submit = () => {
  const workspaceSlug = props.workspace.slug
  if (isEditing.value) {
    form.patch(`/w/${workspaceSlug}/communication/calendar/${editingEventId.value}`, {
      onSuccess: () => {
        showCreateModal.value = false
        resetForm()
        showToast('Event updated successfully')
      }
    })
  } else {
    form.post(`/w/${workspaceSlug}/communication/calendar`, {
      onSuccess: () => {
        showCreateModal.value = false
        resetForm()
        showToast('Event created successfully')
      }
    })
  }
}

const deleteEvent = (event) => {
  if (!confirm('Are you sure you want to delete this event?')) return
  
  const workspaceSlug = props.workspace.slug
  router.delete(`/w/${workspaceSlug}/communication/calendar/${event.id}`, {
    onSuccess: () => {
      selectedEvent.value = null
      showToast('Event deleted successfully')
    }
  })
}

const eventTypes = [
  { id: 'task', label: 'Tasks', color: 'bg-emerald-500' },
  { id: 'meeting', label: 'Meetings', color: 'bg-purple-500' },
  { id: 'invoice', label: 'Invoices', color: 'bg-amber-500' },
  { id: 'social', label: 'Social Posts', color: 'bg-sky-500' },
  { id: 'campaign', label: 'Campaigns', color: 'bg-rose-500' },
  { id: 'event', label: 'Custom Events', color: 'bg-blue-500' },
]

const filteredEvents = computed(() => {
  return allEvents.value.filter(e => activeFilters.value.includes(e.type))
})

function toggleFilter(type) {
  if (activeFilters.value.includes(type)) {
    if (activeFilters.value.length > 1) {
      activeFilters.value = activeFilters.value.filter(t => t !== type)
    }
  } else {
    activeFilters.value.push(type)
  }
}

function handleRangeChange(range) {
  isLoading.value = true
  router.reload({
    data: { start: range.start, end: range.end },
    only: ['initialEvents'],
    onSuccess: (page) => {
      allEvents.value = page.props.initialEvents
    },
    onFinish: () => {
      isLoading.value = false
    }
  })
}

function handleSelectEvent(event) {
  selectedEvent.value = event
}

function handleEventDropped({ event, newDate }) {
  // Optimistic update
  const index = allEvents.value.findIndex(e => e.id === event.id)
  if (index !== -1) {
    allEvents.value[index].start = newDate
    showToast(`Event moved to ${new Date(newDate).toLocaleDateString()}`)
  }
  
  // Here we would typically call a backend endpoint to persist the move
}

function showToast(message) {
  toast.value = message
  setTimeout(() => toast.value = '', 3000)
}

function getEventIcon(type) {
  const map = {
    task: CheckCircle2,
    meeting: Video,
    invoice: FileText,
    social: Share2,
    campaign: Rocket,
    event: Info
  }
  return map[type] || Info
}

function getEventBg(color) {
  const map = {
    blue: 'bg-blue-500',
    purple: 'bg-purple-500',
    emerald: 'bg-emerald-500',
    amber: 'bg-amber-500',
    sky: 'bg-sky-500',
    rose: 'bg-rose-500',
  }
  return map[color] || 'bg-blue-500'
}

function formatFullDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('default', { 
    weekday: 'long', 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric' 
  })
}

function formatTimeRange(start, end) {
  const s = new Date(start).toLocaleTimeString('default', { hour: '2-digit', minute: '2-digit' })
  if (!end) return s
  const e = new Date(end).toLocaleTimeString('default', { hour: '2-digit', minute: '2-digit' })
  return `${s} - ${e}`
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(20px);
}
</style>
