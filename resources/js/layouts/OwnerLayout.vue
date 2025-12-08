<template>
  <div class="flex h-screen bg-slate-50 overflow-hidden font-sans">
    <OwnerSidebar
      :is-mobile-open="isMobileSidebarOpen"
      @close-sidebar="isMobileSidebarOpen = false"
    />

    <div class="flex-1 flex flex-col min-w-0 h-screen">
      <OwnerHeader @toggle-sidebar="isMobileSidebarOpen = true" />

      <main
        class="flex-1 overflow-y-auto overflow-x-hidden p-4 md:p-8 scroll-smooth"
      >
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useRoute } from "vue-router";
import OwnerHeader from "../components/owner/OwnerHeader.vue";
import OwnerSidebar from "../components/owner/OwnerSidebar.vue";

const route = useRoute();
const isMobileSidebarOpen = ref(false);

// Close mobile menu immediately when route changes (user clicks a link)
watch(
  () => route.path,
  () => {
    isMobileSidebarOpen.value = false;
  }
);
</script>
