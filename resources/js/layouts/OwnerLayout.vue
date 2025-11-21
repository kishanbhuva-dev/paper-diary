<template>
    <div class="min-h-screen bg-white flex">

        <OwnerSidebar ref="sidebarRef" />

        <div :class="[
            'flex flex-col flex-grow w-full',
            'md:ml-60',
        ]">
            <OwnerHeader @toggleSidebar="handleHeaderToggle" :windowWidth="sidebarRef?.windowWidth ?? 0" />

            <main class="transition-all duration-500 bg-white min-h-screen flex-grow">
                <RouterView />
            </main>
        </div>
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