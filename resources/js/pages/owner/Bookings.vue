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
      <span class="mt-4 text-xl text-blue-700 font-semibold">Loading Bookings...</span>
    </div>

    <div
      v-if="error"
      class="max-w-7xl mx-auto mb-6 p-4 bg-red-100 text-red-700 border border-red-200 rounded-xl flex items-start font-medium shadow-md"
    >
      <Icon
        icon="mdi:alert-circle"
        class="w-6 h-6 mr-3 shrink-0 text-red-500"
      />
      <span class="whitespace-pre-wrap text-base">{{ error }}</span>
    </div>

    <Basetable
      title="Bookings Management"
      :columns="tableColumns"
      :rows="bookings"
      server-side
      :per-page="perPage"
      :available-filters="filterConfig"
      :show-delete="false"
      :show-edit="false"
      :show-search
      :show-add="false"
      :show-download
      :admin-login="false"
      @filter-change="handleFilterChange"
      @search="handleSearch"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
      @sort="handleSort"
      @view="showbookingdata"
      @delete="confirmDelete"
    />
  </div>

  <Basemodal
    v-model="isModalVisible"
    title="Booking Details"
    width="max-w-3xl"
    :show-save="isBookingDateValid"
    @save="handleSave"
  >
    <div class="space-y-6">
      <div
        class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 grid grid-cols-1 md:grid-cols-2 gap-4"
      >
        <div class="space-y-1">
          <label class="text-[10px] font-black text-blue-400 uppercase tracking-widest"
            >Property</label
          >
          <p class="text-sm font-bold text-blue-900">
            {{ selectedBooking.property?.propertyName || '-' }}
          </p>
        </div>
        <div class="space-y-1">
          <label class="text-[10px] font-black text-blue-400 uppercase tracking-widest"
            >Resource</label
          >
          <p class="text-sm font-bold text-blue-900">
            {{ selectedBooking.resource_type_name }}
          </p>
        </div>
        <div class="space-y-1">
          <label class="text-[10px] font-black text-blue-400 uppercase tracking-widest"
            >Booking Status</label
          >
          <div class="flex items-center gap-2">
            <span
              :class="`px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${selectedBooking.status === 'confirm' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`"
            >
              {{ selectedBooking.status }}
            </span>
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-[10px] font-black text-blue-400 uppercase tracking-widest"
            >Booked On</label
          >
          <p class="text-sm font-medium text-blue-900">{{ selectedBooking.bookedOn }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1">
          <label class="text-xs font-bold text-gray-500 uppercase">Guest Name</label>
          <input
            v-model="selectedBooking.guestName"
            disabled
            class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 outline-none"
          />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-bold text-gray-500 uppercase">Guest Email</label>
          <input
            v-model="selectedBooking.guestEmail"
            disabled
            class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 outline-none"
          />
        </div>

        <div class="space-y-1">
          <label class="text-xs font-bold text-gray-500 uppercase">Arrival Date</label>
          <input
            v-model="selectedBooking.arrivalDateTime"
            type="text"
            disabled
            class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 outline-none"
          />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-bold text-gray-500 uppercase">Departure Date</label>
          <input
            v-model="selectedBooking.departureDateTime"
            type="text"
            disabled
            class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 outline-none"
          />
        </div>

        <div class="md:col-span-2 space-y-1">
          <label class="text-xs font-bold text-gray-500 uppercase">Guest Address</label>
          <textarea
            v-model="selectedBooking.guestAddress"
            disabled
            rows="2"
            class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 outline-none resize-none"
          ></textarea>
        </div>

        <div class="md:col-span-2 space-y-1">
          <label class="text-xs font-bold text-blue-600 uppercase flex items-center">
            <Icon
              icon="mdi:note-edit-outline"
              class="mr-1"
            />
            Owner Notes
          </label>
          <textarea
            v-model="selectedBooking.owner_note"
            :disabled="!isBookingDateValid"
            :placeholder="
              isBookingDateValid
                ? 'Add internal notes about this booking here...'
                : 'Notes cannot be edited for past bookings'
            "
            rows="3"
            class="w-full p-3 rounded-xl border border-blue-200 focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/30 disabled:bg-gray-50 disabled:border-gray-200"
          ></textarea>
        </div>
      </div>

      <div
        v-if="selectedBooking.status === 'confirm' && isBookingDateValid"
        class="pt-4 border-t border-gray-100"
      >
        <button
          class="flex items-center text-red-600 font-bold text-sm hover:underline cursor-pointer group"
          @click="confirmDelete(selectedBooking.id)"
        >
          <Icon
            icon="mdi:calendar-remove"
            class="mr-2 w-5 h-5 group-hover:scale-110 transition-transform"
          />
          Cancel This Booking
        </button>
      </div>
    </div>
  </Basemodal>

  <ConfirmModal
    v-model="isConfirmationModalVisible"
    title="Cancel Booking"
    :message="`Are you sure you want to cancel booking ID: ${bookingIdToDelete}?`"
    @confirm="handleDeleteConfirmation"
  />
</template>

<script setup>
import Basetable from '../../components/global/Basetable.vue';
import ConfirmModal from '../../components/owner/ConfirmModal.vue';
import Basemodal from '../../components/global/BaseModal.vue';
import { ref, onMounted, computed } from 'vue';
import { Icon } from '@iconify/vue';
import ownerService from '../../services/ownerService';

// --- 1. STATE MANAGEMENT ---
const sortBy = ref('id');
const sortOrder = ref('asc');
const actieFilters = ref({});
const loading = ref(false);
const error = ref(null);
const bookings = ref([]);
const total = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref('');

// Modal & Selection State
const isModalVisible = ref(false);
const selectedBooking = ref({});
const isConfirmationModalVisible = ref(false);
const bookingIdToDelete = ref(null);

const filterConfig = [
  {
    label: 'Guest Name',
    key: 'guestName',
    type: 'text',
  },
  {
    label: 'Guest Email',
    key: 'guestEmail',
    type: 'text',
  },
  {
    label: 'Arrival Date',
    key: 'arrivalDateTime',
    type: 'date',
  },
  {
    label: 'Departure Date',
    key: 'departureDateTime',
    type: 'date',
  },
  {
    label: 'Booked On',
    key: 'bookedOn',
    type: 'date',
  },
  {
    label: 'From Now',
    key: 'fromNow',
    type: 'select',
    options: [
      { label: 'All From now', value: 'all' },
      { label: '1 Year ago', value: '1year' },
      { label: '6 Months ago', value: '6months' },
      { label: '4 Months ago', value: '4months' },
      { label: '2 Month ago', value: '2month' },
      { label: '1 Month ago', value: '1month' },
      { label: '2 Weeks ago', value: '2weeks' },
      { label: '1 Week ago', value: '1week' },
      { label: 'Yesterday', value: 'yesterday' },
      { label: 'Today', value: 'today' },
    ],
  },
  {
    label: 'Status',
    key: 'status',
    type: 'select',
    options: [
      { label: 'Confirmed', value: 'confirm' },
      { label: 'Cancelled', value: 'cancelled' },
    ],
  },
  {
    label: 'Payment',
    key: 'paymentStatus',
    type: 'select',
    options: [
      { label: 'Paid', value: 'paid' },
      { label: 'Unpaid', value: 'failed' },
    ],
  },
];

const tableColumns = [
  { label: 'Property', key: 'property.propertyName', sortable: true },
  { label: 'Resource', key: 'resource_type_name', sortable: true },
  { label: 'Guest Name', key: 'guestName', sortable: true },
  { label: 'Guest Email', key: 'guestEmail', sortable: true },
  { label: 'Check-in', key: 'arrivalDateTime', sortable: true },
  { label: 'Check-out', key: 'departureDateTime', sortable: true },
  { label: 'Price', key: 'price', sortable: true },
  { label: 'bookedOn', key: 'bookedOn', sortable: true },
  { label: 'FROM-NOW', key: 'fromNow', sortable: true },
  { label: 'Price', key: 'price', sortable: true },
  { label: 'Status', key: 'status', sortable: true },
];

// --- 2. LOGIC & COMPUTED ---

// Check if booking date is passed or today/future
const isBookingDateValid = computed(() => {
  if (!selectedBooking.value.arrivalDateTime) {
    return false;
  }
  const bookingDate = new Date(selectedBooking.value.arrivalDateTime);
  const today = new Date();
  today.setHours(0, 0, 0, 0); // Reset time to compare only dates
  return bookingDate >= today;
});

const loadData = async () => {
  error.value = null;
  loading.value = true;
  try {
    const data = await ownerService.fetchBookings({
      page: currentPage.value,
      perPage: perPage.value,
      search: currentSearch.value,
      sortBy: sortBy.value,
      sortOrder: sortOrder.value,
      ...actieFilters.value,
    });
    bookings.value = data.data || [];
    total.value = data.total || 0;
  } catch (err) {
    error.value = err.message || 'Failed to load bookings';
  } finally {
    loading.value = false;
  }
};

const showbookingdata = (item) => {
  selectedBooking.value = { ...item };
  isModalVisible.value = true;
};

const handleSave = async () => {
  if (!isBookingDateValid.value) {
    return;
  }

  loading.value = true;
  try {
    const payload = {
      id: selectedBooking.value.id,
      ownerNotes: selectedBooking.value.owner_note,
    };
    await ownerService.updateBooking(selectedBooking.value.id, payload);
    isModalVisible.value = false;
    await loadData();
  } catch (err) {
    error.value = err;
  } finally {
    loading.value = false;
  }
};

// Standard Table Handlers
const handleSort = (sortData) => {
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

// Cancellation Logic
const confirmDelete = (id) => {
  bookingIdToDelete.value = id;
  isConfirmationModalVisible.value = true;
};

const handleDeleteConfirmation = async () => {
  isConfirmationModalVisible.value = false;
  if (bookingIdToDelete.value !== null) {
    try {
      await ownerService.deleteBooking(bookingIdToDelete.value);
      isModalVisible.value = false;
      await loadData();
    } catch (err) {
      error.value = err;
    } finally {
      bookingIdToDelete.value = null;
    }
  }
};

onMounted(() => {
  loadData();
});
</script>
