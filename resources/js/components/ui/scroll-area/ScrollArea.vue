<script setup lang="ts">
import { cn } from '@/lib/utils'

interface Props {
  class?: string
}

const props = defineProps<Props>()
</script>

<template>
  <div
    :class="cn(
      'relative overflow-hidden',
      props.class
    )"
  >
    <div
      class="h-full w-full overflow-y-auto overflow-x-hidden custom-scrollbar"
    >
      <slot />
    </div>
  </div>
</template>

<style scoped>
/* Smaller Sidebar Scrollbar - 4px width, dark track */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
  height: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: hsl(var(--muted) / 0.15);
  border-radius: 9999px;
  margin: 2px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.3);
  border-radius: 9999px;
  transition: background 0.2s ease;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--muted-foreground) / 0.5);
}

.custom-scrollbar::-webkit-scrollbar-thumb:active {
  background: hsl(var(--muted-foreground) / 0.7);
}

/* Firefox */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: hsl(var(--muted-foreground) / 0.3) hsl(var(--muted) / 0.15);
}
</style>
