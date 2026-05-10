import { ref, defineAsyncComponent } from 'vue'

// Lazy load dialog components only when needed
export const LazyDialog = defineAsyncComponent(() =>
    import('@/components/ui/dialog/index').then(m => m.Dialog)
)

export const LazyDialogContent = defineAsyncComponent(() =>
    import('@/components/ui/dialog/index').then(m => m.DialogContent)
)

export const LazyDialogHeader = defineAsyncComponent(() =>
    import('@/components/ui/dialog/index').then(m => m.DialogHeader)
)

export const LazyDialogTitle = defineAsyncComponent(() =>
    import('@/components/ui/dialog/index').then(m => m.DialogTitle)
)

export const LazyDialogDescription = defineAsyncComponent(() =>
    import('@/components/ui/dialog/index').then(m => m.DialogDescription)
)

export const LazyDialogFooter = defineAsyncComponent(() =>
    import('@/components/ui/dialog/index').then(m => m.DialogFooter)
)

// Hook for dialog state management
export function useLazyDialog() {
    const isOpen = ref(false)
    const isLoaded = ref(false)

    const open = () => {
        isLoaded.value = true
        isOpen.value = true
    }

    const close = () => {
        isOpen.value = false
    }

    return {
        isOpen,
        isLoaded,
        open,
        close
    }
}
