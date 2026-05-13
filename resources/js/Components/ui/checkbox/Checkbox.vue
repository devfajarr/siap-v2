<script setup>
import { computed } from "vue";
import { useVModel } from "@vueuse/core";
import { Check } from "lucide-vue-next";
import {
  CheckboxIndicator,
  CheckboxRoot,
} from "reka-ui";
import { cn } from "@/lib/utils";

const props = defineProps({
  checked: { type: [Boolean, String], required: false },
  disabled: { type: Boolean, required: false },
  required: { type: Boolean, required: false },
  name: { type: String, required: false },
  value: { type: String, required: false },
  id: { type: String, required: false },
  class: { type: null, required: false },
});

const emits = defineEmits(["update:checked"]);

const modelValue = useVModel(props, "checked", emits, {
  passive: true,
  defaultValue: false,
});
</script>

<template>
  <CheckboxRoot
    v-model:checked="modelValue"
    :disabled="disabled"
    :required="required"
    :name="name"
    :value="value"
    :id="id"
    :class="
      cn(
        'peer h-4 w-4 shrink-0 rounded-sm border border-[#4B49AC] ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-[#4B49AC] data-[state=checked]:text-white transition-colors cursor-pointer',
        props.class
      )
    "
  >
    <CheckboxIndicator
      class="flex items-center justify-center text-current"
    >
      <slot>
        <Check class="h-3.5 w-3.5 stroke-[3]" />
      </slot>
    </CheckboxIndicator>
  </CheckboxRoot>
</template>
