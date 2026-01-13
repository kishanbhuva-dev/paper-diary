<template>
  <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16 lg:h-20">
        <div class="flex items-center">
          <a
            href="/"
            class="flex items-center space-x-2 transition-opacity hover:opacity-90"
          >
            <img
              src="/public/main_logo.png"
              alt="Paper Diary"
              class="h-8 lg:h-10 w-auto"
            />
          </a>
        </div>

        <nav class="hidden lg:flex items-center space-x-8">
          <template
            v-for="item in navigationMenu"
            :key="item.label"
          >
            <router-link
              v-if="
                !item.isDropdown &&
                (!item.role || (item.role === 'user' && isAuthenticated && user?.role === 'user'))
              "
              :to="item.to"
              class="flex items-center text-sm font-semibold text-gray-600 hover:text-blue-600 transition-colors duration-200"
            >
              {{ item.label }}
            </router-link>

            <div
              v-else-if="item.isDropdown"
              class="relative"
            >
              <button
                class="flex items-center text-sm font-semibold text-gray-600 hover:text-blue-600 focus:outline-none transition-colors duration-200"
                @click.stop="toggleDropdown(item.label)"
              >
                <span>{{ item.label }}</span>
                <Icon
                  icon="lucide:chevron-down"
                  :class="{ 'rotate-180': openDropdown === item.label }"
                  class="h-4 w-4 ml-1 transform transition-transform duration-300"
                />
              </button>

              <div
                v-if="openDropdown === item.label"
                class="absolute right-0 mt-3 w-48 rounded-xl shadow-xl bg-white border border-gray-100 py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-200"
              >
                <a
                  v-for="subItem in item.dropdownItems"
                  :key="subItem.label"
                  :href="subItem.to"
                  class="block px-4 py-2 text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors"
                  @click="openDropdown = null"
                >
                  {{ subItem.label }}
                </a>
              </div>
            </div>
          </template>
        </nav>

        <div class="flex items-center">
          <template v-if="!isAuthenticated">
            <router-link
              to="/login"
              class="btn-primary hidden lg:flex items-center justify-center px-6 py-2.5 rounded-full text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95"
            >
              Login Now
            </router-link>
          </template>

          <template v-else>
            <div class="hidden lg:block relative ml-6">
              <button
                class="group flex items-center p-1 rounded-full hover:bg-gray-50 transition-all duration-200 ring-1 ring-transparent hover:ring-gray-200"
                @click.stop="toggleDropdown('userProfile')"
              >
                <div
                  class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center font-bold text-lg shadow-inner group-hover:shadow-md transition-all"
                >
                  {{ userInitials }}
                </div>
                <div class="ml-3 text-left mr-2">
                  <p class="text-xs font-bold text-gray-900 leading-none mb-0.5">
                    {{ user?.firstName }}
                  </p>
                  <p class="text-[10px] text-gray-500 uppercase tracking-tighter">
                    {{ user?.role }}
                  </p>
                </div>
                <Icon
                  icon="lucide:chevron-down"
                  class="h-4 w-4 text-gray-400 group-hover:text-blue-600"
                />
              </button>

              <div
                v-if="openDropdown === 'userProfile'"
                class="absolute right-0 mt-3 w-64 rounded-2xl shadow-2xl bg-white border border-gray-100 overflow-hidden z-50 animate-in fade-in slide-in-from-top-2 duration-200"
              >
                <div class="bg-gray-50/50 px-5 py-4 border-b border-gray-100">
                  <p class="text-sm font-bold text-gray-900 truncate">
                    {{ user?.firstName }} {{ user?.lastName }}
                  </p>
                  <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                </div>

                <div class="p-2">
                  <router-link
                    v-for="link in authLinks"
                    :key="link.name"
                    :to="{ name: link.name }"
                    class="flex items-center px-4 py-2.5 text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group"
                    @click="openDropdown = null"
                  >
                    <div
                      class="w-8 h-8 rounded-lg bg-gray-50 group-hover:bg-blue-100 flex items-center justify-center mr-3 transition-colors"
                    >
                      <Icon
                        :icon="link.icon"
                        class="h-4 w-4 text-gray-500 group-hover:text-blue-600"
                      />
                    </div>
                    <span class="font-medium">{{ link.label }}</span>
                  </router-link>
                </div>

                <div class="p-2 bg-gray-50/50 border-t border-gray-100">
                  <button
                    class="w-full flex items-center px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-lg transition-colors group"
                    @click="handleLogout"
                  >
                    <div
                      class="w-8 h-8 rounded-lg bg-red-50 group-hover:bg-red-100 flex items-center justify-center mr-3 transition-colors"
                    >
                      <Icon
                        icon="lucide:log-out"
                        class="h-4 w-4 text-red-600"
                      />
                    </div>
                    Logout
                  </button>
                </div>
              </div>
            </div>
          </template>

          <div class="lg:hidden ml-4">
            <button
              class="p-2 rounded-xl text-gray-700 hover:bg-gray-100 active:bg-gray-200 transition-colors"
              @click="toggleMobileMenu"
            >
              <Icon
                :icon="isMobileMenuOpen ? 'lucide:x' : 'lucide:menu'"
                class="h-6 w-6"
              />
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="isMobileMenuOpen"
      class="lg:hidden"
    >
      <div
        class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40"
        @click="toggleMobileMenu"
      ></div>
      <div
        class="fixed inset-y-0 right-0 w-[280px] bg-white shadow-2xl z-50 p-6 flex flex-col transform transition-transform duration-300"
      >
        <div class="flex justify-between items-center mb-8">
          <img
            src="/public/main_logo.png"
            alt="Logo"
            class="h-8"
          />
          <button
            class="p-2 rounded-full bg-gray-50 text-gray-500"
            @click="toggleMobileMenu"
          >
            <Icon icon="lucide:x" />
          </button>
        </div>

        <nav class="flex-1 space-y-2">
          <template
            v-for="item in navigationMenu"
            :key="item.label"
          >
            <router-link
              v-if="
                !item.isDropdown &&
                (!item.role || (item.role === 'user' && isAuthenticated && user?.role === 'user'))
              "
              :to="item.to"
              class="block px-4 py-3 text-base font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all"
              @click="toggleMobileMenu"
            >
              {{ item.label }}
            </router-link>

            <div v-else-if="item.isDropdown">
              <button
                class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-base font-semibold text-gray-700 hover:bg-gray-50"
                @click="toggleDropdown(item.label)"
              >
                <span>{{ item.label }}</span>
                <Icon
                  icon="lucide:chevron-down"
                  :class="{ 'rotate-180': openDropdown === item.label }"
                  class="transition-transform"
                />
              </button>
              <div
                v-if="openDropdown === item.label"
                class="pl-4 mt-1 space-y-1"
              >
                <a
                  v-for="subItem in item.dropdownItems"
                  :key="subItem.label"
                  :href="subItem.to"
                  class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-600"
                  @click="toggleMobileMenu"
                >
                  {{ subItem.label }}
                </a>
              </div>
            </div>
          </template>
        </nav>

        <div class="mt-auto pt-6 border-t border-gray-100">
          <template v-if="!isAuthenticated">
            <router-link
              to="/login"
              class="btn-primary w-full block text-center py-3 rounded-xl"
              @click="toggleMobileMenu"
            >
              Login Now
            </router-link>
          </template>
          <template v-else>
            <div class="flex items-center mb-4 px-2">
              <div
                class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold mr-3"
              >
                {{ userInitials }}
              </div>
              <div>
                <p class="text-sm font-bold text-gray-900">{{ user?.firstName }}</p>
                <p class="text-xs text-gray-500">Account Settings</p>
              </div>
            </div>
            <div class="space-y-1">
              <router-link
                v-for="link in authLinks"
                :key="link.name"
                :to="{ name: link.name }"
                class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50"
                @click="toggleMobileMenu"
              >
                <Icon
                  :icon="link.icon"
                  class="mr-3 h-4 w-4 text-gray-400"
                />
                {{ link.label }}
              </router-link>
              <button
                class="w-full flex items-center px-4 py-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50"
                @click="handleLogout"
              >
                <Icon
                  icon="lucide:log-out"
                  class="mr-3 h-4 w-4"
                />
                Logout
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import { ref, watch, onUnmounted, computed, onMounted } from 'vue';
import { useAuth } from '../composables/useAuth';

