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
                class="cursor-pointer rounded-full border border-gray-300 px-3 py-1 text-sm font-semibold hover:bg-gray-100"
              >
                Edit
              </button>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 items-end">
              <div class="max-w-[180px]">
                <BaseDatePicker
                  label="CHECK-IN"
                  v-model="bookingDates.checkIn"
                />
              </div>
              <div class="max-w-[180px]">
                <BaseDatePicker
                  label="CHECK-OUT"
                  v-model="bookingDates.checkOut"
                />
              </div>
              <div class="flex items-center gap-3 pb-2">
                <Icon
                  icon="hugeicons:guest-house"
                  class="text-3xl text-gray-500 shrink-0"
                />
                <div>
                  <p class="text-xs text-gray-500 uppercase font-bold">
                    Room Type
                  </p>
                  <p class="font-semibold text-sm">
                    {{ bookingInfo?.room?.name || "1 Room" }}
                  </p>
                </div>
              </div>
              <div class="flex items-center gap-3 pb-2">
                <Icon
                  icon="hugeicons:user-03"
                  class="text-3xl text-gray-500 shrink-0"
                />
                <div>
                  <p class="text-xs text-gray-500 uppercase font-bold">
                    Guests
                  </p>
                  <p class="font-semibold text-sm">
                    {{ guestDetails.adults }} Adults,
                    {{ guestDetails.children }} Child
                  </p>
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
                    <input
                      type="number"
                      v-model="guestDetails.adults"
                      class="w-full rounded-md border border-gray-300 p-2.5 sm:text-sm"
                      readonly
                    />
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
                    <input
                      type="number"
                      v-model="guestDetails.children"
                      class="w-full rounded-md border border-gray-300 p-2.5 sm:text-sm"
                      readonly
                    />
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
                <button
                  type="submit"
                  class="w-full btn-primary sm:w-auto uppercase py-3 px-10"
                  :disabled="isSubmitting"
                >
                  {{ isSubmitting ? "Processing..." : "BOOK ROOM" }}
                </button>
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
                  <span>Room Price</span>
                  <span>£ {{ bookingInfo?.room?.price || "0" }}</span>
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
                      Number(bookingInfo?.room?.price || 0) + 200
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
import { userService } from "../../services/userService";
import BaseDatePicker from "../../components/global/BaseDatePicker.vue";

const router = useRouter();
const bookingInfo = ref(null);
const bookingDates = ref({ checkIn: "", checkOut: "" });
const isAgreed = ref(false);
const isSubmitting = ref(false);

const guestDetails = ref({
  fullName: "",
  email: "",
  contactNo: "",
  contactAddress: "",
  adults: 3,
  children: 0,
});

onMounted(() => {
  const data = localStorage.getItem("pending_booking");
  if (data) {
    const parsed = JSON.parse(data);
    bookingInfo.value = parsed;
    bookingDates.value = parsed.dates;
  }
});

const updateGuests = (type, value) => {
  if (type === "adults")
    guestDetails.value.adults = Math.max(1, guestDetails.value.adults + value);
  else if (type === "children")
    guestDetails.value.children = Math.max(
      0,
      guestDetails.value.children + value
    );
};

const handleBooking = async () => {
  if (!isAgreed.value) return;

  isSubmitting.value = true;
  try {
    const payload = {
      property_id: bookingInfo.value?.room?.propertyId, // Adjust based on your API
      resource_type_id: bookingInfo.value?.room?.id,
      check_in: bookingDates.value.checkIn,
      check_out: bookingDates.value.checkOut,
      guest_name: guestDetails.value.fullName,
      guest_email: guestDetails.value.email,
      guest_contact: guestDetails.value.contactNo,
      guest_address: guestDetails.value.contactAddress,
      adults: guestDetails.value.adults,
      children: guestDetails.value.children,
      total_price: Number(bookingInfo.value?.room?.price || 0) + 200,
    };
    console.log("payload --- ", payload);

    const res = await userService.createBooking(payload);

    if (res.data.status) {
      localStorage.removeItem("pending_booking");
      router.push({ name: "my-bookings" });
    }
  } catch (err) {
    console.error("Booking Error:", err);
    alert("Something went wrong with the booking. Please try again.");
  } finally {
    isSubmitting.value = false;
  }
};
</script>
