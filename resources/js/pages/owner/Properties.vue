<template>
    <div class="px-4 sm:px-6 py-4 bg-gray-100 min-h-screen">

        <div v-if="propertiesStore.loading && !propertiesStore.properties.length"
            class="fixed inset-0 bg-white/80 z-50 flex flex-col justify-center items-center backdrop-blur-sm">
            <Icon icon="eos-icons:loading" class="w-10 h-10 text-indigo-600" />
            <span class="mt-2 text-indigo-600 font-semibold">Loading properties...</span>
        </div>

        <div v-if="propertiesStore.error"
            class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded flex items-center">
            <Icon icon="mdi:alert-circle" class="w-5 h-5 mr-2" />
            {{ propertiesStore.error }}
        </div>

        <Basetable title="Properties" :columns="tableColumns" :rows="propertiesStore.properties" :server-side="true"
            :total-items="propertiesStore.total" :per-page="perPage" @search="handleSearch"
            @page-change="handlePageChange" @per-page-change="handlePerPageChange" @open-add-modal="openAddModal"
            @open-edit-modal="openEditModal" @delete="handleDelete" :showDelete="true" :show-search="true"
            :showAdd="true" :showDownload="true" :showEdit="true" />
    </div>

    <Propertiesmodal :show="showModal" :item="selectedItem" @close="closeModal" @submit="handleFormSubmit" />
</template>

<script setup>
import Basetable from '../../components/global/Basetable.vue'
import Propertiesmodal from '../../components/modals/Propertiesmodal.vue'
import { ref, onMounted } from 'vue'
import { Icon } from "@iconify/vue"
import { usePropertiesStore } from '../../stores/propertiesStore'

// Initialize Store
const propertiesStore = usePropertiesStore();

// --- TABLE CONFIGURATION ---
const tableColumns = [
    { label: 'ID', key: 'id' },
    { label: 'Name', key: 'propertyName' },
    { label: 'Address', key: 'address' },
    { label: 'City', key: 'city' },
    { label: 'Country', key: 'country' },
    { label: 'Postcode', key: 'postcode' },
    { label: 'Telephone', key: 'telephone' },
    { label: 'Email', key: 'email' },
    { label: 'Status', key: 'statusDisplay' },
]

// --- PAGINATION STATE ---
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref('');

// --- MODAL STATE ---
const showModal = ref(false);
const selectedItem = ref(null);

// --- DATA FETCHING (Connects to Store) ---
const loadData = async () => {
    // Call the store action with pagination params
    await propertiesStore.fetchProperties({
        page: currentPage.value,
        per_page: perPage.value,
        search: currentSearch.value
    });
    // Note: 'total' is auto-updated in the store, passed to Basetable via props
}

// --- EVENT HANDLERS: TABLE ---

const handleSearch = (term) => {
    currentSearch.value = term;
    currentPage.value = 1; // Reset to page 1 when searching
    loadData();
}

const handlePageChange = (page) => {
    currentPage.value = page;
    loadData();
}

const handlePerPageChange = (size) => {
    perPage.value = size;
    currentPage.value = 1; // Reset to page 1 when page size changes
    loadData();
}

const handleDelete = async (id) => {
    if (confirm('Are you sure you want to delete this property?')) {
        await propertiesStore.deleteProperty(id);
        // Check if the current page is empty after delete, if so, go back one page
        if (propertiesStore.properties.length === 0 && currentPage.value > 1) {
            currentPage.value--;
        }
        loadData(); // Refresh data to ensure sync
    }
}

// --- EVENT HANDLERS: MODAL ---

const openAddModal = () => {
    selectedItem.value = null; // Null means "Add Mode"
    showModal.value = true;
}

const openEditModal = (item) => {
    selectedItem.value = item; // Item means "Edit Mode"
    showModal.value = true;
}

const closeModal = () => {
    showModal.value = false;
    selectedItem.value = null;
}

const handleFormSubmit = async (formData) => {
    if (formData.id) {
        await propertiesStore.updateProperty(formData);
    } else {
        await propertiesStore.createProperty(formData);
    }

    // Only close modal if there was no error
    if (!propertiesStore.error) {
        closeModal();
        loadData(); // Refresh table data
    }
}

// --- INITIALIZATION ---
onMounted(() => {
    loadData();
});
</script>