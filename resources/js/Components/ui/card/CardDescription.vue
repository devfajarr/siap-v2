<script setup>
import { cn } from '@/lib/utils'

const props = defineProps({
  class: { type: null, required: false },
})
</script>

<template>
  <p :class="cn('text-sm text-[#6B7280]', props.class)">
    <slot />
  </p>
</template>
