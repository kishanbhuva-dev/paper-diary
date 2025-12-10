<template>
  <section class="h-screen w-full flex bg-gray-50 relative overflow-hidden">
    <div
      class="lg:hidden flex items-center justify-between bg-white px-4 py-3 border-b border-gray-100 fixed top-0 left-0 right-0 z-40 shadow-sm"
    >
      <button @click="sidebarOpen = !sidebarOpen">
        <Icon
          icon="material-symbols:menu-rounded"
          width="30"
          class="text-blue-600"
        />
      </button>

      <img src="/public/main_logo.png" class="h-10" />
    </div>

    <aside
      :class="[
        'fixed lg:static top-0 left-0 h-full w-64 z-50 backdrop-blur-xl bg-white/70 shadow-2xl lg:shadow-xl transition-all duration-300',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      ]"
    >
      <div
        class="flex items-center justify-center py-6 border-b border-gray-100"
      >
        <img src="/public/main_logo.png" class="h-14" />
      </div>

      <nav
        class="p-4 space-y-3 overflow-y-auto scrollbar-hide h-[calc(100%-12rem)]"
      >
        <div
          class="text-[10px] uppercase font-bold text-gray-500 tracking-widest mb-2 px-3"
        >
          Main Navigation
        </div>

        <div v-for="item in menuItems" :key="item.label">
          <router-link
            :to="item.to"
            class="flex items-center gap-3 px-3 py-2 text-sm font-semibold rounded-2xl transition-all duration-200"
            :class="{
              'bg-blue-600 text-white shadow-sm shadow-blue-500/50':
                $route.path === item.to,
              'text-gray-700 hover:bg-blue-50': $route.path !== item.to,
            }"
            @click="closeOnMobile"
          >
            <div
              class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors duration-200"
              :class="$route.path === item.to ? 'bg-white/20' : 'bg-blue-100'"
            >
              <Icon
                :icon="item.icon"
                width="18"
                :class="
                  $route.path === item.to ? 'text-white' : 'text-blue-600'
                "
              />
            </div>
            {{ item.label }}
          </router-link>
        </div>

        <div
          class="text-[10px] uppercase font-bold text-gray-500 tracking-widest mt-6 pt-4 border-t border-gray-100 px-3"
        >
          Management
        </div>

        <div v-for="mg in manageMenu" :key="mg.label">
          <router-link
            :to="mg.to"
            class="flex items-center gap-3 px-3 py-2 text-sm font-semibold rounded-2xl transition-all duration-200"
            :class="{
              'bg-blue-600 text-white shadow-lg shadow-blue-500/50':
                $route.path === mg.to,
              'text-gray-700 hover:bg-blue-50': $route.path !== mg.to,
            }"
            @click="closeOnMobile"
          >
            <div
              class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors duration-200"
              :class="$route.path === mg.to ? 'bg-white/20' : 'bg-blue-100'"
            >
              <Icon
                :icon="mg.icon"
                width="18"
                :class="$route.path === mg.to ? 'text-white' : 'text-blue-600'"
              />
            </div>
            {{ mg.label }}
          </router-link>
        </div>
      </nav>

      <div
        class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100 bg-white z-10"
      >
        <div
          class="flex items-center gap-3 p-3 rounded-2xl bg-blue-50 border border-blue-200 shadow-md transition duration-300 hover:shadow-lg"
        >
          <div
            class="w-10 h-10 uppercase rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-700 text-lg border border-blue-200"
          >
            {{ userData?.username[0] }}
          </div>

          <div class="flex-1 min-w-0">
            <p class="text-gray-900 font-bold text-sm truncate">
              {{ userData?.username }}
            </p>
            <p class="text-gray-600 text-xs truncate">{{ userData?.email }}</p>
          </div>

          <button
            class="p-2 rounded-xl text-gray-400 hover:bg-red-100 hover:text-red-600 transition duration-150 group cursor-pointer ml-auto"
            title="Logout"
            @click="handleLogout"
          >
            <Icon
              icon="heroicons:arrow-right-on-rectangle"
              width="20"
              class="group-hover:scale-105 transition-transform"
            />
          </button>
        </div>
      </div>
    </aside>

    <div
      class="flex-1 flex flex-col overflow-y-auto lg:ml-0 mt-16 lg:mt-0 p-4 lg:shadow-inner"
    >
      <router-view />
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from "vue";
import { Icon } from "@iconify/vue";
import { useAuth } from "../../js/composables/useAuth";

const { logout, user } = useAuth();

const userData = computed(() => ({
  username: user.value?.firstName + " " + user.value?.lastName ?? "",
  email: user.value?.email ?? "",
}));

const sidebarOpen = ref(false);

const menuItems = [
  { label: "Dashboard", to: "/admin", icon: "heroicons:home-modern" },
  { label: "Analytics", to: "/admin/analytics", icon: "heroicons:chart-bar" },
  {
    label: "Reports",
    to: "/admin/reports",
    icon: "heroicons:document-chart-bar",
  },
];

const manageMenu = [
  { label: "Users", to: "/admin/users", icon: "heroicons:user-group" },
  { label: "Owners", to: "/admin/owners", icon: "heroicons:user-plus" },
  { label: "Settings", to: "/admin/settings", icon: "basil:settings-outline" },
];

function closeOnMobile() {
  if (window.innerWidth < 1024) sidebarOpen.value = false;
}

const handleLogout = async () => {
  await logout();
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
</style>
