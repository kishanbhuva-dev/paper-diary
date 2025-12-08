<template>
  <div class="px-4 sm:px-6 py-8 bg-gray-50 min-h-screen">
    <div
      v-if="loading && !properties.length"
      class="fixed inset-0 bg-white/80 z-50 flex flex-col justify-center items-center backdrop-blur-sm transition-opacity duration-300"
    >
      <Icon
        icon="eos-icons:loading"
        class="w-12 h-12 text-indigo-600 animate-spin"
      />
      <span class="mt-4 text-xl text-indigo-700 font-semibold"
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
      @delete="handleDelete"
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

// Initialize Router
const router = useRouter();

// --- STATE ---
const loading = ref(false);
const error = ref(null);
const properties = ref([]);
const total = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref("");

// --- TABLE CONFIGURATION ---
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

// --- DATA FETCHING ---
const loadData = async () => {
  error.value = null;
  loading.value = true;
  try {
    const data = await ownerService.fetchProperties({
      page: currentPage.value,
      per_page: perPage.value,
      search: currentSearch.value,
    });

    // FIX: Map data to convert status (assuming API sends 1/0, true/false, or 'Active'/'Inactive')
    properties.value =
      data.data.map((property) => ({
        ...property,
        // Change this logic if your API uses different values (e.g., true/false)
        status:
          property.status === 1 || property.status === "Active"
            ? "Active"
            : "Inactive",
      })) || [];

    total.value = data.total || 0;
  } catch (err) {
    // FIX: Improved error handling for 401 Unauthorized
    const errorMessage =
      err.response?.status === 401
        ? "Unauthorized. Please log in again."
        : err.message || "Failed to load properties";

    error.value = errorMessage;
  } finally {
    loading.value = false;
  }
};

// --- NAVIGATION HANDLERS (FIXED ROUTE NAMES) ---

const navigateToAdd = () => {
  // FIX: Using the correct, simple route name 'owner-property-form'
  router.push({ name: "property-wizard" });
};

const navigateToEdit = (item) => {
  if (item && item.id) {
    // FIX: Using the correct route name 'owner-property-form'
    router.push({ name: "property-wizard", params: { id: btoa(item.id) } });
  }
};

// --- EVENT HANDLERS: TABLE ---

const handleSearch = (term) => {
  currentSearch.value = term;
  currentPage.value = 1;
  loadData();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  loadData();
};

const handlePerPageChange = (size) => {
  perPage.value = size;
  currentPage.value = 1;
  loadData();
};

// --- DELETE HANDLER ---
const handleDelete = async (id) => {
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
    error.value = null;

    try {
      await ownerService.deleteProperty(id);

      // Ensure that if the last item on the page is deleted, we go back a page
      if (properties.value.length === 1 && currentPage.value > 1) {
        currentPage.value--;
      }
      await loadData();

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
  }
};

// --- INITIALIZATION ---
onMounted(() => {
  loadData();
});
</script>
