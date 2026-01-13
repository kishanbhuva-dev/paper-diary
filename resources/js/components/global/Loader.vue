<template>
  <div
    v-if="visible"
    class="fixed inset-0 z-[9999] bg-black/60 backdrop-blur-md flex items-center justify-center"
  >
    <div
      class="relative w-40 h-40 rounded-3xl bg-white/10 backdrop-blur-xl border border-white/20 flex items-center justify-center shadow-2xl animate-[fadeIn_.35s_ease-out]"
    >
      <div
        class="absolute inset-0 rounded-3xl bg-blue-500/30 blur-2xl animate-[pulseGlow_2.5s_ease-in-out_infinite]"
      ></div>

      <div
        class="absolute w-28 h-28 border-[4px] border-blue-500/40 border-t-transparent rounded-full animate-spin-slow"
      ></div>

      <img
        src="/public/main_logo.png"
        alt="Paper Diary"
        class="w-14 animate-[logoPulse_1.8s_ease-in-out_infinite]"
      />
    </div>

    <p class="absolute bottom-20 text-white/90 text-lg font-semibold tracking-wide drop-shadow">
      Loading Paper Diary...
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const visible = ref(false);

function show() {
  visible.value = true;
}
function hide() {
  visible.value = false;
}

defineExpose({ show, hide });
</script>

<style scoped>
@keyframes fadeIn {
  0% {
    opacity: 0;
    transform: scale(0.95);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes pulseGlow {
  0%,
  100% {
    opacity: 0.6;
    transform: scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.1);
  }
}

@keyframes logoPulse {
  0%,
  100% {
    transform: scale(1);
    filter: drop-shadow(0 0 4px rgba(59, 130, 246, 0.4));
  }
  50% {
    transform: scale(1.15);
    filter: drop-shadow(0 0 18px rgba(59, 130, 246, 0.7));
  }
}

.animate-spin-slow {
  animation: spin 2.6s linear infinite;
}
</style>
