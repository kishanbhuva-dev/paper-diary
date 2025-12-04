<template>
  <main class="space-y-8">
    <!-- Room Selection Form -->
    <section class="bg-blue-100 py-4 sm:py-6 lg:py-8 space-y-6">
      <div class="container mx-auto">
        <div class="px-4 sm:px-6 lg:px-8">
          <form
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
          >
            <BaseSelect
              label="WHERE"
              placeholder="Select Location"
              v-model="details.location"
              :options="[{ value: 'goa', label: 'Goa, India' }]"
            />
            <BaseDatePicker label="CHECK-IN" v-model="details.checkIn" />
            <BaseDatePicker label="CHECK-OUT" v-model="details.checkOut" />
            <BaseSelect
              label="ROOM & GUESTS"
              placeholder="Select Room & Guests"
              v-model="details.roomGuests"
              :options="[{ value: '1room_3adults', label: '1 Room, 3 Adults' }]"
            />
            <BaseSelect
              label="HOTEL NAME"
              placeholder="Select Hotel"
              v-model="details.hotel"
              :options="[{ value: 'hotel_haramain', label: 'Hotel Haramain' }]"
            />
            <div class="w-full self-end-safe">
              <button
                type="button"
                class="btn-primary py-2 text-sm w-full h-fit"
              >
                Select Room
              </button>
            </div>
          </form>
        </div>
      </div>
      <div class="bg-red-500 text-white" role="alert">
        <div
          class="px-4 sm:px-6 lg:px-8 py-1 flex-center gap-x-3 max-sm:flex-wrap"
        >
          <h4 class="font-bold">SOLD OUT</h4>
          <span class="block sm:inline">
            This Property is Sold Out on 13 Nov - 15 Nov.
          </span>
        </div>
      </div>
    </section>

    <!-- Available Dates -->
    <section class="container mx-auto">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex-center flex-col">
          <h6 class="font-semibold text-lg">Available Dates</h6>
          <div class="flex items-center space-x-2 mt-2">
            <button
              class="p-1 border border-gray-200 rounded-full hover:bg-gray-100 transition duration-150 cursor-pointer"
              @click="navigateBackward"
            >
              <Icon icon="mdi:chevron-left" />
            </button>
            <div class="flex-1 grid grid-cols-7 gap-2 text-center">
              <button
                v-for="date in dateList"
                :key="date.dateString"
                @click="selectDate(date.dateString)"
                class="p-2 border border-gray-200 rounded-md transition duration-150 cursor-pointer"
                :class="{
                  'bg-green-600 text-white':
                    date.dateString === selectedDate.format('YYYY-MM-DD'),
                  'bg-white hover:bg-gray-100':
                    date.dateString !== selectedDate.format('YYYY-MM-DD'),
                  'text-gray-900':
                    date.dateString !== selectedDate.format('YYYY-MM-DD'),
                }"
              >
                <div class="font-semibold">{{ date.dayNumber }}</div>
                <div>{{ date.dayName }}</div>
              </button>
            </div>
            <button
              class="p-1 border border-gray-200 rounded-full hover:bg-gray-100 transition duration-150 cursor-pointer"
              @click="navigateForward"
            >
              <Icon icon="mdi:chevron-right" />
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Hotel Info & Gallery -->
    <section class="container mx-auto">
      <div class="px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex-between">
          <div class="space-y-2">
            <h3>San Francisco Marriott Marquis</h3>
            <div class="flex items-center text-gray-500 text-lg space-x-2">
              <Icon icon="mdi:location" />
              <span>
                110 Mission Street, San Francisco, CA 94101, United States
              </span>
            </div>
          </div>
          <div
            class="flex items-center gap-1 bg-green-700 text-white px-2.5 py-2 rounded-md font-semibold text-lg"
          >
            4.6 <Icon icon="mdi:star" />
          </div>
        </div>
        <!-- Gallery -->
        <div class="flex gap-2 h-[400px] w-full" @mouseleave="resetExpanded">
          <div
            v-for="image in images"
            :key="image.id"
            :class="getFlexGrowClass(image.id)"
            class="relative flex-1 rounded-lg overflow-hidden cursor-pointer transition-all duration-500 ease-in-out"
            @mouseover="setExpanded(image.id)"
          >
            <img
              :src="image.src"
              :alt="image.alt"
              class="object-cover w-full h-full"
            />

            <div
              v-if="image.isViewMore"
              class="absolute inset-0 bg-black/50 flex-center"
            >
              <span class="text-white font-bold">View More</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container mx-auto">
      <div class="px-4 sm:px-6 lg:px-8 grid lg:grid-cols-3 gap-8">
        <div class="space-y-8 lg:col-span-2">
          <!-- About -->
          <section>
            <h5>About</h5>
            <p class="mt-4 text-gray-700">
              Whether you are in town for business or leisure, San Francisco
              welcomes travelers to Northern California with exceptional
              service, hotel rooms and suites and a prime downtown location.
            </p>
            <button class="text-primary font-semibold mt-2">
              Show More <Icon icon="mdi:chevron-down" class="inline" />
            </button>
          </section>

          <!-- Popular Services -->
          <section>
            <h5>Popular Service</h5>
            <div class="flex flex-wrap gap-4 mt-4">
              <div
                class="flex items-center gap-2 p-3 border border-gray-400 rounded-lg text-gray-700"
              >
                <Icon icon="mdi:parking" class="text-xl" /> <span>Parking</span>
              </div>
              <div
                class="flex items-center gap-2 p-3 border border-gray-400 rounded-lg text-gray-700"
              >
                <Icon icon="mdi:bathtub-outline" class="text-xl" />
                <span>Attached Bathroom</span>
              </div>
              <div
                class="flex items-center gap-2 p-3 border border-gray-400 rounded-lg text-gray-700"
              >
                <Icon icon="mdi:cctv" class="text-xl" />
                <span>CCTV Cameras</span>
              </div>
              <div
                class="flex items-center gap-2 p-3 border border-gray-400 rounded-lg text-gray-700"
              >
                <Icon icon="mdi:wifi" class="text-xl" /> <span>Wifi</span>
              </div>
            </div>
          </section>

          <!-- Property Policies -->
          <section>
            <h5>Property Policies</h5>
            <ul class="list-disc list-inside mt-4 space-y-2 text-gray-700">
              <li>Check-in Time: 2 PM, Check-out Time: 12 PM</li>
              <li>Primary Guest should be at least 18 years of age.</li>
              <li>
                Passport, Aadhaar, Driving License and Govt. ID are accepted as
                ID proof(s).
              </li>
              <li>Pets are not allowed.</li>
              <li>
                Mandatory: Christmas Eve (December 24) Gala Dinner per adult:
                INR 14750; Christmas Eve (December 24) Gala Dinner per child:
                INR 7375 (from 6 to 12 years old); New Year's Eve (December 31)
                Gala Dinner per adult: INR 20000; New Year's Eve (December 31)
                Gala Dinner per child: INR 10000 (from 6 to 12 years old).
              </li>
              <li>
                Optional: Fee for full breakfast: approximately INR 2100 per
                person; Fee for in-room wired Internet: INR 1000 per day (rates
                may vary); Fee for wireless Internet in public areas: INR 1000
                per day (rates may vary); Airport shuttle fee: INR 4000 per
                vehicle (one-way).
              </li>
            </ul>
          </section>
        </div>

        <!-- Ratings and Reviews -->
        <aside class="border border-gray-400 rounded-lg p-6 h-fit">
          <h5>Ratings and reviews</h5>
          <div class="flex items-center gap-4 mt-4">
            <div class="bg-green-100 text-green-800 p-4 rounded-lg text-center">
              <div class="text-3xl font-bold flex-center gap-1">
                4.6 <Icon icon="mdi:star" class="text-xl" />
              </div>
              <div class="font-semibold">EXCELLENT</div>
              <div class="text-xs text-gray-500">2640 ratings</div>
            </div>
            <div class="w-full text-sm text-gray-600">
              <div class="flex items-center gap-2">
                <span>5</span>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                  <div
                    class="bg-yellow-400 h-1.5 rounded-full"
                    style="width: 70%"
                  ></div>
                </div>
                <span>70%</span>
              </div>
              <div class="flex items-center gap-2">
                <span>4</span>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                  <div
                    class="bg-yellow-400 h-1.5 rounded-full"
                    style="width: 15%"
                  ></div>
                </div>
                <span>15%</span>
              </div>
              <div class="flex items-center gap-2">
                <span>3</span>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                  <div
                    class="bg-yellow-400 h-1.5 rounded-full"
                    style="width: 8%"
                  ></div>
                </div>
                <span>8%</span>
              </div>
              <div class="flex items-center gap-2">
                <span>2</span>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                  <div
                    class="bg-yellow-400 h-1.5 rounded-full"
                    style="width: 4%"
                  ></div>
                </div>
                <span>4%</span>
              </div>
              <div class="flex items-center gap-2">
                <span>1</span>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                  <div
                    class="bg-yellow-400 h-1.5 rounded-full"
                    style="width: 3%"
                  ></div>
                </div>
                <span>3%</span>
              </div>
            </div>
          </div>
          <div class="mt-6 border-t border-gray-400 pt-6">
            <div class="flex-between">
              <div class="flex gap-3">
                <img src="/public/user-1.jpg" class="w-10 h-10 rounded-full" />
                <div>
                  <p class="font-semibold">Brontosaurus</p>
                  <div class="flex items-center text-sm">
                    <Icon icon="mdi:star" class="text-yellow-500" />
                    <Icon icon="mdi:star" class="text-yellow-500" />
                    <Icon icon="mdi:star" class="text-yellow-500" />
                    <Icon icon="mdi:star" class="text-yellow-500" />
                    <Icon icon="mdi:star" class="text-yellow-500" />
                  </div>
                </div>
              </div>
              <div class="flex gap-2">
                <button class="btn-primary p-2">
                  <Icon icon="mdi:chevron-left" />
                </button>
                <button class="btn-primary p-2">
                  <Icon icon="mdi:chevron-right" />
                </button>
              </div>
            </div>
            <p class="mt-4 text-gray-600">
              Every thing is best, no contents to need views Surest I recommend
              the oberoi properties for holidays
            </p>
            <button class="text-primary font-semibold mt-2">
              Read More...
            </button>
          </div>
        </aside>
      </div>
    </section>

    <!-- Location and Map -->
    <section class="container mx-auto px-4 sm:px-6 lg:px-8">
      <h5>Location of The Oberoi Udaivilas</h5>
      <div class="flex items-center text-gray-500 text-lg space-x-2">
        <Icon icon="mdi:location" />
        <span> Haridasji Ki Magri </span>
      </div>
      <div class="mt-4 rounded-lg flex-center">
        <img src="/public/map.png" alt="Map Image" class="w-full h-full" />
      </div>
    </section>

    <!-- Room Types -->
    <div class="bg-stone-100 py-8">
      <section class="container mx-auto px-4 sm:px-6 lg:px-8">
        <h5>Room Types</h5>
        <div class="space-y-6 mt-4">
          <div
            v-for="roomType in roomTypes"
            :key="roomType.id"
            class="grid md:grid-cols-3 gap-6 bg-white border border-gray-400 rounded-2xl p-2"
          >
            <img
              :src="roomType.image"
              class="rounded-lg object-cover aspect-video w-full h-full md:col-span-1"
            />
            <div
              class="md:col-span-2 flex flex-col sm:flex-row justify-between"
            >
              <div class="space-y-4">
                <h6 class="text-primary">{{ roomType.type }}</h6>
                <div class="font-semibold">
                  <p class="text-lg">
                    {{ roomType.hotel }}
                  </p>
                  <span class="text-gray-600">{{ roomType.address }}</span>
                </div>
                <div
                  class="flex flex-wrap items-center gap-x-4 gap-y-1 text-gray-600"
                >
                  <span
                    v-for="feature in roomType.features"
                    :key="feature.label"
                    class="flex items-center gap-1"
                  >
                    <Icon :icon="feature.icon" /> {{ feature.label }}
                  </span>
                  <span class="font-semibold text-primary">More+</span>
                </div>
              </div>
              <div
                class="flex flex-col items-start sm:items-end justify-between mt-4 sm:mt-0"
              >
                <div class="text-right">
                  <span class="text-green-600 font-semibold">
                    {{ roomType.discount }}% off
                  </span>
                  <h5 class="inline ml-2">&pound;{{ roomType.price }}</h5>
                </div>
                <button class="btn-primary mt-2 w-full sm:w-auto">
                  Select Room
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { ref, computed } from "vue";
import { Icon } from "@iconify/vue";
import dayjs from "dayjs";
import BaseSelect from "../../components/global/BaseSelect.vue";
import BaseDatePicker from "../../components/global/BaseDatePicker.vue";

