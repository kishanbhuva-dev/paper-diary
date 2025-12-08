<template>
  <main class="container mx-auto py-12">
    <div class="px-4 sm:px-6 lg:px-8">
      <h2 class="mb-8 text-3xl font-bold">Confirm Your Booking</h2>
      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Left side: Booking form -->
        <div class="space-y-8 lg:col-span-2">
          <!-- Booking Summary -->
          <div class="rounded-lg border p-6 shadow-sm">
            <div class="mb-6 flex-between">
              <h3 class="text-2xl font-semibold">Booking Summary</h3>
              <button
                class="cursor-pointer rounded-full border border-gray-300 px-3 py-1 text-sm font-semibold hover:bg-gray-100"
              >
                Edit
              </button>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
              <div class="flex items-center gap-3">
                <Icon
                  icon="hugeicons:calendar-check-in-01"
                  class="text-3xl text-gray-500"
                />
                <div>
                  <p class="text-gray-500">Check in:</p>
                  <p class="font-semibold">19 Oct, 2025</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <Icon
                  icon="hugeicons:calendar-check-out-01"
                  class="text-3xl text-gray-500"
                />
                <div>
                  <p class="text-gray-500">Check out:</p>
                  <p class="font-semibold">22 Oct, 2025</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <Icon
                  icon="hugeicons:guest-house"
                  class="text-3xl text-gray-500"
                />
                <div>
                  <p class="text-gray-500">Room</p>
                  <p class="font-semibold">1 Room</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <Icon icon="hugeicons:user-03" class="text-3xl text-gray-500" />
                <div>
                  <p class="text-gray-500">Guests</p>
                  <p class="font-semibold">3 Adults</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Guest Details -->
          <div class="rounded-lg border p-6 shadow-sm">
            <h3 class="mb-6 text-2xl font-semibold">Guests Details</h3>
            <form class="space-y-4">
              <div class="grid gap-6 sm:grid-cols-2">
                <div>
                  <label
                    for="fullName"
                    class="mb-1 block text-sm font-medium text-gray-700"
                  >
                    Full name <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="fullName"
                    v-model="guestDetails.fullName"
                    class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                    placeholder="Enter name"
                  />
                </div>
                <div>
                  <label
                    for="email"
                    class="mb-1 block text-sm font-medium text-gray-700"
                  >
                    Email Address <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="email"
                    id="email"
                    v-model="guestDetails.email"
                    class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                    placeholder="Email Address"
                  />
                </div>
                <div>
                  <label
                    for="contactNo"
                    class="mb-1 block text-sm font-medium text-gray-700"
                  >
                    Contact No <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="contactNo"
                    v-model="guestDetails.contactNo"
                    class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                    placeholder="Contact no"
                  />
                </div>
                <div>
                  <label
                    for="adults"
                    class="mb-1 block text-sm font-medium text-gray-700"
                  >
                    Adult <span class="text-red-500">*</span>
                  </label>
                  <div class="flex items-center gap-3">
                    <input
                      id="adults"
                      type="number"
                      v-model="guestDetails.adults"
                      class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm sm:text-sm"
                      placeholder="00"
                    />
                    <div
                      class="flex items-center gap-3 rounded-full border border-gray-300 p-1"
                    >
                      <button
                        type="button"
                        @click="updateGuests('adults', -1)"
                        class="flex-center cursor-pointer rounded-full bg-primary p-1.5 text-xl text-white"
                      >
                        <Icon icon="mdi:minus" />
                      </button>
                      <span class="font-semibold">
                        {{ guestDetails.adults || 0 }}
                      </span>
                      <button
                        type="button"
                        @click="updateGuests('adults', 1)"
                        class="flex-center cursor-pointer rounded-full bg-primary p-1.5 text-xl text-white"
                      >
                        <Icon icon="mdi:plus" />
                      </button>
                    </div>
                  </div>
                </div>
                <div>
                  <label
                    for="contactAddress"
                    class="mb-1 block text-sm font-medium text-gray-700"
                  >
                    Contact Address <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    id="contactAddress"
                    v-model="guestDetails.contactAddress"
                    class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                    placeholder="Contact Address"
                  />
                </div>
                <div>
                  <label
                    for="children"
                    class="mb-1 block text-sm font-medium text-gray-700"
                  >
                    Children <span class="text-red-500">*</span>
                  </label>
                  <div class="flex items-center gap-3">
                    <input
                      id="children"
                      type="number"
                      v-model="guestDetails.children"
                      class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm sm:text-sm"
                      placeholder="00"
                    />
                    <div
                      class="flex items-center gap-3 rounded-full border border-gray-300 p-1"
                    >
                      <button
                        type="button"
                        @click="updateGuests('children', -1)"
                        class="flex-center cursor-pointer rounded-full bg-primary p-1.5 text-xl text-white"
                      >
                        <Icon icon="mdi:minus" />
                      </button>
                      <span class="font-semibold">
                        {{ guestDetails.children || 0 }}
                      </span>
                      <button
                        type="button"
                        @click="updateGuests('children', 1)"
                        class="flex-center cursor-pointer rounded-full bg-primary p-1.5 text-xl text-white"
                      >
                        <Icon icon="mdi:plus" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>

          <div class="flex items-center">
            <input
              id="agreement"
              name="agreement"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
            />
            <label for="agreement" class="ml-2 block text-sm text-gray-900">
              By proceeding, I agree to Paper Diary's Privacy Policy, User
              Agreement & Terms of Service
            </label>
          </div>

          <button class="w-full btn-primary sm:w-auto">BOOK ROOM</button>
        </div>

        <!-- Right side: Property details -->
        <div class="lg:col-span-1">
          <div class="overflow-hidden rounded-lg border shadow-sm">
            <div class="p-6">
              <h3 class="mb-4 text-2xl font-semibold">Property details</h3>
              <img
                src="/public/hotel-1.jpg"
                alt="Property"
                class="h-auto w-full rounded-lg object-cover"
              />
              <h4 class="mt-4 text-xl font-bold">
                San Francisco Marriott Marquis
              </h4>
              <div class="mt-1 flex items-center text-gray-500">
                <Icon icon="mdi:location" />
                <span class="ml-1">United States</span>
              </div>
            </div>
            <div class="border-t p-6">
              <h4 class="mb-4 text-lg font-semibold">Price Details</h4>
              <div class="space-y-2">
                <div class="flex-between">
                  <span>3 nights</span>
                  <span>+ £1,200</span>
                </div>
                <div class="flex-between">
                  <span>Taxes (10%)</span>
                  <span>+ £200</span>
                </div>
              </div>
            </div>
            <div class="border-t bg-gray-50 p-6">
              <div class="flex-between text-lg font-bold">
                <span>Total</span>
                <span>£ 1,400</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref } from "vue";
import { Icon } from "@iconify/vue";

const guestDetails = ref({
  fullName: "",
  email: "",
  contactNo: "",
  contactAddress: "",
  adults: 0,
  children: 0,
});

const updateGuests = (type, value) => {
  if (type === "adults") {
    guestDetails.value.adults = Math.max(0, guestDetails.value.adults + value);
  } else if (type === "children") {
    guestDetails.value.children = Math.max(
      0,
      guestDetails.value.children + value
    );
  }
};
</script>
