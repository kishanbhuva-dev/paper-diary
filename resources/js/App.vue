<template>
  <div id="app-root">
    <router-view />
    <!-- Global Toaster for app-wide notifications. This ensures toasts persist across route changes. -->
    <Toaster position="top-right" :expand="true" richColors :style="{ zIndex: 99999 }" />
  </div>
</template>

<script setup>
import { Toaster } from 'vue-sonner';
</script>
