<template>
  <div class="base-table bg-white p-4 w-full box-border border border-slate-100 shadow-sm">
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 mb-1">Admin Dashboard</h1>
          <p class="text-gray-600 text-sm">Welcome back! Here's what's happening with your platform today.</p>
        </div>
        <button
          class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
          <Icon icon="heroicons:arrow-path" class="w-4 h-4" />
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Main Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div
v-for="(stat, index) in mainStats" :key="index"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-gray-600 mb-1">{{ stat.label }}</p>
            <p class="text-xl font-bold text-gray-900">{{ stat.value || '0' }}</p>
            <p v-if="stat.trend" class="text-xs text-green-600 mt-1">{{ stat.trend }}</p>
          </div>
          <div class="p-2 rounded-lg" :class="stat.iconBgClass">
            <Icon :icon="stat.icon" class="text-lg text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Revenue & Growth Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Revenue Overview -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Revenue Overview</h2>
            <div class="flex items-center space-x-2">
              <span class="text-xs text-gray-500">Last 7 days</span>
              <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            </div>
          </div>
        </div>
        <div class="p-4">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-3 border border-green-100">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-medium text-gray-600 mb-1">Today</p>
                  <p class="text-lg font-bold text-gray-900">
                    £{{ formatCurrency(dashboardDetail?.details?.revenue?.today) }}
                  </p>
                </div>
                <div class="p-2 rounded-lg bg-green-500">
                  <Icon icon="heroicons:currency-pound" class="text-sm text-white" />
                </div>
              </div>
            </div>
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 border border-blue-100">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-medium text-gray-600 mb-1">This Week</p>
                  <p class="text-lg font-bold text-gray-900">
                    £{{ formatCurrency(dashboardDetail?.details?.revenue?.thisWeek) }}
                  </p>
                </div>
                <div class="p-2 rounded-lg bg-blue-500">
                  <Icon icon="heroicons:calendar" class="text-sm text-white" />
                </div>
              </div>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-violet-50 rounded-lg p-3 border border-purple-100">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-medium text-gray-600 mb-1">This Month</p>
                  <p class="text-lg font-bold text-gray-900">
                    £{{ formatCurrency(dashboardDetail?.details?.revenue?.thisMonth) }}
                  </p>
                </div>
                <div class="p-2 rounded-lg bg-purple-500">
                  <Icon icon="heroicons:chart-bar" class="text-sm text-white" />
                </div>
              </div>
            </div>
          </div>

          <!-- Revenue Chart -->
          <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-900">Revenue Trend</h3>
            <div class="bg-gray-50 rounded-lg p-3">
              <div class="flex items-end space-x-1 h-24">
                <div
v-for="(day, index) in dashboardDetail?.details?.revenue?.trend7Days || []" :key="index"
                  class="flex-1 bg-blue-500 rounded-t"
                  :style="{ height: getChartHeight(day.amount, maxRevenue) + '%' }"></div>
              </div>
              <div class="flex justify-between text-xs text-gray-500 mt-2">
                <span v-for="(day, index) in dashboardDetail?.details?.revenue?.trend7Days || []" :key="index">
                  {{ day.day }}
                </span>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Booking Analytics -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Booking Analytics</h2>
            <div class="flex items-center space-x-2">
              <span class="text-xs text-gray-500">Today's data</span>
              <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            </div>
          </div>
        </div>

        <div class="p-4">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 border border-blue-100">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-medium text-gray-600 mb-1">Today's Bookings</p>
                  <p class="text-lg font-bold text-gray-900">
                    {{ dashboardDetail?.details?.bookingAnalytics?.todaysBookings || 0 }}
                  </p>
                </div>
                <div class="p-2 rounded-lg bg-blue-500">
                  <Icon icon="heroicons:calendar" class="text-sm text-white" />
                </div>
              </div>
            </div>
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-3 border border-green-100">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-medium text-gray-600 mb-1">Today's Revenue</p>
                  <p class="text-lg font-bold text-gray-900">
                    {{ dashboardDetail?.details?.bookingAnalytics?.todaysRevenue || 0 }}
                  </p>
                </div>
                <div class="p-2 rounded-lg bg-green-500">
                  <Icon icon="heroicons:currency-pound" class="text-sm text-white" />
                </div>
              </div>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-violet-50 rounded-lg p-3 border border-purple-100">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-medium text-gray-600 mb-1">Today's Lost</p>
                  <p class="text-lg font-bold text-gray-900">
                    £{{ formatCurrency(dashboardDetail?.details?.bookingAnalytics?.lostAmountToday) || 0 }}
                  </p>
                </div>
                <div class="p-2 rounded-lg bg-purple-500">
                  <Icon icon="heroicons:currency-pound" class="text-sm text-white" />
                </div>
              </div>
            </div>
          </div>

          <!-- Booking Status -->
          <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-900">Booking Status</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-lg font-bold text-green-600">
                  {{ dashboardDetail?.details?.bookingAnalytics?.bookingStatus?.confirmed || 0 }}
                </p>
                <p class="text-xs text-green-700">Confirmed</p>
              </div>
              <div class="text-center p-3 bg-red-50 rounded-lg">
                <p class="text-lg font-bold text-red-600">
                  {{ dashboardDetail?.details?.bookingAnalytics?.bookingStatus?.cancelled || 0 }}
                </p>
                <p class="text-xs text-red-700">Cancelled</p>
              </div>
              <div class="text-center p-3 bg-yellow-50 rounded-lg">
                <p class="text-lg font-bold text-yellow-600">
                  {{ dashboardDetail?.details?.bookingAnalytics?.bookingStatus?.pending || 0 }}
                </p>
                <p class="text-xs text-yellow-700">Pending</p>
              </div>
            </div>
          </div>
        </div>
      </div>


    </div>

  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import adminService from '../../services/adminService'

const dashboardDetail = ref({});
const loading = ref(true);
const error = ref(null);

const mainStats = computed(() => [
  {
    label: 'Live Properties',
    value: dashboardDetail.value?.summary?.liveProperties,
    icon: 'heroicons:map-pin',
    iconBgClass: 'bg-blue-500',
    trend: '',
  },
  {
    label: 'Total Properties',
    value: dashboardDetail.value?.summary?.totalProperties,
    icon: 'heroicons:building-office-2',
    iconBgClass: 'bg-indigo-500',
    trend: '',
  },
  {
    label: 'Total LostAmount',
    value: `£${formatCurrency(dashboardDetail.value?.summary?.totalLostAmount) || '0.00'}`,
    icon: 'heroicons:currency-pound',
    iconBgClass: 'bg-purple-500',
    trend: '',
  },
  {
    label: 'Total Revenue',
    value: `£${formatCurrency(dashboardDetail.value?.summary?.totalRevenue) || '0.00'}`,
    icon: 'heroicons:currency-pound',
    iconBgClass: 'bg-green-500',
    trend: '',
  },
]);

const dashboardData = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await adminService.adminDashboard();
    if (response.data.status === true) {
      dashboardDetail.value = response.data.data
    }
  } catch {
    error.value = err.message || 'Failed to load dashboard data';
  } finally {
    loading.value = false;
  }
}

const formatCurrency = amount => {
  if (!amount) {return '0.00';}
  return parseFloat(amount).toLocaleString('en-GB', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const getChartHeight = (value, max) => {
  if (!value || !max) {return 0;}
  return Math.max((value / max) * 100, 5);
};

const maxRevenue = computed(() => {
  const trends = dashboardDetail?.value?.details?.revenue?.trend7Days || [];
  return Math.max(...trends.map(t => t.amount), 1);
});

onMounted(() => {
  dashboardData();
})
</script>