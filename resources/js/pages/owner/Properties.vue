<template>
    <div class="px-4 sm:px-6 py-4 bg-gray-100 min-h-screen">

        <!-- Optional: Keep the fixed loading indicator for initial data fetch -->
        <div v-if="propertiesStore.loading && !propertiesStore.properties.length"
            class="fixed inset-0 bg-white/80 z-50 flex flex-col justify-center items-center backdrop-blur-sm">
            <Icon icon="eos-icons:loading" class="w-10 h-10 text-indigo-600" />
            <span class="mt-2 text-indigo-600 font-semibold">Loading properties...</span>
        </div>

        <!-- FIX APPLIED HERE: Removed 'truncate' and added 'whitespace-pre-wrap' -->
        <div v-if="propertiesStore.error"
            class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded flex items-start font-medium">
            <Icon icon="mdi:alert-circle" class="w-5 h-5 mr-2 flex-shrink-0 text-red-500 mt-0.5" />
            <span class="whitespace-pre-wrap">{{ propertiesStore.error }}</span>
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
import Swal from 'sweetalert2'

// Initialize Store
const propertiesStore = usePropertiesStore();

// --- SWEETALERT TOAST CONFIGURATION ---
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// --- HELPER FUNCTION FOR COLORED TOASTS ---
const showToast = (icon, title, message = null) => {
    let bgColor = '';

    // Use strong colors that align with Tailwind's 500-600 range
    if (icon === 'success') {
        bgColor = '#10B981'; // Tailwind Green-500/Emerald
    } else if (icon === 'error') {
        bgColor = '#EF4444'; // Tailwind Red-500
    }

    Toast.fire({
        icon: icon,
        title: title,
        html: message, // Use html for multi-line or detailed messages
        background: bgColor, // Set the strong background color
        color: '#FFFFFF', // Ensure text is white for contrast
        iconColor: '#FFFFFF', // Ensure icon is white
    });
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

// --- MODAL STATE ---
const showModal = ref(false);
const selectedItem = ref(null);

// --- DATA FETCHING (Connects to Store) ---
const loadData = async () => {
    // Clear any previous error before fetching new data
    propertiesStore.error = null;
    await propertiesStore.fetchProperties({
        page: currentPage.value,
        per_page: perPage.value,
        search: currentSearch.value
    });
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

// --- DELETE HANDLER ---
const handleDelete = async (id) => {
    // 1. Show Cool Confirmation Modal
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

    // 2. If user clicked Yes
    if (result.isConfirmed) {
        // Clear previous error state
        propertiesStore.error = null;

        try {
            await propertiesStore.deleteProperty(id);

            if (!propertiesStore.error) {
                // SUCCESS LOGIC

                // Pagination logic: Go back a page if current page becomes empty
                if (propertiesStore.properties.length === 0 && currentPage.value > 1) {
                    currentPage.value--;
                }

                await loadData(); // Refresh data

                // 3. Show Success Toast (Green background)
                showToast('success', 'Property deleted successfully');
            } else {
                // FAILURE LOGIC: API error or Integrity Constraint Violation from server
                const errorMessage = propertiesStore.error;

                // 4. Show Red Error Toast
                showToast('error', 'Deletion Failed');

                // The persistent error banner automatically displays 'errorMessage' 
                // because propertiesStore.error is still set.
            }
        } catch (error) {
            // Unexpected JavaScript error
            propertiesStore.error = 'An unexpected error occurred during deletion.';
            showToast('error', 'An unexpected error occurred');
        }
    }
}

// --- EVENT HANDLERS: MODAL ---

const openAddModal = () => {
    selectedItem.value = null;
    propertiesStore.error = null; // Clear error when opening modal
    showModal.value = true;
}

const openEditModal = (item) => {
    selectedItem.value = item;
    propertiesStore.error = null; // Clear error when opening modal
    showModal.value = true;
}

const closeModal = () => {
    showModal.value = false;
    selectedItem.value = null;
    propertiesStore.error = null; // Clear error when closing modal
}

// --- ADD/EDIT SUBMIT HANDLER ---
const handleFormSubmit = async (formData) => {
    const isEdit = !!formData.id;
    propertiesStore.error = null; // Clear error before submission

    try {
        if (isEdit) {
            await propertiesStore.updateProperty(formData);
        } else {
            await propertiesStore.createProperty(formData);
        }

        if (!propertiesStore.error) {
            // SUCCESS LOGIC
            closeModal(); // Close modal only on success
            loadData(); // Refresh table data

            // Show Success Toast (Green background)
            showToast('success', isEdit ? 'Property updated successfully' : 'Property created successfully');
        } else {
            // FAILURE LOGIC (Validation or API error caught in store)
            const errorMessage = propertiesStore.error;

            // Show Red Error Toast (Modal remains open for user to fix validation errors)
            showToast('error', isEdit ? 'Update Failed' : 'Creation Failed');

            // The persistent error banner is now responsible for displaying the detailed 'errorMessage'.
        }
    } catch (e) {
        // Catch unexpected errors
        propertiesStore.error = 'An unexpected error occurred during form submission.';
        showToast('error', 'An unexpected error occurred');
    }
}

// --- INITIALIZATION ---
onMounted(() => {
    loadData();
});
</script>