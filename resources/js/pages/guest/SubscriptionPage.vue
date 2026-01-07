<template>
  <main class="bg-gray-50">
    <!-- Loading Indicator -->
    <div v-if="loading" class="flex h-96 items-center justify-center">
      <p>Loading plans...</p>
    </div>

    <div v-else class="container mx-auto px-4 py-12 sm:px-6 lg:px-8">
      <!-- Header Section with Back Button -->
      <div class="mx-auto max-w-4xl">
        <div class="flex items-center mb-8">
          <button
            @click="goBack"
            class="flex items-center text-gray-600 hover:text-gray-900 transition-colors mr-4"
          >
            <Icon icon="mdi:arrow-left" class="h-5 w-5 mr-2" />
            Back
          </button>
        </div>
        
        <div class="text-center">
          <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
            Find the Perfect Plan for Your Business
          </h1>
          <p class="mt-4 text-lg text-gray-600">
            Simple, transparent pricing. No hidden fees. Choose the plan that
            fits your needs.
          </p>
        </div>
      </div>

      <!-- Pricing Cards Section -->
      <div
        v-if="plans.length"
        class="mt-16 grid grid-cols-1 items-start gap-8 md:grid-cols-2 lg:grid-cols-3"
      >
        <!-- Plan Card -->
        <div
          v-for="plan in plans"
          :key="plan.id"
          class="relative flex h-full cursor-pointer flex-col rounded-2xl p-8 transition-all duration-300"
          :class="
            selectedPlan === plan.id
              ? 'border-2 border-primary shadow-lg'
              : 'border border-gray-200 bg-white shadow-sm hover:shadow-md'
          "
          @click="selectPlan(plan.id)"
        >
          <!-- Most Popular Badge -->
          <div v-if="plan.nickname === 'Pro' || plan.name === 'Pro'"
            class="absolute -top-4 left-1/2 -translate-x-1/2 transform rounded-full bg-primary px-4 py-1 text-sm font-semibold text-white"
          >
            Most Popular
          </div>

          <div class="flex-grow">
            <h3
              class="text-2xl font-semibold"
              :class="{ 'text-primary': plan.nickname === 'Pro' || plan.name === 'Pro' }"
            >
              {{ plan.nickname || plan.name }}
            </h3>
            <p class="mt-2 text-gray-500">{{ plan.description }}</p>

            <!-- Price -->
            <p class="mt-6 text-4xl font-bold text-gray-900">
              ${{ plan.display_amount }}
              <span class="text-base font-medium text-gray-500">/{{ plan.display_interval }}</span>
            </p>

            <!-- Features -->
            <ul class="mt-8 space-y-4 text-sm text-gray-600">
              <li class="flex items-center">
                <Icon icon="mdi:check-circle" class="mr-2 h-5 w-5 shrink-0 text-green-500" />
                <span>{{ plan.description || 'Full access to all features' }}</span>
              </li>
              <li class="flex items-center">
                <Icon icon="mdi:check-circle" class="mr-2 h-5 w-5 shrink-0 text-green-500" />
                <span v-if="plan.display_interval === '3 months'">Billed every 3 months</span>
                <span v-else-if="plan.recurring?.interval === 'year'">Billed annually (save 20%)</span>
                <span v-else>Billed monthly</span>
              </li>
              <li class="flex items-center">
                <Icon icon="mdi:check-circle" class="mr-2 h-5 w-5 shrink-0 text-green-500" />
                <span>Cancel anytime</span>
              </li>
            </ul>
          </div>
          <button
            :class="selectedPlan === plan.id ? 'btn-primary' : 'btn-outlined'"
            class="mt-8 w-full"
            @click="selectPlanAndShowPayment(plan.id)"
          >
            Choose Plan
          </button>
        </div>
      </div>
      <div v-else class="text-center mt-16">
        <p class="text-gray-600">No subscription plans found.</p>
      </div>

      <!-- Payment Form Section (shown when plan is selected) -->
      <div v-if="selectedPlanDetails" class="mt-16 max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
          <!-- Header -->
          <div class="border-b border-gray-200 px-8 py-6">
            <div class="text-center">
              <h2 class="text-2xl font-bold text-gray-900">Complete Your Subscription</h2>
              <p class="text-gray-600 mt-1">Secure payment powered by Stripe</p>
            </div>
          </div>

          <!-- Selected Plan Summary -->
          <div class="px-8 py-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-semibold text-lg text-gray-900">{{ selectedPlanDetails?.nickname || selectedPlanDetails?.name }}</h3>
                <p class="text-gray-600">{{ selectedPlanDetails?.description }}</p>
              </div>
              <div class="text-right">
                <p class="text-3xl font-bold text-gray-900">${{ selectedPlanDetails?.display_amount }}</p>
                <p class="text-gray-600">/{{ selectedPlanDetails?.display_interval }}</p>
              </div>
            </div>
          </div>

          <!-- Payment Form -->
          <form @submit.prevent="processPayment" class="px-8 py-6">
            <!-- Email -->
            <div class="mb-6">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Icon icon="mdi:email" class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  v-model="paymentForm.email"
                  type="email"
                  required
                  class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="your@email.com"
                />
              </div>
            </div>

            <!-- Card Information -->
            <div class="mb-6">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Card Information</label>
              
              <!-- Card Number -->
              <div class="relative mb-4">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Icon icon="mdi:credit-card" class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  v-model="paymentForm.cardNumber"
                  type="text"
                  required
                  maxlength="19"
                  class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="1234 5678 9012 3456"
                  @input="formatCardNumber"
                />
              </div>

              <!-- Expiry and CVC -->
              <div class="grid grid-cols-2 gap-4">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Icon icon="mdi:calendar" class="h-5 w-5 text-gray-400" />
                  </div>
                  <input
                    v-model="paymentForm.expiry"
                    type="text"
                    required
                    maxlength="5"
                    class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="MM/YY"
                    @input="formatExpiry"
                  />
                </div>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Icon icon="mdi:lock" class="h-5 w-5 text-gray-400" />
                  </div>
                  <input
                    v-model="paymentForm.cvc"
                    type="text"
                    required
                    maxlength="4"
                    class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="CVC"
                  />
                </div>
              </div>
            </div>

            <!-- Cardholder Name -->
            <div class="mb-6">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Cardholder Name</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Icon icon="mdi:account" class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  v-model="paymentForm.cardholderName"
                  type="text"
                  required
                  class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="John Doe"
                />
              </div>
            </div>

            <!-- Security Notice -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
              <div class="flex items-start">
                <Icon icon="mdi:shield-check" class="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" />
                <div class="text-sm text-blue-800">
                  <p class="font-semibold mb-1">Secure Payment</p>
                  <p>Your payment information is encrypted and secure. We never store your card details.</p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4">
              <button
                type="button"
                @click="clearSelection"
                class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
              >
                Back to Plans
              </button>
              <button
                type="submit"
                :disabled="processing"
                class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
              >
                <Icon v-if="processing" icon="mdi:loading" class="animate-spin h-5 w-5 mr-2" />
                {{ processing ? 'Processing...' : `Pay $${selectedPlanDetails?.display_amount}` }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <!-- Payment Modal -->
  <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="border-b border-gray-200 px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Complete Your Subscription</h2>
            <p class="text-gray-600 mt-1">Secure payment powered by Stripe</p>
          </div>
          <button
            @click="closePaymentModal"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <Icon icon="mdi:close" class="h-6 w-6" />
          </button>
        </div>
      </div>

      <!-- Selected Plan Summary -->
      <div class="px-8 py-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-semibold text-lg text-gray-900">{{ selectedPlanDetails?.nickname || selectedPlanDetails?.name }}</h3>
            <p class="text-gray-600">{{ selectedPlanDetails?.description }}</p>
          </div>
          <div class="text-right">
            <p class="text-3xl font-bold text-gray-900">${{ selectedPlanDetails?.display_amount }}</p>
            <p class="text-gray-600">/{{ selectedPlanDetails?.display_interval }}</p>
          </div>
        </div>
      </div>

      <!-- Payment Form -->
      <form @submit.prevent="processPayment" class="px-8 py-6">
        <!-- Email -->
        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Icon icon="mdi:email" class="h-5 w-5 text-gray-400" />
            </div>
            <input
              v-model="paymentForm.email"
              type="email"
              required
              class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              placeholder="your@email.com"
            />
          </div>
        </div>

        <!-- Card Information -->
        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 mb-2">Card Information</label>
          
          <!-- Card Number -->
          <div class="relative mb-4">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Icon icon="mdi:credit-card" class="h-5 w-5 text-gray-400" />
            </div>
            <input
              v-model="paymentForm.cardNumber"
              type="text"
              required
              maxlength="19"
              class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              placeholder="1234 5678 9012 3456"
              @input="formatCardNumber"
            />
          </div>

          <!-- Expiry and CVC -->
          <div class="grid grid-cols-2 gap-4">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Icon icon="mdi:calendar" class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="paymentForm.expiry"
                type="text"
                required
                maxlength="5"
                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                placeholder="MM/YY"
                @input="formatExpiry"
              />
            </div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Icon icon="mdi:lock" class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="paymentForm.cvc"
                type="text"
                required
                maxlength="4"
                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                placeholder="CVC"
              />
            </div>
          </div>
        </div>

        <!-- Cardholder Name -->
        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 mb-2">Cardholder Name</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Icon icon="mdi:account" class="h-5 w-5 text-gray-400" />
            </div>
            <input
              v-model="paymentForm.cardholderName"
              type="text"
              required
              class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              placeholder="John Doe"
            />
          </div>
        </div>

        <!-- Security Notice -->
        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
          <div class="flex items-start">
            <Icon icon="mdi:shield-check" class="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" />
            <div class="text-sm text-blue-800">
              <p class="font-semibold mb-1">Secure Payment</p>
              <p>Your payment information is encrypted and secure. We never store your card details.</p>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4">
          <button
            type="button"
            @click="closePaymentModal"
            class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="processing"
            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
          >
            <Icon v-if="processing" icon="mdi:loading" class="animate-spin h-5 w-5 mr-2" />
            {{ processing ? 'Processing...' : `Pay $${selectedPlanDetails?.display_amount}` }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Icon } from "@iconify/vue";
import axios from 'axios';

const plans = ref([]);
const loading = ref(true);
const selectedPlan = ref(null);
const selectedPlanDetails = ref(null);
const processing = ref(false);

const paymentForm = ref({
  email: '',
  cardNumber: '',
  expiry: '',
  cvc: '',
  cardholderName: ''
});

onMounted(async () => {
  try {
    const response = await axios.get('/api/products');
    if (response.data.status) {
      // Flatten products and their prices into individual plans
      const flattenedPlans = [];
      response.data.data.forEach(product => {
        if (product.prices && product.prices.length > 0) {
          product.prices.forEach(price => {
            // Calculate display price and interval
            let displayAmount = price.unit_amount / 100;
            let displayInterval = price.recurring?.interval || 'month';
            
            // Special handling for the $12 plan - show as $12 for 3 months
            if (price.unit_amount === 1200 && price.recurring?.interval === 'month') {
              displayAmount = 12; // Keep original $12
              displayInterval = '3 months'; // Show as 3 months
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
              product_id: product.id
            });
          });
        }
      });
      
      // Show all plans, sorted by display price
      plans.value = flattenedPlans.sort((a, b) => a.display_amount - b.display_amount);
      
      // Set first plan as default selected
      if (plans.value.length > 0) {
        selectedPlan.value = plans.value[0].id;
      }
    }
  } catch (error) {
    console.error('Failed to fetch products:', error);
  } finally {
    loading.value = false;
  }
});

