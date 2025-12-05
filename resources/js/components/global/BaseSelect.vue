<template>
  <div class="w-full max-w-xs min-w-[100px]">
    <label :for="labelSlug" class="block text-sm font-medium text-gray-500">
      {{ label }}
    </label>
    <div class="relative">
      <select
        :id="labelSlug"
        v-model="model"
        class="ease w-full cursor-pointer appearance-none rounded-lg border border-gray-200 bg-white py-2 pr-8 pl-3 text-sm font-medium shadow-sm transition duration-300 placeholder:text-gray-400 hover:border-gray-400 focus:border-gray-400 focus:shadow-md focus:outline-none"
      >
        <option value="">{{ placeholder }}</option>
        <option
          v-for="option in options"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
      <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.2"
        stroke="currentColor"
        class="absolute top-2.5 right-2.5 ml-1 h-5 w-5"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"
        />
      </svg>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const model = defineModel();

const props = defineProps({
  label: { type: String, required: true, default: "Label" },
  placeholder: { type: String, default: "Select an option" },
  options: { type: Array, required: true },
});

const labelSlug = computed(() => {
  return props.label.toLowerCase().replace(/\s+/g, "-");
});
</script>
