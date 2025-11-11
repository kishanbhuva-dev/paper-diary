<template>
        <!-- The RouterLink component is typically imported globally or locally. -->
        <aside :class="[
                // Base classes: fixed, top-0, left-0, full height, z-index, transition
                'bg-blue-900 text-white fixed top-0 left-0 h-screen transition-all duration-500 ease-in-out z-20 flex flex-col overflow-hidden',

                // --- DESKTOP (md+) ALWAYS OPEN AND STATIC ---
                // NOTE: Updated from w-64 to w-60 as per your template
                'md:w-60 md:translate-x-0',

                // --- MOBILE (<md) DYNAMIC BEHAVIOR ---
                // If Collapsed on Mobile: Hide it off-screen
                windowWidth < 768 && isCollapsed
                        ? '-translate-x-full'
                        // If Expanded on Mobile: Full width and fully visible
                        : windowWidth < 768
                                ? 'w-full translate-x-0'
                                // Default (Fallback): This only applies if we missed a state, but the md: classes cover desktop
                                : '',
        ]">
                <div class=" border-blue-800 border-b h-18 py-2 px-4">
                        <div class="flex items-center justify-between ">
                                <span class="text-lg font-bold tracking-wide">PAPER DIARY</span>

                                <button v-if="windowWidth < 768 && !isCollapsed" @click="toggleSidebar"
                                        class="text-3xl focus:outline-none hover:text-red-400 transition-colors">
                                        <Icon icon="mdi:close" />
                                </button>
                        </div>
                        <span class="text-sm"> welcome mr. owner</span>
                </div>
                <ul class="flex-1 overflow-y-auto space-y-1">
                        <li v-for="item in menuItems" :key="item.name">

                                <RouterLink :to="item.href"
                                        class="flex items-center px-5 py-2 transition-all duration-200 hover:scale-105  group justify-start"
                                        active-class="bg-white text-blue-900"
                                        @click="windowWidth < 768 ? toggleSidebar() : null">
                                        <Icon :icon="item.icon"
                                                class="text-2xl min-w-[2rem] transition-colors duration-300 " />
                                        <span
                                                class="ml-3 text-sm font-medium transition-colors duration-300 whitespace-nowrap">
                                                {{ item.label }}
                                        </span>
                                </RouterLink>
                        </li>
                </ul>
        </aside>
</template>

<script setup>
import { Icon } from "@iconify/vue";
import { ref, onMounted, onUnmounted, defineExpose } from "vue";
// NOTE: <RouterLink> is often globally registered, but if not, you would need to import it.
// e.g., import { RouterLink } from 'vue-router';

const isCollapsed = ref(true);
const windowWidth = ref(window.innerWidth);

const menuItems = [
        { name: "Dashboard", label: "Dashboard", icon: "mdi:home-outline", href: "/owner" },
        { name: "Profile", label: "Profile", icon: "mdi:account-circle-outline", href: "/my_profile" },
        { name: "Register", label: "Register Admin", icon: "mdi:account-plus-outline", href: "/register_admin" },
        { name: "Products", label: "Products", icon: "mdi:cube-outline", href: "/admin_product" },
        { name: "Logout", label: "Logout", icon: "mdi:logout-variant", href: "/login" },
];

function toggleSidebar() {
        // Only allow toggling on mobile
        if (windowWidth.value < 768) {
                isCollapsed.value = !isCollapsed.value;
        }
}

function handleResize() {
        windowWidth.value = window.innerWidth;
        console.log('Window Width Updated To: ' + windowWidth.value);
        if (window.innerWidth >= 768) {
                // Desktop: Ensure it is always considered "expanded"
                isCollapsed.value = false;
        } else {
                // Mobile: Default to collapsed (hidden)
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