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
              <span class="text-xl text-gray-800">
                {{ dashboardStats.totalProperties }}
              </span>
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
              <span class="text-xl text-gray-800">
                {{ dashboardStats.totalBookings }}
              </span>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div
        class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
      >
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            Recent Bookings
          </h2>
          <RouterLink
            :to="{ name: 'bookings' }"
            class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg transition-colors"
          >
            View All
          </RouterLink>
        </div>

        <ul class="divide-y divide-gray-50">
          <li
            v-for="booking in recentBookings"
            :key="booking.id"
            class="py-3 group hover:bg-gray-50/50 rounded-xl transition-colors px-2 -mx-2"
          >
            <div class="flex items-center gap-4">
              <div
                class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0"
              >
                <Icon icon="mdi:key-outline" class="text-lg" />
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start">
                  <p class="text-sm font-bold text-gray-800 truncate">
                    {{ booking.property_name }}
                  </p>
                  <span
                    :class="bookingStatusClasses(booking.status)"
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                  >
                    {{ booking.status }}
                  </span>
                </div>
                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                  <span class="font-medium text-gray-600"
                    >#{{ booking.id }}</span
                  >
                  <span>•</span>
                  <span>{{ booking.start_date }} — {{ booking.end_date }}</span>
                </div>
              </div>
            </div>
          </li>

          <li
            v-if="recentBookings.length === 0 && !isLoadingBookings"
            class="py-8 text-center"
          >
            <p class="text-sm text-gray-400">No recent activity found.</p>
          </li>
          <li v-if="isLoadingBookings" class="py-8 text-center text-blue-600">
            <Icon icon="mdi:loading" class="animate-spin text-2xl mx-auto" />
          </li>
        </ul>
      </div>

      <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h3
            class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2"
          >
            <Icon icon="mdi:list-status" class="text-gray-400" />
            Inventory Status
          </h3>

          <div class="space-y-3">
            <div
              class="flex justify-between items-center p-3 rounded-xl bg-green-50/50 border border-green-100/50"
            >
              <span class="text-xs font-medium text-gray-600"
                >Available Now</span
              >
              <span class="text-lg font-bold text-green-600">8</span>
            </div>
            <div
              class="flex justify-between items-center p-3 rounded-xl bg-amber-50/50 border border-amber-100/50"
            >
              <span class="text-xs font-medium text-gray-600"
                >Booked (48h)</span
              >
              <span class="text-lg font-bold text-amber-600">5</span>
            </div>
            <div
              class="flex justify-between items-center p-3 rounded-xl bg-red-50/50 border border-red-100/50"
            >
              <span class="text-xs font-medium text-gray-600">Blocked</span>
              <span class="text-lg font-bold text-red-600">2</span>
            </div>
          </div>

          <RouterLink
            :to="{ name: 'properties' }"
            class="mt-4 w-full flex items-center justify-center gap-2 text-xs font-semibold text-gray-600 hover:text-blue-600 py-2 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Manage Availability <Icon icon="mdi:arrow-right" />
          </RouterLink>
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
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import { RouterLink } from "vue-router";
// Ensure this path is correct
import ownerService from "@/services/ownerService";

// --- STATE MANAGEMENT ---
const isLoadingBookings = ref(false);

const dashboardStats = ref({
  totalProperties: 0,
  totalPropertiesLoaded: false,
  totalBookings: 0,
  totalBookingsLoaded: false,
});

const recentBookings = ref([]);

// Mock data remains the same
const operationalStats = ref({
  checkIns: 3,
  checkOuts: 2,
});

const propertyStats = ref([
  { property: "Hill View Villa", performance: 92 },
  { property: "Luxury Downtown Apt", performance: 85 },
  { property: "Cozy Studio Tech Park", performance: 71 },
]);

// --- API FETCHING LOGIC ---
const fetchKpiStats = async () => {
  try {
    const propertyData = await ownerService.fetchProperties({ limit: 1 });
    dashboardStats.value.totalProperties = propertyData.total || 0;
    dashboardStats.value.totalPropertiesLoaded = true;
  } catch (error) {
    console.error("Error fetching properties count:", error);
  }

  try {
    const bookingData = await ownerService.fetchBookings({ limit: 1 });
    dashboardStats.value.totalBookings = bookingData.total || 0;
    dashboardStats.value.totalBookingsLoaded = true;
  } catch (error) {
    console.error("Error fetching bookings count:", error);
  }
};

const fetchRecentBookings = async () => {
  isLoadingBookings.value = true;
  try {
    const bookingData = await ownerService.fetchBookings({
      limit: 5,
      orderBy: "created_at",
      direction: "desc",
    });

    recentBookings.value = bookingData.data.map((b) => ({
      id: b.id,
      property_name: b.propertyName || "N/A",
      start_date: b.arrivalDateTime || b.start_date,
      end_date: b.departureDateTime || b.end_date,
      status: b.status || "Pending",
    }));
  } catch (error) {
    console.error("Error fetching recent bookings:", error);
    recentBookings.value = [];
  } finally {
    isLoadingBookings.value = false;
  }
};

// --- UTILITIES ---
const bookingStatusClasses = (status) => {
  switch (status?.toLowerCase()) {
    case "confirmed":
    case "completed":
      return "bg-green-100 text-green-700";
    case "pending":
    case "on-hold":
      return "bg-amber-100 text-amber-700";
    case "canceled":
    case "rejected":
      return "bg-red-50 text-red-600";
    default:
      return "bg-gray-100 text-gray-600";
  }
};

onMounted(() => {
  fetchKpiStats();
  fetchRecentBookings();
});
</script>
