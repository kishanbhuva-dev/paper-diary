<template>
  <main class="container mx-auto py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
    
    <div v-if="isProcessingFinal" class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-white/90 backdrop-blur-md">
      <Icon icon="svg-spinners:ring-resize" class="text-7xl text-primary" />
      <h2 class="mt-6 text-2xl font-black text-gray-900 font-oswald uppercase tracking-widest">Processing Payment</h2>
      <p class="mt-2 text-gray-500 font-medium">Please do not refresh the page...</p>
    </div>

    <div v-if="loadingInitial" class="flex flex-col items-center justify-center py-32">
      <Icon icon="svg-spinners:90-ring-with-bg" class="text-6xl text-primary" />
      <p class="mt-4 text-gray-500 font-medium animate-pulse">Syncing availability...</p>
    </div>

    <div v-else class="max-w-7xl mx-auto">
      <div class="flex items-center gap-3 mb-8">
        <button @click="showPaymentArea ? handleDeleteBooking() : router.back() " class="p-2 hover:bg-gray-200 rounded-full transition-colors">
          <Icon icon="mdi:arrow-left" class="text-2xl" />
        </button>
        <h2 class="text-3xl font-black tracking-tight text-gray-900 uppercase font-oswald">
          {{ showPaymentArea ? 'Complete Payment' : 'Confirm Booking' }}
        </h2>
      </div>
      
      <div class="grid gap-10 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
          
          <template v-if="!showPaymentArea">
            <section ref="stayDetailsSection" class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
              <div class="mb-8 flex items-center justify-between border-b border-gray-100 pb-6">
                <div>
                  <h3 class="text-xl font-bold text-gray-800">1. Stay Details</h3>
                  <p class="text-sm text-gray-500">Review or modify your booking period</p>
                </div>
                <button
                  type="button"
                  class="flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-bold transition-all shadow-sm active:scale-95"
                  :class="isEditing ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-gray-900 text-white hover:bg-black'"
                  @click="handleEditToggle"
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
                    <span class="font-bold">{{ formatDateDisplay(bookingDates.checkIn) }}</span>
                  </div>
                  <BaseDatePicker v-else v-model="bookingDates.checkIn" />
                </div>

                <div class="relative p-5 rounded-2xl bg-gray-50 border border-gray-100">
                  <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2">Check-Out</span>
                  <div v-if="!isEditing" class="flex items-center gap-3 text-gray-800">
                    <Icon icon="mdi:calendar-export" class="text-primary text-xl" />
                    <span class="font-bold">{{ formatDateDisplay(bookingDates.checkOut) }}</span>
                  </div>
                  <BaseDatePicker v-else v-model="bookingDates.checkOut" />
                </div>

                <div v-if="isDateValid" class="relative p-5 rounded-2xl bg-gray-50 border border-gray-100 md:col-span-1">
                  <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2">Resource Type</span>
                  <div v-if="!isEditing" class="flex items-center gap-3 text-gray-800">
                    <Icon icon="hugeicons:villa" class="text-primary text-xl" />
                    <span class="font-bold truncate">{{ bookingInfo?.resourcetype?.name }}</span>
                  </div>
                  <select v-else v-model="selectedResourceId" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm font-bold outline-none">
                    <option v-for="res in availableResources" :key="res.id" :value="res.id">{{ res.name }}</option>
                  </select>
                </div>
                <div v-else class="relative p-5 rounded-2xl bg-red-50 border border-red-100 md:col-span-1 flex items-center justify-center">
                  <span class="text-xs font-bold text-red-600">Select valid future dates</span>
                </div>
              </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm relative overflow-hidden">
              <h3 class="mb-8 text-xl font-bold text-gray-800 border-b border-gray-100 pb-6">2. Guest Information</h3>
              
              <form class="space-y-8" @submit.prevent="initiateBooking">
                <div class="grid gap-x-8 gap-y-6 sm:grid-cols-2">
                  <div v-for="(field, key) in formSchema" :key="key" class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700">{{ field.label }} <span class="text-red-500">*</span></label>
                    <input 
                      v-model="guestDetails[key]" 
                      :type="field.type" 
                      class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm transition-all focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none placeholder:text-gray-400"
                      :placeholder="field.placeholder"
                      :class="{'border-red-500 bg-red-50': errors[key]}"
                    />
                    <p v-if="errors[key]" class="text-[11px] font-bold text-red-500">{{ errors[key] }}</p>
                  </div>

                  <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700">Additional Information / Requests</label>
                    <textarea 
                      v-model="guestDetails.additionalInfo"
                      rows="3"
                      class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm transition-all focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none resize-none"
                      placeholder="e.g. Early check-in requests..."
                    ></textarea>
                  </div>

                  <div class="flex gap-12 items-center sm:col-span-2 pt-4">
                    <div class="space-y-3">
                      <label class="block text-sm font-bold text-gray-700">Adults</label>
                      <div class="inline-flex items-center gap-4 rounded-xl border border-gray-200 p-1.5 bg-gray-50">
                        <button type="button" class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-gray-200 hover:bg-gray-100 text-primary transition-all" @click="updateGuests('adults', -1)"><Icon icon="mdi:minus" /></button>
                        <span class="w-6 text-center font-bold text-lg">{{ guestDetails.adults }}</span>
                        <button type="button" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary text-white hover:bg-blue-700 transition-all" @click="updateGuests('adults', 1)"><Icon icon="mdi:plus" /></button>
                      </div>
                    </div>
                    <div class="space-y-3">
                      <label class="block text-sm font-bold text-gray-700">Children</label>
                      <div class="inline-flex items-center gap-4 rounded-xl border border-gray-200 p-1.5 bg-gray-50">
                        <button type="button" class="w-10 h-10 flex items-center justify-center rounded-lg bg-white border border-gray-200 hover:bg-gray-100 text-primary transition-all" @click="updateGuests('children', -1)"><Icon icon="mdi:minus" /></button>
                        <span class="w-6 text-center font-bold text-lg">{{ guestDetails.children }}</span>
                        <button type="button" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary text-white hover:bg-blue-700 transition-all" @click="updateGuests('children', 1)"><Icon icon="mdi:plus" /></button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="pt-6 border-t border-gray-100">
                  <div class="flex items-center gap-3 mb-8">
                    <input id="agreement" v-model="isAgreed" type="checkbox" class="h-6 w-6 rounded-lg border-gray-300 text-primary cursor-pointer transition-all focus:ring-primary" required />
                    <label for="agreement" class="text-sm text-gray-600 font-medium cursor-pointer">I agree to the <span class="text-primary underline font-bold">Booking Terms & Cancellation Policy</span></label>
                  </div>
                  
                  <div class="flex flex-col sm:flex-row gap-4">
                    <button
                      type="submit"
                      :disabled="isSubmitting || isEditing || !isAgreed || !isDateValid"
                      class="flex-[2] rounded-2xl bg-primary py-5 px-8 text-lg font-black text-white shadow-xl hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-all uppercase tracking-widest font-oswald flex items-center justify-center gap-4"
                    >
                      <Icon v-if="isSubmitting" icon="svg-spinners:ring-resize" />
                      <span>Confirm & Proceed to Payment</span>
                    </button>
                    <button type="button" class="flex-1 rounded-2xl border-2 border-gray-200 bg-white py-5 px-8 text-sm font-black text-gray-400 hover:bg-gray-50 transition-all uppercase tracking-widest font-oswald" @click="cancelAndExit">Cancel</button>
                  </div>
                </div>
              </form>
            </section>
          </template>

          <div v-else class="animate-in slide-in-from-bottom duration-500">
            <section class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm relative overflow-hidden">
              <div class="flex items-center gap-3 mb-8 border-b border-gray-100 pb-6">
                <div class="bg-primary text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">3</div>
                <h3 class="text-xl font-black text-gray-900 font-oswald uppercase tracking-widest">Payment Details</h3>
              </div>

              <div class="bg-gray-50 border-2 border-gray-200 rounded-2xl p-6">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Secure Payment</label>
                <div class="bg-white border border-gray-300 rounded-xl p-4 shadow-sm focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                  <div id="payment-element"></div>
                </div>
                <p id="card-errors" class="mt-3 text-xs font-bold text-red-500" role="alert"></p>
                
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                  <button 
                    :disabled="isProcessingFinal"
                    class="flex-[2] rounded-xl bg-green-600 py-5 px-8 text-lg font-black text-white shadow-lg hover:bg-green-700 disabled:bg-gray-300 transition-all uppercase tracking-widest font-oswald flex items-center justify-center gap-3"
                    @click="processFinalPayment"
                  >
                    <Icon v-if="isProcessingFinal" icon="svg-spinners:ring-resize" />
                    {{ isProcessingFinal ? 'Processing...' : `Pay £${totalPrice.toLocaleString()} Now` }}
                  </button>
                  <button class="flex-1 text-sm font-bold text-gray-400 hover:text-gray-600" @click="showPaymentArea = false">Go Back</button>
                </div>
                
                <div class="mt-6 flex items-center justify-center gap-4 opacity-50">
                  <Icon icon="logos:stripe" class="h-6" />
                  <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Secure 256-bit SSL Encrypted</span>
                </div>
              </div>
            </section>
          </div>
        </div>

        <aside class="lg:col-span-1">
          <div class="sticky top-8 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl">
            <div class="relative h-64 overflow-hidden">
              <img :src="bookingInfo?.property?.image || '/hotel-1.jpg'" class="h-full w-full object-cover" />
              <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent p-8 flex flex-col justify-end">
                <h4 class="text-2xl font-black text-white font-oswald tracking-wide mb-2">{{ bookingInfo?.property?.name }}</h4>
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
            </div>
          </div>
        </aside>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, watch, onBeforeUnmount } from "vue";
