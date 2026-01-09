<template>
  <div class="px-2 sm:px-3 py-4 bg-gray-50 min-h-screen">
    <div
      v-if="loading && !bookings.length"
      class="fixed inset-0 bg-white/80 z-50 flex flex-col justify-center items-center backdrop-blur-sm transition-opacity duration-300"
    >
      <Icon
        icon="eos-icons:loading"
        class="w-12 h-12 text-blue-600 animate-spin"
      />
      <span class="mt-4 text-xl text-blue-700 font-semibold"
        >Loading Bookings...</span
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
      title="Bookings Management"
      :columns="tableColumns"
      :rows="bookings"
      :server-side="true"
      :total-items="total"
      :per-page="perPage"
      :available-filters="filterConfig"
      @filter-change="handleFilterChange"
      @search="handleSearch"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
      @sort="handleSort"
      @open-edit-modal="navigateToViewEdit"
      @delete="confirmDelete"
      :showDelete="false"
      :show-search="true"
      :showAdd="false"
      :showDownload="true"
      :showEdit="false"
      :admin-login="false"
    />
  </div>

  <DeleteModal
    v-model="isConfirmationModalVisible"
    :title="'Cancel Booking'"
    :message="`Are you sure you want to cancel booking ID: ${bookingIdToDelete}?`"
    :warning="'This action will permanently cancel the booking and may trigger a refund process!'"
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

// --- 1. STATE MANAGEMENT ---
const router = useRouter();
const sortBy = ref("id");
const sortOrder = ref("asc");
const actieFilters = ref({});

const loading = ref(false);
const error = ref(null);
const bookings = ref([]);
const total = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref("");

const isConfirmationModalVisible = ref(false);
const bookingIdToDelete = ref(null);

const filterConfig = [
  {
    label: "Status",
    key: "status",
    type: "select",
    options: [
      { label: "Confirmed", value: "Confirmed" },
      { label: "Cancelled", value: "Cancelled" },
    ],
  },
  {
    label: "Payment",
    key: "paymentStatus",
    type: "select",
    options: [
      { label: "Paid", value: "paid" },
      { label: "Unpaid", value: "unpaid" },
    ],
  },
  { label: "Guest Name", key: "guestName", type: "text" },
  { label: "Check-in Date", key: "arrivalDateTime", type: "date" },
  { label: "Check-out Date", key: "departureDateTime", type: "date" },
];

const tableColumns = [
  { label: "ID", key: "id", sortable: true },
  { label: "Property", key: "property.PropertyName", sortable: true },
  { label: "Resource Type", key: "resource_type_name", sortable: true },
  { label: "Guest Name", key: "guestName", sortable: true },
  { label: "Check-in", key: "arrivalDateTime", sortable: true },
  { label: "Check-out", key: "departureDateTime", sortable: true },
  { label: "Price", key: "price", sortable: true },
  { label: "Status", key: "status", sortable: true },
  { label: "Payment Status", key: "paymentStatus", sortable: true },
  { label: "Booked On", key: "bookedOn", sortable: true },
];

// --- 2. DATA FETCHING LOGIC ---
const loadData = async () => {
  error.value = null;
  loading.value = true;
  
  // CLEANING LOGIC: Strip "property." from the sortBy key if it exists
  const cleanSortBy = sortBy.value.includes(".") 
    ? sortBy.value.split(".").pop() 
    : sortBy.value;

  try {
    const data = await ownerService.fetchBookings({
      page: currentPage.value,
      perPage: perPage.value,
      search: currentSearch.value,
      sortBy: cleanSortBy, // Send the flattened key to API
      sortOrder: sortOrder.value,
      ...actieFilters.value,
    });

    bookings.value = data.data || [];
    total.value = data.total || 0;
  } catch (err) {
    const errorMessage = err.response?.status === 401
        ? "Unauthorized. Please log in again."
        : err.message || "Failed to load bookings";
    error.value = errorMessage;
  } finally {
    loading.value = false;
  }
};

const handleSort = (sortData) => {
  // Capture the key exactly as it comes from Basetable
  sortBy.value = sortData.key;
  sortOrder.value = sortData.order;
  loadData();
};

const handleFilterChange = (filters) => {
  actieFilters.value = filters;
  currentPage.value = 1;
  loadData();
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

// --- 3. NAVIGATION & DELETION ---
const navigateToViewEdit = (item) => {
  if (item && item.id) {
    router.push({ name: "owner-booking-detail", params: { id: btoa(item.id) } });
  }
};

const handleDeleteConfirmation = async () => {
  isConfirmationModalVisible.value = false;
  if (bookingIdToDelete.value !== null) {
    await handleDelete(bookingIdToDelete.value);
    bookingIdToDelete.value = null;
  }
};

const handleDelete = async (id) => {
  error.value = null;
  try {
    await ownerService.deleteBooking(id);
    if (bookings.value.length === 1 && currentPage.value > 1) {
      currentPage.value--;
    }
    await loadData();
  } catch (err) {
    error.value = err.message || "Cancellation failed";
  }
};

const confirmDelete = (id) => {
  bookingIdToDelete.value = id;
  isConfirmationModalVisible.value = true;
};

onMounted(() => {
  loadData();
});
</script>