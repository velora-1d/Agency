<template>
  <Head :title="`Detail Post: ${post.title || 'Untitled'}`" />

  <WorkspaceLayout :title="post.title || 'Untitled Post'" subtitle="Detail konten, pratinjau caption, dan analitik interaksi post sosial media.">
    <template #actions>
      <div class="flex gap-2">
        <button
          type="button"
          @click="router.get(route('workspace.marketing.index', workspace.slug))"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-stone-950"
        >
          <ArrowLeft class="h-4 w-4" />
          <span>Kembali</span>
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Jangkauan (Reach)</p>
          <p class="mt-3 text-3xl font-semibold text-stone-950">{{ post.reach }}</p>
          <p class="mt-1 text-xs text-stone-500">Unique accounts reached</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Interaksi</p>
          <p class="mt-3 text-3xl font-semibold text-stone-950">{{ post.engagement }}</p>
          <p class="mt-1 text-xs text-stone-500">Likes, Comments, Shares</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Klik Link</p>
          <p class="mt-3 text-3xl font-semibold text-stone-950">{{ post.clicks }}</p>
          <p class="mt-1 text-xs text-stone-500">Traffic ke website</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Status Post</p>
          <div class="mt-3">
              <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest" :class="statusClass(post.status)">
                {{ translateStatus(post.status) }}
              </span>
          </div>
          <p class="mt-2 text-xs text-stone-500">{{ post.posted_at ? `Diposting: ${post.posted_at}` : `Jadwal: ${post.scheduled_at || '-'}` }}</p>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2 space-y-6">
            <article class="rounded-[2.2rem] border border-stone-200 bg-white overflow-hidden shadow-sm">
                <div class="bg-stone-50 px-8 py-5 border-b border-stone-100 flex items-center justify-between">
                    <h4 class="text-sm font-bold uppercase tracking-widest text-stone-500">Pratinjau Konten</h4>
                    <div class="flex gap-2">
                        <span v-for="p in post.platforms" :key="p" class="text-[10px] font-bold uppercase px-2 py-0.5 bg-stone-200 text-stone-600 rounded-md">
                            {{ p }}
                        </span>
                    </div>
                </div>
                <div class="p-8">
                    <div class="max-w-md mx-auto rounded-3xl border border-stone-100 bg-stone-50 p-6 shadow-inner">
                        <p class="text-sm text-stone-800 leading-relaxed whitespace-pre-wrap">{{ post.caption }}</p>
                        <p class="mt-4 text-sm font-medium text-sky-600">{{ post.hashtags }}</p>
                    </div>
                </div>
            </article>
        </section>

        <aside class="space-y-6">
            <article class="rounded-[2.2rem] border border-stone-200 bg-white p-6 shadow-sm">
                <h4 class="text-sm font-bold uppercase tracking-widest text-stone-400 mb-4">Detail Publikasi</h4>
                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-sm">
                        <Calendar class="h-4 w-4 text-stone-400" />
                        <span class="text-stone-600">Dijadwalkan: {{ post.scheduled_at || 'Manual' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <Clock class="h-4 w-4 text-stone-400" />
                        <span class="text-stone-600">Terakhir Update: {{ post.updated_at_label }}</span>
                    </div>
                </div>
                
                <div class="mt-8">
                    <button class="w-full rounded-2xl bg-stone-950 py-3 text-sm font-bold text-white transition hover:bg-stone-800">
                        Post Sekarang
                    </button>
                </div>
            </article>
        </aside>
      </div>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import {
  ArrowLeft,
  Calendar,
  Clock,
  Instagram,
  Twitter,
  Linkedin,
  Facebook
} from 'lucide-vue-next'
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: Object,
  post: Object,
})

function translateStatus(status) {
    const map = {
        idea: 'Ide',
        draft: 'Draf',
        review: 'Review',
        scheduled: 'Terjadwal',
        posted: 'Diposting'
    }
    return map[status] || status
}

function statusClass(status) {
    const map = {
        idea: 'bg-stone-100 text-stone-600',
        draft: 'bg-amber-100 text-amber-700',
        review: 'bg-blue-100 text-blue-700',
        scheduled: 'bg-emerald-100 text-emerald-700',
        posted: 'bg-stone-900 text-white'
    }
    return map[status] || 'bg-stone-100 text-stone-600'
}
</script>
