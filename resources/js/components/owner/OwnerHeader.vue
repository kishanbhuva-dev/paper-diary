<template>
    <header class="fixed flex items-center h-18 px-4 bg-white transition-all duration-500 z-10 shadow" :style="{
        // The sidebar is w-60 (15rem)
        left: windowWidth >= 768 ? '15rem' : '0',
        width: windowWidth >= 768 ? 'calc(100% - 15rem)' : '100vw'
    }">

        <!-- 1. LEFT COLUMN (Mobile Menu Toggle) -->
        <!-- This takes up space on mobile to push the logo slightly right, ensuring true center -->
        <div class="flex items-center justify-start w-10 h-10 md:w-auto">
            <button v-if="windowWidth < 768"
                class="flex items-center justify-center w-full h-full rounded hover:bg-gray-100"
                @click="$emit('toggleSidebar')">
                <Icon icon="mdi:view-grid-outline" class="text-2xl text-gray-700" />
            </button>
            <!-- Desktop view: This div is effectively empty/zero width -->
        </div>

        <!-- 2. CENTER COLUMN (Logo) -->
        <!-- This uses mx-auto to center it within the remaining space -->
        <div class="flex-grow flex justify-center">
            <a href="/dashboard">
                <!-- Added a meaningful alt text and max-h-10 to fit the header height -->
                <img src="" alt="Paper Diary Logo" class="max-h-10 object-contain" />
            </a>
        </div>


        <!-- 3. RIGHT COLUMN (Spacer/Profile/Other Buttons) -->
        <!-- This acts as a spacer on mobile to balance the left toggle button. -->
        <!-- On desktop, you can put user info or notifications here. -->
        <div class="flex items-center justify-end w-10 h-10 md:w-auto">
            <!-- Currently empty, or add profile/notifications here -->
            <div v-if="windowWidth < 768" class="w-10 h-10"></div>
            <!-- If you had profile icons, they'd go here: -->
            <!-- <Icon v-else icon="mdi:bell-outline" class="text-2xl text-gray-700 ml-4 cursor-pointer" /> -->
        </div>

    </header>
</template>

<script setup>
import { Icon } from "@iconify/vue";
import { ref, computed } from "vue";

const props = defineProps({
    windowWidth: {
        type: Number,
        required: true,
    },
});

// Define emits
const emits = defineEmits(['toggleSidebar']);
</script>