import { useRouter, useRoute } from "vue-router";
import { Icon } from "@iconify/vue";
import { loadStripe } from "@stripe/stripe-js";
import { userService } from "../../services/userService";
import { useAuth } from "../../composables/useAuth";
import BaseDatePicker from "../../components/global/BaseDatePicker.vue";
import dayjs from "dayjs";
import { toast } from "vue-sonner";

const router = useRouter();
const route = useRoute();
const { user } = useAuth();

const formSchema = {
  fullName: { label: "Full Name", placeholder: "e.g. John Doe", type: "text" },
  email: { label: "Email Address", placeholder: "user@example.com", type: "email" },
  contactNo: { label: "Contact Number", placeholder: "+44 ...", type: "text" },
  contactAddress: { label: "Home Address", placeholder: "Street, City, Postcode", type: "text" },
};

// --- State ---
const loadingInitial = ref(true);
const isSubmitting = ref(false);
const isProcessingFinal = ref(false);
const showPaymentArea = ref(false);
const isEditing = ref(false);
const isAgreed = ref(false);
const errors = ref({});

const availableResources = ref([]);
const selectedResourceId = ref(null);
const bookingInfo = ref(null);
const bookingDates = ref({ checkIn: "", checkOut: "" });
const stayDetailsSection = ref(null);

