<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

interface DataPoint {
  date: string
  [key: string]: string | number
}

interface Props {
  data: DataPoint[]
  categories: string[]
  index: string
  colors?: string[]
  showLegend?: boolean
  showTooltip?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  colors: () => ['#3b82f6', '#93c5fd', '#60a5fa', '#bfdbfe'],
  showLegend: true,
  showTooltip: true,
})

const containerRef = ref<HTMLElement | null>(null)
const tooltipRef = ref<HTMLElement | null>(null)
const tooltipData = ref<{ date: string; values: Record<string, number> } | null>(null)
const mouseX = ref(0)
const mouseY = ref(0)
const isHovering = ref(false)
const containerWidth = ref(0)
const containerHeight = ref(0)

// Chart dimensions
const margin = { top: 20, right: 20, bottom: 40, left: 20 }
const width = computed(() => containerWidth.value - margin.left - margin.right)
const height = computed(() => containerHeight.value - margin.top - margin.bottom)

// Calculate scales
const allValues = computed(() => {
  const values: number[] = []
  props.data.forEach((d) => {
    props.categories.forEach((cat) => {
      values.push(d[cat] as number)
    })
  })
  return values
})

const yMax = computed(() => Math.max(...allValues.value, 0) * 1.1)
const yMin = computed(() => 0)

const xScale = (index: number) => {
  if (props.data.length <= 1) return 0
  return (index / (props.data.length - 1)) * width.value
}

const yScale = (value: number) => {
  if (yMax.value === yMin.value) return height.value
  return height.value - ((value - yMin.value) / (yMax.value - yMin.value)) * height.value
}

// Generate smooth path using cubic bezier
const generatePath = (category: string) => {
  if (props.data.length === 0) return ''

  const points = props.data.map((d, i) => ({
    x: xScale(i),
    y: yScale(d[category] as number),
  }))

  if (points.length === 0) return ''
  if (points.length === 1) return `M ${points[0].x} ${points[0].y}`

  // Catmull-Rom to cubic bezier conversion for smooth curve
  const line = (pointA: typeof points[0], pointB: typeof points[0]) => {
    const lengthX = pointB.x - pointA.x
    const lengthY = pointB.y - pointA.y
    return {
      length: Math.sqrt(Math.pow(lengthX, 2) + Math.pow(lengthY, 2)),
      angle: Math.atan2(lengthY, lengthX),
    }
  }

  const controlPoint = (
    current: typeof points[0],
    previous: typeof points[0],
    next: typeof points[0],
    reverse?: boolean
  ) => {
    const p = previous || current
    const n = next || current
    const smoothing = 0.2
    const o = line(p, n)
    const angle = o.angle + (reverse ? Math.PI : 0)
    const length = o.length * smoothing
    const x = current.x + Math.cos(angle) * length
    const y = current.y + Math.sin(angle) * length
    return [x, y]
  }

  const bezierCommand = (point: typeof points[0], i: number, a: typeof points) => {
    const [cpsX, cpsY] = controlPoint(a[i - 1], a[i - 2], point)
    const [cpeX, cpeY] = controlPoint(point, a[i - 1], a[i + 1], true)
    return `C ${cpsX} ${cpsY}, ${cpeX} ${cpeY}, ${point.x} ${point.y}`
  }

  const d = points.reduce((acc, point, i, a) => {
    if (i === 0) return `M ${point.x} ${point.y}`
    return `${acc} ${bezierCommand(point, i, a)}`
  }, '')

  return d
}

// Generate area path (line + close at bottom)
const generateAreaPath = (category: string) => {
  const linePath = generatePath(category)
  if (!linePath) return ''

  const lastX = xScale(props.data.length - 1)
  const startX = xScale(0)

  return `${linePath} L ${lastX} ${height.value} L ${startX} ${height.value} Z`
}

// Handle mouse move for tooltip
const handleMouseMove = (e: MouseEvent) => {
  if (!containerRef.value || !props.showTooltip) return

  const rect = containerRef.value.getBoundingClientRect()
  const x = e.clientX - rect.left - margin.left
  const y = e.clientY - rect.top

  // Find nearest data point
  const dataIndex = Math.min(
    Math.max(0, Math.round((x / width.value) * (props.data.length - 1))),
    props.data.length - 1
  )

  const dataPoint = props.data[dataIndex]
  if (dataPoint) {
    const values: Record<string, number> = {}
    props.categories.forEach((cat) => {
      values[cat] = dataPoint[cat] as number
    })
    tooltipData.value = { date: dataPoint[props.index] as string, values }
    mouseX.value = e.clientX - rect.left
    mouseY.value = y
    isHovering.value = true
  }
}

const handleMouseLeave = () => {
  isHovering.value = false
  tooltipData.value = null
}

// Update dimensions on resize
const updateDimensions = () => {
  if (containerRef.value) {
    const rect = containerRef.value.getBoundingClientRect()
    containerWidth.value = rect.width
    containerHeight.value = rect.height
  }
}

let resizeObserver: ResizeObserver | null = null

