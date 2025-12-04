<template>
  <router-view />

  <Toaster
    position="top-right"
    :expand="true"
    richColors
    :style="{ zIndex: 99999 }"
  />
</template>

<script setup>
import { Toaster } from "vue-sonner";
</script>
