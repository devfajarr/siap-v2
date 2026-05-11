<script setup>
import { computed } from 'vue'
import { SwitchRoot, SwitchThumb } from 'radix-vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  class: { type: null, default: '' }
})

const emits = defineEmits(['update:modelValue'])

const modelValue = computed({
  get: () => props.modelValue,
  set: (val) => emits('update:modelValue', val)
})
</script>

<template>
  <SwitchRoot
    v-model:checked="modelValue"
    :disabled="disabled"
    :class="cn(
      'peer inline-flex h-[24px] w-[44px] shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-[#4B49AC] data-[state=unchecked]:bg-gray-200',
      props.class
    )"
  >
    <SwitchThumb
      :class="cn(
        'pointer-events-none block h-5 w-5 rounded-full bg-white shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0'
      )"
    />
  </SwitchRoot>
</template>
