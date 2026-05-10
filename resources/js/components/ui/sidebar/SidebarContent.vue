<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { ScrollArea } from "@/components/ui/scroll-area"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <ScrollArea
    data-slot="sidebar-content"
    data-sidebar="content"
    :class="cn('flex min-h-0 flex-1 flex-col gap-2', props.class)"
  >
    <slot />
  </ScrollArea>
</template>
