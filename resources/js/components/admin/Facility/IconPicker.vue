<template>
    <div class="max-w-xl mx-auto p-2.5">
        <!-- Font Awesome CDN -->
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
        />

        <!-- Search Input -->
        <div class="mb-3 relative">
            <input
                v-model="searchTerm"
                type="text"
                placeholder="Search for an icon..."
                class="w-full text-xs border-gray-300 rounded-md text-gray-400 focus:outline-none focus:ring-0 transition duration-300 ease focus:border-blue-900"
            />
            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-blue-950"></i>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="text-center py-16 text-gray-500">
            <div
                class="animate-spin inline-block w-8 h-8 border-4 border-blue-400 border-t-transparent rounded-full"
            ></div>
            <p class="mt-4">Loading icons...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="text-red-600 text-center py-10">
            <i class="fas fa-triangle-exclamation mr-2"></i>
            {{ error }}
        </div>

        <!-- No Results -->
        <div v-else-if="filteredIcons.length === 0" class="text-center text-gray-500 py-10">
            <i class="fas fa-face-frown mr-2"></i>
            No icons found for "{{ searchTerm }}"
        </div>

        <!-- Icon Grid -->
        <div v-else>
            <div class="grid grid-cols-5 sm:grid-cols-6 gap-2">
                <div
                    v-for="icon in paginatedIcons"
                    :key="icon.id + '-' + icon.style"
                    class="cursor-pointer border rounded-md p-1.5 text-center hover:border-blue-900 transition duration-150 ease-in-out"
                    :class="{
                        'border-blue-500 shadow-md':
                            selectedIcon && selectedIcon.id === icon.id && selectedIcon.style === icon.style,
                    }"
                    @click="selectIcon(icon)"
                >
                    <i :class="`fa-${icon.style} fa-${icon.id} text-gray-700 text-lg`"></i>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="flex justify-center mt-6 space-x-1 text-sm">
                <button
                    :disabled="currentPage === 1"
                    class="px-2 py-1 rounded border hover:bg-gray-100 disabled:opacity-50"
                    @click="currentPage = 1"
                >
                    &laquo;
                </button>
                <button
                    :disabled="currentPage === 1"
                    class="px-2 py-1 rounded border hover:bg-gray-100 disabled:opacity-50"
                    @click="currentPage--"
                >
                    &lsaquo;
                </button>
                <span class="px-3 py-1">{{ currentPage }} / {{ totalPages }}</span>
                <button
                    :disabled="currentPage === totalPages"
                    class="px-2 py-1 rounded border hover:bg-gray-100 disabled:opacity-50"
                    @click="currentPage++"
                >
                    &rsaquo;
                </button>
                <button
                    :disabled="currentPage === totalPages"
                    class="px-2 py-1 rounded border hover:bg-gray-100 disabled:opacity-50"
                    @click="currentPage = totalPages"
                >
                    &raquo;
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue';

    const emit = defineEmits(['select-icon']);

    const ICONS_PER_PAGE = 20;
    const FONT_AWESOME_METADATA_URL =
        'https://raw.githubusercontent.com/FortAwesome/Font-Awesome/6.x/metadata/icons.json';

    const searchTerm = ref('');
    const allIcons = ref([]);
    const selectedIcon = ref(null);
    const currentPage = ref(1);
    const isLoading = ref(true);
    const error = ref(null);

    // Filtered icons based on search term
    const filteredIcons = computed(() => {
        const query = searchTerm.value.toLowerCase().trim();
        if (!query) {return allIcons.value.slice(0, 100);}
        return allIcons.value.filter(icon => icon.searchTerms.some(term => term.includes(query)));
    });

    // Paginated icons
    const paginatedIcons = computed(() => {
        const start = (currentPage.value - 1) * ICONS_PER_PAGE;
        return filteredIcons.value.slice(start, start + ICONS_PER_PAGE);
    });

    const totalPages = computed(() => Math.ceil(filteredIcons.value.length / ICONS_PER_PAGE));

    // Fetch icon data
    const fetchIcons = async () => {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await fetch(FONT_AWESOME_METADATA_URL);
            if (!response.ok) {throw new Error(`HTTP error: ${response.status}`);}
            const data = await response.json();

            const freeIcons = Object.entries(data).flatMap(([id, icon]) => {
                const validStyles = icon.styles?.filter(style => ['solid', 'regular', 'brands'].includes(style));
                return (
                    validStyles?.map(style => ({
                        id,
                        name: icon.label,
                        style,
                        searchTerms: [id, icon.label, ...(icon.search?.terms || [])].map(term => term.toLowerCase()),
                    })) || []
                );
            });

            allIcons.value = freeIcons;
        } catch (e) {
            console.error('Failed to fetch Font Awesome icons:', e);
            error.value = 'Failed to load icons. Please try again later.';
        } finally {
            isLoading.value = false;
        }
    };

    const selectIcon = icon => {
        selectedIcon.value = icon;
        emit('select-icon', icon);
    };

    onMounted(fetchIcons);
</script>
