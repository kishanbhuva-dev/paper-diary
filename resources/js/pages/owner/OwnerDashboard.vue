<template>
  <div class="p-2 sm:p-3 lg:p-4 bg-gray-50 min-h-max">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div
        v-for="stat in statsList"
        :key="stat.key"
        class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200"
      >
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
              {{ stat.label }}
            </p>
            <div class="mt-2 flex items-baseline gap-2">
              <span
                class="text-xl font-bold"
                :class="stat.textClass"
              >
                {{ stat.value }}
              </span>
              <span class="text-xs text-gray-400 font-medium">Units</span>
            </div>
          </div>
          <div
            class="p-2.5 rounded-xl"
            :class="stat.bgClass"
          >
            <Icon
              :icon="stat.icon"
              class="text-xl"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Property Filter -->
    <div class="mb-6">
      <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">
        Property Filter
      </label>
      <div class="flex flex-wrap items-center gap-3">
        <div class="relative w-full sm:w-64">
          <div
            class="bg-white border border-gray-300 px-3 py-2.5 rounded-xl flex justify-between items-center cursor-pointer hover:border-blue-500 transition-colors select-none"
            @click="isDropdownOpen = !isDropdownOpen"
          >
            <span class="text-sm text-gray-600 truncate">
              {{
                selectedProperties.length > 0
                  ? selectedProperties.length + ' Selected'
                  : 'All Properties'
              }}
            </span>
            <Icon
              icon="mdi:chevron-down"
              class="text-gray-400 transition-transform"
              :class="{ 'rotate-180': isDropdownOpen }"
            />
          </div>

          <div
            v-if="isDropdownOpen"
            class="absolute z-50 mt-2 w-full bg-white border border-gray-100 shadow-xl rounded-xl p-2 max-h-60 overflow-y-auto"
          >
            <label
              v-for="item in propertyDropdown"
              :key="item.id"
              class="flex items-center gap-3 px-3 py-2.5 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors group"
            >
              <input
                v-model="selectedProperties"
                type="checkbox"
                :value="item.id"
                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
              />
              <span class="text-sm text-gray-700 font-medium group-hover:text-blue-700">
                {{ item.name }}
              </span>
            </label>
          </div>
        </div>

        <div class="flex flex-wrap gap-2 items-center">
          <div
            v-for="id in selectedProperties"
            :key="id"
            class="flex items-center gap-1.5 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-xl border border-blue-100 animate-in fade-in zoom-in duration-200"
          >
            <span class="text-xs font-bold whitespace-nowrap">
              {{ propertyDropdown.find((p) => p.id === id)?.name }}
            </span>
            <button
              class="hover:bg-blue-200 rounded-full p-0.5 transition-colors"
              @click="removeProperty(id)"
            >
              <Icon
                icon="mdi:close"
                class="text-sm"
              />
            </button>
          </div>

          <button
            v-if="selectedProperties.length > 0"
            class="text-[10px] font-bold text-gray-400 hover:text-red-500 uppercase tracking-tighter ml-1"
            @click="selectedProperties = []"
          >
            Clear All
          </button>
        </div>
      </div>
    </div>
    <!-- booking calendar -->
    <HotelDashboardCalendar
      :rooms="rooms"
      :bookings="bookings"
      :status-config="customStatuses"
      allow-previous-month-navigation
      :text-labels="{
        room: 'Resources',
        available: 'Free',
        previousMonth: '← Previous',
        nextMonth: 'Next →',
      }"
      theme="light"
      @booking-click="handleBookingClick"
    />
    <!-- recent booking -->
    <div class="mt-10">
      <div class="flex items-center justify-between mb-5 px-1">
        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
          <Icon
            icon="mdi:history"
            class="text-blue-600"
          />
          Recent Bookings
        </h3>
        <div class="flex items-center gap-4">
          <span class="text-xs font-semibold text-gray-400 uppercase">
            Top {{ recentBookings.length }} Latest Bookings
          </span>
          <button
            class="relative px-8 py-3 bg-blue-600 text-white text-sm font-bold rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center group overflow-hidden"
            @click="viewAllBookings"
          >
            <span class="relative z-10">View All</span>
            <span
              class="absolute left-0 top-0 w-full h-full bg-blue-700 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-300"
            ></span>
          </button>
        </div>
      </div>

      <div class="space-y-3">
        <div
          v-for="(booking, index) in recentBookings"
          :key="booking.id"
          class="group bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all flex flex-wrap items-center gap-4 lg:gap-8"
        >
          <div class="flex items-center gap-4 min-w-[180px]">
            <div
              class="flex-shrink-0 w-9 h-9 flex items-center justify-center bg-gray-50 rounded-lg text-xs font-bold text-gray-400 group-hover:bg-blue-600 group-hover:text-white transition-all"
            >
              {{ index + 1 }}
            </div>
            <div class="min-w-0">
              <h4
                class="text-sm font-bold text-gray-800 truncate group-hover:text-blue-700 transition-colors"
              >
                {{ booking.guestName || 'Guest' }}
              </h4>
              <p class="text-[10px] font-bold text-blue-500 uppercase tracking-tight">
                Ref #{{ booking.id }}
              </p>
            </div>
          </div>

          <div class="flex-grow min-w-[280px]">
            <div class="flex items-center gap-2 mb-1.5">
              <Icon
                icon="mdi:email-outline"
                class="text-gray-400 text-xs"
              />
              <span class="text-[11px] text-gray-500 truncate font-medium">
                {{ booking.guestEmail || 'no-email-provided@mail.com' }}
              </span>
            </div>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
              <span class="text-[11px] text-gray-600 flex items-center gap-1">
                <Icon
                  icon="mdi:office-building"
                  class="text-blue-400"
                />
                {{ booking.property?.propertyName || 'Main Property' }}
              </span>
              <span class="text-[11px] text-gray-600 flex items-center gap-1">
                <Icon
                  icon="mdi:door-open"
                  class="text-green-500"
                />
                {{ booking.resource_type_name }}
              </span>
              <span class="text-[11px] text-gray-600 flex items-center gap-1">
                <Icon
                  icon="mdi:clock-check-outline"
                  class="text-orange-400"
                />
                Booked On: {{ booking.bookedOn || 'N/A' }}
              </span>
            </div>
          </div>

          <div class="flex items-center gap-5 px-6 border-l border-gray-100">
            <div class="text-center">
              <p class="text-[9px] uppercase text-gray-400 font-bold mb-0.5">Check-In</p>
              <p class="text-xs font-bold text-gray-700 whitespace-nowrap">
                {{ booking.arrivalDateTime }}
              </p>
            </div>
            <div class="h-6 w-px bg-gray-100 rotate-[20deg]"></div>
            <div class="text-center">
              <p class="text-[9px] uppercase text-gray-400 font-bold mb-0.5">Check-Out</p>
              <p class="text-xs font-bold text-gray-700 whitespace-nowrap">
                {{ booking.departureDateTime }}
              </p>
            </div>
          </div>

          <div class="ml-auto flex items-center">
            <span
              class="px-4 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider min-w-[90px] text-center border"
              :class="getStatusBadgeClass(booking.status)"
            >
              {{ booking.status }}
            </span>
          </div>
        </div>

        <div
          v-if="recentBookings.length === 0"
          class="py-12 bg-white rounded-xl border border-dashed border-gray-200 text-center"
        >
          <Icon
            icon="mdi:calendar-blank-outline"
            class="text-4xl text-gray-200 mx-auto mb-2"
          />
          <p class="text-gray-400 text-sm">No activity recorded for the selected filter.</p>
        </div>
      </div>
    </div>

    <BookingDetailModal
      :show="showBookingModal"
      :booking="selectedBooking"
      :can-cancel="isFutureBooking(selectedBooking?.checkIn || selectedBooking?.arrivalDateTime)"
      @close="showBookingModal = false"
      @cancel="triggerCancelFlow"
    />
    <ConfirmModal
      v-model="isCancelModalOpen"
      title="Cancel Booking?"
      message="This will permanently cancel the guest's reservation. Are you sure you want to proceed?"
      @confirm="handleCancelBooking"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Icon } from '@iconify/vue';
