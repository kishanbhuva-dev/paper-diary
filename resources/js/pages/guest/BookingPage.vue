<template>
  <main class="container mx-auto py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
    <div v-if="loadingInitial" class="flex flex-col items-center justify-center py-32">
      <Icon icon="svg-spinners:90-ring-with-bg" class="text-6xl text-primary" />
      <p class="mt-4 text-gray-500 font-medium animate-pulse">Syncing availability...</p>
    </div>

    <div v-else class="max-w-7xl mx-auto">
      <div class="flex items-center gap-3 mb-8">
        <button @click="router.back()" class="p-2 hover:bg-gray-200 rounded-full transition-colors">
          <Icon icon="mdi:arrow-left" class="text-2xl" />
        </button>
        <h2 class="text-3xl font-black tracking-tight text-gray-900 uppercase font-oswald">Confirm Booking</h2>
      </div>
      
      <div class="grid gap-10 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
          
          <section class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <div class="mb-8 flex items-center justify-between border-b border-gray-100 pb-6">
              <div>
                <h3 class="text-xl font-bold text-gray-800">1. Stay Details</h3>
                <p class="text-sm text-gray-500">Review or modify your booking period</p>
              </div>
              <button
                @click="handleEditToggle"
                type="button"
                class="flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-bold transition-all shadow-sm active:scale-95"
                :class="isEditing ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-gray-900 text-white hover:bg-black'"
              >
                <Icon :icon="isEditing ? 'mdi:check-circle' : 'mdi:calendar-edit'" />
                {{ isEditing ? "Save Selection" : "Edit Selection" }}
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="relative p-5 rounded-2xl bg-gray-50 border border-gray-100">
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2">Check-In</span>
                <div v-if="!isEditing" class="flex items-center gap-3 text-gray-800">
                  <Icon icon="mdi:calendar-import" class="text-primary text-xl" />
                  <span class="font-bold">{{ bookingDates.checkIn }}</span>
                </div>
                <BaseDatePicker v-else v-model="bookingDates.checkIn" />
              </div>

              <div class="relative p-5 rounded-2xl bg-gray-50 border border-gray-100">
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2">Check-Out</span>
                <div v-if="!isEditing" class="flex items-center gap-3 text-gray-800">
                  <Icon icon="mdi:calendar-export" class="text-primary text-xl" />
                  <span class="font-bold">{{ bookingDates.checkOut }}</span>
                </div>
                <BaseDatePicker v-else v-model="bookingDates.checkOut" />
              </div>

              <div class="relative p-5 rounded-2xl bg-gray-50 border border-gray-100 md:col-span-1">
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2">Selected Resource Type</span>
                <div v-if="!isEditing" class="flex items-center gap-3 text-gray-800">
                  <Icon icon="hugeicons:villa" class="text-primary text-xl" />
                  <span class="font-bold truncate">{{ bookingInfo?.resourcetype?.name }}</span>
                </div>
                <select v-else v-model="selectedResourceId" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                  <option v-for="res in availableResources" :key="res.id" :value="res.id">{{ res.name }}</option>
                </select>
              </div>
            </div>
          </section>

          <section class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <h3 class="mb-8 text-xl font-bold text-gray-800 border-b border-gray-100 pb-6">2. Guest Information</h3>
            <form @submit.prevent="handleBooking" class="space-y-8">
              <div class="grid gap-x-8 gap-y-6 sm:grid-cols-2">
                <div v-for="(field, key) in formSchema" :key="key" class="space-y-1.5">
                  <label class="block text-sm font-bold text-gray-700">{{ field.label }} <span class="text-red-500">*</span></label>
                  <input 
                    :type="field.type" 
                    v-model="guestDetails[key]" 
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm transition-all focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none placeholder:text-gray-400"
                    :placeholder="field.placeholder"
                    :class="{'border-red-500 bg-red-50': errors[key]}"
                  />
                  <p v-if="errors[key]" class="text-[11px] font-bold text-red-500 animate-pulse">{{ errors[key] }}</p>
                </div>

                <div class="flex gap-12 items-center sm:col-span-2 pt-4">
                  <div class="space-y-3">
                    <label class="block text-sm font-bold text-gray-700">Adults</label>
                    <div class="inline-flex items-center gap-4 rounded-xl border border-gray-200 p-1.5 bg-gray-50">
                      <button type="button" @click="updateGuests('adults', -1)" class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-gray-200 shadow-sm hover:bg-gray-100 text-primary transition-all"><Icon icon="mdi:minus" /></button>
                      <span class="w-6 text-center font-bold text-lg">{{ guestDetails.adults }}</span>
                      <button type="button" @click="updateGuests('adults', 1)" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary text-white shadow-md hover:bg-blue-700 transition-all"><Icon icon="mdi:plus" /></button>
                    </div>
                  </div>

                  <div class="space-y-3">
                    <label class="block text-sm font-bold text-gray-700">Children</label>
                    <div class="inline-flex items-center gap-4 rounded-xl border border-gray-200 p-1.5 bg-gray-50">
                      <button type="button" @click="updateGuests('children', -1)" class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-gray-200 shadow-sm hover:bg-gray-100 text-primary transition-all"><Icon icon="mdi:minus" /></button>
                      <span class="w-6 text-center font-bold text-lg">{{ guestDetails.children }}</span>
                      <button type="button" @click="updateGuests('children', 1)" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary text-white shadow-md hover:bg-blue-700 transition-all"><Icon icon="mdi:plus" /></button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-10 rounded-2xl bg-gray-900 p-8 text-white">
                <div class="flex items-center gap-3 mb-6">
                  <div class="p-2 bg-green-500/20 rounded-lg">
                    <Icon icon="mdi:shield-check" class="text-green-400 text-xl" />
                  </div>
                  <div>
                    <h4 class="text-sm font-bold uppercase tracking-widest">Secure Payment</h4>
                    <p class="text-[11px] text-gray-400">Encrypted via Stripe Gateway</p>
                  </div>
                </div>
                <div id="card-element" class="rounded-xl bg-white p-4 shadow-inner"></div>
                <p id="card-errors" class="mt-4 text-xs font-bold text-red-400"></p>
              </div>

              <div class="pt-6 border-t border-gray-100">
                <div class="flex items-center gap-3 mb-8">
                  <input id="agreement" type="checkbox" v-model="isAgreed" class="h-6 w-6 rounded-lg border-gray-300 text-primary cursor-pointer transition-all focus:ring-primary" required />
                  <label for="agreement" class="text-sm text-gray-600 font-medium cursor-pointer">I agree to the <span class="text-primary underline">Booking Terms & Cancellation Policy</span></label>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4">
                  <button
                    type="submit"
                    :disabled="isSubmitting || isEditing || !isAgreed"
                    class="flex-[2] rounded-2xl bg-primary py-5 px-8 text-lg font-black text-white shadow-xl hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-all uppercase tracking-widest font-oswald"
                  >
                    <span v-if="isSubmitting" class="flex items-center justify-center gap-3">
                      <Icon icon="svg-spinners:ring-resize" class="text-2xl" /> Processing...
                    </span>
                    <span v-else>Pay & Confirm £{{ totalPrice.toLocaleString() }}</span>
                  </button>
                  <button @click="cancelAndExit" type="button" class="flex-1 rounded-2xl border-2 border-gray-200 bg-white py-5 px-8 text-sm font-black text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all uppercase tracking-widest font-oswald">Cancel</button>
                </div>
              </div>
            </form>
          </section>
        </div>

        <aside class="lg:col-span-1">
          <div class="sticky top-8 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl ring-1 ring-black/5">
            <div class="relative h-64 overflow-hidden">
              <img :src="bookingInfo?.property?.image || '/public/hotel-1.jpg'" class="h-full w-full object-cover transition-transform duration-700 hover:scale-110" />
              <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent p-8 flex flex-col justify-end">
                <h4 class="text-2xl font-black text-white font-oswald tracking-wide leading-none mb-2">{{ bookingInfo?.property?.name }}</h4>
                <div class="flex items-center text-xs text-gray-300 font-bold uppercase tracking-tighter">
                  <Icon icon="mdi:map-marker" class="mr-1 text-primary" /> {{ bookingInfo?.property?.address }}
                </div>
              </div>
            </div>

            <div class="p-8">
              <h5 class="mb-6 text-[11px] font-black uppercase text-gray-400 tracking-[0.2em] border-b border-gray-100 pb-2">Order Breakdown</h5>
              <div class="space-y-5">
                <div class="flex justify-between items-start">
                  <div>
                    <span class="text-sm font-black text-gray-900 block">{{ bookingInfo?.resourcetype?.name }}</span>
                    <p class="text-[11px] font-bold text-gray-500 mt-1 uppercase tracking-wide">£{{ bookingInfo?.resourcetype?.price }} / night × {{ totalNights }} nights</p>
                  </div>
                  <span class="font-black text-gray-900">£{{ (bookingInfo?.resourcetype?.price * totalNights).toLocaleString() }}</span>
                </div>
                
                <div class="flex justify-between items-center border-t-2 border-gray-900 pt-6">
                  <span class="text-lg font-black text-gray-900 font-oswald uppercase tracking-widest">Total</span>
                  <span class="text-4xl font-black text-primary font-oswald tracking-tighter">£{{ totalPrice.toLocaleString() }}</span>
                </div>
              </div>

              <!-- <div class="mt-10 p-4 rounded-xl bg-blue-50 border border-blue-100 flex gap-3">
                <Icon icon="mdi:clock-fast" class="text-blue-600 text-xl flex-shrink-0" />
                <p class="text-[11px] text-blue-800 font-bold leading-relaxed uppercase tracking-tight">
                  Instant Confirmation. A receipt will be sent to your email immediately after payment.
                </p>
              </div> -->
            </div>
          </div>
        </aside>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import { Icon } from "@iconify/vue";
