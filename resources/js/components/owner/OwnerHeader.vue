<template>
    <header class=" sticky top-0 z-10 flex items-center h-18 px-4 bg-white transition-all duration-500 border-b border-indigo-100">

        <!-- (Mobile Menu Toggle) -->
        <!-- This takes up space on mobile to push the logo slightly right, ensuring true center -->
        <div class="flex items-center justify-start w-10 h-10 md:w-auto">
            <button v-if="windowWidth < 768"
                class="flex items-center justify-center w-full h-full rounded hover:bg-gray-100"
                @click="$emit('toggleSidebar')">
                <Icon icon="mdi:view-grid-outline" class="text-2xl text-gray-700" />
            </button>
        </div>

        <!-- (Logo) -->
        <div class="flex-grow flex justify-center">
            <a href="/dashboard">
                <!-- <img src="" alt="Paper Diary Logo" class="max-h-10 object-contain" /> -->
            </a>
        </div>


        <!-- (Spacer/Profile/Other Buttons) -->
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