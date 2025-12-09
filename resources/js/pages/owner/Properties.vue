<template>
  <div class="px-4 sm:px-6 py-8 bg-gray-50 min-h-screen">
    <div
      v-if="loading && !properties.length"
      class="fixed inset-0 bg-white/80 z-50 flex flex-col justify-center items-center backdrop-blur-sm transition-opacity duration-300"
    >
      <Icon
        icon="eos-icons:loading"
        class="w-12 h-12 text-blue-600 animate-spin"
      />
      <span class="mt-4 text-xl text-blue-700 font-semibold"
        >Loading properties...</span
      >
    </div>

    <div
      v-if="error"
      class="max-w-7xl mx-auto mb-6 p-4 bg-red-100 text-red-700 border border-red-200 rounded-xl flex items-start font-medium shadow-md"
    >
      <Icon
        icon="mdi:alert-circle"
        class="w-6 h-6 mr-3 flex-shrink-0 text-red-500"
      />
      <span class="whitespace-pre-wrap text-base">{{ error }}</span>
    </div>

    <Basetable
      title="Property Management"
      :columns="tableColumns"
      :rows="properties"
      :server-side="true"
      :total-items="total"
      :per-page="perPage"
      @search="handleSearch"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
      @open-add-modal="navigateToAdd"
      @open-edit-modal="navigateToEdit"
      @delete="confirmDelete"
      :showDelete="true"
      :show-search="true"
      :showAdd="true"
      :showDownload="true"
      :showEdit="true"
    />
  </div>
</template>

<script setup>
import Basetable from "../../components/global/Basetable.vue";
import { ref, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import ownerService from "../../services/ownerService";

// --- 1. STATE MANAGEMENT & INITIALIZATION ---
// Initialize Router
const router = useRouter();

// Reactive State
const loading = ref(false);
const error = ref(null);
const properties = ref([]);
const total = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref("");

// Table Configuration
const tableColumns = [
  { label: "ID", key: "id" },
  { label: "Name", key: "propertyName" },
  { label: "Address", key: "address" },
  { label: "City", key: "city" },
  { label: "Country", key: "country" },
  { label: "Postcode", key: "postcode" },
  { label: "Telephone", key: "telephone" },
  { label: "Email", key: "email" },
  { label: "Status", key: "status" },
];

// --- 2. DATA FETCHING LOGIC ---

/**
 * Maps the status value from the API response to a human-readable string.
 * @param {object} property - The property object from the API.
 * @returns {string} 'Active' or 'Inactive'.
 */
const formatStatus = (property) => {
  // Assuming 1 or 'Active' means Active, otherwise Inactive
  return property.status === 1 || property.status === "Active"
    ? "Active"
    : "Inactive";
};

/**
 * Fetches the property data from the API based on current pagination/search state.
 */
const loadData = async () => {
  error.value = null;
  loading.value = true;
  try {
    const data = await ownerService.fetchProperties({
      page: currentPage.value,
      per_page: perPage.value,
      search: currentSearch.value,
    });

    properties.value =
      data.data.map((property) => ({
        ...property,
        status: formatStatus(property),
      })) || [];

    total.value = data.total || 0;
  } catch (err) {
    // Standardized error message handling
    const errorMessage =
      err.response?.status === 401
        ? "Unauthorized. Please log in again."
        : err.message || "Failed to load properties";

    error.value = errorMessage;
  } finally {
    loading.value = false;
  }
};

// --- 3. NAVIGATION HANDLERS ---

/**
 * Navigates to the property creation form.
 */
const navigateToAdd = () => {
  router.push({ name: "property-wizard" });
};

/**
 * Navigates to the property edit form.
 * @param {object} item - The property object to edit.
 */
const navigateToEdit = (item) => {
  if (item && item.id) {
    // Encodes ID before passing as a URL parameter (using btoa for simple base64 encoding)
    router.push({ name: "property-wizard", params: { id: btoa(item.id) } });
  }
};

// --- 4. TABLE EVENT HANDLERS ---

const handleSearch = (term) => {
  currentSearch.value = term;
  currentPage.value = 1; // Reset to first page on search
  loadData();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  loadData();
};

const handlePerPageChange = (size) => {
  perPage.value = size;
  currentPage.value = 1; // Reset to first page when changing page size
  loadData();
};

// --- 5. DELETION LOGIC ---

/**
 * Performs the actual property deletion via the service and updates the view.
 * @param {number} id - The ID of the property to delete.
 */
const handleDelete = async (id) => {
  error.value = null;

  try {
    // Perform cascade delete orchestration here (service is thin)
    try {
      const images = await ownerService.fetchPropertyImages(id);
      const imageIds = (images || []).map((i) => i.id).filter(Boolean);
      if (imageIds.length) {
        await ownerService.deletePropertyImages(imageIds);
      }
    } catch (err) {
      console.debug("[Properties] delete images failed:", err);
      // continue to attempt to delete property even if image cleanup fails
    }

    await ownerService.deleteProperty(id);

    // Logic to prevent an empty page after deletion
    if (properties.value.length === 1 && currentPage.value > 1) {
      currentPage.value--;
    }
    await loadData(); // Reload data to show updated list

    Swal.fire({
      title: "Deleted!",
      text: "The property has been successfully deleted.",
      icon: "success",
      confirmButtonColor: "#4f46e5",
    });
  } catch (err) {
    const errorMessage =
      err.response?.status === 401
        ? "Unauthorized: Deletion failed."
        : err.message || "Deletion failed";

    error.value = errorMessage;
    Swal.fire({
      title: "Error!",
      text: errorMessage,
      icon: "error",
      confirmButtonColor: "#4f46e5",
    });
  }
};

/**
 * Shows the confirmation dialog before attempting deletion.
 * @param {number} id - The ID of the property to delete.
 */
const confirmDelete = async (id) => {
  const result = await Swal.fire({
    title: "Are you sure?",
    text: "This action cannot be undone and will delete associated data!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#4f46e5", // Indigo-600
    cancelButtonColor: "#ef4444", // Red-500
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
    reverseButtons: true,
  });

  if (result.isConfirmed) {
    await handleDelete(id);
  }
};

// --- 6. SETUP HOOKS ---

onMounted(() => {
  loadData(); // Initial data load when component mounts
});
</script>