import { loadStripe } from "@stripe/stripe-js";
import { userService } from "../../services/userService";
import { useAuth } from "../../composables/useAuth";
import BaseDatePicker from "../../components/global/BaseDatePicker.vue";
import dayjs from "dayjs";

const router = useRouter();
const route = useRoute();
const { user } = useAuth();

// --- Schema for Validation ---
const formSchema = {
  fullName: { label: "Full Name", placeholder: "e.g. John Doe", type: "text" },
  email: { label: "Email Address", placeholder: "user@example.com", type: "email" },
  contactNo: { label: "Contact Number", placeholder: "+44 ...", type: "text" },
  contactAddress: { label: "Home Address", placeholder: "Street, City, Postcode", type: "text" },
};

// --- State ---
const loadingInitial = ref(true);
const isSubmitting = ref(false);
const isEditing = ref(false);
const isAgreed = ref(false);
const errors = ref({});

const availableResources = ref([]);
const selectedResourceId = ref(null);
const bookingInfo = ref(null);
const bookingDates = ref({ checkIn: "", checkOut: "" });

const guestDetails = ref({
  fullName: user.value?.name || "",
  email: user.value?.email || "",
  contactNo: "", contactAddress: "", adults: 1, children: 0,
});

const stripe = ref(null);
const card = ref(null);
const isCardComplete = ref(false);