// Room Details
const details = ref({
  location: "goa",
  roomGuests: "1room_3adults",
  hotel: "hotel_haramain",
  checkIn: formatDate(dayjs()),
  checkOut: formatDate(dayjs().add(1, "day")),
});

function formatDate(dateObject) {
  return dayjs(dateObject).format("YYYY-MM-DD");
}

// Interactive Calendar
const startDate = ref(dayjs().startOf("day"));
const selectedDate = ref(dayjs().startOf("day"));

const dateList = computed(() => {
  const dates = [];
  for (let i = 0; i < 7; i++) {
    const date = startDate.value.add(i, "day");
    dates.push({
      dateString: date.format("YYYY-MM-DD"),
      dayNumber: date.date(),
      dayName: date.format("ddd"),
    });
  }
  return dates;
});

const navigateBackward = () => {
  startDate.value = startDate.value.subtract(7, "day");
};

const navigateForward = () => {
  startDate.value = startDate.value.add(7, "day");
};

const selectDate = (dateString) => {
  selectedDate.value = dayjs(dateString);
  console.log("Selected Date:", dateString);
};

// Interactive Gallery
const images = [
  {
    id: 1,
    src: "/hotel-1.jpg",
    alt: "Modern Villa with Pool",
    isViewMore: false,
  },
  {
    id: 2,
    src: "/hotel-2.jpg",
    alt: "Kitchen and Dining Area",
    isViewMore: false,
  },
  {
    id: 3,
    src: "/hotel-3.jpg",
    alt: "Staircase and Hallway",
    isViewMore: false,
  },
  {
    id: 4,
    src: "/hotel-4.jpg",
    alt: "Luxury Bedroom",
    isViewMore: false,
  },
  {
    id: 5,
    src: "/hotel-5.jpg",
    alt: "Classic Wooden Bedroom",
    isViewMore: true,
  },
];

