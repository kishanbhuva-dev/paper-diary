<template>
  <header
    class="flex-shrink-0 bg-white h-16 flex items-center justify-between px-4 md:px-8 border-b border-slate-200 shadow-sm z-20 relative"
  >
    <div class="flex items-center gap-4">
      <button
        class="md:hidden p-2 -ml-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors focus:outline-none"
        @click="$emit('toggleSidebar')"
      >
        <Icon icon="mdi:menu" class="text-2xl" />
      </button>
    </div>

    <!-- <div class="flex items-center gap-4">
      <button
        class="p-2 text-slate-400 hover:text-indigo-600 transition-colors relative"
      >
        <Icon icon="mdi:bell-outline" class="text-2xl" />
        <span
          class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"
        ></span>
      </button>

      <div
        class="h-8 w-8 rounded-full bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-700 font-bold text-xs"
      >
        PD
      </div>
    </div> -->
  </header>
</template>

<script setup>
import { Icon } from "@iconify/vue";
const emits = defineEmits(["toggleSidebar"]);
</script>
