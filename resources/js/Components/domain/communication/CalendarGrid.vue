<template>
  <div class="flex flex-col h-full bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
    <!-- Calendar Header Controls -->
    <div class="flex items-center justify-between p-4 border-b border-stone-100">
      <div class="flex items-center gap-4">
        <h2 class="text-lg font-semibold text-stone-900">{{ currentPeriodLabel }}</h2>
        <div class="flex items-center bg-stone-50 rounded-lg p-1 border border-stone-200">
          <button @click="navigate(-1)" class="p-1.5 hover:bg-white hover:shadow-sm rounded-md transition-all text-stone-600">
            <ChevronLeft class="w-4 h-4" />
          </button>
          <button @click="goToToday()" class="px-3 py-1 text-xs font-medium hover:bg-white hover:shadow-sm rounded-md transition-all text-stone-700">
            Today
          </button>
          <button @click="navigate(1)" class="p-1.5 hover:bg-white hover:shadow-sm rounded-md transition-all text-stone-600">
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <div class="flex bg-stone-100 rounded-lg p-1">
          <button 
            v-for="v in views" 
            :key="v.id"
            @click="viewMode = v.id"
            :class="[
              'px-3 py-1.5 text-xs font-medium rounded-md transition-all capitalize',
              viewMode === v.id ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700'
            ]"
          >
            {{ v.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Calendar Body -->
    <div class="flex-1 overflow-auto relative scrollbar-none">
      <Transition name="fade-slide" mode="out-in">
        <!-- Month View -->
        <div v-if="viewMode === 'month'" :key="'month'" class="h-full flex flex-col">
          <div class="grid grid-cols-7 border-b border-stone-100 bg-stone-50/50">
            <div v-for="day in weekDays" :key="day" class="py-2 text-center text-[10px] font-bold text-stone-400 uppercase tracking-wider">
              {{ day }}
            </div>
          </div>
          <div class="flex-1 grid grid-cols-7 grid-rows-6">
            <div 
              v-for="(day, idx) in monthDays" 
              :key="idx"
              @dragover.prevent
              @drop="onDrop(day.date)"
              class="border-r border-b border-stone-50 p-2 min-h-[100px] transition-colors hover:bg-stone-50/30 group relative"
              :class="[
                !day.isCurrentMonth ? 'bg-stone-50/40' : '',
                day.isToday ? 'bg-blue-50/30' : ''
              ]"
            >
              <div class="flex justify-between items-start mb-1">
                <span 
                  :class="[
                    'text-xs font-medium w-6 h-6 flex items-center justify-center rounded-full transition-all duration-300',
                    day.isToday ? 'bg-blue-600 text-white shadow-lg shadow-blue-200 scale-110' : 'text-stone-500 group-hover:text-stone-900'
                  ]"
                >
                  {{ day.date.getDate() }}
                </span>
              </div>
              
              <div class="space-y-1">
                <div 
                  v-for="event in getEventsForDay(day.date)" 
                  :key="event.id"
                  draggable="true"
                  @dragstart="onDragStart(event)"
                  @click="$emit('select-event', event)"
                  :class="[
                    'px-2 py-0.5 text-[10px] font-medium rounded-md border truncate cursor-grab active:cursor-grabbing transition-all hover:brightness-95 hover:translate-x-0.5 shadow-sm',
                    getEventClasses(event.color)
                  ]"
                >
                  {{ event.title }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Agenda View -->
        <div v-else :key="'agenda'" class="p-6">
          <div v-if="sortedEvents.length === 0" class="flex flex-col items-center justify-center py-20 text-stone-400">
            <CalendarIcon class="w-12 h-12 mb-4 opacity-20" />
            <p class="text-sm">No events scheduled for this period.</p>
          </div>
          <div v-else class="max-w-3xl mx-auto space-y-8">
            <div v-for="(group, date) in groupedEvents" :key="date" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
              <h3 class="text-sm font-semibold text-stone-900 mb-4 sticky top-0 bg-white/80 backdrop-blur-md py-2 z-10 border-b border-stone-100">
                {{ formatDate(date) }}
              </h3>
              <div class="space-y-3">
                <div 
                  v-for="event in group" 
                  :key="event.id"
                  @click="$emit('select-event', event)"
                  class="flex items-center gap-4 p-3 rounded-xl border border-stone-100 hover:border-blue-200 hover:bg-blue-50/30 hover:shadow-md hover:shadow-blue-500/5 transition-all cursor-pointer group"
                >
                  <div :class="['w-1 h-10 rounded-full transition-all group-hover:scale-y-110', getEventBg(event.color)]"></div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                      <h4 class="text-sm font-medium text-stone-900 truncate group-hover:text-blue-700 transition-colors">{{ event.title }}</h4>
                      <span class="text-[10px] font-medium text-stone-400 uppercase">{{ formatTime(event.start) }}</span>
                    </div>
                    <p class="text-xs text-stone-500 truncate mt-0.5">{{ event.description }}</p>
                  </div>
                  <div class="opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                    <ChevronRight class="w-4 h-4 text-blue-500" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ChevronLeft, ChevronRight, Calendar as CalendarIcon, Clock } from 'lucide-vue-next'

const props = defineProps({
  events: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['select-event', 'range-change', 'event-dropped'])

const draggedEvent = ref(null)

const viewMode = ref('month')
const views = [
  { id: 'month', label: 'Month' },
  { id: 'agenda', label: 'Agenda' }
]

const currentDate = ref(new Date())
const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const currentPeriodLabel = computed(() => {
  return currentDate.value.toLocaleString('default', { month: 'long', year: 'numeric' })
})

const monthDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  
  const days = []
  
  // Padding from prev month
  const startPadding = firstDay.getDay()
  for (let i = startPadding - 1; i >= 0; i--) {
    days.push({
      date: new Date(year, month, -i),
      isCurrentMonth: false
    })
  }
  
  // Current month
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const d = new Date(year, month, i)
    days.push({
      date: d,
      isCurrentMonth: true,
      isToday: isSameDay(d, new Date())
    })
  }
  
  // Padding to next month (to fill 6 rows)
  const remaining = 42 - days.length
  for (let i = 1; i <= remaining; i++) {
    days.push({
      date: new Date(year, month + 1, i),
      isCurrentMonth: false
    })
  }
  
  return days
})

