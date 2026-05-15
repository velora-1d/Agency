<template>
  <AppShell
    :title="title"
    :subtitle="subtitle"
    :navigation="computedNavigation"
    :workspace-name="computedWorkspaceName"
    :workspace-slug="computedWorkspaceSlug"
  >
    <template #actions>
      <slot name="actions" />
    </template>
    <slot />
  </AppShell>
  <AiAssistant />
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppShell from './AppShell.vue'
import AiAssistant from '../Components/System/AiAssistant.vue'

const props = defineProps({
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
    default: null,
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

const page = usePage()

const computedNavigation = computed(() => props.navigation || page.props.navigation || [])
const computedWorkspaceName = computed(() => props.workspaceName || page.props.workspace?.name || '')
const computedWorkspaceSlug = computed(() => props.workspaceSlug || page.props.workspace?.slug || '')
</script>