// --- Calculations ---
const totalNights = computed(() => {
  if (!bookingDates.value.checkIn || !bookingDates.value.checkOut) return 1;
  const start = dayjs(bookingDates.value.checkIn);
  const end = dayjs(bookingDates.value.checkOut);
  const diff = end.diff(start, 'day');
  return diff > 0 ? diff : 1;
});

const totalPrice = computed(() => {
  const base = Number(bookingInfo.value?.resourcetype?.price || 0);
  return base * totalNights.value;
});

// --- Logic ---
onMounted(async () => {
  const { slug, r_id, in: cin, out: cout } = route.query;
  if (!slug || !r_id) return router.push("/");

  bookingDates.value = { checkIn: cin, checkOut: cout };
  selectedResourceId.value = r_id;

  await refreshAvailability(slug);
  loadingInitial.value = false;

  await nextTick();
  await initStripe();
});

const initStripe = async () => {
  stripe.value = await loadStripe("pk_test_51RvEjJ7fYIrC7aOkBuyFRaQM8EH4P3nCf8sW5BEFVufQaLOlM2ZNk8lRDNh7uCtm6sVV2Wa2dhIVbIwI6P2q2xQx00PpDGeuDB");
  const elements = stripe.value.elements();
  card.value = elements.create("card", { 
    style: { base: { fontSize: "16px", color: "#111827", fontFamily: 'Inter, sans-serif', '::placeholder': { color: '#9ca3af' } } } 
  });
  card.value.mount("#card-element");
  card.value.on("change", (e) => {
    isCardComplete.value = e.complete;
    const errEl = document.getElementById("card-errors");
    if (errEl) errEl.textContent = e.error ? e.error.message : "";
  });
};
watch(
  () => [bookingDates.value.checkIn, bookingDates.value.checkOut],
  async ([newIn, newOut]) => {
    // Only auto-refresh if we are in editing mode and both dates are present
    if (isEditing.value && newIn && newOut) {
      // Small delay or check to ensure checkout isn't before checkin
      if (dayjs(newOut).isAfter(dayjs(newIn))) {
        await refreshAvailability(route.query.slug);
      }
    }
  },
  { deep: true }
);
const refreshAvailability = async (slug) => {
  try {
    const res = await userService.getAvailableResourcesTypes({
      slug, arrivalDateTime: bookingDates.value.checkIn, departureDateTime: bookingDates.value.checkOut, totalResources: 1,
    });
    if (res.data.status) {
      availableResources.value = res.data.data;
      const match = res.data.data.find(r => r.id.toString() === selectedResourceId.value?.toString()) || res.data.data[0];
      if (match) {
        selectedResourceId.value = match.id;
        const propRes = await userService.getPropertyDetails(slug);
        bookingInfo.value = {
          property: { id: propRes.data.data.id, name: propRes.data.data.propertyName, address: propRes.data.data.address, image: propRes.data.data.property_image[0]?.image, slug: propRes.data.data.slug },
          resourcetype: match,
        };
      }
    }
  } catch (e) { console.error(e); }
};

