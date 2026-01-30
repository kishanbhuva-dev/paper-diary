<template>
  <!-- Comment -->
  <div class="p-2 sm:p-4 lg:p-6 bg-gray-50 min-h-max">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div
        v-for="stat in statsList"
        :key="stat.key"
        class="bg-white p-3 sm:p-4 lg:p-3 xl:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200"
      >
        <div class="flex justify-between items-start">
          <div>
            <p class="text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider">
              {{ stat.label }}
            </p>
            <div class="mt-2 flex items-baseline gap-2">
              <span
                class="text-lg sm:text-xl lg:text-2xl font-bold"
                :class="stat.textClass"
              >
                {{ stat.value }}
              </span>
              <span class="text-[10px] text-gray-400 font-medium">Units</span>
            </div>
          </div>
          <div
            class="p-2 sm:p-2.5 lg:p-3 rounded-xl lg:rounded-2xl"
            :class="stat.bgClass"
          >
            <Icon
              :icon="stat.icon"
              class="text-lg sm:text-xl lg:text-2xl"
            />
          </div>
        </div>
      </div>
    </div>
    <div
      v-if="propertyDropdown.values"
      class=""
    >
      <div class="mb-6 lg:mb-8">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">
          Property Filter
        </label>
        <div class="flex flex-wrap items-center gap-3">
          <div class="relative w-full sm:w-64 lg:w-72">
            <div
              class="bg-white border border-gray-300 px-3 py-2.5 lg:py-3 rounded-xl flex justify-between items-center cursor-pointer hover:border-blue-500 transition-colors select-none"
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

      <!-- <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-1 lg:p-2 mb-10"> -->
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
      <!-- </div> -->

      <div
        v-if="recentBookings && recentBookings.length > 0"
        class="mt-10"
      >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4 px-1">
          <h3 class="text-lg lg:text-xl font-bold text-gray-800 flex items-center gap-2">
            <Icon
              icon="mdi:history"
              class="text-blue-600"
            />
            Recent Bookings
          </h3>
          <div class="flex items-center justify-between sm:justify-end gap-4">
            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
              Top {{ recentBookings.length }} Latest Bookings
            </span>
            <button
              class="relative px-6 py-2.5 sm:px-8 sm:py-3 bg-blue-600 text-white text-xs sm:text-sm font-bold rounded-xl sm:rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all flex items-center group overflow-hidden active:scale-95"
              @click="viewAllBookings"
            >
              <span class="relative z-10">View All</span>
            </button>
          </div>
        </div>

        <div class="space-y-4">
          <div
            v-for="(booking, index) in recentBookings"
            :key="booking.id"
            class="group bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center gap-4 md:gap-0"
          >
            <div class="flex items-center gap-4 min-w-0 md:w-[200px] lg:w-[240px] xl:w-1/5">
              <div
                class="flex-shrink-0 w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center bg-gray-50 rounded-xl text-xs font-bold text-gray-400 group-hover:bg-blue-600 group-hover:text-white transition-all"
              >
                {{ index + 1 }}
              </div>
              <div class="min-w-0">
                <h4
                  class="text-sm font-bold text-gray-800 truncate group-hover:text-blue-700 transition-colors"
                >
                  {{ booking.guestName || 'Guest' }}
                </h4>
                <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest">
                  Ref #{{ booking.id }}
                </p>
              </div>
              <div class="ml-auto md:hidden">
                <span
                  class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider border whitespace-nowrap"
                  :class="getStatusBadgeClass(booking.status)"
                >
                  {{ booking.status }}
                </span>
              </div>
            </div>

            <div class="flex-grow min-w-0 md:px-4 lg:px-6">
              <div class="flex items-center gap-2 mb-2">
                <Icon
                  icon="mdi:email-outline"
                  class="text-gray-400 text-xs flex-shrink-0"
                />
                <span
                  class="text-[10px] sm:text-[11px] lg:text-xs text-gray-500 truncate font-medium"
                >
                  {{ booking.guestEmail || 'no-email-provided@mail.com' }}
                </span>
              </div>
              <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                <span
                  class="text-[10px] sm:text-[11px] text-gray-600 flex items-center gap-1 bg-gray-50 px-2 py-0.5 rounded-md"
                >
                  <Icon
                    icon="mdi:office-building"
                    class="text-blue-400"
                  />
                  {{ booking.property?.propertyName || 'Main Property' }}
                </span>
                <span
                  class="text-[10px] sm:text-[11px] text-gray-600 flex items-center gap-1 bg-gray-50 px-2 py-0.5 rounded-md"
                >
                  <Icon
                    icon="mdi:door-open"
                    class="text-green-500"
                  />
                  {{ booking.resource_type_name }}
                </span>
              </div>
            </div>

            <div
              class="flex items-center justify-between md:justify-center gap-4 sm:gap-6 pt-4 md:pt-0 border-t md:border-t-0 md:border-l md:border-r border-gray-100 md:px-6 lg:px-10 md:w-[220px] lg:w-[280px]"
            >
              <div class="text-center lg:text-left">
                <p
                  class="text-[8px] sm:text-[9px] uppercase text-gray-400 font-bold mb-0.5 tracking-tighter"
                >
                  Check-In
                </p>
                <p class="text-[10px] sm:text-[11px] font-bold text-gray-700 whitespace-nowrap">
                  {{ booking.arrivalDateTime }}
                </p>
              </div>
              <div class="h-6 w-px bg-gray-200 rotate-[20deg] md:mx-1 lg:mx-2"></div>
              <div class="text-center lg:text-left">
                <p
                  class="text-[8px] sm:text-[9px] uppercase text-gray-400 font-bold mb-0.5 tracking-tighter"
                >
                  Check-Out
                </p>
                <p class="text-[10px] sm:text-[11px] font-bold text-gray-700 whitespace-nowrap">
                  {{ booking.departureDateTime }}
                </p>
              </div>
            </div>

            <div
              class="hidden md:flex items-center justify-end md:pl-4 lg:pl-6 md:min-w-[120px] lg:min-w-[140px]"
            >
              <span
                class="px-3 lg:px-4 py-1.5 rounded-lg text-[9px] lg:text-[10px] font-bold uppercase tracking-wider w-full text-center border"
                :class="getStatusBadgeClass(booking.status)"
              >
                {{ booking.status }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div
      v-else
      class="flex flex-col items-center justify-center min-h-[450px] w-full bg-white border border-slate-100 rounded-2xl shadow-sm p-12"
    >
      <div class="relative mb-6">
        <div class="absolute inset-0 bg-blue-100 rounded-full blur-2xl opacity-40 scale-150"></div>
        <div
          class="relative flex items-center justify-center w-24 h-24 bg-blue-50 rounded-2xl rotate-3 border border-blue-100 shadow-inner"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-12 h-12 text-blue-600 -rotate-3"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
            />
          </svg>
        </div>
      </div>

      <div class="max-w-md text-center">
        <h3 class="text-2xl font-bold text-slate-800">No properties listed yet</h3>
        <p class="mt-3 text-slate-500 leading-relaxed">
          Your dashboard is looking a bit empty. Add your first property to start managing your
          listings and tracking performance.
        </p>
      </div>

      <button
        class="group mt-10 flex items-center gap-2 px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-200 transition-all duration-200 active:scale-95"
        @click="router.push({ name: 'property-wizard' })"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5 transition-transform group-hover:rotate-90"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2.5"
            d="M12 4v16m8-8H4"
          />
        </svg>
        Add Your Property
      </button>
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
      message="This will permanently cancel the guest's reservation."
      @confirm="handleCancelBooking"
    />
  </div>
</template>

<script setup>
// ... (Script remains exactly as you provided to maintain functionality)
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

const viewAllBookings = () => router.push({ name: 'bookings' });
const triggerCancelFlow = (id) => {
  bookingToCancelId.value = id;
  isCancelModalOpen.value = true;
};
const removeProperty = (id) => {
  selectedProperties.value = selectedProperties.value.filter((item) => item !== id);
};

const customStatuses = [
  { key: 'available', label: 'Available', color: '', backgroundColor: '' },
  { key: 'confirm', label: 'Confirm', color: '#155e75', backgroundColor: '#a7f3d0' },
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
      text: 'text-blue-600',
    },
    totalBooking: {
      icon: 'mdi:calendar-check',
      bg: 'bg-indigo-50 text-indigo-600',
      text: 'text-indigo-600',
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
    configs[key] || { icon: 'mdi:chart-bar', bg: 'bg-gray-50 text-gray-600', text: 'text-gray-800' }
  );
};

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
  isCancelModalOpen.value = false;
  try {
    const res = await ownerService.cancelBooking(bookingToCancelId.value);
    if (res && res.status === true) {
      await Promise.all([getResources(), getRecentBookings()]);
      showBookingModal.value = false;
    }
  } catch (error) {
    console.error(error);
  } finally {
    isLoading.value = false;
    bookingToCancelId.value = null;
  }
};

const getRecentBookings = async () => {
  try {
    const res = await ownerService.fetchBookings({
      limit: 10,
      property_ids: selectedProperties.value.length ? selectedProperties.value : undefined,
    });
    recentBookings.value = (res.data || res).slice(0, 10);
  } catch (error) {
    console.error(error);
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
    console.error(error);
  }
};

onMounted(() => {
  ownerService
    .fetchPropertiesdropdown()
    .then((res) => (propertyDropdown.value = res.data.data || []));
  getResources();
  getRecentBookings();
});

watch(selectedProperties, () => {
  getResources();
  getRecentBookings();
});
</script>

<style scoped>
.animate-in {
  animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
