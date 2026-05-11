<template>
  <Head :title="`Activity Feed - ${workspace.name}`" />

  <WorkspaceLayout
    title="Activity Feed"
    subtitle="Audit log dan interaksi sosial dalam workspace. Pantau setiap pergerakan pekerjaan, komentar, dan pembaruan status secara real-time."
    :workspace-name="workspace.name"
    :workspace-slug="workspace.slug"
    :navigation="navigation"
  >
    <template #actions>
      <div class="flex gap-3">
        <button class="rounded-full bg-stone-950 px-6 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-amber-200 shadow-lg transition hover:scale-105 hover:bg-stone-800 active:scale-95">
          Refresh Feed
        </button>
      </div>
    </template>

    <div class="space-y-8">
      <!-- Filters -->
      <section class="animate-reveal">
        <div class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.05)]">
          <div class="flex flex-wrap items-center gap-6">
            <div class="flex-auto min-w-[200px]">
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Filter Modul</label>
              <select 
                v-model="form.type"
                class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-2.5 text-sm text-stone-900 transition focus:border-amber-300 focus:bg-white focus:ring-0"
              >
                <option value="" class="bg-white text-stone-900">Semua Modul</option>
                <option v-for="type in filters.options.types" :key="type" :value="type" class="bg-white text-stone-900">{{ type.charAt(0).toUpperCase() + type.slice(1) }}</option>
              </select>
            </div>

            <div class="flex-auto min-w-[200px]">
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Filter Anggota</label>
              <select 
                v-model="form.user_id"
                class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-2.5 text-sm text-stone-900 transition focus:border-amber-300 focus:bg-white focus:ring-0"
              >
                <option value="" class="bg-white text-stone-900">Semua Anggota</option>
                <option v-for="user in filters.options.users" :key="user.id" :value="user.id" class="bg-white text-stone-900">{{ user.name }}</option>
              </select>
            </div>

            <div class="flex-auto min-w-[200px]">
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Rentang Tanggal</label>
              <div class="grid grid-cols-2 gap-2">
                <input 
                  v-model="form.date_from"
                  type="date" 
                  class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-2.5 text-xs transition focus:border-amber-300 focus:ring-0"
                />
                <input 
                  v-model="form.date_to"
                  type="date" 
                  class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-2.5 text-xs transition focus:border-amber-300 focus:ring-0"
                />
              </div>
            </div>

            <div class="flex items-end pt-5">
              <button 
                @click="resetFilters"
                class="rounded-full bg-stone-100 px-5 py-2.5 text-[10px] font-bold uppercase tracking-[0.15em] text-stone-500 transition hover:bg-stone-200 hover:text-stone-700"
              >
                Reset
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Feed Timeline -->
      <section class="max-w-7xl mx-auto py-4">
        <div v-if="activities.data.length" class="flow-root">
          <ul role="list" class="-mb-8">
            <li 
              v-for="(activity, index) in activities.data" 
              :key="activity.id" 
              class="pb-8 animate-reveal"
              :style="{ animationDelay: `${index * 0.1}s` }"
            >
              <ActivityItem 
                :activity="activity" 
                :is-last="index === activities.data.length - 1" 
              />
            </li>
          </ul>
          
          <!-- Pagination Placeholder -->
          <div v-if="activities.meta && activities.meta.last_page > 1" class="mt-12 flex justify-center animate-reveal" style="animation-delay: 0.5s">
             <nav class="flex items-center gap-2">
                <button 
                  v-for="link in (activities.meta?.links || [])" 
                  :key="link.label"
                  @click="goToPage(link.url)"
                  :disabled="!link.url || link.active"
                  class="rounded-xl px-4 py-2 text-xs font-semibold transition hover:scale-105 active:scale-95"
                  :class="[
                    link.active ? 'bg-stone-950 text-amber-200 shadow-md' : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200',
                    !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                  ]"
                  v-html="link.label"
                />
             </nav>
          </div>
        </div>

        <div v-else class="rounded-4xl border-2 border-dashed border-stone-200 bg-stone-50 py-20 text-center animate-reveal">
           <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-stone-100 text-stone-400">
             <ActivityIcon class="h-8 w-8" />
           </div>
           <h3 class="mt-4 text-lg font-semibold text-stone-900">Belum ada aktivitas</h3>
           <p class="mt-2 text-sm text-stone-500">Coba ubah filter atau tambahkan aktivitas baru dalam workspace.</p>
        </div>
      </section>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { reactive, watch, onMounted } from 'vue'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import ActivityItem from '../../Components/domain/communication/ActivityItem.vue'
import { Activity as ActivityIcon } from 'lucide-vue-next'
import debounce from 'lodash/debounce'

const props = defineProps({
  workspace: Object,
  navigation: Array,
  filters: Object,
  activities: Object,
})

const form = reactive({
  type: props.filters.values.type || '',
  user_id: props.filters.values.user_id || '',
  date_from: props.filters.values.date_from || '',
  date_to: props.filters.values.date_to || '',
})

onMounted(() => {
  // Real-time updates
  if (window.Echo) {
    window.Echo.private(`workspace.${props.workspace.id}`)
      .listen('.ActivityCreated', (e) => {
        // Refresh feed data when new activity is created
        router.reload({ 
          only: ['activities'],
          preserveScroll: true 
        })
      })
      .listen('.CommentAdded', (e) => {
        // Refresh feed data when new comment is added
        router.reload({ 
          only: ['activities'],
          preserveScroll: true 
        })
      })
  }
})

const submitFilters = debounce(() => {
  router.get(route('workspace.communication.activity-feed', props.workspace.slug), form, {
    preserveState: true,
    preserveScroll: true,
    only: ['activities', 'filters'],
  })
}, 300)

watch(form, () => {
  submitFilters()
})

function resetFilters() {
  form.type = ''
  form.user_id = ''
  form.date_from = ''
  form.date_to = ''
}

function goToPage(url) {
  if (!url) return
  router.get(url, form, {
    preserveState: true,
    preserveScroll: true,
    only: ['activities'],
  })
}
</script>

<style scoped>
.animate-reveal {
  opacity: 0;
  animation: reveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes reveal {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
