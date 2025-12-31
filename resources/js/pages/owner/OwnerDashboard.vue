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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 my-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h3
            class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2"
          >
            <Icon icon="mdi:list-status" class="text-gray-400" />
            Inventory Status Summary
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
              class="flex justify-between items-center p-4 rounded-xl bg-green-50/50 border border-green-100/50"
            >
              <span class="text-xs font-medium text-gray-600"
                >Available Now</span
              >
              <span class="text-lg font-bold text-green-600">8</span>
            </div>
            <div
              class="flex justify-between items-center p-4 rounded-xl bg-amber-50/50 border border-amber-100/50"
            >
              <span class="text-xs font-medium text-gray-600"
                >Booked (48h)</span
              >
              <span class="text-lg font-bold text-amber-600">5</span>
            </div>
            <div
              class="flex justify-between items-center p-4 rounded-xl bg-red-50/50 border border-red-100/50"
            >
              <span class="text-xs font-medium text-gray-600">Blocked</span>
              <span class="text-lg font-bold text-red-600">2</span>
            </div>
          </div>
        </div>
      </div>

      <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
              <Icon icon="mdi:star-outline" class="text-amber-500" />
              Latest Reviews
            </h3>
            <span
              class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full"
              >4.8 Avg</span
            >
          </div>

          <div class="space-y-4">
            <div
              v-for="review in staticReviews"
              :key="review.id"
              class="border-b border-gray-50 last:border-0 pb-3 last:pb-0"
            >
              <div class="flex justify-between items-start mb-1">
                <span
                  class="text-[11px] font-bold text-gray-700 truncate w-2/3"
                  >{{ review.propertyName }}</span
                >
                <div class="flex text-amber-400">
                  <Icon
                    icon="mdi:star"
                    v-for="i in review.rating"
                    :key="i"
                    class="text-[10px]"
                  />
                </div>
              </div>
              <p class="text-[11px] text-gray-500 line-clamp-2 italic">
                "{{ review.comment }}"
              </p>
              <div class="flex justify-between items-center mt-2">
                <span class="text-[10px] text-gray-400">{{
                  review.guestName
                }}</span>
                <span class="text-[10px] text-gray-400">{{ review.date }}</span>
              </div>
            </div>
          </div>
          <button
            class="mt-4 w-full py-2 text-xs font-semibold text-gray-600 hover:text-blue-600 bg-gray-50 rounded-lg transition-colors"
          >
            View All Reviews
          </button>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h3 class="text-sm font-bold text-gray-800 mb-4">Top Performers</h3>
          <div class="space-y-5">
            <div v-for="(stat, index) in propertyStats" :key="stat.property">
              <div class="flex justify-between text-xs mb-1.5">
                <span class="font-medium text-gray-700 truncate w-3/4">{{
                  stat.property
                }}</span>
                <span class="font-bold text-gray-900"
                  >{{ stat.performance }}%</span
                >
              </div>
              <div
                class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden"
              >
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="index === 0 ? 'bg-blue-600' : 'bg-blue-400'"
                  :style="{ width: stat.performance + '%' }"
                ></div>
              </div>
            </div>
          </div>
          <button
            class="mt-6 w-full py-2.5 text-xs font-bold text-blue-700 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors"
          >
            View Full Report
          </button>
        </div>
      </div>
    </div>

    <BookingDetailModal
      :show="showBookingModal"
      :booking="selectedBooking"
      :canCancel="isFutureBooking(selectedBooking?.checkIn)"
      @close="showBookingModal = false"
      @cancel="handleCancelBooking"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import ownerService from "@/services/ownerService";
import BookingDetailModal from "@/components/modals/BookingDetailModal.vue";
import { HotelDashboardCalendar } from "vue-hotel-booking-calendar";
import "vue-hotel-booking-calendar/dist/style.css";

const rooms = ref([]);
const bookings = ref([]);
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
 * IMPROVED DATE LOGIC:
 * Compares only the YYYY-MM-DD parts to avoid timezone/time-of-day issues.
 */
const isFutureBooking = (checkInStr) => {
  if (!checkInStr) return false;

  // Create a clean "Today" date at 00:00:00
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  // Parse the check-in string (assumes YYYY-MM-DD from the logic below)
  const bookingDate = new Date(checkInStr);
  bookingDate.setHours(0, 0, 0, 0);

  // Return true if booking is today or in the future
  return bookingDate.getTime() >= today.getTime();
};

const handleBookingClick = (booking) => {
  selectedBooking.value = booking;
  showBookingModal.value = true;
};

const handleCancelBooking = async (bookingId) => {
  const confirmCancel = confirm(
    "Are you sure you want to cancel this booking?"
  );
  if (confirmCancel) {
    try {
      await getResources();
      showBookingModal.value = false;
    } catch (error) {
      console.error("Error cancelling booking:", error);
    }
  }
};

const dashboardStats = ref({ totalProperties: 0, totalBookings: 0 });
const staticReviews = ref([
  {
    id: 1,
    propertyName: "Hill View Villa",
    guestName: "Arjun M.",
    rating: 5,
    comment: "Absolutely stunning view and very clean property.",
    date: "2 days ago",
  },
  {
    id: 2,
    propertyName: "Luxury Downtown Apt",
    guestName: "Sarah K.",
    rating: 4,
    comment: "Great location, close to everything.",
    date: "Dec 15",
  },
  {
    id: 3,
    propertyName: "Hill View Villa",
    guestName: "Rahul S.",
    rating: 5,
    comment: "The host was very accommodating.",
    date: "Dec 12",
  },
]);
const operationalStats = ref({ checkIns: 3, checkOuts: 2 });
const propertyStats = ref([
  { property: "Hill View Villa", performance: 92 },
  { property: "Luxury Downtown Apt", performance: 85 },
  { property: "Cozy Studio Tech Park", performance: 71 },
]);

const getResources = async () => {
  const ResourcesData = await ownerService.resourceList();

  rooms.value = ResourcesData.resource.map((res) => ({
    id: res.id.toString(),
    number: res.name,
  }));

  const formatDate = (dateStr) => {
    if (!dateStr) return "";
    const [d, m, y] = dateStr.trim().split("-");
    return `${y}-${m}-${d}`; // Result is YYYY-MM-DD
  };

  bookings.value = ResourcesData.booking.map((book) => ({
    id: book.id.toString(),
    guestName: book.guestFullName,
    roomNumber: book.resource_name,
    checkIn: formatDate(book.arrivalDateTime),
    checkOut: formatDate(book.departureDateTime),
    status: book.status,
  }));
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

onMounted(() => {
  fetchKpiStats();
  getResources();
});
</script>