const { user, isAuthenticated, isAdmin, isOwner, logout } = useAuth();

const openDropdown = ref(null);
const isMobileMenuOpen = ref(false);

const toggleDropdown = (label) => {
  openDropdown.value = openDropdown.value === label ? null : label;
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeAllDropdowns = () => {
  openDropdown.value = null;
};

onMounted(() => {
  window.addEventListener('click', closeAllDropdowns);
});

onUnmounted(() => {
  window.removeEventListener('click', closeAllDropdowns);
  document.body.style.overflow = '';
});

const userInitials = computed(() => {
  if (!user.value?.firstName) {
    return 'U';
  }
  return user.value.firstName
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

const authLinks = computed(() => {
  const role = user.value?.role?.toLowerCase();
  if (!role) {
    return [];
  }

  const links = [
    { name: `${role}-profile`, label: 'My Profile', icon: 'lucide:user-circle' },
    { name: `${role}-change-password`, label: 'Change password', icon: 'lucide:shield-check' },
  ];

  if (isAdmin.value) {
    links.push({ name: 'admin-dashboard', label: 'Admin Dashboard', icon: 'lucide:layout-grid' });
  } else if (isOwner.value) {
    links.push({ name: 'owner-dashboard', label: 'Owner Dashboard', icon: 'lucide:home' });
  }

  return links;
});

const handleLogout = async () => {
  await logout();
  isMobileMenuOpen.value = false;
  openDropdown.value = null;
};

watch(isMobileMenuOpen, (isOpen) => {
  document.body.style.overflow = isOpen ? 'hidden' : '';
});

const navigationMenu = [
  { to: '/', label: 'Home' },
  { to: '/my-bookings', label: 'My Bookings', role: 'user' },
  { to: '/', label: 'About us' },
  { to: '/', label: 'Blog' },
  {
    label: 'Center',
    isDropdown: true,
    dropdownItems: [
      { to: '/', label: 'Centre A' },
      { to: '/', label: 'Centre B' },
      { to: '/', label: 'Online' },
    ],
  },
];
</script>

<style scoped>
.animate-in {
  animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