const expandedImageId = ref(1);

const setExpanded = (id) => {
  expandedImageId.value = id;
};

const resetExpanded = () => {
  expandedImageId.value = 1;
};

const getFlexGrowClass = (id) => {
  if (id === expandedImageId.value) return "grow-2";
  return "grow-1";
};

// Room Types
const roomTypes = [
  {
    id: 1,
    type: "Deluxe Twin Room",
    image: "/hotel-1.jpg",
    hotel: "Le ROI, Udaipur Udaipur City Railway Station",
    address: "Near railway station, Shirdi",
    price: 473,
    discount: 23,
    features: [
      { label: "Parking", icon: "mdi:parking" },
      { label: "Attached Bathroom", icon: "mdi:bathtub-outline" },
      { label: "CCTV Cameras", icon: "mdi:cctv" },
      { label: "Wifi", icon: "mdi:wifi" },
    ],
  },
  {
    id: 2,
    type: "Deluxe Silver with Balcony",
    image: "/hotel-2.jpg",
    hotel: "Shahpura Bariyas House, Udaipur",
    address: "Shavri Colony, Udaipur",
    price: 474,
    discount: 24,
    features: [
      { label: "Parking", icon: "mdi:parking" },
      { label: "Attached Bathroom", icon: "mdi:bathtub-outline" },
      { label: "CCTV Cameras", icon: "mdi:cctv" },
      { label: "Wifi", icon: "mdi:wifi" },
    ],
  },
  {
    id: 3,
    type: "Golden Premium with Balcony",
    image: "/hotel-3.jpg",
    hotel: "Zone Connect by The Park Udaipur",
    address: "Near Sukhadia Circle, Panchwati, Udaipur",
    price: 475,
    discount: 25,
    features: [
      { label: "Parking", icon: "mdi:parking" },
      { label: "Attached Bathroom", icon: "mdi:bathtub-outline" },
      { label: "CCTV Cameras", icon: "mdi:cctv" },
      { label: "Wifi", icon: "mdi:wifi" },
    ],
  },
];
</script>
