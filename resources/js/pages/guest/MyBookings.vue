<template>
  <main class="container mx-auto py-12">
    <div class="px-4 sm:px-6 lg:px-8">
      <div
        class="mb-3 flex flex-col justify-between gap-3 text-sm font-semibold text-gray-600 md:flex-row"
      >
        <div
          class="grid divide-gray-300 overflow-hidden rounded-md border border-gray-300 max-md:divide-y md:grid-cols-4 md:divide-x"
        >
          <div class="relative transition-colors hover:bg-gray-50">
            <select
              v-model="filters.status"
              @change="fetchBookings"
              class="mr-6 w-full cursor-pointer appearance-none p-2 outline-0"
            >
              <option value="">Status</option>
              <option value="pending">Pending</option>
              <option value="confirm">Confirmed</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <Icon
              icon="mdi:chevron-up-down"
              class="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2"
            />
          </div>
          <div class="relative transition-colors hover:bg-gray-50">
            <input
              type="date"
              v-model="filters.arrivalDateTime"
              @change="fetchBookings"
              class="w-full cursor-pointer p-2 outline-0"
              onclick="this.showPicker()"
            />
          </div>
          <div class="relative transition-colors hover:bg-gray-50">
            <input
              type="date"
              v-model="filters.departureDateTime"
              @change="fetchBookings"
              class="w-full cursor-pointer p-2 outline-0"
              onclick="this.showPicker()"
            />
          </div>
          <div>
            <button
              @click="resetFilters"
              class="flex-center w-full cursor-pointer gap-1 p-2 text-red-600 outline-0 transition-colors hover:bg-red-50"
            >
              <Icon icon="mdi:refresh" />
              Reset Filter
            </button>
          </div>
        </div>
        <div class="relative transition-colors hover:bg-gray-50">
          <input
            type="text"
            v-model="filters.search"
            @keyup.enter="fetchBookings"
            placeholder="Search property or status"
            class="w-full rounded-md border border-gray-300 p-2 pr-10 outline-0"
          />
          <button
            @click="fetchBookings"
            class="absolute top-1/2 right-1 -translate-y-1/2 cursor-pointer rounded bg-primary p-2 text-white"
          >
            <Icon icon="mdi:search" />
          </button>
        </div>
      </div>

      <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            @click="handleTabChange(tab.value)"
            :class="[
              activeTab === tab.value
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'cursor-pointer border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap',
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <div class="overflow-hidden rounded-lg border border-gray-300">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  v-for="header in tableHeaders"
                  :key="header"
                  class="px-6 py-3 text-left text-xs font-semibold tracking-wider uppercase"
                >
                  {{ header }}
                </th>
                <th
                  v-if="activeTab === 'upcoming'"
                  class="px-6 py-3 text-left text-xs font-semibold tracking-wider uppercase"
                >
                  ACTION
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-if="isLoading">
                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                  Loading bookings...
                </td>
              </tr>
              <tr v-else-if="bookings.length === 0">
                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                  No bookings found.
                </td>
              </tr>
              <tr v-for="(booking, index) in bookings" :key="booking.id" v-else>
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  <!-- #{{ booking.id }} -->
                  {{ ++index }}
                </td>
                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                  {{ booking.property?.propertyName || "N/A" }}
                  <span class="text-xs text-gray-500">
                    - {{ booking.resource_type?.name }}</span
                  >
                </td>
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  {{ booking.arrivalDateTime }}
                </td>
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  {{ booking.departureDateTime }}
                </td>
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex rounded-full px-2 text-xs leading-5 font-semibold',
                      statusClasses[booking.status] ||
                        'bg-gray-100 text-gray-800',
                    ]"
                  >
                    {{ booking.status }}
                  </span>
                </td>
                <td
                  v-if="activeTab === 'upcoming'"
                  class="px-6 py-4 text-sm whitespace-nowrap"
                >
                  <button
                    v-if="booking.status !== 'cancelled'"
                    @click="initiateCancel(booking)"
                    class="text-red-600 hover:text-red-900 font-bold transition-colors cursor-pointer"
                  >
                    Cancel
                  </button>
                  <!-- <span v-else class="text-gray-400">N/A</span> -->
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h3 class="mb-4 text-lg font-bold text-gray-900">Cancel Booking</h3>
        <p class="mb-4 text-sm text-gray-600">
          Are you sure you want to cancel your booking for
          <strong>{{ selectedBooking?.property?.propertyName }}</strong
          >? This will initiate a refund through Stripe.
        </p>

        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-700"
            >Reason for cancellation</label
          >
          <select
            v-model="cancelReason"
            class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-primary focus:outline-none"
          >
            <option value="">Select a reason</option>
            <option value="Change of plans">Change of plans</option>
            <option value="Found a better deal">Found a better deal</option>
            <option value="Personal emergency">Personal emergency</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="flex justify-end gap-3">
          <button
            @click="isModalOpen = false"
            class="rounded-md px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer"
          >
            Keep Booking
          </button>
          <button
            @click="confirmCancel"
            :disabled="!cancelReason || isSubmitting"
            class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50 transition-colors cursor-pointer"
          >
            {{ isSubmitting ? "Processing..." : "Confirm Cancellation" }}
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import { userService } from "../../services/userService";
// import dayjs from "dayjs";

const tabs = [
  { name: "Past Bookings", value: "past" },
  { name: "Upcoming Bookings", value: "upcoming" },
];
const activeTab = ref("past");
const bookings = ref([]);
const isLoading = ref(false);

const isModalOpen = ref(false);
const isSubmitting = ref(false);
const selectedBooking = ref(null);
const cancelReason = ref("");

const tableHeaders = ["ID", "PROPERTY", "ARRIVAL", "DEPARTURE", "STATUS"];

const filters = ref({
  status: "",
  arrivalDateTime: "",
  departureDateTime: "",
  search: "",
  pagination: 10,
});

const statusClasses = {
  confirm: "bg-green-100 text-green-800",
  pending: "bg-yellow-100 text-yellow-800",
  cancelled: "bg-red-100 text-red-800",
  completed: "bg-blue-100 text-blue-800",
};

const fetchBookings = async () => {
  isLoading.value = true;
  try {
    const params = {
      type: activeTab.value,
      ...filters.value,
    };

    const res = await userService.getBookings(params);

    if (res.data.status) {
      bookings.value = res.data.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch bookings:", error);
  } finally {
    isLoading.value = false;
  }
};

const initiateCancel = (booking) => {
  selectedBooking.value = booking;
  cancelReason.value = "";
  isModalOpen.value = true;
};

const confirmCancel = async () => {
  isSubmitting.value = true;
  try {
    const res = await userService.cancelBooking({
      bookingId: selectedBooking.value.id,
      reason: cancelReason.value,
    });

    if (res.data.status) {
      isModalOpen.value = false;
      fetchBookings(); // Refresh the list
    }
  } catch (error) {
    console.error("Cancellation error:", error);
  } finally {
    isSubmitting.value = false;
  }
};

const handleTabChange = (tabValue) => {
  activeTab.value = tabValue;
  fetchBookings();
};

const resetFilters = () => {
  filters.value = {
    status: "",
    arrivalDateTime: "",
    departureDateTime: "",
    search: "",
    pagination: 10,
  };
  fetchBookings();
};

onMounted(() => {
  fetchBookings();
});
</script>
