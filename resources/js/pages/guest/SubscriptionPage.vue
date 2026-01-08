<template>
  <main class="bg-gray-50 min-h-screen">
    <div
      v-if="loading"
      class="flex h-96 items-center justify-center flex-col gap-4"
    >
      <Icon
        icon="eos-icons:loading"
        class="w-12 h-12 text-blue-600 animate-spin"
      />
      <p class="text-gray-600 font-medium">Loading plans...</p>
    </div>

    <div v-else class="container mx-auto px-4 py-8 sm:py-12 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-4xl" v-if="!selectedPlanDetails">
        <div class="text-center">
          <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl md:text-5xl">
            Find the Perfect Plan for Your Business
          </h1>
          <p class="mt-4 text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
            Simple, transparent pricing. No hidden fees. Choose the plan that
            fits your needs.
          </p>
        </div>
      </div>

      <div
        v-if="plans.length && !selectedPlanDetails"
        class="mt-10 sm:mt-16 grid grid-cols-1 gap-6 sm:gap-8 md:grid-cols-2 lg:grid-cols-3 items-stretch"
      >
        <div
          v-for="plan in plans"
          :key="plan.id"
          class="relative flex flex-col rounded-2xl p-6 sm:p-8 transition-all duration-300 border border-gray-200 bg-white shadow-sm hover:shadow-md hover:border-blue-500/50 cursor-pointer"
          @click="selectPlan(plan)"
        >
          <div
            v-if="plan.nickname === 'Pro' || plan.name === 'Pro'"
            class="absolute -top-4 left-1/2 -translate-x-1/2 transform rounded-full bg-blue-600 px-4 py-1 text-sm font-semibold text-white whitespace-nowrap"
          >
            Most Popular
          </div>

          <div class="flex-grow">
            <h3
              class="text-xl sm:text-2xl font-semibold"
              :class="{
                'text-blue-600': plan.nickname === 'Pro' || plan.name === 'Pro',
              }"
            >
              {{ plan.nickname || plan.name }}
            </h3>
            <p class="mt-2 text-sm sm:text-base text-gray-500">{{ plan.description }}</p>

            <p class="mt-6 text-3xl sm:text-4xl font-bold text-gray-900">
              ${{ plan.display_amount }}
              <span class="text-base font-medium text-gray-500"
                >/{{ plan.display_interval }}</span
              >
            </p>

            <ul class="mt-8 space-y-4 text-sm text-gray-600">
              <li class="flex items-start">
                <Icon
                  icon="mdi:check-circle"
                  class="mr-2 h-5 w-5 shrink-0 text-green-500 mt-0.5"
                />
                <span>{{ plan.description || "Full access to all features" }}</span>
              </li>
              <li class="flex items-start">
                <Icon
                  icon="mdi:check-circle"
                  class="mr-2 h-5 w-5 shrink-0 text-green-500 mt-0.5"
                />
                <span v-if="plan.display_interval === '3 months'">Billed every 3 months</span>
                <span v-else-if="plan.recurring?.interval === 'year'">Billed annually (save 20%)</span>
                <span v-else>Billed monthly</span>
              </li>
              <li class="flex items-start">
                <Icon
                  icon="mdi:check-circle"
                  class="mr-2 h-5 w-5 shrink-0 text-green-500 mt-0.5"
                />
                <span>Cancel anytime</span>
              </li>
            </ul>
          </div>
          <button
            class="mt-8 w-full cursor-pointer border-2 border-blue-600 text-blue-600 px-6 py-3 rounded-xl font-bold transition-all hover:bg-blue-600 hover:text-white text-center text-sm sm:text-base"
          >
            Choose Plan
          </button>
        </div>
      </div>

      <div v-else-if="!plans.length && !loading" class="text-center mt-16">
        <p class="text-gray-600">No subscription plans found.</p>
      </div>

      <div v-if="selectedPlanDetails" class="mt-4 sm:mt-8 max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
          
          <div class="border-b border-gray-200 px-4 py-4 sm:px-8 sm:py-6 grid grid-cols-3 items-center">
            <div class="flex justify-start">
              <button
                @click="clearSelection"
                class="flex items-center text-gray-600 hover:text-gray-900 transition-colors cursor-pointer font-medium text-sm sm:text-base whitespace-nowrap"
              >
                <Icon icon="mdi:arrow-left" class="h-5 w-5 mr-1" />
                <span class="hidden sm:inline">Back</span>
                <span class="sm:hidden">Back</span>
              </button>
            </div>

            <div class="text-center">
              <h2 class="text-lg sm:text-2xl font-bold text-gray-900 whitespace-nowrap">
                Complete Subscription
              </h2>
              <p class="text-gray-500 text-[10px] sm:text-sm mt-0.5 hidden sm:block">Secure payment via Stripe</p>
            </div>

            <div class="flex justify-end invisible pointer-events-none">
                <div class="flex items-center text-sm sm:text-base">
                    <Icon icon="mdi:arrow-left" class="h-5 w-5 mr-1" />
                    <span class="hidden sm:inline">Back to Plans</span>
                    <span class="sm:hidden">Back</span>
                </div>
            </div>
          </div>

          <div class="px-4 py-4 sm:px-8 sm:py-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
            <div class="flex flex-row items-center justify-between gap-4">
              <div class="min-w-0">
                <h3 class="font-bold text-base sm:text-lg text-gray-900 truncate">
                  {{ selectedPlanDetails?.nickname || selectedPlanDetails?.name }}
                </h3>
                <p class="text-gray-600 text-xs sm:text-sm truncate">
                  {{ selectedPlanDetails?.description }}
                </p>
              </div>
              <div class="text-right shrink-0">
                <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">
                  ${{ selectedPlanDetails?.display_amount }}
                </p>
                <p class="text-gray-500 text-[10px] sm:text-xs font-bold uppercase tracking-wider">
                  /{{ selectedPlanDetails?.display_interval }}
                </p>
              </div>
            </div>
          </div>

          <form @submit.prevent="handleSubscriptionPayment" class="px-4 py-6 sm:px-8 sm:py-8 space-y-5 sm:space-y-6">
            <div>
              <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5 uppercase tracking-wide">
                Cardholder Name
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Icon icon="mdi:account" class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  v-model="guestDetails.fullName"
                  type="text"
                  required
                  class="w-full pl-10 pr-3 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition-all shadow-sm"
                  placeholder="Enter full name"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5 uppercase tracking-wide">
                Email Address
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Icon icon="mdi:email" class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  v-model="guestDetails.email"
                  type="email"
                  required
                  class="w-full pl-10 pr-3 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition-all shadow-sm"
                  placeholder="your@email.com"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5 uppercase tracking-wide">
                Card Details
              </label>
              <div
                id="card-element"
                class="p-3 sm:p-4 bg-white border border-gray-300 rounded-lg shadow-sm min-h-[44px]"
              ></div>
              <p id="card-errors" role="alert" class="mt-2 text-[11px] sm:text-xs text-red-500 font-medium"></p>
            </div>

            <div class="p-3 sm:p-4 bg-blue-50 rounded-xl border border-blue-100 flex items-start">
              <Icon
                icon="mdi:shield-check"
                class="h-5 w-5 text-blue-600 mt-0.5 mr-2 sm:mr-3 flex-shrink-0"
              />
              <div class="text-[11px] sm:text-sm text-blue-800">
                <p class="font-bold mb-0.5">Secure Transaction</p>
                <p class="opacity-80">Encrypted payment. We never store your card details.</p>
              </div>
            </div>

            <div class="pt-2 sm:pt-4">
              <button
                type="submit"
                :disabled="isSubmitting"
                class="w-full px-6 py-3.5 sm:py-4 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-bold uppercase tracking-widest disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center shadow-lg shadow-blue-300 text-sm sm:text-base"
              >
                <Icon
                  v-if="isSubmitting"
                  icon="mdi:loading"
                  class="animate-spin h-5 w-5 mr-2"
                />
                {{ isSubmitting ? "Processing..." : `Confirm & Pay $${selectedPlanDetails?.display_amount}` }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import { Icon } from "@iconify/vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { loadStripe } from "@stripe/stripe-js";

const router = useRouter();
const plans = ref([]);
const loading = ref(true);
const selectedPlanDetails = ref(null);
const isSubmitting = ref(false);

const guestDetails = ref({
  fullName: "",
  email: "",
});

const stripe = ref(null);
const elements = ref(null);
const card = ref(null);
const isCardComplete = ref(false);

onMounted(async () => {
  await fetchPlans();
  const publishableKey =
    "pk_test_51RvEjJ7fYIrC7aOkBuyFRaQM8EH4P3nCf8sW5BEFVufQaLOlM2ZNk8lRDNh7uCtm6sVV2Wa2dhIVbIwI6P2q2xQx00PpDGeuDB";
  stripe.value = await loadStripe(publishableKey);
});

const fetchPlans = async () => {
  try {
    const response = await axios.get("/api/products");
    if (response.data.status) {
      const flattenedPlans = [];
      response.data.data.forEach((product) => {
        if (product.prices && product.prices.length > 0) {
          product.prices.forEach((price) => {
            let displayAmount = price.unit_amount / 100;
            let displayInterval = price.recurring?.interval || "month";

            if (
              price.unit_amount === 1200 &&
              price.recurring?.interval === "month"
            ) {
              displayAmount = 12;
              displayInterval = "3 months";
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
        }
      });
      plans.value = flattenedPlans.sort(
        (a, b) => a.display_amount - b.display_amount
      );
    }
  } catch (error) {
    console.error("Failed to fetch products:", error);
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
  if (!stripe.value) return;

  elements.value = stripe.value.elements();
  card.value = elements.value.create("card", {
    style: {
      base: {
        fontSize: "16px",
        color: "#32325d",
        fontFamily: "sans-serif",
        "::placeholder": { color: "#aab7c4" },
      },
      invalid: { color: "#fa755a" },
    },
  });

  card.value.mount("#card-element");

  card.value.on("change", (event) => {
    isCardComplete.value = event.complete;
    const cardErrors = document.getElementById("card-errors");
    if (event.error) {
      cardErrors.textContent = event.error.message;
    } else {
      cardErrors.textContent = "";
    }
  });
};

const clearSelection = () => {
  selectedPlanDetails.value = null;
  if (card.value) {
    card.value.unmount();
    card.value = null;
  }
  guestDetails.value = { fullName: "", email: "" };
};

const handleSubscriptionPayment = async () => {
  if (!isCardComplete.value) {
    const cardErrors = document.getElementById("card-errors");
    cardErrors.textContent = "Please enter complete card details.";
    return;
  }

  isSubmitting.value = true;
  const cardErrors = document.getElementById("card-errors");
  cardErrors.textContent = "";

  try {
    await new Promise((resolve) => setTimeout(resolve, 2000));
    alert(`Successfully subscribed to ${selectedPlanDetails.value.nickname}!`);
    router.push("/");
  } catch (err) {
    console.error("Subscription Error:", err);
    cardErrors.textContent = "Payment failed. Please try again.";
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  window.history.back();
};
</script>