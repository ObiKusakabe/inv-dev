<script setup lang="ts">
import type { ComboboxInputEmits, ComboboxInputProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { SearchIcon } from "lucide-vue-next"
import { ComboboxInput, useForwardPropsEmits } from "reka-ui"
import { cn } from "@/lib/utils"

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<ComboboxInputProps & {
  class?: HTMLAttributes["class"]
}>()

const emits = defineEmits<ComboboxInputEmits>()

const delegatedProps = reactiveOmit(props, "class")

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <div
    data-slot="command-input-wrapper"
    class="flex items-center border-b px-3"
  >
    <SearchIcon class="mr-2 h-4 w-4 shrink-0 opacity-50" />
    <ComboboxInput
      data-slot="command-input"
      :class="cn(
        'placeholder:text-muted-foreground flex h-9 w-full rounded-md bg-transparent py-2 text-sm outline-none disabled:cursor-not-allowed disabled:opacity-50',
        props.class,
      )"

      v-bind="{ ...$attrs, ...forwarded }"
    >
      <slot />
    </ComboboxInput>
  </div>
</template>
