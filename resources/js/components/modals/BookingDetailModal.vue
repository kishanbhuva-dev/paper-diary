<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 bg-gray-900/50 flex items-center justify-center p-4 backdrop-blur-sm"
  >
    <div
      class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-auto overflow-hidden border border-indigo-200 flex flex-col max-h-[90vh]"
    >
      <div
        class="p-5 bg-indigo-100 flex justify-between items-center border-b border-indigo-200 shrink-0"
      >
        <h3 class="text-xl font-bold text-indigo-900 flex items-center gap-2">
          <Icon icon="mdi:calendar-account" class="w-6 h-6" />
          Booking Details
        </h3>
        <button
          @click="$emit('close')"
          class="text-indigo-400 hover:text-red-500 transition duration-150 p-1 rounded-full hover:bg-white/50"
        >
          <Icon icon="mdi:close" class="w-6 h-6" />
        </button>
      </div>

      <div class="p-6 overflow-y-auto modal-inner-content">
        <div v-if="booking" class="space-y-6">
          <div class="grid grid-cols-2 gap-4">
            <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
              <label
                class="text-xs font-bold text-gray-400 uppercase tracking-wider"
                >Guest Name</label
              >
              <p class="text-indigo-900 font-semibold mt-1">
                {{ booking.guestName }}
              </p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
              <label
                class="text-xs font-bold text-gray-400 uppercase tracking-wider"
                >Resource/Room</label
              >
              <p class="text-indigo-900 font-semibold mt-1">
                {{ booking.roomNumber }}
              </p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="p-3 bg-green-50/50 rounded-lg border border-green-100">
              <label
                class="text-xs font-bold text-green-600 uppercase tracking-wider flex items-center gap-1"
              >
                <Icon icon="mdi:login" /> Check-In
              </label>
              <p class="text-gray-800 font-medium mt-1">
                {{ booking.checkIn }}
              </p>
            </div>
            <div class="p-3 bg-amber-50/50 rounded-lg border border-amber-100">
              <label
                class="text-xs font-bold text-amber-600 uppercase tracking-wider flex items-center gap-1"
              >
                <Icon icon="mdi:logout" /> Check-Out
              </label>
              <p class="text-gray-800 font-medium mt-1">
                {{ booking.checkOut }}
              </p>
            </div>
          </div>

          <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
            <label
              class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2"
              >Booking Status</label
            >
            <span
              :class="getStatusClasses(booking.status)"
              class="px-3 py-1 rounded-full text-xs font-bold uppercase"
            >
              {{ booking.status }}
            </span>
          </div>

          <div class="pt-4 border-t border-gray-100">
            <p class="text-[10px] text-gray-400">
              Booking Reference ID: #{{ booking.id }}
            </p>
          </div>
        </div>
      </div>

      <div
        class="flex justify-between items-center pt-4 px-6 pb-6 bg-white border-t border-gray-300 shrink-0"
      >
        <button
          v-if="booking?.status?.toLowerCase() !== 'cancelled' && canCancel"
          type="button"
          @click="$emit('cancel', booking.id)"
          class="px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 border border-red-200 rounded-lg transition duration-150 flex items-center"
        >
          <Icon icon="mdi:calendar-remove" class="w-5 h-5 mr-1" />
          Cancel Booking
        </button>
        <div v-else></div>
        <button
          type="button"
          @click="$emit('close')"
          class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition duration-150 flex items-center shadow-md outline-indigo-400 shadow-indigo-200"
        >
          Done
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Icon } from "@iconify/vue";

const props = defineProps({
  show: Boolean,
  booking: Object,
  canCancel: Boolean, // Receives true if date is today or future
});

defineEmits(["close", "cancel"]);

const getStatusClasses = (status) => {
  switch (status?.toLowerCase()) {
    case "confirm":
      return "bg-green-100 text-green-700 border border-green-200";
    case "cancelled":
      return "bg-red-100 text-red-700 border border-red-200";
    default:
      return "bg-gray-100 text-gray-700 border border-gray-200";
  }
};
</script>

<style scoped>
.modal-inner-content::-webkit-scrollbar {
  width: 8px;
}
.modal-inner-content::-webkit-scrollbar-track {
  background-color: transparent;
}
.modal-inner-content::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
  border: 2px solid transparent;
  background-clip: content-box;
}
</style>
