<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { WifiOff, RefreshCw } from 'lucide-vue-next';
import { ref } from 'vue';

const isRetrying = ref(false);

const emit = defineEmits<{
    (e: 'retry'): void;
}>();

function handleRetry() {
    isRetrying.value = true;
    emit('retry');
    
    // Reset setelah 2 detik
    setTimeout(() => {
        isRetrying.value = false;
    }, 2000);
}
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-background p-4">
        <div class="text-center space-y-6 max-w-md">
            <!-- Icon -->
            <div class="flex justify-center">
                <div class="w-24 h-24 rounded-full bg-muted flex items-center justify-center">
                    <WifiOff class="w-12 h-12 text-muted-foreground" />
                </div>
            </div>
            
            <!-- Text -->
            <div class="space-y-2">
                <h1 class="text-2xl font-bold text-foreground">
                    Tidak Ada Koneksi Internet
                </h1>
                <p class="text-muted-foreground">
                    Periksa koneksi internet Anda dan coba lagi.
                </p>
            </div>
            
            <!-- Retry Button -->
            <Button
                size="lg"
                :disabled="isRetrying"
                @click="handleRetry"
                class="gap-2"
            >
                <RefreshCw :class="{ 'animate-spin': isRetrying }" class="w-4 h-4" />
                {{ isRetrying ? 'Mencoba...' : 'Coba Lagi' }}
            </Button>
        </div>
    </div>
</template>