const guestDetails = ref({
  fullName: "",
  email: "",
  contactNo: "", 
  contactAddress: "", 
  additionalInfo: "",
  adults: 1, 
  children: 0,
});
const bookingToken = ref(null);

const stripe = ref(null);
const elements = ref(null);
const paymentElement = ref(null);
const clientSecret = ref(null);
const pendingBookingId = ref(null);

// --- New Requirements Logic ---

const isDateValid = computed(() => {
  const checkIn = dayjs(bookingDates.value.checkIn);
  const checkOut = dayjs(bookingDates.value.checkOut);
  const today = dayjs().startOf('day');

  return (
    checkIn.isValid() && 
    checkOut.isValid() && 
    !checkIn.isBefore(today) && 
    checkOut.isAfter(checkIn)
  );
});

const formatDateDisplay = (date) => {
  if (!date) return "";
  return dayjs(date).format("DD/MM/YYYY");
};

const handleOutsideClick = (event) => {
  if (isEditing.value && stayDetailsSection.value && !stayDetailsSection.value.contains(event.target)) {
    handleEditToggle();
  }
};
const handleDeleteBooking = async () => {
  try {
    
    isProcessingFinal.value = true;
    const res = await userService.deleteBooking(pendingBookingId.value);
    if (res.data.status) {
      router.push({ name: "details", params: { slug: route.query.slug } });
    } else {
      throw new Error(res.data.message || "Failed to delete booking.");
    }
  } catch (err) {
    toast.error(err.message || "Error deleting booking.");
  } finally {
    isProcessingFinal.value = false;
  }
};

