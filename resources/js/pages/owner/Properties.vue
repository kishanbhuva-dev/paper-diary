<template>
    <div class="px-40 sm:px-6 py-4 bg-gray-100 min-h-screen">

        <div v-if="propertiesStore.loading && !propertiesStore.properties.length"
            class="fixed inset-0 bg-white/80 z-50 flex flex-col justify-center items-center backdrop-blur-sm">
            <Icon icon="eos-icons:loading" class="w-10 h-10 text-indigo-600" />
            <span class="mt-2 text-indigo-600 font-semibold">Loading properties...</span>
        </div>

        <div v-if="propertiesStore.error"
            class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded flex items-start font-medium">
            <Icon icon="mdi:alert-circle" class="w-5 h-5 mr-2 flex-shrink-0 text-red-500 mt-0.5" />
            <span class="whitespace-pre-wrap">{{ propertiesStore.error }}</span>
        </div>

        <Basetable title="Properties" :columns="tableColumns" :rows="propertiesStore.properties" :server-side="true"
            :total-items="propertiesStore.total" :per-page="perPage" @search="handleSearch"
            @page-change="handlePageChange" @per-page-change="handlePerPageChange" @open-add-modal="navigateToAdd"
            @open-edit-modal="navigateToEdit" @delete="handleDelete" :showDelete="true" :show-search="true"
            :showAdd="true" :showDownload="true" :showEdit="true" />
    </div>

    <!-- Global Toaster is in App.vue so toasts persist across pages -->
</template>

<script setup>
import Basetable from '../../components/global/Basetable.vue'
// REMOVED: import Propertiesmodal from '../../components/modals/Propertiesmodal.vue'
import { ref, onMounted } from 'vue'
import { Icon } from "@iconify/vue"
import { usePropertiesStore } from '../../stores/propertiesStore'
import { useRouter } from 'vue-router' // <--- REQUIRED for navigation
import Swal from 'sweetalert2'

// Import Vue Sonner for toasts instead of SweetAlert mixin for modern UX
import { toast } from 'vue-sonner'

// Initialize Router and Store
const router = useRouter(); // <--- Initialize Router
const propertiesStore = usePropertiesStore();

// --- TOAST HELPER (Using Vue Sonner) ---
// Replacing the complex Swal.mixin helper with simple Vue Sonner calls
const DEFAULT_TOAST_DURATION = 4000; // milliseconds
const showToast = (type, title, duration = DEFAULT_TOAST_DURATION) => {
    if (type === 'success') {
        toast.success(title, { duration });
    } else if (type === 'error') {
        toast.error(title, { duration });
    } else if (type === 'warning') {
        toast.warning(title, { duration });
    }
};

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

// --- DATA FETCHING (Connects to Store) ---
const loadData = async () => {
    propertiesStore.error = null;
    await propertiesStore.fetchProperties({
        page: currentPage.value,
        per_page: perPage.value,
        search: currentSearch.value
    });
}

// --- NAVIGATION HANDLERS (New/Restored Logic) ---

const navigateToAdd = () => {
    // Navigate to the form page for creation (ID parameter is optional)
    router.push({ name: 'property-form' });
}

const navigateToEdit = (item) => {
    // Navigate to the form page, passing the property ID as a route parameter
    if (item && item.id) {
        router.push({ name: 'property-form', params: { id: String(item.id) } });
    } else {
        showToast('error', "Error: Cannot edit property without an ID.");
    }
}

// --- EVENT HANDLERS: TABLE ---

const handleSearch = (term) => {
    currentSearch.value = term;
    currentPage.value = 1;
    loadData();
}

const handlePageChange = (page) => {
    currentPage.value = page;
    loadData();
}

const handlePerPageChange = (size) => {
    perPage.value = size;
    currentPage.value = 1;
    loadData();
}

// --- DELETE HANDLER (Logic changed to use showToast) ---
const handleDelete = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone and will delete associated data!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        propertiesStore.error = null;

        try {
            const success = await propertiesStore.deleteProperty(id);

            if (success) {
                if (propertiesStore.properties.length === 0 && currentPage.value > 1) {
                    currentPage.value--;
                }
                await loadData();
                showToast('success', 'Property deleted successfully');
            } else {
                // FAILURE LOGIC
                showToast('error', 'Deletion Failed');
                // propertiesStore.error is set, so the banner displays the details
            }
        } catch (error) {
            propertiesStore.error = 'An unexpected error occurred during deletion.';
            showToast('error', 'An unexpected error occurred');
        }
    }
}

// --- INITIALIZATION ---
onMounted(() => {
    loadData();
});
</script>