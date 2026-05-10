<script setup lang="ts">
import { cn } from '@/lib/utils'
import { type HTMLAttributes, computed } from 'vue'

const props = defineProps<{
  modelValue?: number | null
  class?: HTMLAttributes['class']
}>()

const progress = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined) return 0
  return Math.min(100, Math.max(0, props.modelValue))
})
</script>

<template>
  <div
    :class="cn('relative h-1 w-full overflow-hidden rounded-full bg-muted', props.class)"
  >
    <div
      class="h-full w-full flex-1 bg-primary transition-all duration-100 ease-linear"
      :style="{ transform: `translateX(-${100 - progress}%)` }"
    />
  </div>
</template>