const totalNights = computed(() => {
  if (!bookingDates.value.checkIn || !bookingDates.value.checkOut) {return 1;}
  const start = dayjs(bookingDates.value.checkIn);
  const end = dayjs(bookingDates.value.checkOut);
  const diff = end.diff(start, 'day');
  return diff > 0 ? diff : 1;
});

const totalPrice = computed(() => {
  const base = Number(bookingInfo.value?.resourcetype?.price || 0);
  return base * totalNights.value;
});

watch(() => bookingDates.value, () => {
  if (isEditing.value) {
    if (!isDateValid.value) {
      toast.error("Please select valid future dates.");
    } else {
      refreshAvailability(route.query.slug);
    }
  }
}, { deep: true });

const refreshAvailability = async (slug) => {
  try {
    const res = await userService.getAvailableResourcesTypes({
      slug, 
      arrivalDateTime: bookingDates.value.checkIn, 
      departureDateTime: bookingDates.value.checkOut, 
      totalResources: 1,
    });

    if (!res.data.status || !res.data.data || res.data.data.length === 0) {
      toast.warning("No resources available for these dates. Redirecting...");
      setTimeout(() => {
        router.push({ name: "details", params: { slug: route.query.slug } });
      }, 2000);
      return;
    }

    availableResources.value = res.data.data;
    const match = res.data.data.find(r => r.id.toString() === selectedResourceId.value?.toString()) || res.data.data[0];
    
    if (match) {
      selectedResourceId.value = match.id;
      const propRes = await userService.getPropertyDetails(slug);
      bookingInfo.value = {
        property: { 
          id: propRes.data.data.id, 
          name: propRes.data.data.propertyName, 
          address: propRes.data.data.address, 
          image: propRes.data.data.property_image[0]?.image, 
          slug: propRes.data.data.slug 
        },
        resourcetype: match,
      };
    }
  } catch (e) { 
    console.error(e);
  }
};

const handleEditToggle = async () => {
  if (isEditing.value) {
    if (!isDateValid.value) {
        toast.error("Please correct your dates before saving.");
        return; 
    }
    loadingInitial.value = true;
    await refreshAvailability(route.query.slug);
    loadingInitial.value = false;
  }
  isEditing.value = !isEditing.value;
};

onMounted(async () => {
  window.addEventListener('click', handleOutsideClick);
  const storedUser = JSON.parse(localStorage.getItem('user'));
  if (storedUser) {
    guestDetails.value.fullName = `${storedUser.firstName || ''} ${storedUser.lastName || ''}`.trim();
    guestDetails.value.email = storedUser.email || '';
    guestDetails.value.contactNo = storedUser.phone || storedUser.telephone || '';
    guestDetails.value.contactAddress = storedUser.address || '';
  }

  const { slug, r_id, in: cin, out: cout } = route.query;
  if (!slug || !r_id) {return router.push("/");}

  bookingDates.value = { checkIn: cin, checkOut: cout };
  selectedResourceId.value = r_id;

  await refreshAvailability(slug);
  loadingInitial.value = false;
  stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY);
});

