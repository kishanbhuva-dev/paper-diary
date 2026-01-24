<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4"
    >
      <div
        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
        @click="$emit('close')"
      ></div>

      <div
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 flex flex-col max-h-[90vh] sm:max-h-[85vh] transform transition-all"
      >
        <div
          class="px-4 py-3 sm:px-8 sm:py-6 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 flex justify-between items-center shrink-0"
        >
          <div class="flex items-center gap-3 sm:gap-4">
            <div
              class="flex items-center justify-center w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200"
            >
              <Icon
                icon="mdi:calendar-account"
                class="text-lg sm:text-2xl"
              />
            </div>
            <div class="min-w-0">
              <h3 class="text-base sm:text-xl font-bold text-slate-800 tracking-tight truncate">
                Booking Details
              </h3>
              <p class="text-[10px] sm:text-sm font-medium text-slate-500">
                Ref: #{{ booking?.id }}
              </p>
            </div>
          </div>
          <button
            type="button"
            class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors active:scale-90"
            @click="$emit('close')"
          >
            <Icon
              icon="mdi:close"
              class="text-xl sm:text-2xl"
            />
          </button>
        </div>

        <div class="p-4 sm:p-8 overflow-y-auto bg-white modal-inner-content">
          <div
            v-if="booking"
            class="space-y-3 sm:space-y-6"
          >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
              <div
                class="p-3 sm:p-4 bg-slate-50/50 rounded-2xl border border-slate-200 hover:border-blue-400 hover:bg-white transition-all duration-300"
              >
                <label
                  class="text-[9px] sm:text-[10px] font-bold text-slate-500 uppercase tracking-widest"
                  >Guest Name</label
                >
                <p class="text-slate-800 text-sm sm:text-base font-bold mt-0.5 sm:mt-1 truncate">
                  {{ booking.guestName }}
                </p>
              </div>
              <div
                class="p-3 sm:p-4 bg-slate-50/50 rounded-2xl border border-slate-200 hover:border-blue-400 hover:bg-white transition-all duration-300"
              >
                <label
                  class="text-[9px] sm:text-[10px] font-bold text-slate-500 uppercase tracking-widest"
                  >Adult / Child</label
                >
                <p class="text-slate-800 text-sm sm:text-base font-bold mt-0.5 sm:mt-1">
                  {{ booking.adult }} Adults
                  <span
                    v-if="booking.child"
                    class="text-blue-600"
                    >/ {{ booking.child }} Child</span
                  >
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
              <div class="p-3 sm:p-4 bg-blue-50/30 rounded-2xl border border-blue-100">
                <label
                  class="text-[9px] sm:text-[10px] font-bold text-blue-600 uppercase tracking-widest flex items-center gap-1"
                >
                  <Icon icon="mdi:login" /> Check-In
                </label>
                <p class="text-slate-700 font-bold mt-0.5 sm:mt-1 text-xs sm:text-base">
                  {{ booking.modalcheckIn }}
                </p>
              </div>
              <div class="p-3 sm:p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <label
                  class="text-[9px] sm:text-[10px] font-bold text-slate-600 uppercase tracking-widest flex items-center gap-1"
                >
                  <Icon icon="mdi:logout" /> Check-Out
                </label>
                <p class="text-slate-700 font-bold mt-0.5 sm:mt-1 text-xs sm:text-base">
                  {{ booking.modalcheckOut }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
              <div class="p-3 sm:p-4 bg-slate-50/50 rounded-2xl border border-slate-200">
                <label
                  class="text-[9px] sm:text-[10px] font-bold text-slate-500 uppercase tracking-widest"
                  >Resource</label
                >
                <div class="flex items-center gap-2 mt-0.5 sm:mt-1">
                  <Icon
                    icon="heroicons:building-office-2"
                    class="text-blue-600 text-base sm:text-lg"
                  />
                  <p class="text-slate-800 text-sm sm:text-base font-bold">
                    {{ booking.roomNumber }}
                  </p>
                </div>
              </div>
              <div class="p-3 sm:p-4 bg-slate-50/50 rounded-2xl border border-slate-200">
                <label
                  class="text-[9px] sm:text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1.5 sm:mb-2"
                  >Booking Status</label
                >
                <span
                  :class="getStatusClasses(booking.status)"
                  class="px-3 py-0.5 sm:px-3.5 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-bold uppercase border inline-block"
                >
                  {{ booking.status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div
          class="px-4 py-3 sm:px-8 sm:py-5 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-2 sm:gap-3 shrink-0"
        >
          <div class="w-full sm:w-auto order-2 sm:order-1">
            <button
              v-if="booking?.status?.toLowerCase() !== 'cancelled' && canCancel"
              type="button"
              class="w-full sm:w-auto px-4 py-2 sm:px-5 sm:py-2.5 text-xs sm:text-sm font-bold text-red-600 bg-white border border-red-200 rounded-xl hover:bg-red-50 transition-all active:scale-95 flex items-center justify-center gap-2"
              @click="$emit('cancel', booking.id)"
            >
              <Icon
                icon="mdi:calendar-remove"
                class="w-4 h-4 sm:w-5 sm:h-5"
              />
              Cancel Booking
            </button>
          </div>

          <div class="w-full sm:w-auto order-1 sm:order-2">
            <button
              type="button"
              class="w-full sm:w-auto px-8 py-2 sm:px-10 sm:py-2.5 text-xs sm:text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-all active:scale-95 shadow-lg shadow-blue-100 flex items-center justify-center"
              @click="$emit('close')"
            >
              Done
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { Icon } from '@iconify/vue';

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  booking: {
    type: Object,
    default: null,
  },
  canCancel: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['close', 'cancel']);

const getStatusClasses = (status) => {
  switch (status?.toLowerCase()) {
    case 'confirm':
    case 'confirmed':
      return 'bg-emerald-50 text-emerald-700 border-emerald-100';
    case 'cancelled':
      return 'bg-red-50 text-red-700 border-red-100';
    default:
      return 'bg-slate-100 text-slate-600 border-slate-200';
  }
};
</script>

<style scoped>
.modal-inner-content::-webkit-scrollbar {
  width: 4px; /* Slimmer scrollbar for mobile */
}
.modal-inner-content::-webkit-scrollbar-track {
  background-color: transparent;
}
.modal-inner-content::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}
</style>
