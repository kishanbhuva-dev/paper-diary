<template>
  <div class="p-2 sm:p-3 lg:p-4 bg-gray-50 min-h-max">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div
        class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200"
      >
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
              Total Inventory
            </p>
            <div class="mt-2 flex items-baseline gap-2">
              <span class="text-xl text-gray-800">{{
                dashboardStats.totalProperties
              }}</span>
              <span class="text-sm text-gray-500 font-medium">Units</span>
            </div>
          </div>
          <div class="p-2.5 rounded-xl bg-blue-50 text-blue-600">
            <Icon icon="mdi:office-building-marker-outline" class="text-xl" />
          </div>
        </div>
      </div>

      <div
        class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200"
      >
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
              Total Bookings
            </p>
            <div class="mt-2 flex items-baseline gap-2">
              <span class="text-xl text-gray-800">{{
                dashboardStats.totalBookings
              }}</span>
              <span class="text-sm text-gray-500 font-medium">Lifetime</span>
            </div>
          </div>
          <div class="p-2.5 rounded-xl bg-indigo-50 text-indigo-600">
            <Icon icon="mdi:calendar-check" class="text-xl" />
          </div>
        </div>
      </div>

      <div
        class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200"
      >
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
              Today's Ops
            </p>
            <div class="mt-2 flex items-baseline gap-4">
              <div class="flex items-baseline gap-1">
                <span class="text-xl text-green-600">{{
                  operationalStats.checkIns
                }}</span>
                <span class="text-sm text-gray-500 font-medium">In</span>
              </div>
              <span class="text-gray-200">|</span>
              <div class="flex items-baseline gap-1">
                <span class="text-xl text-amber-600">{{
                  operationalStats.checkOuts
                }}</span>
                <span class="text-sm text-gray-500 font-medium">Out</span>
              </div>
            </div>
          </div>
          <div class="p-2.5 rounded-xl bg-gray-100 text-gray-600">
            <Icon icon="mdi:timeline-check-outline" class="text-xl" />
          </div>
        </div>
      </div>

      <div
        class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200"
      >
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
              Next Payout
            </p>
            <div class="mt-2 flex items-baseline gap-2">
              <span class="text-xl text-gray-800">₹85.5k</span>
              <span class="text-sm text-blue-600 font-medium">Dec 20</span>
            </div>
          </div>
          <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600">
            <Icon icon="mdi:currency-usd" class="text-xl" />
          </div>
        </div>
      </div>
    </div>

    <div class="mb-6 relative">
      <div class="max-w-xs">
        <label
          class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1"
        >
          Property Filter
        </label>
        <div
          @click="isDropdownOpen = !isDropdownOpen"
          class="bg-white border border-gray-300 px-3 py-2.5 rounded-xl flex justify-between items-center cursor-pointer hover:border-blue-500 transition-colors select-none"
        >
          <span class="text-sm text-gray-600 truncate">
            {{
              selectedProperties.length > 0
                ? selectedProperties.length + " Selected"
                : "All Properties"
            }}
          </span>
          <Icon
            icon="mdi:chevron-down"
            class="text-gray-400 transition-transform"
            :class="{ 'rotate-180': isDropdownOpen }"
          />
        </div>

        <div
          v-if="isDropdownOpen"
          class="absolute z-50 mt-2 w-xs bg-white border border-gray-100 shadow-xl rounded-xl p-2 max-h-60 overflow-y-auto"
        >
          <label
            v-for="item in propertyDropdown"
            :key="item.id"
            class="flex items-center gap-3 px-3 py-2.5 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors group"
          >
            <input
              type="checkbox"
              :value="item.id"
              v-model="selectedProperties"
              class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
            />
            <span
              class="text-sm text-gray-700 font-medium group-hover:text-blue-700"
            >
              {{ item.name }}
            </span>
          </label>
        </div>
      </div>
    </div>

    <HotelDashboardCalendar
      :rooms="rooms"
      :bookings="bookings"
      :status-config="customStatuses"
      :allow-previous-month-navigation="true"
      :text-labels="{
        room: 'Resources',
        available: 'Free',
        previousMonth: '← Previous',
        nextMonth: 'Next →',
      }"
      theme="light"
      @booking-click="handleBookingClick"
    />

    <div class="mt-8">
      <div class="flex items-center justify-between mb-4 px-1">
        <h3 class="text-lg font-bold text-gray-800">Recent Bookings</h3>
      </div>

      <div
        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
      >
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-gray-50/50 border-b border-gray-100">
                <th
                  class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider"
                >
                  Guest
                </th>
                <th
                  class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider"
                >
                  Stay Dates
                </th>
                <th
                  class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider"
                >
                  Total
                </th>
                <th
                  class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider"
                >
                  Status
                </th>
                <th
                  class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right"
                >
                  Action
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="booking in recentBookings"
                :key="booking.id"
                class="hover:bg-gray-50/50 transition-colors"
              >
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-semibold text-gray-700">{{
                      booking.guestFullName
                    }}</span>
                    <span class="text-xs text-gray-400">{{
                      booking.bookingNumber
                    }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-600 italic">
                    {{ booking.arrivalDateTime }} to
                    {{ booking.departureDateTime }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm font-medium text-gray-700"
                    >₹{{ booking.grandTotal }}</span
                  >
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="[
                      'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider',
                      booking.status === 'confirm'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-amber-100 text-amber-700',
                    ]"
                  >
                    {{ booking.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <button
                    @click="handleBookingClick(booking)"
                    class="p-2 text-gray-400 hover:text-blue-600 transition-colors"
                  >
                    <Icon icon="mdi:eye-outline" class="text-xl" />
                  </button>
                </td>
              </tr>
              <tr v-if="recentBookings.length === 0">
                <td
                  colspan="5"
                  class="px-6 py-10 text-center text-gray-400 text-sm"
                >
                  No bookings found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 my-6"></div>

    <BookingDetailModal
      :show="showBookingModal"
      :booking="selectedBooking"
      :canCancel="
        isFutureBooking(
          selectedBooking?.checkIn || selectedBooking?.arrivalDateTime
        )
      "
      @close="showBookingModal = false"
      @cancel="handleCancelBooking"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { Icon } from "@iconify/vue";
import ownerService from "@/services/ownerService";
import BookingDetailModal from "@/components/modals/BookingDetailModal.vue";
import { HotelDashboardCalendar } from "vue-hotel-booking-calendar";
import "vue-hotel-booking-calendar/dist/style.css";

// Dropdown State
const propertyDropdown = ref([]);
const selectedProperties = ref([]);
const isDropdownOpen = ref(false);

const rooms = ref([]);
const bookings = ref([]);
const recentBookings = ref([]); // New ref for the table
const showBookingModal = ref(false);
const selectedBooking = ref(null);

const customStatuses = [
  { key: "available", label: "Available", color: "", backgroundColor: "" },
  {
    key: "confirm",
    label: "Confirm",
    color: "#155e75",
    backgroundColor: "#a7f3d0",
  },
  {
    key: "cancelled",
    label: "Cancelled",
    color: "#991b1b",
    backgroundColor: "#fee2e2",
  },
];

/**
 * WATCHER: Automatically triggers when selection changes
 */
watch(selectedProperties, () => {
  getResources();
  getRecentBookings();
});

const isFutureBooking = (checkInStr) => {
  if (!checkInStr) return false;
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  // Handle both YYYY-MM-DD and DD-MM-YYYY
  let dateParts = checkInStr.includes("-") ? checkInStr.split("-") : [];
  let bookingDate;
  if (dateParts[0].length === 4) {
    bookingDate = new Date(checkInStr);
  } else {
    bookingDate = new Date(`${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`);
  }

  bookingDate.setHours(0, 0, 0, 0);
  return bookingDate.getTime() >= today.getTime();
};

const handleBookingClick = (booking) => {
  selectedBooking.value = booking;
  showBookingModal.value = true;
};

const handleCancelBooking = async (bookingId) => {
  if (confirm("Are you sure you want to cancel this booking?")) {
    try {
      await getResources();
      await getRecentBookings();
      showBookingModal.value = false;
    } catch (error) {
      console.error("Error cancelling booking:", error);
    }
  }
};

const dashboardStats = ref({ totalProperties: 0, totalBookings: 0 });
const operationalStats = ref({ checkIns: 3, checkOuts: 2 });

/**
 * FETCH RECENT BOOKINGS (API Integration)
 */
const getRecentBookings = async () => {
  try {
    const params = {
      limit: 10,
      property_ids: selectedProperties.value.length
        ? selectedProperties.value
        : undefined,
    };
    const res = await ownerService.fetchBookings(params);
    console.log(res);

    // Assuming the API returns a list in 'data' or the array directly based on ownerService structure
    recentBookings.value = res.data || res;
  } catch (error) {
    console.error("Error fetching recent bookings:", error);
  }
};

/**
 * GET RESOURCES (For Calendar)
 */
const getResources = async () => {
  try {
    const ResourcesData = await ownerService.resourceList({
      propertyIds: selectedProperties.value,
    });

    rooms.value = ResourcesData.resource.map((res) => ({
      id: res.id.toString(),
      number: res.name,
    }));

    const formatDate = (dateStr) => {
      if (!dateStr) return "";
      const [d, m, y] = dateStr.trim().split("-");
      return `${y}-${m}-${d}`;
    };

    bookings.value = ResourcesData.booking.map((book) => ({
      id: book.id.toString(),
      guestName: book.guestFullName,
      roomNumber: book.resource_name,
      checkIn: formatDate(book.arrivalDateTime),
      checkOut: formatDate(book.departureDateTime),
      status: book.status,
    }));
  } catch (error) {
    console.error("Error loading resources:", error);
  }
};

const fetchKpiStats = async () => {
  try {
    const propertyData = await ownerService.fetchProperties({ limit: 1 });
    dashboardStats.value.totalProperties = propertyData?.total || 0;
    const bookingData = await ownerService.fetchBookings({ limit: 1 });
    dashboardStats.value.totalBookings = bookingData?.total || 0;
  } catch (error) {
    console.error("Error fetching stats:", error);
  }
};

const fetchPropertiesdropdown = async () => {
  try {
    const res = await ownerService.fetchPropertiesdropdown();
    propertyDropdown.value = res.data.data || [];
  } catch (error) {
    console.error("Error fetching properties dropdown:", error);
  }
};

onMounted(() => {
  fetchPropertiesdropdown();
  fetchKpiStats();
  getResources();
  getRecentBookings();
});
</script>
