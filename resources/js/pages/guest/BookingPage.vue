<template>
  <main class="container mx-auto py-12">
    <div class="px-4 sm:px-6 lg:px-8">
      <h2 class="mb-8 text-3xl font-bold">Confirm Your Booking</h2>
      <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
          <div class="rounded-lg border p-6 shadow-sm">
            <div class="mb-6 flex-between">
              <h3 class="text-2xl font-semibold">Booking Summary</h3>
              <button
                type="button"
                @click="handleDateEditToggle"
                class="cursor-pointer rounded-full border px-4 py-1 text-sm font-semibold transition-colors"
                :class="isEditingDates ? 'bg-green-600 border-green-600 text-white hover:bg-green-700' : 'border-gray-300 hover:bg-gray-100 text-gray-700'"
              >
                <div class="flex items-center gap-1">
                  <Icon :icon="isEditingDates ? 'mdi:check' : 'mdi:pencil'" />
                  {{ isEditingDates ? 'Save' : 'Edit' }}
                </div>
              </button>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 items-start">
              
              <div class="space-y-2">
                <p class="text-xs text-gray-500 uppercase font-bold">Check-In</p>
                <div v-if="!isEditingDates" class="flex items-center h-[42px] px-3 bg-gray-50 rounded border border-gray-200 text-sm font-medium">
                  {{ bookingDates.checkIn }}
                </div>
                <BaseDatePicker
                  v-else
                  label="" 
                  v-model="bookingDates.checkIn"
                />
              </div>

              <div class="space-y-2">
                <p class="text-xs text-gray-500 uppercase font-bold">Check-Out</p>
                <div v-if="!isEditingDates" class="flex items-center h-[42px] px-3 bg-gray-50 rounded border border-gray-200 text-sm font-medium">
                  {{ bookingDates.checkOut }}
                </div>
                <BaseDatePicker
                  v-else
                  label=""
                  v-model="bookingDates.checkOut"
                />
              </div>

              <div class="space-y-2">
                <p class="text-xs text-gray-500 uppercase font-bold">Resource Type</p>
                <div class="flex items-center gap-2 h-[42px]">
                  <Icon icon="hugeicons:guest-house" class="text-2xl text-gray-400" />
                  <span class="font-semibold text-sm truncate">
                    {{ bookingInfo?.resourcetype?.name || "1 Resource" }}
                  </span>
                </div>
              </div>

              <div class="space-y-2">
                <p class="text-xs text-gray-500 uppercase font-bold">Guests</p>
                <div class="flex items-center gap-2 h-[42px]">
                  <Icon icon="hugeicons:user-03" class="text-2xl text-gray-400" />
                  <span class="font-semibold text-sm">
                    {{ guestDetails.adults }} Adults, {{ guestDetails.children }} Child
                  </span>
                </div>
              </div>

            </div>
          </div>

          <div class="rounded-lg border p-6 shadow-sm">
            <h3 class="mb-6 text-2xl font-semibold">Guests Details</h3>
            <form class="space-y-4" @submit.prevent="handleBooking">
              <div class="grid gap-6 sm:grid-cols-2">
                <div>
                  <label class="mb-1 block text-sm font-medium text-gray-700"
                    >Full name <span class="text-red-500">*</span></label
                  >
                  <input
                    type="text"
                    v-model="guestDetails.fullName"
                    class="w-full rounded-md border border-gray-300 p-2.5 sm:text-sm"
                    placeholder="Enter name"
                    required
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-gray-700"
                    >Email Address <span class="text-red-500">*</span></label
                  >
                  <input
                    type="email"
                    v-model="guestDetails.email"
                    class="w-full rounded-md border border-gray-300 p-2.5 sm:text-sm"
                    placeholder="Email Address"
                    required
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-gray-700"
                    >Contact No <span class="text-red-500">*</span></label
                  >
                  <input
                    type="text"
                    v-model="guestDetails.contactNo"
                    class="w-full rounded-md border border-gray-300 p-2.5 sm:text-sm"
                    placeholder="Contact no"
                    required
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-gray-700"
                    >Contact Address <span class="text-red-500">*</span></label
                  >
                  <input
                    type="text"
                    v-model="guestDetails.contactAddress"
                    class="w-full rounded-md border border-gray-300 p-2.5 sm:text-sm"
                    placeholder="Contact Address"
                    required
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-gray-700"
                    >Adult <span class="text-red-500">*</span></label
                  >
                  <div class="flex items-center gap-3">
                    <div
                      class="flex items-center gap-3 rounded-full border border-gray-300 p-1"
                    >
                      <button
                        type="button"
                        @click="updateGuests('adults', -1)"
                        class="bg-primary p-1.5 rounded-full text-white"
                      >
                        <Icon icon="mdi:minus" />
                      </button>
                      <span class="font-semibold w-4 text-center">{{
                        guestDetails.adults
                      }}</span>
                      <button
                        type="button"
                        @click="updateGuests('adults', 1)"
                        class="bg-primary p-1.5 rounded-full text-white"
                      >
                        <Icon icon="mdi:plus" />
                      </button>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-gray-700"
                    >Children <span class="text-red-500">*</span></label
                  >
                  <div class="flex items-center gap-3">
                    <div
                      class="flex items-center gap-3 rounded-full border border-gray-300 p-1"
                    >
                      <button
                        type="button"
                        @click="updateGuests('children', -1)"
                        class="bg-primary p-1.5 rounded-full text-white"
                      >
                        <Icon icon="mdi:minus" />
                      </button>
                      <span class="font-semibold w-4 text-center">{{
                        guestDetails.children
                      }}</span>
                      <button
                        type="button"
                        @click="updateGuests('children', 1)"
                        class="bg-primary p-1.5 rounded-full text-white"
                      >
                        <Icon icon="mdi:plus" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div
                class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200"
              >
                <h4 class="mb-4 text-sm font-bold text-gray-700 uppercase">
                  Secure Payment
                </h4>
                <div
                  id="card-element"
                  class="p-3 bg-white border rounded-md shadow-sm"
                ></div>
                <p
                  id="card-errors"
                  role="alert"
                  class="mt-2 text-xs text-red-500"
                ></p>
              </div>

              <div class="pt-6">
                <div class="flex items-center mb-4">
                  <input
                    id="agreement"
                    type="checkbox"
                    v-model="isAgreed"
                    class="h-4 w-4 rounded border-gray-300 text-primary"
                    required
                  />
                  <label
                    for="agreement"
                    class="ml-2 block text-sm text-gray-900"
                    >By proceeding, I agree to Privacy Policy & Terms of
                    Service</label
                  >
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                  <button
                    type="submit"
                    class="w-full btn-primary sm:w-auto uppercase py-3 px-10"
                    :disabled="isSubmitting"
                  >
                    {{
                      isSubmitting
                        ? "Processing Payment..."
                        : "PAY & BOOK RESOURCE"
                    }}
                  </button>
                  <button
                    type="button"
                    @click="cancelAndExit"
                    class="w-full sm:w-auto border border-gray-300 rounded-md py-3 px-10 text-sm font-bold uppercase hover:bg-gray-50 transition-colors"
                    :disabled="isSubmitting"
                  >
                    Cancel & Exit
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="lg:col-span-1">
          <div class="overflow-hidden rounded-lg border shadow-sm sticky top-8">
            <div class="p-6">
              <h3 class="mb-4 text-2xl font-semibold">Property details</h3>
              <img
                :src="bookingInfo?.property?.image || '/public/hotel-1.jpg'"
                alt="Property"
                class="aspect-video w-full rounded-lg object-cover"
              />
              <h4 class="mt-4 text-xl font-bold">
                {{ bookingInfo?.property?.name || "Loading..." }}
              </h4>
              <div class="mt-1 flex items-center text-gray-500">
                <Icon icon="mdi:location" />
                <span class="ml-1 text-sm">{{
                  bookingInfo?.property?.address || "Location Details"
                }}</span>
              </div>
            </div>
            <div class="border-t p-6">
              <h4 class="mb-4 text-lg font-semibold">Price Details</h4>
              <div class="space-y-2">
                <div class="flex-between text-sm">
                  <span>Resource Price</span>
                  <span>£ {{ bookingInfo?.resourcetype?.price || "0" }}</span>
                </div>
                <div class="flex-between text-sm">
                  <span>Taxes (10%)</span><span>+ £200</span>
                </div>
              </div>
            </div>
            <div class="border-t bg-gray-50 p-6">
              <div class="flex-between text-lg font-bold">
                <span>Total</span>
                <span
                  >£
                  {{
                    (
                      Number(bookingInfo?.resourcetype?.price || 0) + 200
                    ).toLocaleString()
                  }}</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { Icon } from "@iconify/vue";