import { useRouter } from 'vue-router';
import ownerService from '@/services/ownerService';
import BookingDetailModal from '@/components/modals/BookingDetailModal.vue';
import { HotelDashboardCalendar } from 'vue-hotel-booking-calendar';
import 'vue-hotel-booking-calendar/dist/style.css';
import ConfirmModal from '@/components/owner/ConfirmModal.vue';

const propertyDropdown = ref([]);
const selectedProperties = ref([]);
const isDropdownOpen = ref(false);

// --- State ---
const isCancelModalOpen = ref(false);
const bookingToCancelId = ref(null);
const isLoading = ref(false);

const rooms = ref([]);
const bookings = ref([]);
const recentBookings = ref([]);
const showBookingModal = ref(false);
const selectedBooking = ref(null);
const statsList = ref([]);
const router = useRouter();

const viewAllBookings = () => {
  router.push({ name: 'bookings' });
};

const triggerCancelFlow = (id) => {
  bookingToCancelId.value = id;
  isCancelModalOpen.value = true;
};
const removeProperty = (id) => {
  selectedProperties.value = selectedProperties.value.filter((item) => item !== id);
};

const customStatuses = [
  { key: 'available', label: 'Available', color: '', backgroundColor: '' },
  {
    key: 'confirm',
    label: 'Confirm',
    color: '#155e75',
    backgroundColor: '#a7f3d0',
  },
];

