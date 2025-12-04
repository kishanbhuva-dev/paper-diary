<template>
  <div class="w-full max-w-xs min-w-[100px]">
    <label :for="labelSlug" class="block text-sm text-gray-500 font-medium">
      {{ label }}
    </label>
    <input
      ref="dateInput"
      :id="labelSlug"
      v-model="model"
      type="date"
      @click="$refs.dateInput.showPicker()"
      class="w-full bg-white placeholder:text-gray-400 text-sm font-medium border border-gray-200 rounded-lg px-3 py-2 transition duration-300 ease focus:outline-none focus:border-gray-400 hover:border-gray-400 shadow-sm focus:shadow-md appearance-none cursor-pointer"
    />
  </div>
</template>

<script setup>
import { computed } from "vue";

const model = defineModel();

const props = defineProps({
  label: { type: String, required: true, default: "Label" },
});

const labelSlug = computed(() => {
  return props.label.toLowerCase().replace(/\s+/g, "-");
});
</script>
