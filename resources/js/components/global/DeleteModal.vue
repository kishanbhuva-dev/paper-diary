<template>
  <div
    v-if="modelValue"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity duration-200"
  >
    <div
      class="w-full max-w-xl p-6 rounded-2xl bg-white backdrop-blur-xl border border-white/40 scale-95"
    >
      <!-- Title & Description -->
      <div class="space-y-2">
        <!-- Title -->
        <div class="space-y-1">
          <h2 class="text-xl font-extrabold text-blue-600 tracking-tight">
            {{ title }}
          </h2>
          <div class="w-14 h-0.5 bg-blue-600 rounded-full"></div>
        </div>

        <p class="text-sm text-gray-600">{{ message }}</p>
      </div>

      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mt-4">
        <div class="flex items-center gap-2 text-blue-700 font-semibold">
          <Icon
            icon="mdi:alert-circle-outline"
            class="animate-pulse"
            width="22"
            height="22"
          />
          Warning
        </div>
        <p class="text-sm mt-1 text-blue-600">{{ warning }}</p>
      </div>

      <div class="flex gap-3 mt-6">
        <button
          class="w-full py-2 px-4 rounded-xl border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-100 hover:scale-105 transition-all cursor-pointer"
          @click="$emit('update:modelValue', false)"
        >
          Cancel
        </button>

        <button
          :class="[
            'w-full py-2 px-4 rounded-xl text-white font-semibold text-sm flex items-center justify-center gap-2 transition-all hover:scale-105 cursor-pointer',
            action === 'deactivate'
              ? 'bg-blue-600 hover:bg-blue-700'
              : 'bg-red-600 hover:bg-red-700',
          ]"
          @click="confirmDelete"
        >
          <Icon
            :icon="action === 'deactivate' ? 'mdi:account-off-outline' : 'mdi:trash-can-outline'"
            width="18"
            height="18"
          />
          {{ action === 'deactivate' ? 'Deactivate' : 'Delete' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Icon } from '@iconify/vue';

defineProps({
  modelValue: Boolean,
  title: { type: String, default: 'Delete Item' },
  message: {
    type: String,
    default: 'Are you sure you want to delete this item?',
  },
  warning: { type: String, default: 'This action cannot be undone.' },

  action: { type: String, default: 'delete' },
});

const emit = defineEmits(['update:modelValue', 'confirm']);

const confirmDelete = () => {
  emit('confirm');
};
</script>
