<template>
  <div class="px-2 sm:px-3 py-4 bg-gray-50 min-h-screen">
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
      :per-page="perPage"
      :show-delete="true"
      :show-view="false"
      :show-search="true"
      :show-add="true"
      :show-download="true"
      :show-edit="true"
      :admin-login="false"
      @search="handleSearch"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
      @sort="handleSort"
      @open-add-modal="navigateToAdd"
      @open-edit-modal="navigateToEdit"
      @delete="confirmDelete"
    />
  </div>

  <DeleteModal
    v-model="isConfirmationModalVisible"
    title="Delete Property"
    :message="`Are you sure you want to delete the property: ${propertyNameToDelete} ?`"
    warning="This action cannot be undone and will permanently delete the property, all associated resource types, and images!"
    @confirm="handleDeleteConfirmation"
  />
</template>

<script setup>
import Basetable from "../../components/global/Basetable.vue";
import DeleteModal from "../../components/global/DeleteModal.vue";
import { ref, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import { useRouter } from "vue-router";
import ownerService from "../../services/ownerService";

const router = useRouter();
const loading = ref(false);
const error = ref(null);
const properties = ref([]);
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref("");


// Sorting state (default to backend defaults)
const orderBy = ref("id");
const orderDirection = ref("asc");

const isConfirmationModalVisible = ref(false);
const propertyIdToDelete = ref(null);
const propertyNameToDelete = ref("");

const truncateString = (str, maxLen = 15) => {
  if (!str) {return "";}
  const s = String(str);
  return s.length > maxLen ? `${s.substring(0, maxLen)  }...` : s;
};

// Table Configuration with sortable flag
const tableColumns = [
  { label: "Name", key: "propertyName", sortable: true },
  {
    label: "Address",
    key: (item) => truncateString(item.address, 15),
    sortable: false,
  },
  { label: "City", key: "city", sortable: true },
  { label: "Country", key: "country", sortable: true },
  { label: "Postcode", key: "postcode", sortable: true },
  { label: "Telephone", key: "telephone", sortable: true },
  { label: "Email", key: "email", sortable: true },
  { label: "Status", key: "status", sortable: true },
];


const loadData = async () => {
  error.value = null;
  loading.value = true;
  try {
    // Calling service with your existing backend parameter names
    const data = await ownerService.fetchProperties({
      page: currentPage.value,
      pagination: perPage.value,
      search: currentSearch.value,
      orderBy: orderBy.value,
      orderDirection: orderDirection.value,
    });

    properties.value = data.data || [];
  } catch (err) {
    error.value =
      err.response?.status === 401
        ? "Unauthorized. Please log in again."
        : err.message || "Failed to load properties";
  } finally {
    loading.value = false;
  }
};

const navigateToAdd = () => router.push({ name: "property-wizard" });
const navigateToEdit = (item) => {
  if (item && item.id) {
    router.push({ name: "property-wizard", params: { id: btoa(item.id) } });
  }
};

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

const handleSort = (sortData) => {
  orderBy.value = sortData.key;
  orderDirection.value = sortData.order;
  loadData();
};

const handleDeleteConfirmation = async () => {
  isConfirmationModalVisible.value = false;
  if (propertyIdToDelete.value !== null) {
    await handleDelete(propertyIdToDelete.value);
    propertyIdToDelete.value = null;
    propertyNameToDelete.value = "";
  }
};

const handleDelete = async (id) => {
  error.value = null;
  try {
    try {
      const images = await ownerService.fetchPropertyImages(id);
      const imageIds = (images || []).map((i) => i.id).filter(Boolean);
      if (imageIds.length) {await ownerService.deletePropertyImages(imageIds);}
    } catch (err) {
      console.debug("delete images failed:", err);
    }

    await ownerService.deleteProperty(id);
    if (properties.value.length === 1 && currentPage.value > 1)
      {currentPage.value--;}
    await loadData();
  } catch (err) {
    error.value = err.response?.status === 401 ? "Unauthorized" : err.message;
  }
};

const confirmDelete = (id) => {
  const property = properties.value.find((p) => p.id === id);
  propertyIdToDelete.value = id;
  propertyNameToDelete.value = property
    ? property.propertyName
    : "this property";
  isConfirmationModalVisible.value = true;
};

onMounted(() => {
  loadData();
});
</script>
