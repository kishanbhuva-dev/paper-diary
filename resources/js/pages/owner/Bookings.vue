// 4. Booking.vue (Refactored for Bookings Management)

<template>
  <div class="px-4 sm:px-6 py-8 bg-gray-50 min-h-screen">
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
      @search="handleSearch"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
      @open-edit-modal="navigateToViewEdit"
      @delete="confirmDelete"
      :showDelete="true"
      :show-search="true"
      :showAdd="false"
      :showDownload="true"
      :showEdit="true"
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

// --- 1. STATE MANAGEMENT & INITIALIZATION ---
// Initialize Router
const router = useRouter();

// Reactive State
const loading = ref(false);
const error = ref(null);
const bookings = ref([]); // Changed from 'properties' to 'bookings'
const total = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref("");

// Delete Modal State (adapted for bookings)
const isConfirmationModalVisible = ref(false);
const bookingIdToDelete = ref(null);

const tableColumns = [
  { label: "ID", key: "id" },
  { label: "Property", key: "propertyName" },
  { label: "Resource Type", key: "resourceTypeName" },
  { label: "Guest Name", key: "userName" },
  { label: "Check-in", key: "arrivalDateTime" },
  { label: "Check-out", key: "departureDateTime" },
  { label: "Price", key: "price" },
  { label: "Status", key: "status" },
  { label: "Payment Status", key: "paymentStatus" },
  { label: "Booked On", key: "from" },
];

// --- 2. DATA FETCHING LOGIC ---
/** Fetches the booking data from the API based on current pagination/search state.
 */
const loadData = async () => {
  error.value = null;
  loading.value = true;
  try {
    const data = await ownerService.fetchBookings({
      page: currentPage.value,
      perPage: perPage.value,
      search: currentSearch.value,
      // We can also pass sortBy/sortOrder if Basetable supports it
    });

    bookings.value = data.data || [];
    total.value = data.total || 0;
  } catch (err) {
    // Standardized error message handling
    const errorMessage =
      err.response?.status === 401
        ? "Unauthorized. Please log in again."
        : err.message || "Failed to load bookings";

    error.value = errorMessage;
  } finally {
    loading.value = false;
  }
};

// --- 3. NAVIGATION HANDLERS ---

/* Navigates to the booking view/edit form.
 * @param {object} item - The booking object to edit/view.
 * Note: Assuming you have a route set up for a single booking detail/edit.
 */
const navigateToViewEdit = (item) => {
  if (item && item.id) {
    // Encodes ID before passing as a URL parameter (using btoa for simple base64 encoding)
    // You will need a route named 'owner-booking-detail' or similar
    router.push({
      name: "owner-booking-detail",
      params: { id: btoa(item.id) },
    });
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

// --- 5. DELETION (Cancellation) LOGIC ---

/**
 * Executes when the custom modal's confirmation button is clicked.
 */
const handleDeleteConfirmation = async () => {
  isConfirmationModalVisible.value = false;

  if (bookingIdToDelete.value !== null) {
    // In a real application, you might use an update to change status to 'Cancelled'
    // rather than a hard delete, but based on the original property logic, we'll use delete.
    await handleDelete(bookingIdToDelete.value);
    bookingIdToDelete.value = null; // Clear ID state
  }
};

/**
 * Performs the actual booking deletion/cancellation via the service and updates the view.
 * @param {number} id - The ID of the booking to delete.
 */
const handleDelete = async (id) => {
  error.value = null;

  try {
    await ownerService.deleteBooking(id);

    // prevent an empty page after deletion
    if (bookings.value.length === 1 && currentPage.value > 1) {
      currentPage.value--;
    }
    await loadData();
  } catch (err) {
    const errorMessage =
      err.response?.status === 401
        ? "Unauthorized: Cancellation failed."
        : err.message || "Cancellation failed";

    error.value = errorMessage;
    console.error("Cancellation failed:", errorMessage);
  }
};

/**
 * Shows the custom confirmation dialog before attempting deletion.
 * @param {number} id - The ID of the booking to delete.
 */
const confirmDelete = (id) => {
  bookingIdToDelete.value = id;
  isConfirmationModalVisible.value = true;
};

// --- 6. SETUP HOOKS ---

onMounted(() => {
  loadData(); // Initial data load when component mounts
});
</script>