import { loadStripe } from "@stripe/stripe-js";
import { userService } from "../../services/userService";
import BaseDatePicker from "../../components/global/BaseDatePicker.vue";

const router = useRouter();
const bookingInfo = ref(null);
const bookingDates = ref({ checkIn: "", checkOut: "" });
const isAgreed = ref(false);
const isSubmitting = ref(false);
const isEditingDates = ref(false);

const guestDetails = ref({
  fullName: "",
  email: "",
  contactNo: "",
  contactAddress: "",
  adults: 1,
  children: 0,
});

const stripe = ref(null);
const elements = ref(null);
const card = ref(null);
const isCardComplete = ref(false);

onMounted(async () => {
  const publishableKey = "pk_test_51RvEjJ7fYIrC7aOkBuyFRaQM8EH4P3nCf8sW5BEFVufQaLOlM2ZNk8lRDNh7uCtm6sVV2Wa2dhIVbIwI6P2q2xQx00PpDGeuDB";

  stripe.value = await loadStripe(publishableKey);
  elements.value = stripe.value.elements();

  card.value = elements.value.create("card", {
    style: {
      base: {
        fontSize: "16px",
        color: "#32325d",
        fontFamily: "sans-serif",
      },
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

  const qdata = router.currentRoute.value.query;

  if (qdata.slug && qdata.r_id && qdata.in && qdata.out) {
    bookingDates.value = {
      checkIn: qdata.in,
      checkOut: qdata.out,
    };
    await fetchInitialData(qdata.slug, qdata.r_id);
  } else {
    router.push("/");
  }
});

const fetchInitialData = async (slug, r_id) => {
    try {
      const res = await userService.getAvailableResourcesTypes({
        slug: slug,
        arrivalDateTime: bookingDates.value.checkIn,
        departureDateTime: bookingDates.value.checkOut,
        totalResources: 1,
      });
      if (res.data.status) {
        const selectresourceType = res.data.data.find(
          (r) => r.id.toString() === r_id.toString()
        );

        if (selectresourceType) {
          const propRes = await userService.getPropertyDetails(slug);

          bookingInfo.value = {
            property: {
              id: propRes.data.data.id,
              name: propRes.data.data.propertyName,
              address: propRes.data.data.address,
              image: propRes.data.data.property_image[0]?.image,
              slug: propRes.data.data.slug,
            },
            resourcetype: selectresourceType,
          };
        } else {
          router.push({ name: "details", params: { slug: slug } });
        }
      }
    } catch (error) {
      console.error("Initialization error:", error);
    }
}

const handleDateEditToggle = () => {
  if (isEditingDates.value) {
    const q = router.currentRoute.value.query;
    fetchInitialData(q.slug, q.r_id); 
    isEditingDates.value = false;
  } else {
    isEditingDates.value = true;
  }
};

const updateGuests = (type, value) => {
  if (type === "adults")
    guestDetails.value.adults = Math.max(1, guestDetails.value.adults + value);
  else if (type === "children")
    guestDetails.value.children = Math.max(0, guestDetails.value.children + value);
};

const cancelAndExit = () => {
  const propertySlug = bookingInfo.value?.property?.slug;
  if (propertySlug) {
    router.push({ name: "details", params: { slug: propertySlug } });
  } else {
    router.push("/");
  }
};

const handleBooking = async () => {
  if (!isCardComplete.value) {
    const cardErrors = document.getElementById("card-errors");
    cardErrors.textContent = "Please enter your card details to proceed.";
    return;
  }
  isSubmitting.value = true;
  const cardErrors = document.getElementById("card-errors");
  cardErrors.textContent = "";

  try {
    const totalAmount = Number(bookingInfo.value?.resourcetype?.price || 0) + 200;
    const intentRes = await userService.createPaymentIntent({
      amount: totalAmount,
      currency: "gbp",
    });

    const clientSecret = intentRes.data.clientSecret || intentRes.data.data?.clientSecret;

    if (!clientSecret) throw new Error("Invalid response from payment server.");

    const { paymentIntent, error } = await stripe.value.confirmCardPayment(
      clientSecret,
      {
        payment_method: {
          card: card.value,
          billing_details: {
            name: guestDetails.value.fullName,
            email: guestDetails.value.email,
          },
        },
      }
    );

    if (error) {
      cardErrors.textContent = error.message;
      isSubmitting.value = false;
      return;
    }

    if (paymentIntent.status === "succeeded") {
      const resourcetypePrice = Number(bookingInfo.value?.resourcetype?.price || 0);

      const payload = {
        propertyId: bookingInfo.value?.property?.id,
        resourceTypeId: bookingInfo.value?.resourcetype?.id,
        arrivalDateTime: bookingDates.value.checkIn,
        departureDateTime: bookingDates.value.checkOut,
        adults: guestDetails.value.adults,
        children: guestDetails.value.children,
        resources: 1,
        guestFullName: guestDetails.value.fullName,
        guestEmail: guestDetails.value.email,
        guestPhone: guestDetails.value.contactNo,
        guestAddress: guestDetails.value.contactAddress,
        status: "confirm",
        paymentStatus: "paid",
        price: resourcetypePrice,
        cost: resourcetypePrice,
        userId: 5,
        payment_intent_id: paymentIntent.id,
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
    console.error("Payment Error:", err);
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.flex-between {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>