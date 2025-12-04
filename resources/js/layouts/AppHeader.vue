<template>
  <header class="sticky top-0 z-50 bg-white shadow-sm">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16 lg:h-20">
        <div class="flex items-center">
          <a href="/" class="flex items-center space-x-2">
            <img src="/public/main_logo.png" alt="Paper Diary" />
          </a>
        </div>

        <nav class="hidden lg:flex items-center space-x-10">
          <template v-for="item in navigationMenu" :key="item.label">
            <a
              v-if="!item.isDropdown"
              :href="item.to"
              class="flex items-center text-base font-medium text-gray-700 hover:text-blue-600 transition duration-150 ease-in-out"
            >
              {{ item.label }}
            </a>

            <div v-else class="relative">
              <button
                @click="toggleDropdown(item.label)"
                class="flex items-center text-base font-medium text-gray-700 hover:text-blue-600 focus:outline-none"
              >
                <span>{{ item.label }}</span>
                <Icon
                  icon="lucide:chevron-down"
                  :class="{ 'rotate-180': openDropdown === item.label }"
                  class="h-4 w-4 ml-1 transform transition-transform duration-200"
                />
              </button>

              <div
                :class="{
                  'opacity-100 visible translate-y-0':
                    openDropdown === item.label,
                  'opacity-0 invisible translate-y-2':
                    openDropdown !== item.label,
                }"
                class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 transition-all duration-200 z-50"
              >
                <div class="py-1">
                  <a
                    v-for="subItem in item.dropdownItems"
                    :key="subItem.label"
                    :href="subItem.to"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    {{ subItem.label }}
                  </a>
                </div>
              </div>
            </div>
          </template>
        </nav>

        <div class="flex items-center">
          <router-link
            to="/login"
            class="btn-primary hidden lg:block px-6 py-2"
          >
            Login Now
          </router-link>
          <div class="lg:hidden">
            <button
              @click="toggleMobileMenu"
              class="p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-900"
            >
              <Icon icon="lucide:menu" class="h-6 w-6" />
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- mobile menu -->
    <div v-if="isMobileMenuOpen" class="lg:hidden">
      <div
        class="fixed inset-0 bg-black opacity-50 z-40"
        @click="toggleMobileMenu"
      ></div>
      <div class="fixed inset-y-0 right-0 max-w-64 w-full bg-white z-50 p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-lg font-semibold">Menu</h2>
          <button
            @click="toggleMobileMenu"
            class="p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100"
          >
            <Icon icon="lucide:x" class="h-6 w-6" />
          </button>
        </div>
        <nav class="flex flex-col space-y-4">
          <template v-for="item in navigationMenu" :key="item.label">
            <a
              v-if="!item.isDropdown"
              :href="item.to"
              class="block px-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-md"
              @click="toggleMobileMenu"
            >
              {{ item.label }}
            </a>
            <div v-else>
              <button
                @click="toggleDropdown(item.label)"
                class="w-full text-left flex items-center justify-between px-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-md"
              >
                <span>{{ item.label }}</span>
                <Icon
                  icon="lucide:chevron-down"
                  :class="{ 'rotate-180': openDropdown === item.label }"
                  class="h-5 w-5 transform transition-transform duration-200"
                />
              </button>
              <div v-if="openDropdown === item.label" class="mt-2 pl-4">
                <a
                  v-for="subItem in item.dropdownItems"
                  :key="subItem.label"
                  :href="subItem.to"
                  class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md"
                  @click="toggleMobileMenu"
                >
                  {{ subItem.label }}
                </a>
              </div>
            </div>
          </template>
          <router-link
            to="/login"
            class="btn-primary px-6 py-2 mt-4 text-center"
            @click="toggleMobileMenu"
          >
            Login Now
          </router-link>
        </nav>
      </div>
    </div>
  </header>
</template>

<script setup>
import { Icon } from "@iconify/vue";
import { ref, watch, onUnmounted } from "vue";

const openDropdown = ref(null);
const isMobileMenuOpen = ref(false);

const toggleDropdown = (label) => {
  openDropdown.value = openDropdown.value === label ? null : label;
};
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

watch(isMobileMenuOpen, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = "hidden";
  } else {
    document.body.style.overflow = "";
  }
});

onUnmounted(() => {
  document.body.style.overflow = "";
});

const navigationMenu = [
  {
    to: "/",
    label: "Home",
  },
  {
    to: "/",
    label: "About us",
  },
  {
    to: "/",
    label: "Blog",
  },
  {
    label: "Center",
    isDropdown: true,
    dropdownItems: [
      { to: "/", label: "Centre A" },
      { to: "/", label: "Centre B" },
      { to: "/", label: "Online" },
    ],
  },
];
</script>
