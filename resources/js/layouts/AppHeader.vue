<template>
  <header class="sticky top-0 z-50 bg-white shadow-sm">
    <div class="container mx-auto">
      <div class="flex justify-between items-center h-16 lg:h-20">
        <div class="flex items-center">
          <a href="/" class="flex items-center space-x-2">
            <Icon icon="lucide:zap" class="w-8 h-8 text-blue-600 shadow-lg" />
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
          <a
            href="/contact"
            class="px-6 py-2.5 text-base font-semibold text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50 transition duration-150 ease-in-out"
          >
            Get in touch
          </a>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { Icon } from "@iconify/vue";
import { ref } from "vue";

const openDropdown = ref(null);

const toggleDropdown = (label) => {
  openDropdown.value = openDropdown.value === label ? null : label;
};

const navigationMenu = [
  {
    to: "/",
    label: "Home",
  },
  {
    to: "/about",
    label: "About us",
  },
  {
    to: "/blog",
    label: "Blog",
  },
  {
    label: "Centres",
    isDropdown: true,
    dropdownItems: [
      { to: "/centres/location-a", label: "Centre A" },
      { to: "/centres/location-b", label: "Centre B" },
      { to: "/centres/online", label: "Online" },
    ],
  },
];
</script>
