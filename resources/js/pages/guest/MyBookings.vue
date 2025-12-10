<template>
  <main class="container mx-auto py-12">
    <div class="px-4 sm:px-6 lg:px-8">
      <!-- <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-12">
        <div class="relative w-full md:col-span-2">
          <select
            class="w-full appearance-none rounded-md border border-gray-300 p-2.5 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
          >
            <option>Status</option>
            <option>Booked</option>
            <option>Pending</option>
            <option>Cancel</option>
          </select>
          <Icon
            icon="mdi:chevron-up-down"
            class="pointer-events-none absolute top-1/2 right-2.5 ml-1 size-5 -translate-y-1/2"
          />
        </div>
        <div class="w-full md:col-span-2">
          <input
            ref="fromDateInput"
            type="date"
            class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            :value="dayjs().format('YYYY-MM-DD')"
            @click="$refs.fromDateInput.showPicker()"
          />
        </div>
        <div class="w-full md:col-span-2">
          <input
            ref="toDateInput"
            type="date"
            class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            :value="dayjs().add(1, 'day').format('YYYY-MM-DD')"
            @click="$refs.toDateInput.showPicker()"
          />
        </div>
        <div class="w-full md:col-span-2">
          <button
            class="flex-center w-full gap-2 rounded-md border border-gray-300 p-2.5 text-sm font-semibold shadow-sm hover:bg-gray-100"
          >
            <Icon icon="mdi:refresh" />
            Reset Filter
          </button>
        </div>
        <div class="relative w-full md:col-span-4">
          <input
            type="text"
            placeholder="Enter Your Interest"
            class="w-full rounded-md border border-gray-300 p-2.5 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
          />
          <button
            class="absolute inset-y-0 right-0 flex-center rounded-r-md bg-primary px-4 text-white"
          >
            <Icon icon="mdi:magnify" />
          </button>
        </div>
      </div> -->

      <!-- Filter Section -->
      <div
        class="mb-3 flex flex-col justify-between gap-3 text-sm font-semibold text-gray-600 md:flex-row"
      >
        <div
          class="grid divide-gray-300 overflow-hidden rounded-md border border-gray-300 max-md:divide-y md:grid-cols-4 md:divide-x"
        >
          <div class="relative transition-colors hover:bg-gray-50">
            <label for="status-filter" class="sr-only">Status</label>
            <select
              id="status-filter"
              class="mr-6 w-full cursor-pointer appearance-none p-2 outline-0"
            >
              <option value="" selected>Status</option>
              <option value="Booked">Booked</option>
              <option value="Pending">Pending</option>
              <option value="Cancelled">Cancelled</option>
            </select>
            <Icon
              icon="mdi:chevron-up-down"
              class="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2"
            />
          </div>
          <div class="relative transition-colors hover:bg-gray-50">
            <label for="from-date-filter" class="sr-only">Date</label>
            <input
              id="from-date-filter"
              type="date"
              class="w-full cursor-pointer p-2 outline-0"
              onclick="this.showPicker()"
              :value="dayjs().format('YYYY-MM-DD')"
              :min="dayjs().format('YYYY-MM-DD')"
            />
            <!-- <Icon
              icon="mdi:calendar-month"
              class="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2"
            /> -->
          </div>
          <div class="relative transition-colors hover:bg-gray-50">
            <label for="to-date-filter" class="sr-only">Date</label>
            <input
              id="to-date-filter"
              type="date"
              class="w-full cursor-pointer p-2 outline-0"
              onclick="this.showPicker()"
              :value="dayjs().add(1, 'day').format('YYYY-MM-DD')"
              :min="dayjs().format('YYYY-MM-DD')"
            />
            <!-- <Icon
              icon="mdi:calendar-month"
              class="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2"
            /> -->
          </div>
          <div>
            <button
              class="flex-center w-full cursor-pointer gap-1 p-2 text-red-600 outline-0 transition-colors hover:bg-red-50"
              aria-label="Reset Filter"
            >
              <Icon icon="mdi:refresh" />
              Reset Filter
            </button>
          </div>
        </div>
        <!-- Search Input -->
        <div class="relative transition-colors hover:bg-gray-50">
          <label for="search-filter" class="sr-only">Search</label>
          <input
            id="search-filter"
            type="text"
            placeholder="Enter Your Interest"
            class="w-full rounded-md border border-gray-300 p-2 pr-10 outline-0"
          />
          <button
            class="absolute top-1/2 right-1 -translate-y-1/2 cursor-pointer rounded bg-primary p-2 text-white"
            aria-label="Search"
          >
            <Icon icon="mdi:search" />
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button
            v-for="tab in tabs"
            :key="tab.name"
            @click="activeTab = tab.name"
            :class="[
              activeTab === tab.name
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'cursor-pointer border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap',
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Bookings Table -->
      <div class="overflow-hidden rounded-lg border border-gray-300">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  v-for="header in tableHeaders"
                  :key="header"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-semibold tracking-wider uppercase"
                >
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="booking in filteredBookings" :key="booking.id">
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  {{ booking.id }}
                </td>
                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                  {{ booking.name }}
                </td>
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  {{ booking.address }}
                </td>
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  {{ booking.date }}
                </td>
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  {{ booking.type }}
                </td>
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex rounded-full px-2 text-xs leading-5 font-semibold',
                      statusClasses[booking.status],
                    ]"
                  >
                    {{ booking.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed } from "vue";
import { Icon } from "@iconify/vue";
import dayjs from "dayjs";

const tabs = [{ name: "Past Bookings" }, { name: "Upcoming Bookings" }];
const activeTab = ref("Past Bookings");

const tableHeaders = ["ID", "NAME", "ADDRESS", "DATE", "TYPE", "STATUS"];

const bookings = ref([
  {
    id: "00001",
    name: "Christine Brooks",
    address: "089 Kutch Green Apt. 448",
    date: "04 Sep 2019",
    type: "Office",
    status: "Booked",
    bookingType: "past",
  },
  {
    id: "00002",
    name: "Rosie Pearson",
    address: "979 Immanuel Ferry Suite 526",
    date: "28 May 2019",
    type: "Room",
    status: "Pending",
    bookingType: "past",
  },
  {
    id: "00003",
    name: "Darrell Caldwell",
    address: "8587 Frida Ports",
    date: "23 Nov 2019",
    type: "Conference room",
    status: "Cancel",
    bookingType: "past",
  },
  {
    id: "00004",
    name: "Gilbert Johnston",
    address: "768 Destiny Lake Suite 600",
    date: "05 Feb 2019",
    type: "Hotel room",
    status: "Booked",
    bookingType: "past",
  },
  {
    id: "00005",
    name: "Alan Cain",
    address: "042 Mylene Throughway",
    date: "29 Jul 2019",
    type: "Restaurant",
    status: "Pending",
    bookingType: "past",
  },
  {
    id: "00006",
    name: "Alfred Murray",
    address: "543 Weimann Mountain",
    date: "15 Aug 2019",
    type: "Parking Slot",
    status: "Booked",
    bookingType: "past",
  },
  {
    id: "00007",
    name: "Maggie Sullivan",
    address: "New Scottieberg",
    date: "21 Dec 2019",
    type: "Place",
    status: "Pending",
    bookingType: "past",
  },
  {
    id: "00008",
    name: "Rosie Todd",
    address: "New Jon",
    date: "30 Apr 2019",
    type: "Banquet",
    status: "Cancel",
    bookingType: "past",
  },
  {
    id: "00009",
    name: "Dollie Hines",
    address: "124 Lyla Forge Suite 975",
    date: "09 Jan 2019",
    type: "Hotel room",
    status: "Pending",
    bookingType: "past",
  },
  // Upcoming bookings
  {
    id: "00010",
    name: "John Doe",
    address: "123 Upcoming St",
    date: "25 Dec 2025",
    type: "Hotel room",
    status: "Booked",
    bookingType: "upcoming",
  },
  {
    id: "00011",
    name: "Jane Smith",
    address: "456 Future Ave",
    date: "15 Jan 2026",
    type: "Conference room",
    status: "Pending",
    bookingType: "upcoming",
  },
]);

const statusClasses = {
  Booked: "bg-green-100 text-green-800",
  Pending: "bg-yellow-100 text-yellow-800",
  Cancel: "bg-red-100 text-red-800",
};

const filteredBookings = computed(() => {
  if (activeTab.value === "Past Bookings") {
    return bookings.value.filter((b) => b.bookingType === "past");
  }
  return bookings.value.filter((b) => b.bookingType === "upcoming");
});
</script>

<!-- <style scoped>
input[type="date"]::-webkit-calendar-picker-indicator {
  display: none;
}
</style> -->
