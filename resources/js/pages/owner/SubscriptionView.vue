<template>
  <main class="bg-[#f8fafc] min-h-screen font-sans antialiased text-slate-900 pb-12">
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
      <div class="mb-8 lg:mb-12">
        <h1 class="text-xl sm:text-3xl font-bold tracking-tight text-slate-900 mb-2">
          Subscription Management
        </h1>
        <p class="text-slate-500 text-xs sm:text-base">
          Manage your subscription, billing, and payment methods
        </p>
      </div>

      <div
        v-if="subscription"
        class="mb-12"
      >
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="p-5 sm:p-8 border-b border-slate-50">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
              <div>
                <h2 class="text-lg sm:text-2xl font-bold text-slate-900 mb-1 sm:mb-2">
                  Current Plan
                </h2>
                <div class="flex items-center gap-2">
                  <div
                    :class="[
                      'w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full',
                      subscription.is_active ? 'bg-emerald-500' : 'bg-rose-500',
                    ]"
                  ></div>
                  <span
                    :class="[
                      'text-xs sm:text-sm font-semibold',
                      subscription.is_active ? 'text-emerald-600' : 'text-rose-600',
                    ]"
                  >
                    {{ subscription.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
              <div class="bg-slate-50 sm:bg-transparent p-4 sm:p-0 rounded-xl sm:text-right">
                <div class="text-[22px] sm:text-3xl font-black text-slate-900">
                  £{{ getPlanPrice() }}
                </div>
                <div class="text-slate-500 text-[10px] sm:text-sm font-medium">
                  {{ getPlanInterval() }}
                </div>
                <div
                  class="text-slate-400 text-[9px] sm:text-xs mt-1 font-bold uppercase tracking-wider"
                >
                  {{ getPlanName() }}
                </div>
              </div>
            </div>
          </div>

          <div class="p-5 sm:p-8 space-y-6 sm:space-y-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-100">
                <div class="flex items-center gap-3 mb-2 sm:mb-3">
                  <Icon
                    icon="heroicons:calendar-20-solid"
                    class="w-4 h-4 sm:w-5 sm:h-5 text-slate-400"
                  />
                  <span
                    class="text-[9px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider"
                    >Next Billing Date</span
                  >
                </div>
                <div class="text-base sm:text-lg font-bold text-slate-900">
                  {{ getNextBillingDate() }}
                </div>
              </div>
              <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-100">
                <div class="flex items-center gap-3 mb-2 sm:mb-3">
                  <Icon
                    icon="heroicons:credit-card-20-solid"
                    class="w-4 h-4 sm:w-5 sm:h-5 text-slate-400"
                  />
                  <span
                    class="text-[9px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider"
                    >Payment Status</span
                  >
                </div>
                <div class="text-base sm:text-lg font-bold text-slate-900 capitalize">
                  {{ subscription.payment_status || 'Unknown' }}
                </div>
              </div>
            </div>

            <div class="p-4 sm:p-6 bg-blue-50 rounded-2xl border border-blue-100">
              <div class="flex items-center gap-3 mb-3 sm:mb-4">
                <Icon
                  icon="heroicons:information-circle-20-solid"
                  class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600"
                />
                <span class="text-xs sm:text-sm font-bold text-blue-900 uppercase tracking-wider"
                  >Subscription Details</span
                >
              </div>
              <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 text-xs sm:text-sm"
              >
                <div>
                  <span
                    class="block text-blue-600/60 font-bold text-[9px] sm:text-[10px] uppercase mb-1"
                    >Plan ID:</span
                  >
                  <span class="font-mono text-slate-700 break-all text-[10px] sm:text-xs">{{
                    subscription.subscription_id?.slice(0, 30)
                  }}</span>
                </div>
                <div>
                  <span
                    class="block text-blue-600/60 font-bold text-[9px] sm:text-[10px] uppercase mb-1"
                    >Price ID:</span
                  >
                  <span class="font-mono text-slate-700 break-all text-[10px] sm:text-xs">{{
                    subscription.price_id?.slice(0, 30)
                  }}</span>
                </div>
                <div>
                  <span
                    class="block text-blue-600/60 font-bold text-[9px] sm:text-[10px] uppercase mb-1"
                    >Plan Name:</span
                  >
                  <span class="font-bold text-slate-900 text-[11px] sm:text-sm">{{
                    getPlanName()
                  }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="recentSubscriptions.length > 0"
        class="mt-12"
      >
        <div class="mb-6">
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-1 sm:mb-2">
            Subscriptions History
          </h2>
          <p class="text-slate-500 text-xs sm:text-sm">
            View your subscription history and past billing cycles
          </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="overflow-x-auto scrollbar-thin">
            <table class="w-full min-w-[800px] lg:min-w-full">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                  <th
                    class="px-6 py-4 text-left text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest"
                  >
                    NO.
                  </th>
                  <th
                    class="px-6 py-4 text-left text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest"
                  >
                    Plan
                  </th>
                  <th
                    class="px-6 py-4 text-left text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest"
                  >
                    Status
                  </th>
                  <th
                    class="px-6 py-4 text-left text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest"
                  >
                    Duration
                  </th>
                  <th
                    class="px-6 py-4 text-left text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest"
                  >
                    Amount
                  </th>
                  <th
                    class="px-6 py-4 text-left text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest"
                  >
                    Purchased On
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="(sub, index) in recentSubscriptions"
                  :key="sub.id"
                  class="hover:bg-slate-50/50 transition-colors"
                >
                  <td class="px-6 py-4">
                    <span class="text-xs sm:text-sm font-bold text-slate-400 italic">{{
                      index + 1
                    }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-xs sm:text-sm font-bold text-slate-900">
                      {{ sub.plan_name || 'Professional Plan' }}
                    </div>
                    <div
                      class="text-[9px] sm:text-[10px] text-slate-400 font-mono mt-0.5 truncate max-w-[180px]"
                    >
                      {{ sub.stripe_id?.slice(0, 30) }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div
                        :class="[
                          'w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full',
                          sub.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500',
                        ]"
                      ></div>
                      <span class="text-[10px] sm:text-xs font-bold capitalize">{{
                        sub.status.replace('_', ' ')
                      }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-[10px] sm:text-xs text-slate-600">
                    {{ formatDate(sub.created_at) }} -
                    {{ calculateEndDate(sub.created_at, sub.interval) }}
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-xs sm:text-sm font-black text-slate-900">
                      £{{ sub.amount || '12.00' }}
                    </div>
                    <div class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase">
                      {{ sub.interval || '3 months' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-xs sm:text-sm text-slate-500 font-medium">
                    {{ formatDate(sub.created_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div
        v-else
        class="text-center py-12 sm:py-20 bg-white rounded-3xl border border-slate-200 border-dashed px-6"
      >
        <div class="max-w-md mx-auto">
          <div
            class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6"
          >
            <Icon
              icon="heroicons:credit-card-20-solid"
              class="w-8 h-8 sm:w-10 sm:h-10 text-slate-400"
            />
          </div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-2 sm:mb-4">
            No Active Subscription
          </h2>
          <p class="text-sm sm:text-base text-slate-500 mb-6 sm:mb-8">
            You don't have an active subscription. Choose a plan to get started.
          </p>
          <button
            class="w-full sm:w-auto px-6 py-3 sm:px-8 sm:py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm sm:text-base font-bold shadow-lg shadow-blue-100 transition-all active:scale-95 flex items-center justify-center gap-2 mx-auto"
            @click="$router.push('/subscription')"
          >
            <Icon
              icon="heroicons:plus-20-solid"
              class="w-5 h-5"
            />
            Choose a Plan
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="showDetailsModal && selectedSubscription"
      class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50"
    >
      <div
        class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl animate-in fade-in zoom-in duration-200"
      >
        <div class="p-5 sm:p-8">
          <div class="flex items-center justify-between mb-6 sm:mb-8">
            <h3 class="text-lg sm:text-2xl font-bold text-slate-900">Subscription Details</h3>
            <button
              class="p-2 hover:bg-slate-100 rounded-lg transition-colors"
              @click="showDetailsModal = false"
            >
              <Icon
                icon="heroicons:x-mark-20-solid"
                class="w-5 h-5 text-slate-400"
              />
            </button>
          </div>

          <div class="space-y-4 sm:space-y-6">
            <div class="bg-slate-50 rounded-xl p-4 sm:p-6 border border-slate-100">
              <h4
                class="text-xs sm:text-sm font-bold text-slate-900 mb-3 sm:mb-4 flex items-center gap-2"
              >
                <Icon
                  icon="heroicons:crown-20-solid"
                  class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600"
                />
                Plan Information
              </h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                  <label
                    class="text-[8px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest"
                    >Plan Name</label
                  >
                  <p class="text-slate-900 text-xs sm:text-base font-bold">
                    {{ selectedSubscription.plan_name || 'Professional Plan' }}
                  </p>
                </div>
                <div>
                  <label
                    class="text-[8px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest"
                    >Status</label
                  >
                  <div class="flex items-center gap-2 mt-0.5">
                    <div
                      :class="[
                        'w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full',
                        selectedSubscription.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500',
                      ]"
                    ></div>
                    <span class="text-[11px] sm:text-sm font-bold capitalize">{{
                      selectedSubscription.status?.replace('_', ' ') || 'Unknown'
                    }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-8 flex flex-col sm:flex-row gap-4">
            <button
              class="flex-1 px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm sm:text-base rounded-xl font-bold transition-colors"
              @click="showDetailsModal = false"
            >
              Close
            </button>
            <button
              v-if="selectedSubscription.status === 'canceled'"
              class="flex-1 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm sm:text-base rounded-xl font-bold transition-all flex items-center justify-center gap-2"
              @click="reactivateAndClose(selectedSubscription)"
            >
              <Icon
                icon="heroicons:arrow-path-20-solid"
                class="w-4 h-4"
              />
              Reactivate Subscription
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import ownerService from '../../services/ownerService';

const subscription = ref(null);
const recentSubscriptions = ref([]);
const showDetailsModal = ref(false);
const selectedSubscription = ref(null);

onMounted(async () => {
  await Promise.all([fetchSubscriptionDetails(), fetchRecentSubscriptions()]);
});

const fetchSubscriptionDetails = async () => {
  try {
    const response = await ownerService.fetchSubscriptionDetails();
    if (response.status) {
      subscription.value = response.data.current_plan;
      recentSubscriptions.value = response.data.recent_subscriptions;
    }
  } catch (err) {
    console.error(err);
  }
};

const fetchRecentSubscriptions = async () => {};

const getPlanPrice = () => subscription.value?.amount || '12.00';
const getPlanInterval = () => subscription.value?.interval || 'every 3 months';
const getPlanName = () => subscription.value?.plan_name || 'Professional Plan';

const getNextBillingDate = () => {
  if (
    subscription.value?.next_billing_date &&
    subscription.value.next_billing_date !== 'No billing date'
  ) {
    return subscription.value.next_billing_date;
  }
  if (!subscription.value?.current_period_end) {
    return 'No billing date';
  }
  return new Date(subscription.value.current_period_end * 1000).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const calculateEndDate = (startDate, interval) => {
  if (!startDate) {
    return 'No date';
  }
  const date = typeof startDate === 'number' ? new Date(startDate * 1000) : new Date(startDate);
  let monthsToAdd = 3;
  if (interval) {
    const match = interval.match(/(\d+)\s*(month|year|week|day)s?/i);
    if (match) {
      const val = parseInt(match[1]);
      const unit = match[2].toLowerCase();
      if (unit === 'month') {
        monthsToAdd = val;
      } else if (unit === 'year') {
        monthsToAdd = val * 12;
      }
    }
  }
  const endDate = new Date(date);
  endDate.setMonth(endDate.getMonth() + monthsToAdd);
  return endDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatDate = (timestamp) => {
  if (!timestamp) {
    return 'No date';
  }
  const date = typeof timestamp === 'number' ? new Date(timestamp * 1000) : new Date(timestamp);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const reactivateSubscription = async (sub) => {
  try {
    const response = await axios.post(
      '/api/stripe/subscription/reactivate',
      { subscriptionId: sub.stripe_id },
      { headers: { Authorization: `Bearer ${localStorage.getItem('authToken')}` } }
    );
    if (response.data.status) {
      toast.success('Subscription reactivated successfully');
      await fetchSubscriptionDetails();
    } else {
      toast.error(response.data.message || 'Failed to reactivate subscription');
    }
  } catch (error) {
    toast.error(error.response?.data?.error || 'Failed to reactivate subscription');
  }
};

const reactivateAndClose = async (sub) => {
  await reactivateSubscription(sub);
  showDetailsModal.value = false;
};
</script>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
  height: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: #f1f5f9;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.animate-in {
  animation: modalEnter 0.25s ease-out;
}
@keyframes modalEnter {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
