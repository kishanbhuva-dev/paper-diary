<template>
  <main class="bg-[#f8fafc] min-h-screen font-sans antialiased text-slate-900">
    <!-- <div v-if="loading" class="flex h-screen items-center justify-center flex-col gap-6">
      <div class="relative flex items-center justify-center">
        <div class="w-16 h-16 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin"></div>
        <Icon icon="logos:stripe" class="absolute w-6 h-6 opacity-40" />
      </div>
      <p class="text-slate-500 font-medium tracking-wide">Loading subscription details...</p>
    </div> -->

    <div class="p-4">
      <!-- Header Section -->
      <div class="mb-12">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 mb-2">
          Subscription Management
        </h1>
        <p class="text-slate-500">Manage your subscription, billing, and payment methods</p>
      </div>

      <!-- Subscription Status Card -->
      <div
        v-if="subscription"
        class="mb-12"
      >
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
          <div class="flex items-center justify-between mb-8">
            <div>
              <h2 class="text-2xl font-bold text-slate-900 mb-2">Current Plan</h2>
              <div class="flex items-center gap-2">
                <div
                  :class="[
                    'w-2 h-2 rounded-full',
                    subscription.is_active ? 'bg-emerald-500' : 'bg-rose-500',
                  ]"
                ></div>
                <span
                  :class="[
                    'text-sm font-medium',
                    subscription.is_active ? 'text-emerald-600' : 'text-rose-600',
                  ]"
                >
                  {{ subscription.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
            <div class="text-right">
              <div class="text-3xl font-black text-slate-900">£{{ getPlanPrice() }}</div>
              <div class="text-slate-500 text-sm">{{ getPlanInterval() }}</div>
              <div class="text-slate-400 text-xs mt-1">{{ getPlanName() }}</div>
            </div>
          </div>

          <div class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div class="p-4 bg-slate-50 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                  <Icon
                    icon="heroicons:calendar-20-solid"
                    class="w-5 h-5 text-slate-400"
                  />
                  <span class="text-sm font-medium text-slate-600">Next Billing Date</span>
                </div>
                <div class="text-lg font-semibold text-slate-900">
                  {{ getNextBillingDate() }}
                </div>
              </div>
              <div class="p-4 bg-slate-50 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                  <Icon
                    icon="heroicons:credit-card-20-solid"
                    class="w-5 h-5 text-slate-400"
                  />
                  <span class="text-sm font-medium text-slate-600">Payment Status</span>
                </div>
                <div class="text-lg font-semibold text-slate-900 capitalize">
                  {{ subscription.payment_status || 'Unknown' }}
                </div>
              </div>
            </div>

            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
              <div class="flex items-center gap-3 mb-2">
                <Icon
                  icon="heroicons:information-circle-20-solid"
                  class="w-5 h-5 text-blue-600"
                />
                <span class="text-sm font-medium text-blue-900">Subscription Details</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-slate-600">Plan ID:</span>
                  <span class="ml-2 font-mono text-slate-900">{{
                    subscription.subscription_id?.slice(0, 30)
                  }}</span>
                </div>
                <div>
                  <span class="text-slate-600">Price ID:</span>
                  <span class="ml-2 font-mono text-slate-900">{{
                    subscription.price_id?.slice(0, 30)
                  }}</span>
                </div>
                <div>
                  <span class="text-slate-600">Plan Name:</span>
                  <span class="ml-2 font-medium text-slate-900">{{ getPlanName() }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Subscriptions History -->
      <div
        v-if="recentSubscriptions.length > 0"
        class="mt-12"
      >
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-slate-900 mb-2">Recent Subscriptions</h2>
          <p class="text-slate-500">View your subscription history and past billing cycles</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                  <th
                    class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                  >
                    Plan
                  </th>
                  <th
                    class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                  >
                    Status
                  </th>
                  <th
                    class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                  >
                    Duration
                  </th>
                  <th
                    class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                  >
                    Amount
                  </th>
                  <th
                    class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                  >
                    Created
                  </th>
                  <!-- <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th> -->
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                <tr
                  v-for="sub in recentSubscriptions"
                  :key="sub.id"
                  class="hover:bg-slate-50 transition-colors"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div>
                        <div class="text-sm font-medium text-slate-900">
                          {{ sub.plan_name || 'Professional Plan' }}
                        </div>
                        <div class="text-xs text-slate-500 font-mono">
                          {{ sub.stripe_id?.slice(0, 30) }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div
                        :class="[
                          'w-2 h-2 rounded-full',
                          sub.status === 'active'
                            ? 'bg-emerald-500'
                            : sub.status === 'canceled'
                              ? 'bg-rose-500'
                              : sub.status === 'past_due'
                                ? 'bg-amber-500'
                                : 'bg-slate-400',
                        ]"
                      ></div>
                      <span
                        :class="[
                          'text-sm font-medium capitalize',
                          sub.status === 'active'
                            ? 'text-emerald-600'
                            : sub.status === 'canceled'
                              ? 'text-rose-600'
                              : sub.status === 'past_due'
                                ? 'text-amber-600'
                                : 'text-slate-600',
                        ]"
                      >
                        {{ sub.status.replace('_', ' ') }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-slate-900">
                      <div>
                        {{ formatDate(sub.created_at) }} -
                        {{ calculateEndDate(sub.created_at, sub.interval) }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-slate-900">
                      £{{ sub.amount || '12.00' }}
                    </div>
                    <div class="text-xs text-slate-500">{{ sub.interval || '3 months' }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-slate-900">{{ formatDate(sub.created_at) }}</div>
                  </td>
                  <!-- <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <button
                        @click="viewSubscriptionDetails(sub)"
                        class="p-2 hover:bg-slate-100 rounded-lg transition-colors"
                        title="View Details"
                      >
                        <Icon icon="heroicons:eye-20-solid" class="w-4 h-4 text-slate-400 hover:text-slate-600" />
                      </button>
                      <button
                        v-if="sub.status === 'canceled' && index === 0"
                        @click="reactivateSubscription(sub)"
                        class="p-2 hover:bg-emerald-50 rounded-lg transition-colors"
                        title="Reactivate"
                      >
                        <Icon icon="heroicons:arrow-path-20-solid" class="w-4 h-4 text-emerald-400 hover:text-emerald-600" />
                      </button>
                    </div>
                  </td> -->
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- No Subscription State -->
      <div
        v-else
        class="text-center py-20"
      >
        <div class="max-w-md mx-auto">
          <div
            class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6"
          >
            <Icon
              icon="heroicons:credit-card-20-solid"
              class="w-10 h-10 text-slate-400"
            />
          </div>
          <h2 class="text-2xl font-bold text-slate-900 mb-4">No Active Subscription</h2>
          <p class="text-slate-500 mb-8">
            You don't have an active subscription. Choose a plan to get started.
          </p>
          <button
            class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-colors flex items-center gap-2 mx-auto"
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
    <!-- Subscription Details Modal -->
    <div
      v-if="showDetailsModal && selectedSubscription"
      class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
    >
      <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-slate-900">Subscription Details</h3>
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

          <div class="space-y-6">
            <!-- Plan Information -->
            <div class="bg-slate-50 rounded-xl p-6">
              <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <Icon
                  icon="heroicons:crown-20-solid"
                  class="w-5 h-5 text-blue-600"
                />
                Plan Information
              </h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-slate-600">Plan Name</label>
                  <p class="text-slate-900 font-medium">
                    {{ selectedSubscription.plan_name || 'Professional Plan' }}
                  </p>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">Status</label>
                  <div class="flex items-center gap-2 mt-1">
                    <div
                      :class="[
                        'w-2 h-2 rounded-full',
                        selectedSubscription.status === 'active'
                          ? 'bg-emerald-500'
                          : selectedSubscription.status === 'canceled'
                            ? 'bg-rose-500'
                            : selectedSubscription.status === 'past_due'
                              ? 'bg-amber-500'
                              : 'bg-slate-400',
                      ]"
                    ></div>
                    <span
                      :class="[
                        'text-sm font-medium capitalize',
                        selectedSubscription.status === 'active'
                          ? 'text-emerald-600'
                          : selectedSubscription.status === 'canceled'
                            ? 'text-rose-600'
                            : selectedSubscription.status === 'past_due'
                              ? 'text-amber-600'
                              : 'text-slate-600',
                      ]"
                    >
                      {{ selectedSubscription.status?.replace('_', ' ') || 'Unknown' }}
                    </span>
                  </div>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">Amount</label>
                  <p class="text-slate-900 font-medium">
                    ${{ selectedSubscription.amount || '12.00' }}
                  </p>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">Billing Interval</label>
                  <p class="text-slate-900 font-medium">
                    {{ selectedSubscription.interval || '3 months' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Billing Period -->
            <div class="bg-blue-50 rounded-xl p-6">
              <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <Icon
                  icon="heroicons:calendar-20-solid"
                  class="w-5 h-5 text-blue-600"
                />
                Billing Period
              </h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-slate-600">Period Start</label>
                  <p class="text-slate-900 font-medium">
                    {{ formatDate(selectedSubscription.current_period_start) }}
                  </p>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">Period End</label>
                  <p class="text-slate-900 font-medium">
                    {{ formatDate(selectedSubscription.current_period_end) }}
                  </p>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">Created Date</label>
                  <p class="text-slate-900 font-medium">
                    {{ formatDate(selectedSubscription.created_at) }}
                  </p>
                </div>
                <div v-if="selectedSubscription.canceled_at">
                  <label class="text-sm font-medium text-slate-600">Canceled Date</label>
                  <p class="text-slate-900 font-medium">
                    {{ formatDate(selectedSubscription.canceled_at) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Subscription IDs -->
            <div class="bg-amber-50 rounded-xl p-6">
              <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <Icon
                  icon="heroicons:information-circle-20-solid"
                  class="w-5 h-5 text-amber-600"
                />
                Subscription Identifiers
              </h4>
              <div class="space-y-3">
                <div>
                  <label class="text-sm font-medium text-slate-600">Subscription ID</label>
                  <p
                    class="font-mono text-sm text-slate-900 bg-white px-3 py-2 rounded-lg border border-slate-200"
                  >
                    {{ selectedSubscription.stripe_id || 'N/A' }}
                  </p>
                </div>
                <div>
                  <label class="text-sm font-medium text-slate-600">Database ID</label>
                  <p
                    class="font-mono text-sm text-slate-900 bg-white px-3 py-2 rounded-lg border border-slate-200"
                  >
                    #{{ selectedSubscription.id }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-slate-50 rounded-xl p-6">
              <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <Icon
                  icon="heroicons:clipboard-document-list-20-solid"
                  class="w-5 h-5 text-slate-600"
                />
                Additional Information
              </h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-slate-600">Cancel at Period End</label>
                  <p class="text-slate-900 font-medium">
                    {{ selectedSubscription.cancel_at_period_end ? 'Yes' : 'No' }}
                  </p>
                </div>
                <div v-if="selectedSubscription.ended_at">
                  <label class="text-sm font-medium text-slate-600">Ended Date</label>
                  <p class="text-slate-900 font-medium">
                    {{ formatDate(selectedSubscription.ended_at) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-8 flex gap-4">
            <button
              class="flex-1 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-colors"
              @click="showDetailsModal = false"
            >
              Close
            </button>
            <button
              v-if="selectedSubscription.status === 'canceled'"
              class="flex-1 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-medium transition-colors flex items-center justify-center gap-2"
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

// const loading = ref(true);
const subscription = ref(null);
const recentSubscriptions = ref([]);

onMounted(async () => {
  await Promise.all([fetchSubscriptionDetails(), fetchRecentSubscriptions()]);
});

const fetchSubscriptionDetails = async () => {
  const response = await ownerService.fetchSubscriptionDetails();
  if (response.status) {
    subscription.value = response.data.current_plan;
    recentSubscriptions.value = response.data.recent_subscriptions;
  }
};

const fetchRecentSubscriptions = async () => {
  // Data is already fetched in fetchSubscriptionDetails
  // This function is kept for compatibility but does nothing
};

const getPlanPrice = () => {
  // Extract price from subscription data
  if (subscription.value?.amount) {
    return subscription.value.amount;
  }
  return '12.00';
};

const getPlanInterval = () => {
  // Extract interval from subscription data
  if (subscription.value?.interval) {
    return subscription.value.interval;
  }
  return 'every 3 months';
};

const getPlanName = () => {
  // Extract plan name from subscription data
  if (subscription.value?.plan_name) {
    return subscription.value.plan_name;
  }
  return 'Professional Plan';
};

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

  const date = new Date(subscription.value.current_period_end * 1000);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const calculateEndDate = (startDate, interval) => {
  if (!startDate) {
    return 'No date';
  }

  // Handle both timestamp numbers and string dates
  const date = typeof startDate === 'number' ? new Date(startDate * 1000) : new Date(startDate);

  // Check if date is valid
  if (isNaN(date.getTime())) {
    return 'Invalid date';
  }

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
    day: 'numeric',
  });
};

const formatDate = (timestamp) => {
  if (!timestamp) {
    return 'No date';
  }

  // Handle both timestamp numbers and string dates
  const date = typeof timestamp === 'number' ? new Date(timestamp * 1000) : new Date(timestamp);

  // Check if date is valid
  if (isNaN(date.getTime())) {
    return 'Invalid date';
  }

  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const showDetailsModal = ref(false);
const selectedSubscription = ref(null);

const reactivateAndClose = async (sub) => {
  await reactivateSubscription(sub);
  showDetailsModal.value = false;
};

const reactivateSubscription = async (sub) => {
  try {
    const response = await axios.post(
      '/api/stripe/subscription/reactivate',
      {
        subscriptionId: sub.stripe_id,
      },
      {
        headers: { Authorization: `Bearer ${localStorage.getItem('authToken')}` },
      }
    );

    if (response.data.status) {
      toast.success('Subscription reactivated successfully');
      await Promise.all([fetchSubscriptionDetails(), fetchRecentSubscriptions()]);
    } else {
      toast.error(response.data.message || 'Failed to reactivate subscription');
    }
  } catch (error) {
    toast.error(error.error || 'Failed to reactivate subscription');
  }
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
