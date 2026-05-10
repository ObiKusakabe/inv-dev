<script setup lang="ts">
import { Link as InertiaLink } from '@inertiajs/vue3'

// Link wrapper dengan prefetch saat mount (bukan hover)
// Ini memastikan data di-cache saat page load, navigasi jadi cepat
defineProps<{
  href: string | object
  method?: 'get' | 'post' | 'put' | 'patch' | 'delete'
  as?: string
  class?: string
  replace?: boolean
  preserveScroll?: boolean
  preserveState?: boolean
  prefetch?: boolean | 'mount' | 'hover'
}>()
</script>

<template>
  <InertiaLink v-bind="$props" :prefetch="prefetch ?? 'hover'">
    <slot />
  </InertiaLink>
</template>
