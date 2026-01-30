<template>
  <div class="min-h-screen bg-[#f8fafc] font-sans text-[#1e293b] antialiased">
    <main class="mx-auto max-w-[1600px] px-0 py-6 sm:px-6 lg:px-8 xl:px-10 space-y-6 sm:space-y-8">
      <header>
        <h1
          class="text-lg font-extrabold tracking-tight text-slate-900 sm:text-xl md:text-2xl lg:text-3xl"
        >
          Admin Dashboard
        </h1>
      </header>

      <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 sm:gap-6">
        <div
          v-for="(stat, index) in mainStats"
          :key="index"
          class="bg-white p-3 sm:p-4 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md"
        >
          <div class="flex flex-col gap-2 sm:gap-3">
            <span
              class="text-[10px] sm:text-[11px] font-black uppercase tracking-widest text-slate-400"
            >
              {{ stat.label }}
            </span>
            <div class="flex items-center justify-between">
              <span class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">
                {{ stat.value || '0' }}
              </span>
              <div :class="[stat.iconBgClass, 'p-2 rounded-xl text-white shadow-sm flex-shrink-0']">
                <Icon
                  :icon="stat.icon"
                  class="w-5 h-5 sm:w-6 h-6"
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="grid grid-cols-1 xl:grid-cols-3 gap-6 sm:gap-8">
        <div
          class="xl:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden"
        >
          <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center gap-3 bg-slate-50/30">
            <div
              class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"
            >
              <Icon
                icon="heroicons:presentation-chart-line"
                class="w-5 h-5 sm:w-6 sm:h-6"
              />
            </div>
            <h2 class="text-base sm:text-lg font-bold text-slate-800">Revenue Overview</h2>
          </div>

          <div class="p-5 sm:p-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
              <div
                v-for="item in revenueBreakdown"
                :key="item.label"
                class="p-4 rounded-2xl bg-slate-50 border border-slate-100"
              >
                <p class="text-[9px] sm:text-[10px] font-bold uppercase text-slate-400 mb-1">
                  {{ item.label }}
                </p>
                <p class="text-lg sm:text-xl font-bold text-slate-900">£{{ item.value }}</p>
              </div>
            </div>

            <div class="relative overflow-hidden">
              <h3
                class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-slate-400 mb-6"
              >
                Last 7 Days Trend
              </h3>
              <div class="flex items-end gap-2 sm:gap-3 h-32 sm:h-40">
                <div
                  v-for="(day, index) in dashboardDetail?.details?.revenue?.trend7Days || []"
                  :key="index"
                  class="flex-1 group relative h-full flex flex-col justify-end"
                >
                  <div
                    class="w-full bg-blue-500 rounded-t-lg transition-all hover:bg-blue-600"
                    :style="{
                      height: getChartHeight(day.amount, maxRevenue) + '%',
                      minHeight: '4px',
                    }"
                  ></div>

                  <div class="mt-3 text-center">
                    <p class="text-[8px] sm:text-[10px] font-bold text-slate-500 uppercase">
                      {{ day.day }}
                    </p>
                  </div>

                  <div
                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block z-10"
                  >
                    <span class="bg-slate-800 text-white text-[9px] px-2 py-1 rounded">
                      {{ day.amount }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div
          class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col"
        >
          <div class="p-5 sm:p-6 border-b border-slate-100 bg-slate-50/30">
            <h2 class="text-base sm:text-lg font-bold text-slate-800">Booking Analytics</h2>
          </div>
          <div class="p-5 sm:p-8 space-y-6 flex-1">
            <div class="space-y-3">
              <div
                class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100"
              >
                <div>
                  <p class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400">
                    Today's Bookings
                  </p>
                  <p class="text-xl sm:text-2xl font-bold text-slate-900">
                    {{ dashboardDetail?.details?.bookingAnalytics?.todaysBookings || 0 }}
                  </p>
                </div>
                <Icon
                  icon="heroicons:shopping-cart"
                  class="w-5 h-5 sm:w-6 text-blue-600"
                />
              </div>
              <div
                class="flex items-center justify-between p-4 bg-rose-50 rounded-2xl border border-rose-100"
              >
                <div>
                  <p class="text-[9px] sm:text-[10px] font-black uppercase text-rose-400">
                    Today's Lost Amount
                  </p>
                  <p class="text-xl sm:text-2xl font-bold text-rose-700">
                    £{{
                      formatCurrency(dashboardDetail?.details?.bookingAnalytics?.lostAmountToday)
                    }}
                  </p>
                </div>
                <Icon
                  icon="heroicons:exclamation-triangle"
                  class="w-5 h-5 sm:w-6 text-rose-600"
                />
              </div>
            </div>

            <div class="pt-6 border-t border-slate-100">
              <p
                class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-slate-400 mb-5"
              >
                Booking Status
              </p>
              <div class="space-y-4">
                <div
                  v-for="(val, label) in statusMap"
                  :key="label"
                >
                  <div
                    class="flex justify-between text-[10px] sm:text-[11px] font-bold mb-2 uppercase"
                  >
                    <span class="text-slate-500">{{ label }}</span>
                    <span class="text-slate-900">{{ val.count }}</span>
                  </div>
                  <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div
                      :class="['h-full rounded-full transition-all duration-1000', val.color]"
                      :style="{
                        width: val.count > 0 ? (val.count / totalBookingsCount) * 100 + '%' : '0%',
                      }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="grid grid-cols-1 xl:grid-cols-2 gap-6 sm:gap-8">
        <div
          class="bg-white rounded-3xl border border-slate-200 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden"
        >
          <div
            class="p-5 sm:p-6 border-b border-slate-100 flex justify-between items-center bg-white"
          >
            <div class="flex items-center gap-3">
              <div
                class="h-9 w-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-600"
              >
                <Icon
                  icon="heroicons:clock"
                  class="w-5 h-5"
                />
              </div>
              <h2 class="text-base sm:text-lg font-bold text-slate-800">Recent Activity</h2>
            </div>
            <button
              class="text-xs font-bold text-blue-600 hover:underline px-2 py-1"
              @click="router.push('/admin/bookings')"
            >
              View All
            </button>
          </div>

          <div class="w-full">
            <table class="hidden sm:table w-full text-left border-separate border-spacing-0">
              <thead>
                <tr class="bg-slate-50/50">
                  <th
                    class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-slate-400"
                  >
                    Site / Property
                  </th>
                  <th
                    class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-wider text-slate-400"
                  >
                    Status
                  </th>
                  <th
                    class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-wider text-slate-400"
                  >
                    Booked On
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="(booking, index) in recentBookings"
                  :key="index"
                  class="hover:bg-slate-50 transition-colors"
                >
                  <td class="px-6 py-4">
                    <p class="text-sm font-bold text-slate-800">
                      {{ booking.propertyName || 'Unknown Site' }}
                    </p>
                    <p class="text-[11px] font-medium text-slate-500">{{ booking.ownerName }}</p>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span
                      class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tight ring-1 ring-inset"
                      :class="getStatusClasses(booking.status)"
                    >
                      <span
                        class="w-1.5 h-1.5 rounded-full mr-2"
                        :class="getDotClass(booking.status)"
                      ></span>
                      {{ booking.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right text-xs font-bold text-slate-600">
                    {{ booking.bookedOn }}
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="sm:hidden divide-y divide-slate-100">
              <div
                v-for="(booking, index) in recentBookings"
                :key="'mob-' + index"
                class="p-4 flex flex-col gap-3"
              >
                <div class="flex justify-between items-start">
                  <div class="min-w-0 pr-2">
                    <p class="text-xs font-bold text-slate-800 truncate">
                      {{ booking.propertyName || 'Unknown Site' }}
                    </p>
                    <p class="text-[10px] font-medium text-slate-500">{{ booking.ownerName }}</p>
                  </div>
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase ring-1 ring-inset whitespace-nowrap"
                    :class="getStatusClasses(booking.status)"
                  >
                    {{ booking.status }}
                  </span>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-slate-50">
                  <span class="text-[9px] font-black uppercase tracking-widest text-slate-400"
                    >Booked On</span
                  >
                  <span class="text-[10px] font-bold text-slate-600">{{ booking.bookedOn }}</span>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="!recentBookings.length"
            class="py-12 text-center"
          >
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
              No Recent Activity
            </p>
          </div>
        </div>

        <div
          class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col"
        >
          <div class="p-5 sm:p-6 border-b border-slate-100 bg-slate-50/30">
            <h2 class="text-base sm:text-lg font-bold text-slate-800">Top Sites</h2>
          </div>
          <div class="p-5 sm:p-6 flex-1">
            <div
              v-if="!dashboardDetail?.details?.topSites?.length"
              class="h-full flex flex-col items-center justify-center py-10 text-center"
            >
              <Icon
                icon="heroicons:map"
                class="w-12 h-12 text-slate-200 mb-4"
              />
              <p class="text-xs font-bold text-slate-400 uppercase">No site data available</p>
            </div>
            <div
              v-else
              class="space-y-4"
            >
              <div
                v-for="(site, index) in dashboardDetail?.details?.topSites"
                :key="index"
                class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 hover:border-blue-100 transition-all"
              >
                <div
                  class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center flex-shrink-0"
                >
                  <Icon
                    icon="heroicons:building-storefront"
                    class="w-5 h-5 sm:w-6 sm:h-6"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-bold text-slate-900 truncate">{{ site.property_name }}</p>
                  <p class="text-xs font-medium text-slate-500">
                    {{ site.booking_count }} bookings
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-black text-slate-900">
                    £{{ formatCurrency(site.totalAmount) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section
        class="bg-white rounded-3xl border border-slate-200 px-2 py-6 sm:px-8 sm:py-8 shadow-sm"
      >
        <!-- <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-8">Total Booking Per Month</h3> -->
        <div class="w-full min-h-75">
          <TotalBookingPerMonthChart
            v-if="!loading && totalBookingPerMonthChartData.length"
            :key="chartKey"
            :chart-data="totalBookingPerMonthChartData"
          />
          <div
            v-else
            class="h-full w-full flex items-center justify-center min-h-75"
          >
            <span class="text-sm font-bold text-slate-400 animate-pulse">Loading Chart...</span>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { Icon } from '@iconify/vue';
import adminService from '../../services/adminService';
import TotalBookingPerMonthChart from '@/components/admin/Dashboard/TotalBookingPerMonthChart.vue';

// --- DATA STATES ---
const dashboardDetail = ref({});
const recentBookings = ref([]);
const totalBookingPerMonthChartData = ref([]);
const loading = ref(true);
const router = useRouter();

// --- COMPUTED LOGIC ---
const chartKey = computed(
  () =>
    // Whenever the length of data changes or the first item changes,
    // this key updates, forcing Vue to re-mount the component entirely.
    `booking-chart-${totalBookingPerMonthChartData.value.length || 0}`
);
const revenueBreakdown = computed(() => [
  { label: 'Today', value: formatCurrency(dashboardDetail.value?.details?.revenue?.today) },
  { label: 'This Week', value: formatCurrency(dashboardDetail.value?.details?.revenue?.thisWeek) },
  {
    label: 'This Month',
    value: formatCurrency(dashboardDetail.value?.details?.revenue?.thisMonth),
  },
]);

const getStatusClasses = (status) => {
  const s = status?.toLowerCase();
  if (s === 'confirm') {
    return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20';
  }
  if (s === 'pending') {
    return 'bg-amber-50 text-amber-700 ring-amber-600/20';
  }
  if (s === 'cancelled') {
    return 'bg-rose-50 text-rose-700 ring-rose-600/20';
  }
  return 'bg-slate-50 text-slate-600 ring-slate-600/20';
};

const getDotClass = (status) => {
  const s = status?.toLowerCase();
  if (s === 'confirm') {
    return 'bg-emerald-500';
  }
  if (s === 'pending') {
    return 'bg-amber-500';
  }
  if (s === 'cancelled') {
    return 'bg-rose-500';
  }
  return 'bg-slate-400';
};
const statusMap = computed(() => ({
  Confirm: {
    count: dashboardDetail.value?.details?.bookingAnalytics?.bookingStatus?.confirmed || 0,
    color: 'bg-green-500',
  },
  Pending: {
    count: dashboardDetail.value?.details?.bookingAnalytics?.bookingStatus?.pending || 0,
    color: 'bg-amber-500',
  },
  Cancelled: {
    count: dashboardDetail.value?.details?.bookingAnalytics?.bookingStatus?.cancelled || 0,
    color: 'bg-rose-500',
  },
}));

const totalBookingsCount = computed(() => {
  const stats = dashboardDetail.value?.details?.bookingAnalytics?.bookingStatus;
  if (!stats) {
    return 1;
  }
  return (stats.confirmed || 0) + (stats.pending || 0) + (stats.cancelled || 0) || 1;
});

const mainStats = computed(() => [
  {
    label: 'Live Properties',
    value: dashboardDetail.value?.summary?.liveProperties,
    icon: 'heroicons:map-pin',
    iconBgClass: 'bg-blue-600',
  },
  {
    label: 'Total Properties',
    value: dashboardDetail.value?.summary?.totalProperties,
    icon: 'heroicons:building-office-2',
    iconBgClass: 'bg-indigo-600',
  },
  {
    label: 'Total Lost Amount',
    value: `£${formatCurrency(dashboardDetail.value?.summary?.totalLostAmount)}`,
    icon: 'heroicons:currency-pound',
    iconBgClass: 'bg-purple-600',
  },
  {
    label: 'Total Revenue',
    value: `£${formatCurrency(dashboardDetail.value?.summary?.totalRevenue)}`,
    icon: 'heroicons:banknotes',
    iconBgClass: 'bg-emerald-600',
  },
]);

// --- API METHODS ---
const dashboardData = async () => {
  loading.value = true;
  try {
    const response = await adminService.adminDashboard();
    if (response.data.status === true) {
      dashboardDetail.value = response.data.data;
      const bookingData = response?.data?.data?.details?.bookingTotalPerMonth?.data || [];
      totalBookingPerMonthChartData.value = bookingData.map((item) => ({
        x: item.month,
        y: item.total,
        z: item.count,
      }));
    }
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const fetchAdminBookings = async () => {
  const res = await adminService.fetchAdminBookings({
    page: 1,
    perPage: 10,
    search: '',
    sortBy: 'created_at',
    sortOrder: 'desc',
  });
  if (res.status) {
    recentBookings.value = res.data.data;
  }
};

const formatCurrency = (amount) => {
  if (!amount) {
    return '0.00';
  }
  return parseFloat(amount).toLocaleString('en-GB', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const getChartHeight = (val, max) => {
  const numericVal = Number(val) || 0;
  const numericMax = Number(max) || 1;
  // Returns a percentage. If val is max, returns 100%.
  return (numericVal / numericMax) * 100;
};

const maxRevenue = computed(() => {
  const trends = dashboardDetail.value?.details?.revenue?.trend7Days || [];
  if (trends.length === 0) {
    return 1;
  }
  // Use Number() to ensure the string "14588" from your log is treated as a number
  const values = trends.map((t) => Number(t.amount) || 0);
  return Math.max(...values, 1);
});

onMounted(() => {
  dashboardData();
  fetchAdminBookings();
});
</script>
