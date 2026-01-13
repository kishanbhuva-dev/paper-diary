<template>
  <div class="min-h-screen flex flex-col justify-between bg-gradient-to-br">
    <!-- Header -->
    <AppHeader />

    <!-- Page body -->
    <main>
      <div>
        <router-view />
      </div>
    </main>

    <!-- Footer -->
    <AppFooter />
  </div>
</template>

<script setup>
import AppHeader from './AppHeader.vue';
import AppFooter from './AppFooter.vue';
</script>
