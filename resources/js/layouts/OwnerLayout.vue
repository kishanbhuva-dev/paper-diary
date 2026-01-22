<template>
  <section class="h-screen w-full flex bg-gray-50 relative overflow-hidden font-sans">
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

      <a href="/">
        <img
          src="/main_logo.png"
          class="h-10"
          alt="Logo"
        />
      </a>
    </div>

    <aside
      :class="[
        'fixed lg:static top-0 left-0 h-full w-64 z-50 backdrop-blur-xl bg-white/70 shadow-2xl lg:shadow-xl transition-all duration-300',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      ]"
    >
      <div class="flex items-center justify-center py-6 border-b border-gray-100">
        <a href="/">
          <img
            src="/main_logo.png"
            class="h-14"
            alt="Logo"
          />
        </a>
      </div>

      <nav class="p-4 space-y-3 overflow-y-auto scrollbar-hide h-[calc(100%-12rem)]">
        <div class="text-[10px] uppercase font-bold text-gray-500 tracking-widest mb-2 px-3">
          Main Navigation
        </div>

        <div
          v-for="item in mainMenuItems"
          :key="item.name"
        >
          <router-link
            :to="item.to"
            class="flex items-center gap-3 px-3 py-2 text-sm font-semibold rounded-2xl transition-all duration-200"
            :class="{
              'bg-blue-600 text-white shadow-sm shadow-blue-500/50': $route.name === item.name,
              'text-gray-700 hover:bg-blue-50': $route.name !== item.name,
            }"
            @click="closeOnMobile"
          >
            <div
              class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors duration-200"
              :class="$route.name === item.name ? 'bg-white/20' : 'bg-blue-100'"
            >
              <Icon
                :icon="item.icon"
                width="18"
                :class="$route.name === item.name ? 'text-white' : 'text-blue-600'"
              />
            </div>
            {{ item.label }}
          </router-link>
        </div>

        <div
          class="text-[10px] uppercase font-bold text-gray-500 tracking-widest mt-6 pt-4 border-t border-gray-100 px-3"
        >
          System
        </div>

        <div
          v-for="group in systemMenuItems"
          :key="group.label"
          class="space-y-1"
        >
          <button
            class="w-full flex items-center gap-3 px-3 py-2 text-sm font-semibold rounded-2xl transition-all duration-200 text-gray-700 hover:bg-blue-50"
            :class="{ 'bg-blue-50/50': isSystemRouteActive }"
            @click="settingsOpen = !settingsOpen"
          >
            <div
              class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
              :class="isSystemRouteActive ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600'"
            >
              <Icon
                :icon="group.icon"
                width="18"
              />
            </div>
            <span
              class="flex-1 text-left"
              :class="{ 'text-blue-600': isSystemRouteActive }"
            >
              {{ group.label }}
            </span>
            <Icon
              icon="mdi:chevron-down"
              class="transition-transform duration-200"
              :class="[
                { 'rotate-180': settingsOpen },
                isSystemRouteActive ? 'text-blue-600' : 'text-gray-400',
              ]"
            />
          </button>

          <!-- <div v-show="settingsOpen" class="pl-12 space-y-1 mt-1">
            <router-link
              v-for="subItem in group.children"
              :key="subItem.name"
              :to="subItem.to"
              class="flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-xl transition-all duration-200"
              :class="{
                'bg-blue-600 text-white shadow-sm shadow-blue-500/50':
                  $route.name === subItem.name,
                'text-gray-700 hover:bg-blue-50': $route.name !== subItem.name,
              }"
              @click="closeOnMobile"
            >
              <Icon
                :icon="subItem.icon"
                width="16"
                :class="
                  $route.name === subItem.name ? 'text-white' : 'text-blue-600'
                "
              />
              {{ subItem.label }}
            </router-link>

            <button
                v-if="showBackButton"
                @click="backToAdmin"
                class="flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-xl transition-all duration-200"
            >
                <Icon icon="lucide:step-back" class="w-4 h-4 mr-3 text-blue-600" />
                Back To Admin
            </button>
          </div> -->

          <div
            v-show="settingsOpen"
            class="pl-12 space-y-1 mt-1"
          >
            <component
              :is="subItem.to ? 'router-link' : 'button'"
              v-for="subItem in group.children"
              v-show="!subItem.show || subItem.show.value"
              :key="subItem.name"
              :to="subItem.to"
              class="w-full cursor-pointer flex items-center gap-2 px-3 py-2 text-xs font-bold rounded-xl transition-all duration-200"
              :class="{
                'bg-blue-600 text-white shadow-sm shadow-blue-500/50': $route.name === subItem.name,
                'text-gray-700 hover:bg-blue-50': $route.name !== subItem.name,
              }"
              @click="subItem.onClick && subItem.onClick()"
            >
              <Icon
                :icon="subItem.icon"
                width="16"
                :class="$route.name === subItem.name ? 'text-white' : 'text-blue-600'"
              />
              {{ subItem.label }}
            </component>
          </div>
        </div>
      </nav>

      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100 bg-white z-10">
        <div
          class="flex items-center gap-3 p-3 rounded-2xl bg-blue-50 border border-blue-200 shadow-md transition duration-300 hover:shadow-lg"
        >
          <div
            class="w-10 h-10 uppercase rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-700 text-lg border border-blue-200"
          >
            {{ userData?.initials }}
          </div>

          <div class="flex-1 min-w-0">
            <p class="text-gray-900 font-bold text-sm truncate">
              {{ userData?.firstName }}
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
      class="owner-content flex-1 flex flex-col overflow-y-auto lg:ml-0 mt-16 lg:mt-0 p-4 lg:shadow-inner scroll-smooth"
    >
      <router-view />
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import { useRoute } from 'vue-router';
import { useAuth } from '../../js/composables/useAuth';

