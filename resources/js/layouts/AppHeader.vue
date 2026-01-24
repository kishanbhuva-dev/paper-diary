<template>
  <header class="sticky top-0 z-[100] bg-white/90 backdrop-blur-md border-b border-gray-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16 lg:h-20">
        <div class="flex items-center">
          <router-link
            to="/"
            class="flex items-center space-x-2 transition-transform hover:scale-95 duration-200"
          >
            <img
              src="/main_logo.png"
              alt="Paper Diary"
              class="h-8 lg:h-9 w-auto"
            />
          </router-link>
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
              class="text-sm font-bold text-gray-600 hover:text-blue-600 transition-colors duration-200"
            >
              {{ item.label }}
            </router-link>

            <div
              v-else-if="item.isDropdown"
              class="relative group"
            >
              <button
                class="flex items-center text-sm font-bold text-gray-600 hover:text-blue-600 transition-colors duration-200"
                @click.stop="toggleDropdown(item.label)"
              >
                <span>{{ item.label }}</span>
                <Icon
                  icon="lucide:chevron-down"
                  :class="{ 'rotate-180': openDropdown === item.label }"
                  class="h-4 w-4 ml-1.5 transform transition-transform duration-300"
                />
              </button>

              <div
                v-if="openDropdown === item.label"
                class="absolute left-0 mt-3 w-48 rounded-2xl shadow-2xl bg-white border border-gray-100 py-2 z-50 animate-in fade-in slide-in-from-top-2"
              >
                <router-link
                  v-for="subItem in item.dropdownItems"
                  :key="subItem.label"
                  :to="subItem.to"
                  class="block px-4 py-2.5 text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors"
                  @click="openDropdown = null"
                >
                  {{ subItem.label }}
                </router-link>
              </div>
            </div>
          </template>
        </nav>

        <div class="flex items-center">
          <template v-if="!isAuthenticated">
            <router-link
              to="/login"
              class="btn-primary hidden lg:flex items-center justify-center px-7 py-2.5 rounded-full text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95"
            >
              Login Now
            </router-link>
          </template>

          <template v-else>
            <div class="hidden lg:block relative ml-6">
              <button
                class="group flex items-center p-1.5 rounded-full hover:bg-gray-50 transition-all duration-200 border border-transparent hover:border-gray-100"
                @click.stop="toggleDropdown('userProfile')"
              >
                <div
                  class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold shadow-sm"
                >
                  {{ userInitials }}
                </div>
                <div class="ml-3 text-left mr-2">
                  <p class="text-xs font-bold text-gray-900 leading-none mb-1">
                    {{ user?.firstName }}
                  </p>
                  <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">
                    {{ user?.role }}
                  </p>
                </div>
                <Icon
                  icon="lucide:chevron-down"
                  class="h-4 w-4 text-gray-400"
                />
              </button>

              <div
                v-if="openDropdown === 'userProfile'"
                class="absolute right-0 mt-3 w-72 rounded-2xl shadow-2xl bg-white border border-gray-100 overflow-hidden z-[100] animate-in fade-in slide-in-from-top-2"
              >
                <div class="bg-gray-50/80 px-5 py-4 border-b border-gray-100">
                  <p class="text-sm font-black text-gray-900 truncate">
                    {{ user?.firstName }} {{ user?.lastName }}
                  </p>
                  <p class="text-xs font-medium text-gray-500 truncate">{{ user?.email }}</p>
                </div>

                <div class="p-2">
                  <router-link
                    v-for="link in authLinks"
                    :key="link.name"
                    :to="{ name: link.name }"
                    class="flex items-center px-4 py-2.5 text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-700 rounded-xl transition-colors group"
                    @click="openDropdown = null"
                  >
                    <div
                      class="w-8 h-8 rounded-lg bg-gray-50 group-hover:bg-blue-100 flex items-center justify-center mr-3"
                    >
                      <Icon
                        :icon="link.icon"
                        class="h-4 w-4 text-gray-500 group-hover:text-blue-600"
                      />
                    </div>
                    <span class="font-bold">{{ link.label }}</span>
                  </router-link>

                  <button
                    v-if="showBackButton"
                    class="w-full flex items-center px-4 py-2.5 text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-700 rounded-xl transition-colors group"
                    @click="handleBackToAdmin"
                  >
                    <div
                      class="w-8 h-8 rounded-lg bg-gray-50 group-hover:bg-blue-100 flex items-center justify-center mr-3"
                    >
                      <Icon
                        icon="lucide:step-back"
                        class="h-4 w-4 text-gray-500 group-hover:text-blue-600"
                      />
                    </div>
                    <span class="font-bold">Back To Admin</span>
                  </button>
                </div>

                <div class="p-2 bg-gray-50 border-t border-gray-100">
                  <button
                    class="w-full flex items-center px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 rounded-xl transition-colors group"
                    @click="handleLogout"
                  >
                    <div
                      class="w-8 h-8 rounded-lg bg-red-50 group-hover:bg-red-100 flex items-center justify-center mr-3"
                    >
                      <Icon
                        icon="lucide:log-out"
                        class="h-4 w-4"
                      />
                    </div>
                    Logout Account
                  </button>
                </div>
              </div>
            </div>
          </template>

          <div class="lg:hidden ml-4">
            <button
              class="p-2.5 rounded-xl text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors"
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

    <Teleport to="body">
      <div
        v-if="isMobileMenuOpen"
        class="fixed inset-0 z-[9999] lg:hidden"
      >
        <div
          class="fixed inset-0 bg-black/40 transition-opacity"
          @click="toggleMobileMenu"
        ></div>

        <div
          class="fixed top-0 right-0 w-80 h-full bg-white shadow-2xl flex flex-col transform transition-transform duration-300 overflow-y-auto scrollbar-hide"
        >
          <div class="p-6 flex justify-between items-center border-b border-gray-50 flex-shrink-0">
            <img
              src="/main_logo.png"
              alt="Logo"
              class="h-8"
            />
            <button
              class="p-2 rounded-full bg-gray-100 text-gray-500"
              @click="toggleMobileMenu"
            >
              <Icon
                icon="lucide:x"
                class="h-5 w-5"
              />
            </button>
          </div>

          <div class="flex-1 px-4 py-6">
            <nav class="space-y-1">
              <template
                v-for="item in navigationMenu"
                :key="item.label"
              >
                <router-link
                  v-if="
                    !item.isDropdown &&
                    (!item.role ||
                      (item.role === 'user' && isAuthenticated && user?.role === 'user'))
                  "
                  :to="item.to"
                  class="block px-4 py-3.5 text-base font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-2xl"
                  @click="toggleMobileMenu"
                >
                  {{ item.label }}
                </router-link>

                <div v-else-if="item.isDropdown">
                  <button
                    class="w-full flex items-center justify-between px-4 py-3.5 rounded-2xl text-base font-bold text-gray-700 hover:bg-gray-50"
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
                    <router-link
                      v-for="subItem in item.dropdownItems"
                      :key="subItem.label"
                      :to="subItem.to"
                      class="block px-4 py-2.5 text-sm font-semibold text-gray-500 hover:text-blue-600"
                      @click="toggleMobileMenu"
                    >
                      {{ subItem.label }}
                    </router-link>
                  </div>
                </div>
              </template>
            </nav>
          </div>

          <div class="p-4 bg-gray-50/80 border-t border-gray-100 flex-shrink-0">
            <template v-if="!isAuthenticated">
              <router-link
                to="/login"
                class="btn-primary w-full block text-center py-4 rounded-2xl font-black shadow-lg"
                @click="toggleMobileMenu"
              >
                Login Now
              </router-link>
            </template>
            <template v-else>
              <div class="flex items-center mb-5 px-2">
                <div
                  class="w-11 h-11 rounded-full bg-blue-600 text-white flex items-center justify-center font-black"
                >
                  {{ userInitials }}
                </div>
                <div class="ml-3">
                  <p class="text-sm font-black text-gray-900 leading-tight">
                    {{ user?.firstName }}
                  </p>
                  <p class="text-[10px] text-blue-600 font-bold uppercase">{{ user?.role }}</p>
                </div>
              </div>

              <div class="space-y-1">
                <router-link
                  v-for="link in authLinks"
                  :key="link.name"
                  :to="{ name: link.name }"
                  class="flex items-center px-4 py-3 text-sm font-bold text-gray-600 rounded-xl hover:bg-white transition-all shadow-sm shadow-transparent hover:shadow-gray-200/50"
                  @click="toggleMobileMenu"
                >
                  <Icon
                    :icon="link.icon"
                    class="mr-3 h-4 w-4 text-gray-400"
                  />
                  {{ link.label }}
                </router-link>

                <button
                  v-if="showBackButton"
                  class="w-full flex items-center px-4 py-3 text-sm font-bold text-gray-600 rounded-xl hover:bg-white transition-all shadow-sm shadow-transparent hover:shadow-gray-200/50"
                  @click="handleBackToAdmin"
                >
                  <Icon
                    icon="lucide:step-back"
                    class="mr-3 h-4 w-4 text-gray-400"
                  />
                  Back To Admin
                </button>

                <button
                  class="w-full flex items-center px-4 py-3 text-sm font-black text-red-600 rounded-xl hover:bg-red-50 transition-all mt-2"
                  @click="handleLogout"
                >
                  <Icon
                    icon="lucide:log-out"
                    class="mr-3 h-4 w-4"
                  />
                  Logout Account
                </button>
              </div>
            </template>
          </div>
        </div>
      </div>
    </Teleport>
  </header>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import { ref, watch, computed, onMounted } from 'vue';
