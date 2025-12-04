<template>
  <div class="w-full max-w-xs min-w-[100px]">
    <label :for="labelSlug" class="block text-sm text-gray-500 font-medium">
      {{ label }}
    </label>
    <div class="relative">
      <select
        :id="labelSlug"
        v-model="model"
        class="w-full bg-white placeholder:text-gray-400 text-sm font-medium border border-gray-200 rounded-lg pl-3 pr-8 py-2 transition duration-300 ease focus:outline-none focus:border-gray-400 hover:border-gray-400 shadow-sm focus:shadow-md appearance-none cursor-pointer"
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
        class="h-5 w-5 ml-1 absolute top-2.5 right-2.5"
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