onBeforeUnmount(() => {
  window.removeEventListener('click', handleOutsideClick);
});

const validateForm = () => {
  errors.value = {};
  let valid = true;
  Object.keys(formSchema).forEach(key => {
    if (!guestDetails.value[key]) {
      errors.value[key] = `${formSchema[key].label} is required`;
      valid = false;
    }
  });
  return valid;
};

const initiateBooking = async () => {
  if (!validateForm()) {
    toast.error("Please fill in all required fields.");
    return;
  }
  isSubmitting.value = true;
  try {
    const payload = { 
      propertyId: bookingInfo.value.property.id, 
      resourceTypeId: selectedResourceId.value, 
      arrivalDateTime: bookingDates.value.checkIn, 
      departureDateTime: bookingDates.value.checkOut, 
      adults: guestDetails.value.adults, 
      children: guestDetails.value.children, 
      guestFullName: guestDetails.value.fullName, 
      guestEmail: guestDetails.value.email, 
      guestPhone: guestDetails.value.contactNo, 
      guestAddress: guestDetails.value.contactAddress,
      additionalInformation: guestDetails.value.additionalInfo,
      nightsCount: totalNights.value,
      resources: 1,
      status: "pending", 
      paymentStatus: "unpaid", 
      price: Number(bookingInfo.value?.resourcetype?.price || 0), 
      cost: totalPrice.value,
      userId: user.value?.id || 0,
    };
    const bookingRes = await userService.createBooking(payload);
    if (!bookingRes.data.status) throw new Error(bookingRes.data.message); 
    pendingBookingId.value = bookingRes.data?.bookingId;
    bookingToken.value = bookingRes.data?.token;
    clientSecret.value = bookingRes.data?.clientSecret || bookingRes.data?.data?.clientSecret;
    showPaymentArea.value = true;
    await nextTick();
    const options = { clientSecret: clientSecret.value, appearance: { theme: 'stripe' } };
    elements.value = stripe.value.elements(options);
    paymentElement.value = elements.value.create("payment");
    paymentElement.value.mount("#payment-element");
  } catch (err) {
    toast.error(err.message || "Failed to initiate booking.");
  } finally {
    isSubmitting.value = false;
  }
};

const processFinalPayment = async () => {
  if (isProcessingFinal.value) {return;}
  isProcessingFinal.value = true;
  try {
    const { paymentIntent, error } = await stripe.value.confirmPayment({
      elements: elements.value,
      redirect: "if_required",
      confirmParams: {
        return_url: window.location.origin + "/my-bookings",
        payment_method_data: { billing_details: { name: guestDetails.value.fullName, email: guestDetails.value.email } }
      },
    });
    if (error) {
      document.getElementById("card-errors").textContent = error.message;
      toast.error(error.message);
      isProcessingFinal.value = false;
      return;
    }
    if (paymentIntent && paymentIntent.status === "succeeded") {
      const updateRes = await userService.bookingStatusUpdate({
        bookingId: pendingBookingId.value,
        payment_status: "paid",
        payment_intent_id: paymentIntent.id,
        token: bookingToken.value,
      });
      if (updateRes.data.status) {
        toast.success("Payment successful!");
        router.push({ name: "my-bookings" });
      }
    }
  } catch (err) {
    toast.error("Payment finalization failed.");
  } finally {
    isProcessingFinal.value = false;
  }
};

const updateGuests = (t, v) => {
  if (t === 'adults') {guestDetails.value.adults = Math.max(1, guestDetails.value.adults + v);}
  else {guestDetails.value.children = Math.max(0, guestDetails.value.children + v);}
};

const cancelAndExit = () => router.push({ name: "details", params: { slug: route.query.slug } });
</script>

<style scoped>
.font-oswald { font-family: 'Oswald', sans-serif; }
#payment-element { width: 100%; }
</style>