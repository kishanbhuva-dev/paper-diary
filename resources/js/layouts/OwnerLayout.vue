<template>
    <div class="min-h-scree bg-white">

        <OwnerSidebar ref="sidebarRef" />

        <OwnerHeader @toggleSidebar="handleHeaderToggle" :windowWidth="sidebarRef?.windowWidth ?? 0" />

        <main :class="[
            'transition-all duration-500 pt-16 p-6 min-h-screen bg-white',
            // Shift content to the right by the sidebar's width (w-64 = 16rem) on desktop
            'md:ml-60'
        ]">
            <RouterView />

        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import OwnerHeader from '../components/owner/OwnerHeader.vue';
import OwnerSidebar from '../components/owner/OwnerSidebar.vue';


// Ref to access the child component's exposed methods/properties
const sidebarRef = ref(null);

// Function called when the Header's toggle button is clicked (Mobile Only)
const handleHeaderToggle = () => {
    if (sidebarRef.value) {
        sidebarRef.value.toggleSidebar();
    }
};
</script>