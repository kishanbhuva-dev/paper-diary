<template>
  <div
    class="relative inline-flex"
    @mouseenter="visible = true"
    @mouseleave="visible = false"
  >
    <slot></slot>

    <!-- Tooltip -->
    <transition name="fade-scale">
      <div
        v-if="visible"
        class="absolute z-20 bg-blue-500 text-white text-xs font-bold p-1 rounded-lg shadow-lg whitespace-nowrap"
        :class="positionClass"
      >
        {{ text }}

        <!-- Arrow -->
        <span
          class="absolute w-1.5 h-1.5 rotate-45 bg-blue-500"
          :class="arrowClass"
        ></span>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  text: {
    type: String,
    default: '',
  },
  position: {
    type: String,
    default: 'bottom',
  },
});

const visible = ref(false);

const positionClass = computed(() => {
  switch (props.position) {
    case 'top':
      return 'bottom-full right-0';
    case 'bottom':
      return 'top-full right-0 mt-2';
    default:
      return 'top-full left-1/2  mt-2';
  }
});

const arrowClass = computed(() => {
  switch (props.position) {
    case 'top':
      return '-bottom-1 right-3';
    case 'bottom':
      return '-top-1 right-3';
    default:
      return '-top-1 left-1/2';
  }
});
</script>

<style scoped>
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: all 0.2s ease;
}
.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
