<template>
  <main class="bg-[#f8fafc] min-h-screen font-sans antialiased text-slate-900">
    <div
      v-if="loading"
      class="flex h-screen items-center justify-center flex-col gap-6"
    >
      <div class="relative flex items-center justify-center">
        <div
          class="w-16 h-16 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin"
        ></div>
        <Icon
          icon="logos:stripe"
          class="absolute w-6 h-6 opacity-40"
        />
      </div>
      <p class="text-slate-500 font-medium tracking-wide">Preparing secure checkout...</p>
    </div>

    <div
      v-else
      class="container mx-auto px-4 py-12 sm:py-20 max-w-7xl"
    >
      <transition
        name="fade-down"
        appear
      >
        <div
          v-if="!selectedPlanDetails"
          class="mx-auto max-w-3xl text-center mb-16"
        >
          <span
            class="inline-block px-4 py-1.5 mb-6 text-xs font-semibold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full"
          >
            Pricing & Plans
          </span>
          <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl mb-6">
            Scale your business <br class="hidden sm:block" />
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600"
              >without the limits.</span
            >
          </h1>
          <p class="text-lg text-slate-500 leading-relaxed">
            Simple, transparent pricing. No hidden fees. Choose the plan that fits your needs.
          </p>
        </div>
      </transition>

      <div
        v-if="plans.length && !selectedPlanDetails"
        class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 items-stretch max-w-6xl mx-auto"
      >
        <div
          v-for="plan in plans"
          :key="plan.id"
          class="group relative flex flex-col rounded-3xl p-8 transition-all duration-500 bg-white border border-slate-200 hover:border-blue-500/30 hover:shadow-[0_20px_50px_rgba(0,0,0,0.05)] cursor-pointer"
          @click="selectPlan(plan)"
        >
          <div
            v-if="plan.nickname === 'Pro' || plan.name === 'Pro'"
            class="absolute -top-4 left-1/2 -translate-x-1/2 transform rounded-full bg-slate-900 px-4 py-1 text-xs font-bold text-white shadow-xl"
          >
            Best Value
          </div>

          <div class="flex-grow">
            <h3
              class="text-xl font-bold text-slate-900 group-hover:text-blue-600 transition-colors"
            >
              {{ plan.nickname || plan.name }}
            </h3>
            <p class="mt-3 text-slate-500 text-sm leading-relaxed">
              {{ plan.description || 'Full access to all features to help your business grow.' }}
            </p>

            <div class="mt-8 flex items-baseline gap-1">
              <span class="text-4xl font-black text-slate-900">£{{ plan.display_amount }}</span>
              <span class="text-slate-400 font-medium">/{{ plan.display_interval }}</span>
            </div>

            <div class="mt-8 pt-8 border-t border-slate-50 space-y-4">
              <div class="flex items-start gap-3">
                <div
                  class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-50 flex items-center justify-center mt-0.5"
                >
                  <Icon
                    icon="heroicons:check-16-solid"
                    class="w-3.5 h-3.5 text-emerald-600"
                  />
                </div>
                <span class="text-sm text-slate-600">{{
                  plan.description || 'Full access to all features'
                }}</span>
              </div>
              <div class="flex items-start gap-3">
                <div
                  class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-50 flex items-center justify-center mt-0.5"
                >
                  <Icon
                    icon="heroicons:check-16-solid"
                    class="w-3.5 h-3.5 text-emerald-600"
                  />
                </div>
                <span
                  v-if="plan.display_interval === '3 months'"
                  class="text-sm text-slate-600"
                  >Billed every 3 months</span
                >
                <span
                  v-else-if="plan.recurring?.interval === 'year'"
                  class="text-sm text-slate-600"
                  >Billed annually (save 20%)</span
                >
                <span
                  v-else
                  class="text-sm text-slate-600"
                  >Billed monthly</span
                >
              </div>
              <div class="flex items-start gap-3">
                <div
                  class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-50 flex items-center justify-center mt-0.5"
                >
                  <Icon
                    icon="heroicons:check-16-solid"
                    class="w-3.5 h-3.5 text-emerald-600"
                  />
                </div>
                <span class="text-sm text-slate-600">Cancel anytime</span>
              </div>
            </div>
          </div>

          <button
            class="mt-10 w-full py-4 px-6 rounded-2xl bg-slate-50 text-slate-900 font-bold text-sm transition-all group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-blue-200"
          >
            Choose Plan
          </button>
        </div>
      </div>

      <div
        v-else-if="!plans.length && !loading"
        class="text-center mt-16"
      >
        <p class="text-slate-500">No subscription plans found.</p>
      </div>

      <transition
        name="fade"
        mode="out-in"
      >
        <div
          v-if="selectedPlanDetails"
          class="max-w-2xl mx-auto"
        >
          <div
            class="bg-white rounded-[2rem] shadow-[0_40px_80px_-15px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden"
          >
            <div class="px-8 pt-10 pb-6 text-center relative">
              <button
                class="absolute left-8 top-11 p-2 rounded-full hover:bg-slate-50 transition-colors"
                @click="clearSelection"
              >
                <Icon
                  icon="heroicons:arrow-left-20-solid"
                  class="w-5 h-5 text-slate-400"
                />
              </button>
              <h2 class="text-2xl font-black text-slate-900">Complete your subscription</h2>
              <p class="text-slate-400 text-sm mt-1">Secure Payment Via Stripe</p>
            </div>

            <div
              class="mx-8 p-6 rounded-2xl bg-gradient-to-br from-indigo-600 via-blue-600 to-blue-500 text-white flex items-center justify-between mb-10 shadow-xl shadow-blue-100"
            >
              <div class="text-left">
                <p
                  class="text-blue-100 text-[10px] font-bold uppercase tracking-widest mb-1 opacity-80"
                >
                  Selected Plan
                </p>
                <h4 class="font-bold text-xl tracking-tight">
                  {{ selectedPlanDetails?.nickname || selectedPlanDetails?.name }}
                </h4>
              </div>
              <div class="text-right">
                <div class="flex items-baseline justify-end gap-1">
                  <span class="text-xs font-medium text-blue-100">£</span>
                  <span class="text-3xl font-black">{{ selectedPlanDetails?.display_amount }}</span>
                </div>
                <span
                  class="text-blue-100 text-[11px] font-semibold block capitalize opacity-90 mt-0.5"
                  >per {{ selectedPlanDetails?.display_interval }}</span
                >
              </div>
            </div>

            <form
              class="px-8 pb-12"
              @submit.prevent="handleSubscriptionPayment"
            >
              <div class="space-y-6">
                <div class="space-y-2">
                  <label
                    class="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 text-left"
                    >Cardholder Name</label
                  >
                  <div class="relative">
                    <div
                      class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                    >
                      <Icon
                        icon="heroicons:user-20-solid"
                        class="h-5 w-5 text-slate-300"
                      />
                    </div>
                    <input
                      v-model="guestDetails.fullName"
                      type="text"
                      required
                      class="w-full pl-11 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all outline-none text-sm"
                      placeholder="Enter full name"
                    />
                  </div>
                </div>

                <div class="space-y-2">
                  <label
                    class="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 text-left"
                    >Email Address</label
                  >
                  <div class="relative">
                    <div
                      class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                    >
                      <Icon
                        icon="heroicons:envelope-20-solid"
                        class="h-5 w-5 text-slate-300"
                      />
                    </div>
                    <input
                      v-model="guestDetails.email"
                      type="email"
                      required
                      class="w-full pl-11 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all outline-none text-sm"
                      placeholder="your@email.com"
                    />
                  </div>
                </div>

                <div class="space-y-2">
                  <label
                    class="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 text-left"
                    >Card Details</label
                  >
                  <div
                    id="card-element"
                    class="px-5 py-4 bg-white border border-slate-200 rounded-2xl shadow-sm min-h-[50px]"
                  ></div>
                  <p
                    id="card-errors"
                    role="alert"
                    class="text-xs text-rose-500 mt-2 font-medium text-left"
                  ></p>
                </div>

                <div class="flex items-center justify-center gap-6 pt-4 border-t border-slate-50">
                  <div class="flex items-center gap-2 grayscale opacity-50">
                    <Icon
                      icon="logos:stripe"
                      class="h-4"
                    />
                  </div>
                  <div class="w-px h-4 bg-slate-200"></div>
                  <p
                    class="text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center gap-1.5"
                  >
                    <Icon
                      icon="heroicons:shield-check-20-solid"
                      class="w-4 h-4 text-emerald-500"
                    />
                    SCA Secured Payment
                  </p>
                </div>

                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="group relative w-full py-5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold text-base transition-all shadow-xl shadow-blue-200 disabled:opacity-50 overflow-hidden"
                >
                  <div class="flex items-center justify-center relative z-10">
                    <Icon
                      v-if="isSubmitting"
                      icon="svg-spinners:ring-resize"
                      class="w-5 h-5 mr-3"
                    />
                    <span>{{
                      isSubmitting
                        ? 'Processing...'
                        : `Confirm & Pay ${selectedPlanDetails?.display_amount}`
                    }}</span>
                  </div>
                  <div
                    class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"
                  ></div>
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Icon } from '@iconify/vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { loadStripe } from '@stripe/stripe-js';
import { toast } from 'vue-sonner';

