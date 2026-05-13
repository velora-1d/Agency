<template>
  <div class="group relative flex gap-x-4">
    <div :class="[isLast ? 'h-6' : '-bottom-6', 'absolute left-0 top-0 flex w-10 justify-center']">
      <div class="w-px bg-stone-200" />
    </div>

    <div class="relative flex h-10 w-10 flex-none items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-stone-200 transition-all duration-300 group-hover:scale-110 group-hover:shadow-md">
      <component
        :is="getIcon(activity.metadata?.icon)"
        class="h-5 w-5"
        :class="getColorClass(activity.metadata?.color)"
      />
    </div>

    <div class="flex-auto rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_10px_30px_rgba(60,42,24,0.04)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_20px_50px_rgba(60,42,24,0.08)]">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <img
              :src="activity.user.avatar"
              :alt="activity.user.name"
              class="h-6 w-6 rounded-full ring-1 ring-stone-200"
            />
            <span class="text-sm font-semibold text-stone-950">{{ activity.user.name }}</span>
            <span class="text-xs text-stone-500">-</span>
            <span class="text-xs text-stone-500">{{ activity.created_at }}</span>
          </div>
          <h4 class="text-base font-medium leading-7 text-stone-900">
            {{ activity.description }}
          </h4>
        </div>

        <div v-if="activity.type" class="flex flex-col items-end gap-2 self-start">
          <span
            class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-medium uppercase tracking-[0.15em]"
            :class="getTypeBadgeClass(activity.type)"
          >
            {{ activity.type }}
          </span>

          <Link
            v-if="getSubjectRoute(activity)"
            :href="getSubjectRoute(activity)"
            class="text-[10px] font-bold uppercase tracking-widest text-stone-400 transition hover:text-amber-600"
          >
            View Detail ->
          </Link>
        </div>
      </div>

      <div v-if="activity.comments && activity.comments.length > 0" class="mt-6 space-y-4">
        <div class="h-px bg-stone-100" />
        <div class="space-y-4">
          <ActivityComment
            v-for="comment in activity.comments"
            :key="comment.id"
            :comment="comment"
          />
        </div>
      </div>

      <div class="relative">
        <div
          v-if="showMentions && filteredUsers.length > 0"
          class="animate-reveal absolute bottom-full left-11 z-50 mb-2 w-64 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-2xl"
        >
          <div class="border-b border-stone-100 bg-stone-50 px-4 py-2">
            <span class="text-[10px] font-bold uppercase tracking-widest text-stone-400">Sebut Anggota</span>
          </div>
          <ul class="max-h-48 overflow-y-auto">
            <li
              v-for="user in filteredUsers"
              :key="user.id"
              @click="insertMention(user)"
              class="flex cursor-pointer items-center gap-3 px-4 py-2.5 transition hover:bg-amber-50"
            >
              <img :src="`https://ui-avatars.com/api/?name=${user.name}&background=random`" class="h-6 w-6 rounded-full" />
              <span class="text-sm text-stone-700">{{ user.name }}</span>
            </li>
          </ul>
        </div>

        <form @submit.prevent="submitComment" class="mt-6 flex items-start gap-3">
          <img
            :src="page.props.auth.user.avatar_url || `https://ui-avatars.com/api/?name=${page.props.auth.user.name}`"
            class="mt-1 h-8 w-8 rounded-full bg-stone-100 ring-1 ring-stone-200"
          />
          <div class="relative flex-auto">
            <textarea
              ref="textareaRef"
              v-model="form.content"
              rows="1"
              class="w-full resize-none rounded-2xl border-stone-200 bg-stone-50 px-4 py-2 text-sm transition-all focus:border-amber-300 focus:bg-white focus:ring-0"
              @input="handleInput"
              @keydown.enter.prevent="submitComment"
              @keydown.esc="showMentions = false"
            ></textarea>
            <div class="absolute right-2 top-1.5 flex gap-1">
              <button type="button" class="rounded-lg p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-600">
                <Smile class="h-5 w-5" />
              </button>
              <button type="button" class="rounded-lg p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-600">
                <Paperclip class="h-5 w-5" />
              </button>
            </div>
            <p v-if="form.errors.content" class="mt-1 px-2 text-xs text-rose-500">{{ form.errors.content }}</p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  Activity,
  Calendar,
  CheckCircle,
  CreditCard,
  FolderPlus,
  Paperclip,
  Smile,
  UserCheck,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import ActivityComment from './ActivityComment.vue'

