<template>
  <div class="w-full max-w-xs min-w-[100px]">
    <label :for="labelSlug" class="block text-sm font-medium text-gray-500">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <div class="relative">
      <select
        :id="labelSlug"
        v-model="model"
        :class="[
          'ease w-full cursor-pointer appearance-none rounded-lg border bg-white py-2 pr-8 pl-3 text-sm font-medium shadow-sm transition duration-300 placeholder:text-gray-400 focus:shadow-md focus:outline-none',
          !isValid && touched 
            ? 'border-red-500 focus:border-red-500' 
            : 'border-gray-200 hover:border-gray-400 focus:border-gray-400'
        ]"
        @blur="validate"
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
        class="absolute top-2.5 right-2.5 ml-1 h-5 w-5 pointer-events-none"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"
        />
      </svg>
    </div>
    <p 
      v-if="!isValid && touched" 
      class="text-xs text-red-500 mt-1 transition-all duration-200"
    >
      {{ errorMessage }}
    </p>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";

const props = defineProps({
  label: { type: String, required: true, default: "Label" },
  placeholder: { type: String, default: "Select an option" },
  options: { type: Array, required: true },
  required: { type: Boolean, default: false }, // Added to support validation check
});

const model = defineModel();

// --- VALIDATION STATE ---
const touched = ref(false);
const isValid = ref(true);
const errorMessage = ref("");

// --- VALIDATION METHOD ---
const validate = () => {
  touched.value = true;
  
  // Check if value is empty/null/undefined when required
  if (props.required && (model.value === "" || model.value === null || model.value === undefined)) {
    isValid.value = false;
    errorMessage.value = "This field is required";
    return false;
  }
  
  isValid.value = true;
  errorMessage.value = "";
  return true;
};

// --- EXPOSE TO PARENT ---
// This allows the parent component to call .validate() via the ref
defineExpose({
  validate
});

const labelSlug = computed(() => props.label.toLowerCase().replace(/\s+/g, "-"));
</script>