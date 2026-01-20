<template>
  <main class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 border-b border-gray-200">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          type="button"
          :class="[
            activeTab === tab.value
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
            'cursor-pointer border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap transition-colors',
          ]"
          @click="handleTabChange(tab.value)"
        >
          {{ tab.name }}
        </button>
      </nav>
    </div>

    <Basetable
      title="My Bookings"
      :columns="tableColumns"
      :rows="bookings"
      server-side
      :total-items="totalItems"
      :per-page="filters.pagination"
      :show-add="false"
      :show-edit="false"
      :show-delete="false"
      :show-view="false"
      :show-download="false"
      :admin-login="false"
      :available-filters="filterConfig"
      @search="handleSearch"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
      @sort="handleSort"
      @filter-change="handleFilterChange"
    >
      <template #actions="{ row }">
        <div class="flex items-center gap-1">
          <button
            v-if="row.status === 'cancelled'"
            type="button"
            class="flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white hover:bg-red-700 rounded-lg transition-all duration-200 font-bold text-xs cursor-pointer shadow-sm active:scale-95"
            @click="openDeleteModal(row.id)"
          >
            <Icon
              icon="mdi:delete-outline"
              class="w-4 h-4"
            />
            Delete
          </button>

          <button
            v-else-if="activeTab === 'upcoming' && row.status !== 'cancelled'"
            type="button"
            class="flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg transition-all duration-200 font-bold text-xs border border-red-100 cursor-pointer"
            @click="initiateCancel(row)"
          >
            <Icon
              icon="mdi:close-circle-outline"
              class="w-4 h-4"
            />
            Cancel
          </button>

          <span
            v-else
            class="text-gray-300"
            >-</span
          >
        </div>
      </template>
    </Basetable>

    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl scale-in-center">
        <h3 class="mb-4 text-xl font-extrabold text-slate-800">Cancel Booking</h3>
        <p class="mb-6 text-sm text-gray-600 leading-relaxed">
          Are you sure you want to cancel your booking for
          <strong class="text-slate-900">{{ selectedBooking?.property?.propertyName }}</strong
          >?
        </p>

        <div class="flex justify-end gap-3">
          <button
            type="button"
            class="rounded-xl px-5 py-2.5 text-sm font-bold text-gray-500 hover:bg-gray-100 transition-colors cursor-pointer"
            @click="isModalOpen = false"
          >
            Keep Booking
          </button>
          <button
            type="button"
            :disabled="isSubmitting"
            class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700 disabled:opacity-50 transition-all cursor-pointer shadow-lg shadow-red-200 active:scale-95"
            @click="confirmCancel"
          >
            {{ isSubmitting ? 'Processing...' : 'Confirm Cancellation' }}
          </button>
        </div>
      </div>
    </div>

    <DeleteModal
      v-model="isDeleteModalOpen"
      title="Delete Cancelled Booking"
      message="Are you sure you want to delete this cancelled booking record? This will remove it from your history."
      warning="This action is permanent and cannot be undone."
      action="delete"
      @confirm="handleDeleteConfirm"
    />
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import Basetable from '../../components/global/Basetable.vue';
import DeleteModal from '../../components/global/DeleteModal.vue';
import { userService } from '../../services/userService';

const tabs = [
  { name: 'Past Bookings', value: 'past' },
  { name: 'Upcoming Bookings', value: 'upcoming' },
];

const activeTab = ref('past');
const bookings = ref([]);
const totalItems = ref(0);
const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isSubmitting = ref(false);
const selectedBooking = ref(null);
const bookingToDeleteId = ref(null);

const filters = ref({
  status: '',
  resourceTypeId: '',
  arrivalDateTime: '',
  departureDateTime: '',
  search: '',
  pagination: 10,
  page: 1,
  sortBy: 'id', // Replaced orderBy with sortBy for PHP
  sortOrder: 'desc', // Replaced orderDirection with sortOrder for PHP
});

const tableColumns = [
  { label: 'Property', key: 'property.propertyName', sortable: true },
  { label: 'Resource Type', key: 'resource_type.name', sortable: true }, // Added Column
  { label: 'Arrival', key: 'arrivalDateTime', sortable: true },
  { label: 'Departure', key: 'departureDateTime', sortable: true },
  { label: 'Status', key: 'status', sortable: true },
];

const filterConfig = [
  {
    label: 'Status',
    key: 'status',
    type: 'select',
    options: [
      { label: 'Pending', value: 'pending' },
      { label: 'Confirmed', value: 'confirm' },
      { label: 'Cancelled', value: 'cancelled' },
    ],
  },
  { label: 'Arrival Date', key: 'arrivalDateTime', type: 'date' },
  { label: 'Departure Date', key: 'departureDateTime', type: 'date' },
];

const fetchBookings = async () => {
  try {
    const res = await userService.getBookings({
      type: activeTab.value,
      ...filters.value,
    });
    if (res.data?.status) {
      bookings.value = res.data.data.data || [];
      totalItems.value = res.data.data.total || 0;
    }
  } catch (error) {
    console.error('Error:', error);
  }
};

const handleSearch = (term) => {
  filters.value.search = term;
  filters.value.page = 1;
  fetchBookings();
};

const handlePageChange = (page) => {
  filters.value.page = page;
  fetchBookings();
};

const handlePerPageChange = (size) => {
  filters.value.pagination = size;
  filters.value.page = 1;
  fetchBookings();
};

const handleSort = (sortData) => {
  // Mapping the frontend key to what the PHP backend expects in its query
  filters.value.sortBy = sortData.key;
  filters.value.sortOrder = sortData.order;
  fetchBookings();
};

const handleFilterChange = (applied) => {
  filters.value.status = applied.status || '';
  filters.value.resourceTypeId = applied.resourceTypeId || '';
  filters.value.arrivalDateTime = applied.arrivalDateTime || '';
  filters.value.departureDateTime = applied.departureDateTime || '';
  filters.value.page = 1;
  fetchBookings();
};

const handleTabChange = (tabValue) => {
  activeTab.value = tabValue;
  filters.value.page = 1;
  fetchBookings();
};

const initiateCancel = (booking) => {
  selectedBooking.value = booking;
  isModalOpen.value = true;
};

const confirmCancel = async () => {
  isSubmitting.value = true;
  try {
    const res = await userService.cancelBooking({ bookingId: selectedBooking.value.id });
    if (res.data?.status) {
      isModalOpen.value = false;
      fetchBookings();
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Modal handlers for Delete
const openDeleteModal = (id) => {
  bookingToDeleteId.value = id;
  isDeleteModalOpen.value = true;
};

const handleDeleteConfirm = async () => {
  try {
    const res = await userService.deleteCancelledBooking(bookingToDeleteId.value);
    if (res.data?.status) {
      isDeleteModalOpen.value = false;
      fetchBookings();
    }
  } catch (error) {
    console.error('Delete failed:', error);
  }
};

onMounted(() => {
  fetchBookings();
});
</script>
