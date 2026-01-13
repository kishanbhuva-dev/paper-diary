<template>
  <div
    class="flex flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-lg transition-shadow duration-300 hover:shadow-2xl lg:flex-row"
  >
    <div class="relative w-full flex-shrink-0 lg:w-96">
      <div class="h-64 overflow-hidden bg-gray-200 lg:h-80">
        <img
          :src="room.imageUrls[activeImageIndex]"
          :alt="room.title"
          class="h-full w-full object-cover transition-opacity duration-500 ease-in-out"
        />
      </div>

      <div class="absolute inset-x-0 bottom-0 p-2">
        <div class="flex-between">
          <div class="flex-center gap-1">
            <div
              v-for="(url, index) in room.imageUrls.slice(0, 4)"
              :key="index"
              :class="[
                'size-8 cursor-pointer overflow-hidden rounded-full border-2 shadow-md transition',
                index === activeImageIndex
                  ? 'border-white ring-2 ring-blue-400'
                  : 'border-transparent opacity-80 hover:opacity-100',
              ]"
              @click.stop="activeImageIndex = index"
            >
              <img
                :src="url"
                :alt="'Thumbnail ' + (index + 1)"
                class="h-full w-full object-cover"
              />
            </div>
          </div>

          <div class="flex-center gap-1">
            <button
              class="flex-center size-8 cursor-pointer rounded bg-white/50 text-white backdrop-blur-sm"
              @click.stop="prevImage"
            >
              <Icon
                icon="mdi:chevron-left"
                class="text-2xl"
              />
            </button>
            <button
              class="flex-center size-8 cursor-pointer rounded bg-white/50 text-white backdrop-blur-sm"
              @click.stop="nextImage"
            >
              <Icon
                icon="mdi:chevron-right"
                class="text-2xl"
              />
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-grow flex-col p-5">
      <div class="flex-grow">
        <h6 class="text-primary">
          {{ room.title }}
        </h6>
        <p class="mt-1 font-semibold">{{ room.hotel }}</p>
        <p class="mt-0.5 text-sm font-medium text-gray-500">
          {{ room.location }}
        </p>

        <div class="mt-4 flex flex-wrap items-center space-x-4 text-gray-600">
          <div
            v-for="amenity in room.amenities"
            :key="amenity"
            class="flex items-center space-x-1 text-sm"
          >
            <Icon :icon="amenity.icon" />
            <span>{{ amenity.name }}</span>
          </div>
          <span
            class="cursor-pointer text-sm font-semibold text-blue-500 transition hover:text-blue-600"
          >
            More+
          </span>
        </div>
      </div>

      <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3">
          <span
            class="order-2 mt-2 rounded-full border border-red-200 bg-red-100 px-3 py-1 text-sm font-semibold text-red-600 sm:order-1 sm:mt-0"
          >
            {{ room.discount }}% off
          </span>
          <div class="order-1 flex items-baseline space-x-2 sm:order-2">
            <span class="text-3xl font-bold text-gray-900"> ${{ room.price }} </span>
            <span class="text-lg text-gray-400 line-through">
              ${{ Math.round(room.price / (1 - room.discount / 100)) }}
            </span>
          </div>
        </div>

        <button
          :disabled="!!selectedText"
          :class="[
            'flex-center rounded-lg px-6 py-2.5 text-base font-semibold whitespace-nowrap transition duration-150 ease-in-out',
            selectedText === 'Booked!'
              ? 'bg-green-500 text-white shadow-md hover:bg-green-600'
              : 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 disabled:cursor-wait disabled:opacity-70',
            'shadow-xl',
          ]"
          @click="selectRoom"
        >
          <Icon
            v-if="selectedText === 'Selecting...'"
            icon="mdi:loading"
            class="mr-2 inline text-lg"
            :class="{ 'animate-spin': selectedText === 'Selecting...' }"
          />
          {{ selectedText || 'SELECT ROOM' }}
        </button>
      </div>
    </div>
  </div>

  <!-- Usage -->
  <!-- <RoomCard
    :room="{
      title: 'Deluxe Suite',
      imageUrls: [
        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
      ],
      discount: 20,
      price: 500,
      hotel: 'Grand Hotel',
      location: 'City Center',
      amenities: [
        { name: 'Parking', icon: 'mdi:car' },
        { name: 'Wi-Fi', icon: 'mdi:wifi' },
        { name: 'Attached Bathroom', icon: 'mdi:bathtub' },
        { name: 'CCTV Cameras', icon: 'mdi:cctv' },
      ],
    }"
  /> -->
</template>

<script setup>
import { Icon } from '@iconify/vue';
import { ref } from 'vue';

const props = defineProps({
  room: { type: Object, required: true },
});

const activeImageIndex = ref(0);
const selectedText = ref('');

const nextImage = () => {
  activeImageIndex.value = (activeImageIndex.value + 1) % props.room.imageUrls.length;
};

const prevImage = () => {
  activeImageIndex.value =
    (activeImageIndex.value - 1 + props.room.imageUrls.length) % props.room.imageUrls.length;
};

const selectRoom = () => {
  selectedText.value = `Selecting...`;

  setTimeout(() => {
    selectedText.value = 'Booked!';
  }, 3000);
};
</script>
