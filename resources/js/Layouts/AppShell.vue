<template>
  <section class="h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,rgba(185,129,35,0.12),transparent_28%),linear-gradient(180deg,#f7f1e8_0%,#f4ecdf_100%)] text-stone-950">
    <div class="flex h-full w-full overflow-hidden">
      <!-- Sidebar: Mentok Kiri (Flush Left) -->
      <aside class="flex w-[280px] flex-col bg-[#1f1a17] text-stone-50 shadow-[4px_0_24px_rgba(0,0,0,0.1)] h-full">
        <div class="border-b border-white/10 px-6 py-8">
          <p class="text-sm font-medium uppercase tracking-[0.4em] text-amber-300">Kantor Digital</p>
          <!-- Branded workspace name removed as per request -->
        </div>

        <div class="scrollbar-none space-y-7 px-4 py-8 lg:flex-1 lg:overflow-y-auto">
          <section v-for="group in navigation" :key="group.section" class="space-y-3">
            <p class="px-2 text-[10px] font-medium uppercase tracking-[0.3em] text-stone-500">
              {{ group.section }}
            </p>

            <div class="space-y-1">
              <Link
                v-for="item in group.items"
                :key="`${group.section}-${item.label}`"
                :href="item.href || '#'"
                class="flex items-center justify-between rounded-xl px-4 py-3.5 text-sm transition-all duration-300 transform active:scale-95"
                :class="item.active
                  ? 'bg-amber-200 text-stone-950 shadow-lg shadow-amber-900/20 translate-x-1'
                  : 'text-stone-400 hover:bg-white/5 hover:text-stone-100'"
              >
                <span class="font-medium">{{ item.label }}</span>
                <span
                  v-if="item.active"
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
        <header class="relative z-50 border-b border-stone-200/60 bg-white/50 px-8 py-3 backdrop-blur-md">
          <div class="mx-auto max-w-full">
            <div class="flex flex-col gap-2 xl:flex-row xl:items-end xl:justify-between">
              <div class="space-y-1">
                <p class="text-[9px] font-bold uppercase tracking-[0.32em] text-amber-700">{{ workspaceName }} / Dashboard</p>
                <div>
                  <h1 class="text-xl font-bold tracking-tight text-stone-950 lg:text-2xl">{{ title }}</h1>
                  <p class="mt-0.5 max-w-3xl text-[10px] leading-relaxed text-stone-500">{{ subtitle }}</p>
                </div>
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
  </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

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
</script>
