<template>
  <aside
    :class="[
      'bg-white text-gray-800 fixed h-screen transition-all duration-500 ease-in-out z-30 flex flex-col overflow-hidden border-r border-indigo-100',
      'md:w-60 md:translate-x-0',

      windowWidth < 768 && isCollapsed
        ? '-translate-x-full'
        : windowWidth < 768
        ? 'w-full translate-x-0 transition-all duration-500 ease-in-out'
        : '',
    ]"
  >
    <div class="border-indigo-200 border-b h-18 py-2 px-4">
      <div class="flex items-center justify-between">
        <span class="text-lg font-bold tracking-wide text-indigo-700"
          >PAPER DIARY</span
        >

        <button
          v-if="windowWidth < 768 && !isCollapsed"
          @click="toggleSidebar"
          class="text-3xl focus:outline-none hover:text-red-500 transition-colors"
        >
          <Icon icon="mdi:close" />
        </button>
      </div>
      <!-- FIX: Now uses the reactive userName from the composable -->
      <span v-if="user" class="text-sm text-gray-600"> welcome {{ user.firstName }}</span>
    </div>

    <ul class="flex-1 overflow-y-auto">
      <li v-for="item in mainMenuItems" :key="item.name">
        <RouterLink
          :to="item.to"
          class="flex items-center px-5 py-2 transition-all duration-200 hover:scale-105 group justify-start hover:bg-indigo-50 hover:text-indigo-700"
          :class="{
            'bg-indigo-100 text-indigo-800 font-semibold':
              $route.name === item.name,
          }"
          @click="windowWidth < 768 ? toggleSidebar() : null"
        >
          <Icon
            :icon="item.icon"
            class="text-2xl min-w-[2rem] transition-colors duration-300"
          />
          <span
            class="ml-3 text-sm font-medium transition-colors duration-300 whitespace-nowrap"
          >
            {{ item.label }}
          </span>
        </RouterLink>
      </li>
    </ul>

    <div class="p-2 border-t border-indigo-200">
      <button
        @click="handleLogout"
        class="w-full flex items-center px-3 py-2 transition-all duration-200 hover:bg-indigo-100 hover:text-indigo-700 rounded justify-start text-gray-700"
      >
        <Icon
          icon="mdi:logout"
          class="text-2xl min-w-[2rem] transition-colors duration-300"
        />
        <span class="ml-3 text-md font-medium whitespace-nowrap"> Logout </span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { Icon } from "@iconify/vue";
import { ref, onMounted, onUnmounted, defineExpose } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuth } from "../../composables/useAuth";

const router = useRouter();
const route = useRoute();

// Destructure logout and userName from the composable
const { logout, user } = useAuth();

const isCollapsed = ref(true);
const windowWidth = ref(window.innerWidth);

const mainMenuItems = [
  {
    name: "owner-dashboard",
    label: "Dashboard",
    icon: "mdi:view-dashboard-outline",
    to: { name: "owner-dashboard" },
  },
  {
    name: "properties",
    label: "Properties",
    icon: "mdi:home-city-outline",
    to: { name: "properties" },
  },
];

function toggleSidebar() {
  if (windowWidth.value < 768) {
    isCollapsed.value = !isCollapsed.value;
  }
}

function handleResize() {
  windowWidth.value = window.innerWidth;
  if (window.innerWidth >= 768) {
    isCollapsed.value = false;
  } else {
    isCollapsed.value = true;
  }
}

async function handleLogout() {
  // Use the centralized logout function from useAuth
  // This handles the API call, Pinia state clearing, localStorage cleanup, and redirection.
  await logout();

  if (windowWidth.value < 768) {
    isCollapsed.value = true;
  }
}

onMounted(() => {
  handleResize();
  window.addEventListener("resize", handleResize);
});

onUnmounted(() => {
  window.removeEventListener("resize", handleResize);
});

defineExpose({ isCollapsed, toggleSidebar, windowWidth });
</script>

<style scoped>
/* Hide scrollbar */
ul::-webkit-scrollbar {
  display: none;
}

ul {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
