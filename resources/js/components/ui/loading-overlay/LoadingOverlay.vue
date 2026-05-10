<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Spinner } from '@/components/ui/spinner';

interface Props {
  show: boolean;
  message?: string;
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {
  message: 'Loading...',
});
</script>

<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 translate-y-4"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition-all duration-300 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-4"
  >
    <div
      v-if="show"
      :class="cn(
        'fixed bottom-4 right-4 z-[9999] flex items-center gap-3',
        'rounded-lg bg-card border shadow-lg px-4 py-3',
        props.class
      )"
    >
      <Spinner class="size-5 text-primary" />
      <p v-if="message" class="text-sm font-medium text-foreground">
        {{ message }}
      </p>
    </div>
  </Transition>
</template>
