<template>
  <main class="bg-[#f8fafc] min-h-screen font-sans antialiased text-slate-900">
    <div class="p-4">
      <!-- Header Section -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 mb-2">Owner Subscriptions</h1>
        <p class="text-slate-500">Manage and monitor all owner subscriptions</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-600">Total Owners</p>
              <p class="text-2xl font-bold text-slate-900">{{ stats.totalOwners }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <Icon icon="heroicons:user-group-20-solid" class="w-6 h-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-600">Active Subscriptions</p>
              <p class="text-2xl font-bold text-emerald-600">{{ stats.activeSubscriptions }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
              <Icon icon="heroicons:check-circle-20-solid" class="w-6 h-6 text-emerald-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-600">Canceled Subscriptions</p>
              <p class="text-2xl font-bold text-rose-600">{{ stats.canceledSubscriptions }}</p>
            </div>
            <div class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center">
              <Icon icon="heroicons:x-circle-20-solid" class="w-6 h-6 text-rose-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-600">Total Revenue</p>
              <p class="text-2xl font-bold text-slate-900">£{{ stats.totalRevenue }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
              <Icon icon="heroicons:currency-dollar-20-solid" class="w-6 h-6 text-amber-600" />
            </div>
          </div>
        </div>
      </div>
      <!-- Owner Subscriptions History -->
      <div v-if="subscriptions.length > 0" class="mt-12">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-slate-900 mb-2">Owner Subscriptions</h2>
          <!-- <p class="text-slate-500">View your subscription history and past billing cycles</p> -->
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Plan</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Period
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Amount
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Created
                  </th>
                  <!-- <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th> -->
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                <tr v-for="(sub, index) in subscriptions" :key="sub.id" class="hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">

                      <div>
                        <div class="text-sm font-medium text-slate-900">{{ sub.plan_name || 'Professional Plan' }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ sub.owner_name }}</div>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div>
                        <div class="text-sm font-medium text-slate-900">{{ sub.owner_email || '-' }}</div>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div
:class="[
                        'w-2 h-2 rounded-full',
                        sub.status === 'active' ? 'bg-emerald-500' :
                          sub.status === 'canceled' ? 'bg-rose-500' :
                            sub.status === 'past_due' ? 'bg-amber-500' : 'bg-slate-400'
                      ]"></div>
                      <span
:class="[
                        'text-sm font-medium capitalize',
                        sub.status === 'active' ? 'text-emerald-600' :
                          sub.status === 'canceled' ? 'text-rose-600' :
                            sub.status === 'past_due' ? 'text-amber-600' : 'text-slate-600'
                      ]">
                        {{ sub.status.replace('_', ' ') }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-slate-900">
                      <div>{{ formatDate(sub.created_at) }} - {{ calculateEndDate(sub.created_at, sub.interval) }}</div>

                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-slate-900">£{{ sub.amount || '12.00' }}</div>
                    <div class="text-xs text-slate-500">{{ sub.interval || '3 months' }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-slate-900">{{ formatDate(sub.created_at) }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Subscription Details Modal -->
    <div
v-if="showDetailsModal && selectedSubscription"
      class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-slate-900">Subscription Details</h3>
            <button class="p-2 hover:bg-slate-100 rounded-lg transition-colors" @click="showDetailsModal = false">
              <Icon icon="heroicons:x-mark-20-solid" class="w-5 h-5 text-slate-400" />
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Owner Information -->
            <div class="bg-slate-50 rounded-xl p-6">
              <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <Icon icon="heroicons:user-20-solid" class="w-5 h-5 text-blue-600" />
                Owner Information
              </h4>
              <div class="space-y-3">
                <div>
                  <label class="text-sm font-medium text-slate-600">Name</label>
                  <p class="text-slate-900 font-medium">{{ selectedSubscription.owner_name }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">Email</label>
                  <p class="text-slate-900 font-medium">{{ selectedSubscription.owner_email }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">User ID</label>
                  <p class="font-mono text-sm text-slate-900 bg-white px-3 py-2 rounded-lg border border-slate-200">
                    #{{ selectedSubscription.user_id }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Plan Information -->
            <div class="bg-blue-50 rounded-xl p-6">
              <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <Icon icon="heroicons:crown-20-solid" class="w-5 h-5 text-blue-600" />
                Plan Information
              </h4>
              <div class="space-y-3">
                <div>
                  <label class="text-sm font-medium text-slate-600">Plan Name</label>
                  <p class="text-slate-900 font-medium">{{ selectedSubscription.plan_name || 'Professional Plan' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">Status</label>
                  <div class="flex items-center gap-2 mt-1">
                    <div
:class="[
                      'w-2 h-2 rounded-full',
                      selectedSubscription.status === 'active' ? 'bg-emerald-500' :
                        selectedSubscription.status === 'canceled' ? 'bg-rose-500' :
                          selectedSubscription.status === 'past_due' ? 'bg-amber-500' : 'bg-slate-400'
                    ]"></div>
                    <span
:class="[
                      'text-sm font-medium capitalize',
                      selectedSubscription.status === 'active' ? 'text-emerald-600' :
                        selectedSubscription.status === 'canceled' ? 'text-rose-600' :
                          selectedSubscription.status === 'past_due' ? 'text-amber-600' : 'text-slate-600'
                    ]">
                      {{ selectedSubscription.status?.replace('_', ' ') || 'Unknown' }}
                    </span>
                  </div>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">Amount</label>
                  <p class="text-slate-900 font-medium">${{ selectedSubscription.amount || '12.00' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">Billing Interval</label>
                  <p class="text-slate-900 font-medium">{{ selectedSubscription.interval || '3 months' }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 flex gap-4">
            <button
class="flex-1 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-colors"
              @click="showDetailsModal = false">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { Icon } from "@iconify/vue";
import adminService from "../../services/adminService";
import { toast } from "vue-sonner";
import Basetable from "../../components/global/Basetable.vue";

const loading = ref(true);
const subscriptions = ref([]);
const showDetailsModal = ref(false);
const selectedSubscription = ref(null);

// Filters
const filters = ref({
  status: '',
  plan: ''
});

// Pagination
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref("");
const total = ref(0);


const handlePageChange = (page) => {
  currentPage.value = page;
  fetchSubscriptions();
};

const handlePerPageChange = (size) => {
  perPage.value = size;
  currentPage.value = 1;
  fetchSubscriptions();
};
// Stats
const stats = computed(() => {
  const total = subscriptions.value.length;
  const active = subscriptions.value.filter(sub => sub.status === 'active').length;
  const canceled = subscriptions.value.filter(sub => sub.status === 'canceled').length;
  const revenue = subscriptions.value
    .filter(sub => sub.status === 'active')
    .reduce((sum, sub) => sum + parseFloat(sub.amount || '5.00'), 0);

  return {
    totalOwners: new Set(subscriptions.value.map(sub => sub.user_id)).size,
    activeSubscriptions: active,
    canceledSubscriptions: canceled,
    totalRevenue: revenue.toFixed(2)
  };
});

const filteredSubscriptions = computed(() => {
  let filtered = subscriptions.value;

  // Apply status filter
  if (filters.value.status) {
    filtered = filtered.filter(sub => sub.status === filters.value.status);
  }

  // Apply plan filter
  if (filters.value.plan) {
    filtered = filtered.filter(sub => sub.plan_name === filters.value.plan);
  }

  return filtered;
});

const totalSubscriptions = computed(() => filteredSubscriptions.value.length);
const totalPages = computed(() => Math.ceil(totalSubscriptions.value / perPage.value));

const paginatedSubscriptions = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredSubscriptions.value.slice(start, end);
});
const fetchSubscriptions = async () => {
  try {
    loading.value = true;
    const res = await adminService.fetchSubscriptions({
      page: currentPage.value,
      per_page: perPage.value,
      search: currentSearch.value,
    });

    if (res && res.status && res.data) {
      subscriptions.value = res.data;
      total.value = res.data.length || 0;
    }
  } catch (error) {
    console.error('Error fetching subscriptions:', error);
    toast.error('Failed to fetch subscriptions');
    subscriptions.value = [];
    total.value = 0;
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchSubscriptions();
});

const calculateEndDate = (startDate, interval) => {
  if (!startDate) {return 'No date';}

  // Handle both timestamp numbers and string dates
  const date = typeof startDate === 'number'
    ? new Date(startDate * 1000)
    : new Date(startDate);

  // Check if date is valid
  if (isNaN(date.getTime())) {return 'Invalid date';}

  // Parse interval to get months
  let monthsToAdd = 3; // default

  if (interval) {
    const match = interval.match(/(\d+)\s*(month|year|week|day)s?/i);
    if (match) {
      const value = parseInt(match[1]);
      const unit = match[2].toLowerCase();

      switch (unit) {
        case 'month':
          monthsToAdd = value;
          break;
        case 'year':
          monthsToAdd = value * 12;
          break;
        case 'week':
          monthsToAdd = Math.floor(value / 4); // approximate weeks to months
          break;
        case 'day':
          monthsToAdd = Math.floor(value / 30); // approximate days to months
          break;
      }
    }
  }

  // Calculate end date
  const endDate = new Date(date);
  endDate.setMonth(endDate.getMonth() + monthsToAdd);

  return endDate.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatDate = (timestamp) => {
  if (!timestamp) {return 'No date';}

  // Handle both timestamp numbers and string dates
  const date = typeof timestamp === 'number'
    ? new Date(timestamp * 1000)
    : new Date(timestamp);

  // Check if date is valid
  if (isNaN(date.getTime())) {return 'Invalid date';}

  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatStatus = (status) => {
  const statusMap = {
    'active': 'Active',
    'canceled': 'Canceled',
    'past_due': 'Past Due',
    'incomplete': 'Incomplete',
    'incomplete_expired': 'Incomplete Expired',
    'trialing': 'Trial',
    'unpaid': 'Unpaid'
  };
  return statusMap[status] || status || 'Unknown';
};

const formatPlan = (item) => `${item.plan_name || 'Professional Plan'} (${item.stripe_id || 'N/A'})`;

const formatPeriod = (item) => {
  const startDate = item.current_period_start || 'N/A';
  const endDate = item.current_period_end || 'N/A';
  return `${startDate} - ${endDate}`;
};

const tableColumns = [
  { label: "Plan", key: "plan_name", formatter: formatPlan },
  { label: "Price", key: "amount" },
  { label: "Status", key: "status", formatter: formatStatus },
  { label: "Period", key: "current_period_start", formatter: formatPeriod },
  { label: "Created", key: "created_at" },
];

const applyFilters = () => {
  currentPage.value = 1;
};

const resetFilters = () => {
  filters.value = {
    status: '',
    plan: ''
  };
  currentPage.value = 1;
};

const viewSubscriptionDetails = (subscription) => {
  selectedSubscription.value = subscription;
  showDetailsModal.value = true;
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>