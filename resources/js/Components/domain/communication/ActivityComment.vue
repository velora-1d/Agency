<template>
  <div class="flex gap-3 animate-slide-in">
    <img 
      :src="comment.user.avatar" 
      :alt="comment.user.name"
      class="mt-1 h-6 w-6 flex-none rounded-full bg-stone-100 ring-1 ring-stone-200"
    />
    <div class="flex-auto space-y-1">
      <div class="flex items-center gap-2">
        <span class="text-xs font-semibold text-stone-950">{{ comment.user.name }}</span>
        <span class="text-[10px] text-stone-500">{{ comment.created_at }}</span>
      </div>
      <div class="rounded-2xl bg-stone-50 px-4 py-2 text-sm text-stone-700 transition-all hover:bg-stone-100">
        {{ comment.content }}
      </div>
      <div class="flex items-center gap-3 px-2">
        <button class="text-[10px] font-medium text-stone-500 hover:text-amber-600 uppercase tracking-wider">Balas</button>
        <button class="text-[10px] font-medium text-stone-500 hover:text-amber-600 uppercase tracking-wider">Suka</button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  comment: {
    type: Object,
    required: true
  }
})
</script>

<style scoped>
.animate-slide-in {
  animation: slide-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes slide-in {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
