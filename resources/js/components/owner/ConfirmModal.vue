<template>
  <div
    v-if="modelValue"
    class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
  >
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-in zoom-in duration-200"
    >
      <div class="p-6 text-center">
        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
          <Icon
            icon="mdi:alert-circle-outline"
            class="w-10 h-10 text-red-500"
          />
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">{{ title }}</h3>
        <p class="text-slate-600 text-sm mb-6">{{ message }}</p>

        <div class="flex gap-3">
          <button
            class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors cursor-pointer"
            @click="$emit('update:modelValue', false)"
          >
            No, Keep it
          </button>
          <button
            class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-200 transition-all active:scale-95 cursor-pointer"
            @click="$emit('confirm')"
          >
            Yes, Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Icon } from '@iconify/vue';
// defineProps(['modelValue', 'title', 'message']);
defineEmits(['update:modelValue', 'confirm']);
</script>
