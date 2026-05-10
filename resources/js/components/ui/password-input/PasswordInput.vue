<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    modelValue?: string;
    name?: string;
    id?: string;
    placeholder?: string;
    required?: boolean;
    autocomplete?: string;
    tabindex?: number | string;
    class?: string;
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>()

const showPassword = ref(false);

const inputType = computed(() => showPassword.value ? 'text' : 'password');

const toggleVisibility = () => {
    showPassword.value = !showPassword.value;
};

const updateValue = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emits('update:modelValue', target.value);
};
</script>

<template>
    <div class="relative">
        <Input
            :type="inputType"
            :name="name"
            :id="id"
            :placeholder="placeholder"
            :required="required"
            :autocomplete="autocomplete"
            :tabindex="tabindex"
            :model-value="modelValue"
            @update:model-value="(val) => emits('update:modelValue', val as string)"
            :class="cn('pr-10', props.class)"
        />
        <Button
            type="button"
            variant="ghost"
            size="icon"
            class="absolute right-0 top-0 h-9 w-9 px-2 py-1 hover:bg-transparent"
            @click="toggleVisibility"
            :tabindex="-1"
        >
            <Eye v-if="!showPassword" class="h-4 w-4 text-muted-foreground" />
            <EyeOff v-else class="h-4 w-4 text-muted-foreground" />
            <span class="sr-only">
                {{ showPassword ? 'Hide password' : 'Show password' }}
            </span>
        </Button>
    </div>
</template>
