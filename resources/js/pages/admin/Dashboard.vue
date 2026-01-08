<template>
  <div class="base-table bg-white p-4 w-full box-border border border-slate-100 shadow-sm">
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="(stat, index) in mainStats" :key="index"
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
    value: dashboardDetail.value?.propertyCount,
    // value: 20,
    icon: 'heroicons:map-pin',
    iconBgClass: 'bg-blue-500',
    trend: '',
  },
  {
    label: 'Total Properties',
    // value: dashboardDetail.value?.basic_stats?.totalSite,
    value: 50,
    icon: 'heroicons:map',
    iconBgClass: 'bg-indigo-500',
    trend: '',
  },
  {
    label: 'Active Users',
    // value: dashboardDetail.value?.basic_stats?.noOfUsers,
    value: 200,
    icon: 'heroicons:users',
    iconBgClass: 'bg-green-500',
    trend: '',
  },
  {
    label: 'Total Revenue',
    // value: `£${formatCurrency(dashboardDetail.value?.basic_stats?.totalRevenue) || '0.00'}`,
    value: 500,
    icon: 'heroicons:currency-pound',
    iconBgClass: 'bg-purple-500',
    trend: '',
  },
]);

const dashboardData = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await adminService.adminDashboard();
    console.log("response", response);
    if (response.data.status === true) {
      dashboardDetail.value = response.data.data
    }
  } catch {
    console.error('Dashboard data error:', err);
    error.value = err.message || 'Failed to load dashboard data';
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  dashboardData();
})
</script>
