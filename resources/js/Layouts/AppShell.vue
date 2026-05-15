<template>
  <section class="h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,rgba(185,129,35,0.12),transparent_28%),linear-gradient(180deg,#f7f1e8_0%,#f4ecdf_100%)] text-stone-950">
    <div class="flex h-full w-full overflow-hidden">
      <!-- Sidebar: Mentok Kiri (Flush Left) -->
      <aside class="flex h-full w-[296px] flex-col bg-[#1f1a17] text-stone-50 shadow-[4px_0_24px_rgba(0,0,0,0.1)]">
        <div class="border-b border-white/10 px-6 py-8">
          <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-stone-500 mb-2">Kantor Digital</p>
          
          <!-- Workspace Switcher in Sidebar -->
          <div class="relative">
            <button 
                v-if="page.props.auth.workspaces.length > 1"
                @click="showWorkspaceDropdown = !showWorkspaceDropdown"
                class="group flex w-full items-center justify-between rounded-2xl bg-white/5 border border-white/10 px-4 py-3 text-left transition-all hover:bg-white/10 hover:border-amber-500/30"
            >
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-stone-950 shadow-lg shadow-amber-500/20">
                        <ShieldCheck class="h-4 w-4" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-white">{{ workspaceName }}</p>
                        <p class="text-[9px] font-bold uppercase tracking-widest text-amber-500/60">Owner / Master</p>
                    </div>
                </div>
                <ChevronDown class="h-4 w-4 text-stone-500 transition-transform duration-300" :class="{ 'rotate-180': showWorkspaceDropdown }" />
            </button>
            <div v-else class="flex items-center gap-3 px-1">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/5 text-amber-400">
                    <AppWindowMac class="h-5 w-5" />
                </div>
                <p class="text-sm font-bold text-white">{{ workspaceName }}</p>
            </div>

            <!-- Sidebar Workspace Dropdown -->
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                enter-to-class="transform scale-100 opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="transform scale-100 opacity-100 translate-y-0"
                leave-to-class="transform scale-95 opacity-0 -translate-y-2"
            >
                <div v-if="showWorkspaceDropdown" class="absolute left-0 top-full mt-2 w-full origin-top rounded-2xl border border-stone-800 bg-[#2a2420] p-2 shadow-2xl z-[100]">
                    <div class="px-3 py-2 border-b border-white/5 mb-1">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-stone-500">Pindah Kantor</p>
                    </div>
                    <div class="space-y-1">
                        <button
                            v-for="w in page.props.auth.workspaces"
                            :key="w.id"
                            @click="switchWorkspace(w)"
                            class="w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center justify-between group"
                            :class="w.slug === workspaceSlug ? 'bg-amber-500 text-stone-950' : 'text-stone-400 hover:bg-white/5 hover:text-white'"
                        >
                            <span class="truncate">{{ w.name }}</span>
                            <div v-if="w.slug === workspaceSlug" class="h-1.5 w-1.5 rounded-full bg-stone-950"></div>
                        </button>
                    </div>
                </div>
            </Transition>
          </div>
        </div>

        <div class="scrollbar-none space-y-4 px-4 py-8 lg:flex-1 lg:overflow-y-auto">
          <section
            v-for="group in navigation"
            :key="group.section"
            :class="group.layout === 'tabs'
              ? 'rounded-[1.4rem] border border-white/8 bg-white/[0.04] p-2.5'
              : 'rounded-[1.4rem] border border-white/8 bg-white/[0.03] p-2.5'"
          >
            <p class="px-2 py-1 text-[10px] font-medium uppercase tracking-[0.3em] text-stone-500">
              {{ group.section }}
            </p>

            <div :class="group.layout === 'tabs' ? 'space-y-2 rounded-[1.1rem] bg-black/15 p-1.5' : 'space-y-1'">
              <Link
                v-for="item in group.items"
                :key="`${group.section}-${item.label}`"
                :href="item.href || '#'"
                :class="[
                  'transition-all duration-300 active:scale-[0.98]',
                  group.layout === 'tabs'
                    ? 'flex w-full items-center justify-between gap-3 rounded-[1rem] px-4 py-3 text-sm'
                    : 'flex items-center justify-between gap-3 rounded-xl px-4 py-3.5 text-sm',
                  group.layout === 'tabs'
                    ? (item.active ? 'bg-amber-200 text-stone-950 shadow-lg shadow-amber-900/20' : 'text-stone-400 hover:bg-white/5 hover:text-stone-100')
                    : (item.active ? 'bg-amber-200 text-stone-950 shadow-lg shadow-amber-900/20 translate-x-1' : 'text-stone-400 hover:bg-white/5 hover:text-stone-100'),
                ]"
              >
                <div class="flex min-w-0 items-center gap-3">
                  <span
                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                    :class="group.layout === 'tabs'
                      ? (item.active ? 'bg-stone-950/10 text-stone-950' : 'bg-white/10 text-stone-300')
                      : (item.active ? 'bg-stone-950/10 text-stone-950' : 'bg-white/5 text-stone-400')"
                  >
                    <component :is="resolveNavIcon(item.icon)" class="h-4 w-4" />
                  </span>
                  <span class="min-w-0 truncate font-semibold">{{ item.nav_label || item.label }}</span>
                </div>
                <span
                  v-if="item.active && group.layout !== 'tabs'"
                  class="h-1.5 w-1.5 rounded-full bg-stone-950 animate-pulse"
                />
              </Link>
            </div>
          </section>
        </div>

        <!-- Optional: Footer sidebar for user profile or settings could go here -->
      </aside>

      <!-- Main Content Area -->
      <div class="flex flex-1 flex-col gap-4 overflow-hidden">
        <header class="relative z-50 border-b border-stone-200/60 bg-white/50 px-8 py-4 backdrop-blur-md">
          <div class="mx-auto max-w-full">
            <div class="flex flex-col gap-2 xl:flex-row xl:items-center xl:justify-between">
              <div>
                <h1 class="text-xl font-bold tracking-tight text-stone-950 lg:text-2xl">{{ title }}</h1>
                <p class="mt-0.5 max-w-3xl text-[10px] leading-relaxed text-stone-500 uppercase tracking-widest font-semibold">{{ subtitle }}</p>
              </div>

              <div class="flex flex-wrap gap-2">
                <slot name="actions" />
              </div>
            </div>
          </div>
        </header>

        <main class="scrollbar-none flex-1 overflow-y-auto px-8 pb-8">
          <div class="mx-auto max-w-full">
            <slot />
          </div>
        </main>
      </div>
    </div>

    <!-- Modal Verifikasi Keamanan -->
    <SecurityVerificationModal 
      :show="showVerificationModal"
      :target-workspace-slug="pendingWorkspace?.slug"
      :target-workspace-name="pendingWorkspace?.name"
      :workspace-slug="workspaceSlug"
      @close="showVerificationModal = false"
      @success="handleVerificationSuccess"
    />
  </section>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import {
  Activity,
  AppWindowMac,
  Bell,
  CalendarDays,
  FolderKanban,
  FolderSearch,
  Gauge,
  Inbox,
  LayoutDashboard,
  Megaphone,
  Settings2,
  ShieldCheck,
  UserRoundSearch,
  Users,
  WalletCards,
  Workflow,
  ChevronDown,
} from 'lucide-vue-next'
import { ref, onMounted } from 'vue'
import SecurityVerificationModal from '../Components/domain/system/SecurityVerificationModal.vue'