const { logout, user } = useAuth();
const route = useRoute();
const sidebarOpen = ref(false);
const settingsOpen = ref(false);
const showBackButton = ref(false);

const userData = computed(() => ({
  firstName: user.value?.firstName || 'User',
  email: user.value?.email || '',
  initials: user.value?.firstName?.charAt(0) || 'U',
}));

const mainMenuItems = [
  {
    name: 'owner-dashboard',
    label: 'Dashboard',
    icon: 'mdi:view-dashboard-outline',
    to: { name: 'owner-dashboard' },
  },
  {
    name: 'properties',
    label: 'Properties',
    icon: 'mdi:home-city-outline',
    to: { name: 'properties' },
  },
  {
    name: 'bookings',
    label: 'Bookings',
    icon: 'mdi:calendar-check',
    to: { name: 'bookings' },
  },
  {
    name: 'owner-subcription-view',
    label: 'Subcription',
    icon: 'heroicons:credit-card-20-solid',
    to: { name: 'owner-subcription-view' },
  },
];

const systemMenuItems = [
  {
    label: 'Settings',
    icon: 'mdi:cog-outline',
    children: [
      {
        name: 'owner-profile',
        label: 'Profile',
        icon: 'mdi:account-circle-outline',
        to: { name: 'owner-profile' },
      },
      {
        name: 'owner-change-password',
        label: 'Change Password',
        icon: 'mdi:lock-outline',
        to: { name: 'owner-change-password' },
      },
      {
        name: 'back-to-admin',
        label: 'Back To Admin',
        icon: 'lucide:step-back',
        onClick: backToAdmin,
        show: showBackButton,
      },
    ],
  },
];

const isSystemRouteActive = computed(() =>
  systemMenuItems.some((group) => group.children.some((child) => child.name === route.name))
);

function backToAdmin() {
  const adminToken = localStorage.getItem('adminToken');
  const adminUser = localStorage.getItem('adminUser');

  if (adminToken && adminUser) {
    localStorage.clear();
    localStorage.setItem('authToken', adminToken);
    localStorage.setItem('user', adminUser);
    localStorage.removeItem('adminToken');
    localStorage.removeItem('adminUser');
    setTimeout(() => {
      window.location.href = '/admin';
    }, 500);
  } else {
    throw new Error('Admin session not found.');
  }
}

onMounted(() => {
  if (isSystemRouteActive.value) {
    settingsOpen.value = true;
  }

  const adminToken = localStorage.getItem('adminToken');
  showBackButton.value = !!adminToken;
});

function closeOnMobile() {
  if (window.innerWidth < 1024) {
    sidebarOpen.value = false;
  }
}

const handleLogout = async () => {
  await logout();
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.owner-content {
  scrollbar-color: #96a7fa #f1f5f9;
  scrollbar-width: thin;
}
</style>
