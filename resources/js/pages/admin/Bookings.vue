<template>
  <div class="px-2 sm:px-3 py-4 bg-gray-50 min-h-screen">
    <Basetable
      title="Booking Management"
      :columns="tableColumns"
      :rows="bookings"
      server-side
      :total-items="total"
      :per-page="perPage"
      :available-filters="filterConfig"
      :show-delete="false"
      :show-edit="false"
      show-search
      :show-add="false"
      show-download
      :admin-login="false"
      :show-view="false"
      @filter-change="handleFilterChange"
      @search="handleSearch"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
      @sort="handleSort"
    />
  </div>
</template>

<script setup>
import Basetable from '../../components/global/Basetable.vue';
import { ref, onMounted } from 'vue';
import adminService from '../../services/adminService';

const sortBy = ref('id');
const sortOrder = ref('asc');
const actieFilters = ref({});

const bookings = ref([]);
const perPage = ref(10);
const currentPage = ref(1);
const total = ref(0);
const currentSearch = ref('');

const filterConfig = [
  { label: 'Guest Name', key: 'guestName', type: 'text' },
  { label: 'Guest Email', key: 'guestEmail', type: 'text' },
  { label: 'Arrival Date', key: 'arrivalDateTime', type: 'date' },
  { label: 'Departure Date', key: 'departureDateTime', type: 'date' },
  { label: 'Booked On', key: 'bookedOn', type: 'date' },
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
  { label: 'Property', key: 'propertyName', sortable: true },
  { label: 'Owner Name', key: 'ownerName', sortable: true },
  { label: 'Owner Email', key: 'ownerEmail', sortable: true },
  { label: 'Guest Name', key: 'guestFullName', sortable: true },
  { label: 'Guest Email', key: 'guestEmail', sortable: true },
  { label: 'Check-in', key: 'arrivalDateTime', sortable: true },
  { label: 'Check-out', key: 'departureDateTime', sortable: true },
  { label: 'Booked On', key: 'bookedOn', sortable: true },
  { label: 'Status', key: 'status', sortable: true },
];

const fetchAdminBookings = async () => {
  const res = await adminService.fetchAdminBookings({
    page: currentPage.value,
    perPage: perPage.value,
    search: currentSearch.value,
    sortBy: sortBy.value,
    sortOrder: sortOrder.value,
  });

  if (res.status) {
    bookings.value = res.data.data;
    total.value = res.data.total || 0;
  }
};

const handleSort = (sortData) => {
  sortBy.value = sortData.key;
  sortOrder.value = sortData.order;
  fetchAdminBookings();
};

const handleFilterChange = (filters) => {
  actieFilters.value = filters;
  currentPage.value = 1;
  fetchAdminBookings();
};

const handleSearch = (term) => {
  currentSearch.value = term;
  currentPage.value = 1;
  fetchAdminBookings();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  fetchAdminBookings();
};

const handlePerPageChange = (size) => {
  perPage.value = size;
  currentPage.value = 1;
  fetchAdminBookings();
};

onMounted(() => {
  fetchAdminBookings();
});
</script>