const props = defineProps({
  activity: {
    type: Object,
    required: true,
  },
  isLast: {
    type: Boolean,
    default: false,
  },
})

const page = usePage()
const workspace = page.props.workspace
const workspaceUsers = page.props.filters?.options?.users || []

const form = useForm({
  content: '',
})

const textareaRef = ref(null)
const showMentions = ref(false)
const mentionQuery = ref('')

const filteredUsers = computed(() => {
  if (!mentionQuery.value) return workspaceUsers
  return workspaceUsers.filter((user) => user.name.toLowerCase().includes(mentionQuery.value.toLowerCase()))
})

function handleInput(event) {
  const content = event.target.value
  const cursorPosition = event.target.selectionStart
  const textBeforeCursor = content.substring(0, cursorPosition)
  const lastAtSymbol = textBeforeCursor.lastIndexOf('@')

  if (lastAtSymbol !== -1) {
    const query = textBeforeCursor.substring(lastAtSymbol + 1)

    if (!query.includes(' ')) {
      showMentions.value = true
      mentionQuery.value = query
      return
    }
  }

  showMentions.value = false
}

function insertMention(user) {
  const content = form.content
  const cursorPosition = textareaRef.value.selectionStart
  const textBeforeCursor = content.substring(0, cursorPosition)
  const textAfterCursor = content.substring(cursorPosition)
  const lastAtSymbol = textBeforeCursor.lastIndexOf('@')

  form.content = `${textBeforeCursor.substring(0, lastAtSymbol)}@${user.name} ${textAfterCursor}`
  showMentions.value = false

  window.setTimeout(() => {
    textareaRef.value.focus()
  }, 0)
}

function submitComment() {
  if (!form.content.trim() || form.processing) return

  form.post(route('workspace.communication.activity-feed.comment', {
    workspace: workspace.slug,
    activity: props.activity.id,
  }), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    },
  })
}

function getSubjectRoute(activity) {
  if (!activity.subject_type || !activity.subject_id) return null

  const type = activity.subject_type.split('\\').pop().toLowerCase()

  try {
    switch (type) {
      case 'lead':
        return route('workspace.crm.leads.show', { workspace: workspace.slug, lead: activity.subject_id })
      case 'project':
        return route('workspace.projects.show', { workspace: workspace.slug, project: activity.subject_id })
      case 'invoice':
        return route('workspace.finance.overview', { workspace: workspace.slug })
      case 'task':
        return route('workspace.projects.index', { workspace: workspace.slug })
      case 'meeting':
        return route('workspace.communication.calendar', { workspace: workspace.slug })
      default:
        return null
    }
  } catch {
    return null
  }
}

function getIcon(name) {
  const icons = {
    FolderPlus,
    UserCheck,
    CreditCard,
    CheckCircle,
    Calendar,
    Activity,
  }

  return icons[name] || Activity
}

function getColorClass(color) {
  return {
    blue: 'text-blue-500',
    green: 'text-green-500',
    emerald: 'text-emerald-500',
    purple: 'text-purple-500',
    orange: 'text-orange-500',
    slate: 'text-slate-500',
  }[color] || 'text-stone-500'
}

function getTypeBadgeClass(type) {
  return {
    project: 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10',
    lead: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-700/10',
    invoice: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-700/10',
    task: 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-700/10',
    meeting: 'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-700/10',
  }[type] || 'bg-stone-50 text-stone-600 ring-1 ring-inset ring-stone-500/10'
}
</script>