const router = useRouter();
const plans = ref([]);
const loading = ref(true);
const selectedPlanDetails = ref(null);
const isSubmitting = ref(false);

const guestDetails = ref({
  fullName: '',
  email: '',
});

const stripe = ref(null);
const elements = ref(null);
const card = ref(null);
const isCardComplete = ref(false);

onMounted(async () => {
  await Promise.all([fetchPlans(), fetchStripeConfig()]);
  checkAndPrefillUserDetails();
});

const fetchStripeConfig = async () => {
  try {
    const response = await axios.get('/api/stripe/config');
    if (response.data.status) {
      stripe.value = await loadStripe(response.data.data.publishableKey);
    }
  } catch (error) {
    throw new Error(error.error);
  }
};

const checkAndPrefillUserDetails = () => {
  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (user.role === 'owner') {
      guestDetails.value.fullName =
        [user.firstName, user.lastName].filter(Boolean).join(' ') || user.name || '';
      guestDetails.value.email = user.email || '';
    }
  } catch (error) {
    throw new Error(error.error);
  }
};

const fetchPlans = async () => {
  try {
    const response = await axios.get('/api/products');
    if (response.data.status) {
      const flattenedPlans = [];
      response.data.data.forEach((product) => {
        (product.prices || []).forEach((price) => {
          let displayAmount = price.unit_amount / 100;
          let displayInterval = price.recurring?.interval || 'month';

          if (price.unit_amount === 1200 && price.recurring?.interval === 'month') {
            displayAmount = 12;
            displayInterval = '3 months';
          }

          flattenedPlans.push({
            id: price.id,
            name: product.name,
            nickname: price.nickname || product.name,
            description: product.description,
            unit_amount: price.unit_amount,
            display_amount: displayAmount,
            recurring: price.recurring,
            display_interval: displayInterval,
            product_id: product.id,
          });
        });
      });
      plans.value = flattenedPlans.sort((a, b) => a.display_amount - b.display_amount);
    }
  } catch (error) {
    throw new Error(error.error);
  } finally {
    loading.value = false;
  }
};