const page = usePage()
const showWorkspaceDropdown = ref(false)
const showVerificationModal = ref(false)
const pendingWorkspace = ref(null)

const navIcons = {
  activity: Activity,
  automation: Workflow,
  calendar: CalendarDays,
  clients: Users,
  contracts: ShieldCheck,
  dashboard: LayoutDashboard,
  files: FolderSearch,
  finance: WalletCards,
  inbox: Inbox,
  leads: UserRoundSearch,
  marketing: Megaphone,
  meetings: CalendarDays,
  notes: FolderKanban,
  notifications: Bell,
  projects: FolderKanban,
  system: Settings2,
  tasks: Gauge,
  tickets: ShieldCheck,
  websites: AppWindowMac,
}

defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  navigation: {
    type: Array,
    default: () => [],
  },
  workspaceName: {
    type: String,
    default: '',
  },
  workspaceSlug: {
    type: String,
    default: '',
  },
})

function resolveNavIcon(key) {
  return navIcons[key] || LayoutDashboard
}

async function switchWorkspace(workspace) {
  showWorkspaceDropdown.value = false
  
  // Cek apakah butuh verifikasi (30 menit)
  try {
    const response = await fetch(`/app/verification/check`, {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })
    const data = await response.json()
    
    if (data.is_valid) {
      window.location.href = `/w/${workspace.slug}/dashboard`
    } else {
      pendingWorkspace.value = workspace
      showVerificationModal.value = true
    }
  } catch (error) {
    // Fallback ke verifikasi jika api error
    pendingWorkspace.value = workspace
    showVerificationModal.value = true
  }
}

function handleVerificationSuccess() {
  if (pendingWorkspace.value) {
    window.location.href = `/w/${pendingWorkspace.value.slug}/dashboard`
  }
}
</script>
