<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { onMounted, onUnmounted } from 'vue'

// Hover prefetch - only prefetch when user hovers over link
let hoverListeners: Array<() => void> = []

onMounted(() => {
  // Delay to ensure page is rendered
  setTimeout(() => {
    const links = document.querySelectorAll('a[href^="/"]')
    const prefetched = new Set<string>()
    
    links.forEach((link) => {
      const href = link.getAttribute('href')
      if (!href || !href.startsWith('/') || href.startsWith('//')) return
      if (prefetched.has(href)) return
      
      // Only prefetch on hover
      const handleMouseEnter = () => {
        if (prefetched.has(href)) return
        
        const method = link.getAttribute('data-method')
        if (!method || method === 'get') {
          prefetched.add(href)
          router.prefetch(href, { method: 'get' })
          // console.log removed for production
        }
      }
      
      link.addEventListener('mouseenter', handleMouseEnter)
      hoverListeners.push(() => link.removeEventListener('mouseenter', handleMouseEnter))
    })
  }, 100)
})

onUnmounted(() => {
  hoverListeners.forEach(cleanup => cleanup())
})
</script>

<template>
  <!-- Component provides hover prefetch functionality -->
</template>