const getStatusBadgeClass = (status) => {
  const s = status?.toLowerCase();
  if (s === 'confirm' || s === 'confirmed') {
    return 'bg-emerald-50 text-emerald-700 border-emerald-100';
  }
  return 'bg-amber-50 text-amber-700 border-amber-100';
};

const formatLabel = (key) =>
  key.replace(/([A-Z])/g, ' $1').replace(/^./, (str) => str.toUpperCase());

const getStatConfig = (key) => {
  const configs = {
    totalProperty: {
      icon: 'mdi:office-building-marker-outline',
      bg: 'bg-blue-50 text-blue-600',
      text: 'text-gray-800',
    },
    totalBooking: {
      icon: 'mdi:calendar-check',
      bg: 'bg-indigo-50 text-indigo-600',
      text: 'text-gray-800',
    },
    todayBooking: {
      icon: 'mdi:calendar-today',
      bg: 'bg-green-50 text-green-600',
      text: 'text-green-600',
    },
    cancelledBooking: {
      icon: 'mdi:calendar-remove',
      bg: 'bg-red-50 text-red-600',
      text: 'text-red-600',
    },
  };
  return (
    configs[key] || {
      icon: 'mdi:chart-bar',
      bg: 'bg-gray-50 text-gray-600',
      text: 'text-gray-800',
    }
  );
};

watch(selectedProperties, () => {
  getResources();
  getRecentBookings();
});

const isFutureBooking = (checkInStr) => {
  if (!checkInStr) {
    return false;
  }
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const dateParts = checkInStr.includes('-') ? checkInStr.split('-') : [];
  const bookingDate =
    dateParts[0].length === 4
      ? new Date(checkInStr)
      : new Date(`${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`);
  bookingDate.setHours(0, 0, 0, 0);
  return bookingDate.getTime() >= today.getTime();
};

const handleBookingClick = (booking) => {
  selectedBooking.value = {
    ...booking,
    checkIn: booking.checkIn || booking.arrivalDateTime,
    checkOut: booking.checkOut || booking.departureDateTime,
  };
  showBookingModal.value = true;
};

const handleCancelBooking = async () => {
  if (!bookingToCancelId.value) {
    return;
  }

  isLoading.value = true;
  isCancelModalOpen.value = false; // Close modal immediately for better UX

  try {
    const res = await ownerService.cancelBooking(bookingToCancelId.value);
    if (res && res.status === true) {
      await Promise.all([getResources(), getRecentBookings()]);
      isCancelModalOpen.value = false;
      showBookingModal.value = false;
    } else {
      isCancelModalOpen.value = false;
    }
  } catch (error) {
    throw new Error(error);
  } finally {
    isLoading.value = false;
    bookingToCancelId.value = null;
  }
};

const getRecentBookings = async () => {
  try {
    const params = {
      limit: 10,
      property_ids: selectedProperties.value.length ? selectedProperties.value : undefined,
    };
    const res = await ownerService.fetchBookings(params);
    recentBookings.value = (res.data || res).slice(0, 10);
  } catch (error) {
    throw new Error(error);
  }
};

const getResources = async () => {
  try {
    const ResourcesData = await ownerService.resourceList({
      propertyIds: selectedProperties.value,
    });
    const keysToDisplay = ['totalProperty', 'totalBooking', 'todayBooking', 'cancelledBooking'];
    statsList.value = keysToDisplay.map((key) => {
      const config = getStatConfig(key);
      return {
        key,
        label: formatLabel(key),
        value: ResourcesData[key] || 0,
        icon: config.icon,
        bgClass: config.bg,
        textClass: config.text,
      };
    });
    rooms.value = ResourcesData.resource.map((res) => ({
      id: res.id.toString(),
      number: res.name,
    }));
    const formatDate = (dateStr) => {
      if (!dateStr) {
        return '';
      }
      const [d, m, y] = dateStr.trim().split('-');
      return `${y}-${m}-${d}`;
    };
    bookings.value = ResourcesData.booking.map((book) => ({
      id: book.id.toString(),
      guestName: book.guestFullName,
      roomNumber: book.resource_name,
      checkIn: formatDate(book.arrivalDateTime),
      checkOut: formatDate(book.departureDateTime),
      modalcheckIn: book.arrivalDateTime,
      modalcheckOut: book.departureDateTime,
      adult: book.adult,
      child: book.children ?? 0,
      status: book.status,
    }));
  } catch (error) {
    throw new Error(error);
  }
};

const fetchPropertiesdropdown = async () => {
  try {
    const res = await ownerService.fetchPropertiesdropdown();
    propertyDropdown.value = res.data.data || [];
  } catch (error) {
    throw new Error(error);
  }
};

onMounted(() => {
  fetchPropertiesdropdown();
  getResources();
  getRecentBookings();
});
</script>