const selectPlan = async (plan) => {
  selectedPlanDetails.value = plan;
  await nextTick();
  mountStripeElement();
};

const mountStripeElement = () => {
  if (!stripe.value) {
    return;
  }

  elements.value = stripe.value.elements();
  card.value = elements.value.create('card', {
    style: {
      base: {
        fontSize: '16px',
        color: '#1e293b',
        fontFamily: 'Inter, system-ui, sans-serif',
        '::placeholder': { color: '#94a3b8' },
      },
    },
  });

  card.value.mount('#card-element');
  card.value.on('change', (event) => {
    isCardComplete.value = event.complete;
    document.getElementById('card-errors').textContent = event.error ? event.error.message : '';
  });
};

const clearSelection = () => {
  selectedPlanDetails.value = null;
  guestDetails.value = { fullName: '', email: '' };
};

const handleSubscriptionPayment = async () => {
  if (!isCardComplete.value) {
    document.getElementById('card-errors').textContent = 'Please enter complete card details.';
    return;
  }

  isSubmitting.value = true;
  try {
    const { paymentMethod, error } = await stripe.value.createPaymentMethod({
      type: 'card',
      card: card.value,
      billing_details: {
        name: guestDetails.value.fullName,
        email: guestDetails.value.email,
      },
    });

    if (error) {
      throw new Error(error.message);
    }

    const response = await axios.post(
      '/api/stripe/subscription',
      {
        priceId: selectedPlanDetails.value.id,
        paymentMethodId: paymentMethod.id,
      },
      {
        headers: { Authorization: `Bearer ${localStorage.getItem('authToken')}` },
      }
    );

    if (response.data.status) {
      toast.success(response.data.message || 'Subscription activated successfully!');
      router.push('/owner');
    } else if (response.data.data?.clientSecret) {
      const { error: confirmError } = await stripe.value.confirmCardPayment(
        response.data.data.clientSecret
      );
      if (confirmError) {
        throw new Error(confirmError.message);
      }

      toast.success('Payment confirmed! Subscription activated.');
      router.push('/owner');
    } else {
      throw new Error(response.data.message || 'Payment failed');
    }
  } catch (err) {
    document.getElementById('card-errors').textContent = err.message;
    toast.error(err.message);
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-down-enter-active {
  transition: all 0.6s ease-out;
}
.fade-down-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