const sortedEvents = computed(() => {
  return [...props.events].sort((a, b) => new Date(a.start) - new Date(b.start))
})

const groupedEvents = computed(() => {
  const groups = {}
  sortedEvents.value.forEach(event => {
    const dateKey = new Date(event.start).toDateString()
    if (!groups[dateKey]) groups[dateKey] = []
    groups[dateKey].push(event)
  })
  return groups
})

function navigate(delta) {
  const d = new Date(currentDate.value)
  d.setMonth(d.getMonth() + delta)
  currentDate.value = d
  emitRangeChange()
}

function goToToday() {
  currentDate.value = new Date()
  emitRangeChange()
}

function emitRangeChange() {
  const start = monthDays.value[0].date.toISOString().split('T')[0]
  const end = monthDays.value[monthDays.value.length - 1].date.toISOString().split('T')[0]
  emit('range-change', { start, end })
}

function onDragStart(event) {
  draggedEvent.value = event
}

function onDrop(date) {
  if (!draggedEvent.value) return
  
  const event = draggedEvent.value
  const newDate = new Date(date)
  const oldDate = new Date(event.start)
  
  // Keep the original time
  newDate.setHours(oldDate.getHours())
  newDate.setMinutes(oldDate.getMinutes())
  
  emit('event-dropped', {
    event: event,
    newDate: newDate.toISOString()
  })
  
  draggedEvent.value = null
}

function isSameDay(d1, d2) {
  return d1.getFullYear() === d2.getFullYear() &&
         d1.getMonth() === d2.getMonth() &&
         d1.getDate() === d2.getDate()
}

function getEventsForDay(date) {
  return props.events.filter(e => isSameDay(new Date(e.start), date))
}

function getEventClasses(color) {
  const map = {
    blue: 'bg-blue-50 text-blue-700 border-blue-100',
    purple: 'bg-purple-50 text-purple-700 border-purple-100',
    emerald: 'bg-emerald-50 text-emerald-700 border-emerald-100',
    amber: 'bg-amber-50 text-amber-700 border-amber-100',
    sky: 'bg-sky-50 text-sky-700 border-sky-100',
    rose: 'bg-rose-50 text-rose-700 border-rose-100',
  }
  return map[color] || map.blue
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
  return map[color] || map.blue
}

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('default', { weekday: 'long', day: 'numeric', month: 'long' })
}

function formatTime(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleTimeString('default', { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(10px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}
</style>