import { useAuth } from '../composables/useAuth';

const { user, isAuthenticated, isAdmin, isOwner, logout } = useAuth();

const openDropdown = ref(null);
const isMobileMenuOpen = ref(false);
const showBackButton = ref(false);

const toggleDropdown = (label) => {
  openDropdown.value = openDropdown.value === label ? null : label;
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
  if (!isMobileMenuOpen.value) {
    openDropdown.value = null;
  }
};

const handleBackToAdmin = () => {
  const adminToken = localStorage.getItem('adminToken');
  const adminUser = localStorage.getItem('adminUser');

  if (adminToken && adminUser) {
    localStorage.clear();
    localStorage.setItem('authToken', adminToken);
    localStorage.setItem('user', adminUser);
    window.location.href = '/admin';
  }
};

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
    { name: `${role}-profile`, label: 'Account Profile', icon: 'lucide:user-circle' },
    { name: `${role}-change-password`, label: 'Security & Password', icon: 'lucide:shield-check' },
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

onMounted(() => {
  showBackButton.value = !!localStorage.getItem('adminToken');
  window.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
      openDropdown.value = null;
    }
  });
});

watch(isMobileMenuOpen, (isOpen) => {
  document.body.style.overflow = isOpen ? 'hidden' : '';
});

const navigationMenu = [
  { to: '/', label: 'Home' },
  { to: '/my-bookings', label: 'My Bookings', role: 'user' },
  { to: '/', label: 'About Us' },
  { to: '/', label: 'Blog' },
  {
    label: 'Centers',
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
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.animate-in {
  animation: slideIn 0.2s ease-out;
}
@keyframes slideIn {
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