const selectPlan = (planId) => {
  selectedPlan.value = planId;
  selectedPlanDetails.value = plans.value.find(plan => plan.id === planId);
};

const clearSelection = () => {
  selectedPlan.value = null;
  selectedPlanDetails.value = null;
  // Reset form
  paymentForm.value = {
    email: '',
    cardNumber: '',
    expiry: '',
    cvc: '',
    cardholderName: ''
  };
};

const goBack = () => {
  window.history.back();
};

const formatCardNumber = (event) => {
  let value = event.target.value.replace(/\s/g, '');
  let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
  paymentForm.value.cardNumber = formattedValue;
};

const formatExpiry = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length >= 2) {
    value = value.slice(0, 2) + '/' + value.slice(2, 4);
  }
  paymentForm.value.expiry = value;
};

const processPayment = async () => {
  processing.value = true;
  
  try {
    // Here you would integrate with Stripe or another payment processor
    // For now, we'll simulate the payment process
    
    const paymentData = {
      planId: selectedPlanDetails.value.id,
      email: paymentForm.value.email,
      cardNumber: paymentForm.value.cardNumber.replace(/\s/g, ''),
      expiry: paymentForm.value.expiry,
      cvc: paymentForm.value.cvc,
      cardholderName: paymentForm.value.cardholderName
    };

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    // For demo purposes, we'll just log the payment data
    console.log('Payment processed:', paymentData);
    
    // Show success message
    alert('Payment successful! Welcome to your subscription.');
    
    // Close modal and reset
    closePaymentModal();
    
  } catch (error) {
    console.error('Payment failed:', error);
    alert('Payment failed. Please try again.');
  } finally {
    processing.value = false;
  }
};
</script>