onMounted(() => {
  updateDimensions()
  resizeObserver = new ResizeObserver(updateDimensions)
  if (containerRef.value) {
    resizeObserver.observe(containerRef.value)
  }
})

onUnmounted(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
})

// Format number with commas
const formatNumber = (num: number) => {
  return new Intl.NumberFormat('id-ID').format(num)
}
</script>

<template>
  <div class="relative w-full" style="height: 300px;">
    <!-- Legend -->
    <div v-if="showLegend" class="flex items-center gap-4 mb-4 text-sm">
      <div
        v-for="(category, i) in categories"
        :key="category"
        class="flex items-center gap-2"
      >
        <div
          class="w-3 h-3 rounded-sm"
          :style="{ backgroundColor: colors[i % colors.length] }"
        />
        <span class="text-muted-foreground capitalize">{{ category }}</span>
      </div>
    </div>

    <!-- Chart Container -->
    <div
      ref="containerRef"
      class="relative w-full h-full cursor-crosshair"
      @mousemove="handleMouseMove"
      @mouseleave="handleMouseLeave"
    >
      <svg
        :width="containerWidth"
        :height="containerHeight"
        class="overflow-visible"
      >
        <defs>
          <!-- Gradients for each category -->
          <linearGradient
            v-for="(category, i) in categories"
            :key="`gradient-${category}`"
            :id="`gradient-${category}`"
            x1="0"
            y1="0"
            x2="0"
            y2="1"
          >
            <stop
              offset="5%"
              :stop-color="colors[i % colors.length]"
              stop-opacity="0.3"
            />
            <stop
              offset="95%"
              :stop-color="colors[i % colors.length]"
              stop-opacity="0.05"
            />
          </linearGradient>
        </defs>

        <!-- Chart content with transform -->
        <g :transform="`translate(${margin.left}, ${margin.top})`">
          <!-- Y-axis grid lines -->
          <g class="stroke-border">
            <line
              v-for="i in 5"
              :key="`grid-${i}`"
              :x1="0"
              :y1="(height / 5) * (i - 1)"
              :x2="width"
              :y2="(height / 5) * (i - 1)"
              stroke-width="1"
              stroke-dasharray="4 4"
            />
          </g>

          <!-- Areas (filled) -->
          <g v-for="(category, i) in categories" :key="`area-${category}`">
            <path
              :d="generateAreaPath(category)"
              :fill="`url(#gradient-${category})`"
              stroke="none"
            />
          </g>

          <!-- Lines -->
          <g v-for="(category, i) in categories" :key="`line-${category}`">
            <path
              :d="generatePath(category)"
              fill="none"
              :stroke="colors[i % colors.length]"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </g>

          <!-- Data points -->
          <g v-for="(category, catIndex) in categories" :key="`points-${category}`">
            <circle
              v-for="(d, i) in data"
              :key="`point-${i}`"
              :cx="xScale(i)"
              :cy="yScale(d[category] as number)"
              r="3"
              :fill="colors[catIndex % colors.length]"
              stroke="white"
              stroke-width="2"
              class="transition-opacity duration-200"
              :class="{ 'opacity-0': !isHovering, 'opacity-100': isHovering }"
            />
          </g>

          <!-- Hover indicator line -->
          <line
            v-if="isHovering && tooltipData"
            :x1="xScale(data.findIndex((d) => d[index] === tooltipData?.date) ?? 0)"
            :y1="0"
            :x2="xScale(data.findIndex((d) => d[index] === tooltipData?.date) ?? 0)"
            :y2="height"
            stroke="currentColor"
            stroke-width="1"
            stroke-dasharray="5 5"
            class="text-muted-foreground"
          />
        </g>

        <!-- X-axis labels -->
        <g :transform="`translate(${margin.left}, ${containerHeight - margin.bottom + 20})`">
          <text
            v-for="(d, i) in data.filter((_, i) => i % Math.ceil(data.length / 7) === 0 || i === data.length - 1)"
            :key="`label-${i}`"
            :x="xScale(i)"
            y="0"
            text-anchor="middle"
            class="fill-muted-foreground text-xs"
          >
            {{ d[index] }}
          </text>
        </g>
      </svg>

      <!-- Tooltip -->
      <div
        v-if="showTooltip && tooltipData && isHovering"
        ref="tooltipRef"
        class="absolute z-50 pointer-events-none"
        :style="{
          left: `${Math.min(mouseX + 10, containerWidth - 150)}px`,
          top: `${Math.max(mouseY - 80, 10)}px`,
        }"
      >
        <div class="bg-background border rounded-lg shadow-lg p-3 min-w-[140px]">
          <div class="font-medium text-sm mb-2">{{ tooltipData.date }}</div>
          <div
            v-for="(category, i) in categories"
            :key="`tooltip-${category}`"
            class="flex items-center justify-between gap-3 text-sm"
          >
            <div class="flex items-center gap-2">
              <div
                class="w-2 h-2 rounded-sm"
                :style="{ backgroundColor: colors[i % colors.length] }"
              />
              <span class="text-muted-foreground capitalize">{{ category }}</span>
            </div>
            <span class="font-medium">{{ formatNumber(tooltipData.values[category]) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
