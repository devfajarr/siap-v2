<script setup>
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String
})
</script>

<template>
  <h3 :class="cn('text-lg font-semibold leading-none tracking-tight text-[#1F1F1F]', props.class)">
    <slot />
  </h3>
</template>
