<template>
  <aside
    class="flex flex-col bg-white border-r border-slate-200 transition-all duration-300 ease-in-out"
    :class="[
      // Mobile Styles: Full screen fixed overlay
      'fixed inset-0 z-50 w-full',
      isMobileOpen ? 'translate-x-0' : '-translate-x-full',

      // Desktop Styles: Reset to static side column
      'md:relative md:translate-x-0 md:w-72 md:inset-auto md:h-screen',
    ]"
  >
    <div
      class="h-16 flex items-center justify-between px-6 border-b border-slate-100 flex-shrink-0"
    >
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center">
          <!-- <Icon icon="mdi:paper-roll-outline" class="text-white text-lg" /> -->
          <img src="../../../../public/main_logo.png" alt="Main_Logo" />
        </div>
        <span class="text-xl font-bold tracking-tight text-slate-800">
          PAPER <span class="text-blue-600">DIARY</span>
        </span>
      </div>

      <button
        class="md:hidden p-2 text-slate-400 hover:text-red-500 transition-colors"
        @click="$emit('closeSidebar')"
      >
        <Icon icon="mdi:close" class="text-2xl" />
      </button>
    </div>

    <div class="flex-1 overflow-y-auto py-6 space-y-1">
      <div
        class="px-6 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider"
      >
        Main Menu
      </div>

      <ul class="space-y-1">
        <li v-for="item in mainMenuItems" :key="item.name">
          <RouterLink
            :to="item.to"
            class="group relative flex items-center px-4 py-3 mx-3 rounded-xl transition-all duration-200 font-medium text-sm"
            :class="{
              // Active State: Solid blue, White Text, Shadow
              'bg-blue-700 text-white shadow-md shadow-blue-200':
                $route.name === item.name,
              // Inactive State: Slate text, Hover Light blue
              'text-slate-600 hover:bg-blue-50 hover:text-blue-800':
                $route.name !== item.name,
            }"
          >
            <Icon
              :icon="item.icon"
              class="text-xl mr-3 transition-colors"
              :class="
                $route.name === item.name
                  ? 'text-white'
                  : 'text-slate-400 group-hover:text-blue-600'
              "
            />

            <span>{{ item.label }}</span>

            <!-- <Icon
              v-if="$route.name === item.name"
              icon="mdi:chevron-right"
              class="ml-auto text-blue-200"
            /> -->
          </RouterLink>
        </li>
      </ul>
    </div>

    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
      <div
        class="flex items-center p-3 bg-white border border-slate-200 rounded-xl hover:shadow-lg"
      >
        <div
          class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center uppercase text-blue-700 font-bold border border-blue-200 shrink-0"
        >
          {{ user?.firstName?.charAt(0) || "U" }}
        </div>

        <div class="ml-3 flex-1 overflow-hidden">
          <p class="text-sm font-bold text-slate-800 truncate">
            {{ user?.firstName || "User" }}
          </p>
          <p class="text-xs text-slate-500 truncate">
            {{ user?.email }}
          </p>
        </div>

        <button
          @click="handleLogout"
          class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-100 rounded-lg transition-colors"
          title="Logout"
        >
          <Icon icon="mdi:logout" class="text-xl" />
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { Icon } from "@iconify/vue";
import { useAuth } from "../../composables/useAuth";

const props = defineProps({
  isMobileOpen: {
    type: Boolean,
    default: false,
  },
});

const emits = defineEmits(["closeSidebar"]);
const { logout, user } = useAuth();

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
  // Added an extra item to show how the list looks
  // {
  //   name: "settings",
  //   label: "Settings",
  //   icon: "mdi:cog-outline",
  //   to: { name: "properties" }, // Temporarily pointing to properties
  // },
];

async function handleLogout() {
  await logout();
}
</script>
