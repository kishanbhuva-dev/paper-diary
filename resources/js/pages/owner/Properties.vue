<template>
  <div class="px-40 sm:px-6 py-4 bg-gray-100 min-h-screen">
    <div
      v-if="loading && !properties.length"
      class="fixed inset-0 bg-white/80 z-50 flex flex-col justify-center items-center backdrop-blur-sm"
    >
      <Icon icon="eos-icons:loading" class="w-10 h-10 text-indigo-600" />
      <span class="mt-2 text-indigo-600 font-semibold"
        >Loading properties...</span
      >
    </div>

    <div
      v-if="error"
      class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded flex items-start font-medium"
    >
      <Icon
        icon="mdi:alert-circle"
        class="w-5 h-5 mr-2 flex-shrink-0 text-red-500 mt-0.5"
      />
      <span class="whitespace-pre-wrap">{{ error }}</span>
    </div>

    <Basetable
      title="Properties"
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

  <!-- Global Toaster is in App.vue so toasts persist across pages -->
</template>

<script setup>
import Basetable from "../../components/global/Basetable.vue";
import { ref, reactive, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import { toast } from "vue-sonner";
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
  { label: "Status", key: "statusDisplay" },
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
    properties.value = data.data || [];
    total.value = data.total || 0;
  } catch (err) {
    error.value = err.message || "Failed to load properties";
  } finally {
    loading.value = false;
  }
};

// --- NAVIGATION HANDLERS (New/Restored Logic) ---

const navigateToAdd = () => {
  // Navigate to the form page for creation (ID parameter is optional)
  router.push({ name: "property-form" });
};

const navigateToEdit = (item) => {
  // Navigate to the form page, passing the property ID as a route parameter
  if (item && item.id) {
    router.push({ name: "property-form", params: { id: btoa(item.id) } });
  } else {
    toast.error("Error: Cannot edit property without an ID.");
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
    confirmButtonColor: "#4f46e5",
    cancelButtonColor: "#ef4444",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
    reverseButtons: true,
  });

  if (result.isConfirmed) {
    error.value = null;

    try {
      await ownerService.deleteProperty(id);
      toast.success("Property deleted successfully");

      if (properties.value.length === 0 && currentPage.value > 1) {
        currentPage.value--;
      }
      await loadData();
    } catch (err) {
      error.value = err.message || "Deletion failed";
      toast.error(error.value);
    }
  }
};

// --- INITIALIZATION ---
onMounted(() => {
  loadData();
});
</script>