const validateForm = () => {
  errors.value = {};
  let valid = true;

  // Check inputs
  Object.keys(formSchema).forEach(key => {
    if (!guestDetails.value[key]) {
      errors.value[key] = `${formSchema[key].label} is required`;
      valid = false;
    }
  });

  // Check dates
  if (dayjs(bookingDates.value.checkOut).isBefore(dayjs(bookingDates.value.checkIn))) {
    alert("Checkout date cannot be before Check-in date.");
    valid = false;
  }

  return valid;
};

const handleEditToggle = async () => {
  if (isEditing.value) {
    loadingInitial.value = true;
    await refreshAvailability(route.query.slug);
    loadingInitial.value = false;
    await nextTick();
    await initStripe();
  }
  isEditing.value = !isEditing.value;
};

const handleBooking = async () => {
  if (!validateForm()) return;
  if (!isCardComplete.value) return;
  isSubmitting.value = true;

  try {
    // Concurrency Check
    const finalCheck = await userService.getAvailableResourcesTypes({
      slug: bookingInfo.value.property.slug, arrivalDateTime: bookingDates.value.checkIn, departureDateTime: bookingDates.value.checkOut, totalResources: 1,
    });
    // if (!finalCheck.data.data.some(r => r.id === selectedResourceId.value)) {
    //   alert("Resource no longer available for these dates.");
    //   return;
    // }

    const intentRes = await userService.createPaymentIntent({ slug: bookingInfo.value.property.slug, amount: totalPrice.value, currency: "gbp" });
    const clientSecret = intentRes.data.clientSecret || intentRes.data.data?.clientSecret;
    const { paymentIntent, error } = await stripe.value.confirmCardPayment(clientSecret, {
      payment_method: { card: card.value, billing_details: { name: guestDetails.value.fullName, email: guestDetails.value.email } },
    });

    if (error) throw new Error(error.message);

    if (paymentIntent.status === "succeeded") {

      const payload = { 
        propertyId: bookingInfo.value.property.id, resourceTypeId: selectedResourceId.value, arrivalDateTime: bookingDates.value.checkIn, departureDateTime: bookingDates.value.checkOut, 
        adults: guestDetails.value.adults, children: guestDetails.value.children, guestFullName: guestDetails.value.fullName, guestEmail: guestDetails.value.email, guestPhone: guestDetails.value.contactNo, resources: 1,
        guestAddress: guestDetails.value.contactAddress, status: "confirm", paymentStatus: "paid", price: totalPrice.value, cost: totalPrice.value, userId: user.value?.id || 5, payment_intent_id: paymentIntent.id ,
      };
      const res = await userService.createBooking(payload);
      

      if (res.data.status) {
        const newBookingId = res.data.data?.id;
        if (newBookingId) {
          await userService.bookingStatusUpdate({
            bookingId: newBookingId,
            status: "confirm",
            payment_intent_id: paymentIntent.id,
          });
        }
        router.push({ name: "my-bookings" });
      }
    }
  } catch (err) {
    document.getElementById("card-errors").textContent = err.message;
  } finally { isSubmitting.value = false; }
};

const updateGuests = (t, v) => {
  if (t === 'adults') guestDetails.value.adults = Math.max(1, guestDetails.value.adults + v);
  else guestDetails.value.children = Math.max(0, guestDetails.value.children + v);
};

const cancelAndExit = () => router.push({ name: "details", params: { slug: route.query.slug } });
</script>

<style scoped>
/* Focus transitions for inputs */
input {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
#card-element {
  min-height: 44px;
}
</style>