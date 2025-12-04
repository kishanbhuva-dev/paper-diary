<template>
  <div id="app-root">
    <router-view />

    <Toaster
      position="top-right"
      :expand="true"
      richColors
      :style="{ zIndex: 99999 }"
    />
  </div>
</template>

<script setup>
import { Toaster } from "vue-sonner";
</script>
