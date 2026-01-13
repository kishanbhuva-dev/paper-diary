<template>
  <div
    v-if="modelValue"
    class="fixed inset-0 z-50 md:p-0 p-4 flex items-center bg-black/50 backdrop-blur-sm justify-center transition-all duration-300 animate-[fadeIn_0.25s_ease-out]"
  >
    <div
      :class="`relative ${width} w-full p-7 rounded-2xl shadow-2xl
               bg-white border border-white/50 backdrop-blur-xl
               transition-all duration-300 space-y-6
               animate-[slideUp_0.3s_ease-out]`"
    >
      <!-- Title -->
      <div class="space-y-1">
        <h2 class="text-xl font-extrabold text-blue-600 tracking-tight">
          {{ title }}
        </h2>
        <div class="w-14 h-0.5 bg-blue-600 rounded-full"></div>
      </div>

      <!-- Slot -->
      <div
        class="max-h-[65vh] md:max-h-[65vh] overflow-y-auto px-1 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent"
      >
        <slot></slot>
      </div>

      <!-- Actions -->
      <div class="flex gap-4 pt-2">
        <button
          class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 border border-gray-200 hover:bg-gray-200 hover:scale-[1.03] transition-all duration-200 cursor-pointer"
          @click="$emit('update:modelValue', false)"
        >
          Cancel
        </button>

        <button
          class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold flex items-center justify-center bg-blue-600 text-white shadow-sm hover:bg-blue-700 hover:shadow-md hover:scale-[1.03] transition-all duration-200 cursor-pointer"
          @click="$emit('save')"
        >
          <Icon
            icon="mdi:content-save-outline"
            width="18"
            height="18"
            class="mr-1.5"
          />
          Save
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Icon } from '@iconify/vue';

defineProps({
  modelValue: Boolean,
  title: { type: String, default: '' },
  width: { type: String, default: 'max-w-lg' },
});

defineEmits(['update:modelValue', 'save']);
</script>

<style scoped>
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
