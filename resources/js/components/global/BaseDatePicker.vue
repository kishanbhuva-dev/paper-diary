<template>
  <div class="w-full max-w-xs min-w-[100px]">
    <label
      :for="labelSlug"
      class="block text-sm font-medium text-gray-500"
    >
      {{ label }}
    </label>
    <input
      :id="labelSlug"
      ref="dateInput"
      v-model="model"
      type="date"
      class="ease w-full cursor-pointer appearance-none rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium shadow-sm transition duration-300 placeholder:text-gray-400 hover:border-gray-400 focus:border-gray-400 focus:shadow-md focus:outline-none"
      @click="$refs.dateInput.showPicker()"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: { type: String, required: false, default: '' },
});

const model = defineModel({
  type: [String, Number],
  default: null,
});

const labelSlug = computed(() => props.label.toLowerCase().replace(/\s+/g, '-'));
</script>
