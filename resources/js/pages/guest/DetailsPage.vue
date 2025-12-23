<template>
  <main class="space-y-8" v-if="propertyData">
    <section class="space-y-6 bg-blue-100 py-4 sm:py-6 lg:py-8">
      <div class="container mx-auto">
        <div class="px-4 sm:px-6 lg:px-8 flex justify-center">
          <form
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-fit max-w-full items-end justify-center"
          >
            <div class="w-full sm:w-[180px] lg:w-[220px]">
              <BaseDatePicker label="CHECK-IN" v-model="details.checkIn" />
            </div>

            <div class="w-full sm:w-[180px] lg:w-[220px]">
              <BaseDatePicker label="CHECK-OUT" v-model="details.checkOut" />
            </div>

            <div class="w-full sm:w-[180px] lg:w-[220px]">
              <BaseSelect
                label="ROOM & GUESTS"
                placeholder="Select Room & Guests"
                v-model="details.roomGuests"
                :options="[
                  { value: '1room_3adults', label: '1 Room, 3 Adults' },
                ]"
              />
            </div>

            <div class="w-full sm:w-[180px] lg:w-[220px]">
              <button
                type="button"
                @click="handleShowResources"
                class="w-full btn-primary px-2 py-2.5 text-sm uppercase font-bold h-[42px] flex items-center justify-center box-border m-0 overflow-hidden"
                :disabled="fetchingResources"
              >
                <span class="truncate text-center w-full">
                  {{ fetchingResources ? "..." : "Show Resource Types" }}
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <div
        v-if="hasSearched && availableResourceTypes.length === 0"
        class="bg-red-500 text-white"
        role="alert"
      >
        <div
          class="flex-center gap-x-3 px-4 py-1 max-sm:flex-wrap sm:px-6 lg:px-8"
        >
          <h4 class="font-bold">SOLD OUT</h4>
          <span class="block sm:inline">
            This Property is Sold Out on {{ details.checkIn }} -
            {{ details.checkOut }}
          </span>
        </div>
      </div>
    </section>

    <section
      v-if="hasSearched && availableResourceTypes.length === 0"
      class="container mx-auto"
    >
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex-center flex-col">
          <h6 class="text-lg font-semibold">Available Dates</h6>
          <div class="mt-2 flex items-center space-x-2">
            <button
              class="cursor-pointer rounded-full border border-gray-200 p-1"
              @click="navigateBackward"
            >
              <Icon icon="mdi:chevron-left" />
            </button>
            <div class="grid flex-1 grid-cols-7 gap-2 text-center">
              <button
                v-for="date in dateList"
                :key="date.dateString"
                @click="selectDate(date.dateString)"
                class="cursor-pointer rounded-md border border-gray-200 p-2"
                :class="
                  date.dateString === selectedDate.format('YYYY-MM-DD')
                    ? 'bg-green-600 text-white'
                    : 'bg-white text-gray-900'
                "
              >
                <div class="font-semibold">{{ date.dayNumber }}</div>
                <div>{{ date.dayName }}</div>
              </button>
            </div>
            <button
              class="cursor-pointer rounded-full border border-gray-200 p-1"
              @click="navigateForward"
            >
              <Icon icon="mdi:chevron-right" />
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="container mx-auto">
      <div class="space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="flex-between">
          <div class="space-y-2">
            <h3>{{ propertyData.propertyName }}</h3>
            <div class="flex items-center space-x-2 text-lg text-gray-500">
              <Icon icon="mdi:location" />
              <span>{{ propertyData.address }}, {{ propertyData.city }}</span>
            </div>
          </div>
          <div
            class="flex items-center gap-1 rounded-md bg-green-700 px-2.5 py-2 text-lg font-semibold text-white"
          >
            4.6 <Icon icon="mdi:star" />
          </div>
        </div>
        <div class="flex h-[400px] w-full gap-2" @mouseleave="resetExpanded">
          <div
            v-for="(image, index) in propertyImages"
            :key="image.id"
            v-show="index < 5"
            @click="openCarousel(index)"
            :class="getFlexGrowClass(image.id)"
            class="relative flex-1 cursor-pointer overflow-hidden rounded-lg transition-all duration-500 ease-in-out"
            @mouseover="setExpanded(image.id)"
          >
            <img :src="image.image" class="h-full w-full object-cover" />
            <div
              v-if="index === 4 && propertyImages.length > 5"
              class="absolute inset-0 flex-center bg-black/50"
            >
              <span class="font-bold text-white uppercase">View More</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container mx-auto">
      <div class="grid gap-8 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div class="space-y-8 lg:col-span-2">
          <section>
            <h5>About</h5>
            <div
              class="mt-4 text-gray-700 transition-all duration-300"
              :class="{ 'line-clamp-3': !isAboutExpanded }"
              v-html="propertyData.description"
            ></div>
            <button
              @click="isAboutExpanded = !isAboutExpanded"
              class="mt-2 font-semibold text-primary uppercase text-sm"
            >
              {{ isAboutExpanded ? "Show Less" : "Show More" }}
              <Icon
                :icon="isAboutExpanded ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                class="inline"
              />
            </button>
          </section>

          <section>
            <h5>Popular Service</h5>
            <div class="mt-4 flex flex-wrap gap-4">
              <div
                v-for="facility in propertyData.facilities"
                :key="facility.id"
                class="flex items-center gap-2 rounded-lg border border-gray-400 p-3 text-gray-700 font-semibold text-xs"
              >
                <Icon :icon="facility.icon" class="text-xl" />
                <span>{{ facility.name }}</span>
              </div>
            </div>
          </section>

          <section>
            <h5>Property Policies</h5>
            <ul class="mt-4 list-inside list-disc space-y-2 text-gray-700">
              <li>Check-in Time: 2 PM, Check-out Time: 12 PM</li>
              <li>Primary Guest should be at least 18 years of age.</li>
              <li>
                Passport, Aadhaar, Driving License and Govt. ID are accepted.
              </li>
              <li>Pets are not allowed.</li>
            </ul>
          </section>
        </div>

        <aside class="h-fit rounded-lg border border-gray-400 p-6">
          <h5>Ratings and reviews</h5>
          <div class="mt-4 flex items-center gap-4">
            <div class="rounded-lg bg-green-100 p-4 text-center text-green-800">
              <div class="flex-center gap-1 text-3xl font-bold">
                4.6 <Icon icon="mdi:star" class="text-xl" />
              </div>
              <div class="font-semibold uppercase">Excellent</div>
              <div class="text-xs text-gray-500">2640 ratings</div>
            </div>
            <div class="w-full text-sm text-gray-600">
              <div
                v-for="i in [5, 4, 3, 2, 1]"
                :key="i"
                class="flex items-center gap-2"
              >
                <span>{{ i }}</span>
                <div class="h-1.5 w-full rounded-full bg-gray-200">
                  <div
                    class="h-1.5 rounded-full bg-yellow-400"
                    :style="{ width: i === 5 ? '70%' : '10%' }"
                  ></div>
                </div>
                <span>{{ i === 5 ? "70%" : "10%" }}</span>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </section>

    <section class="container mx-auto px-4 py-4 sm:px-6 lg:px-8">
      <h5>Location of {{ propertyData.propertyName }}</h5>
      <div class="flex items-center space-x-2 text-lg text-gray-500">
        <Icon icon="mdi:location" />
        <span> {{ propertyData.address }} </span>
      </div>
      <div class="mt-4 flex-center rounded-lg overflow-hidden">
        <img
          src="/public/map.png"
          alt="Map Image"
          class="h-full w-full object-cover"
        />
      </div>
    </section>

    <div ref="resourcesSection" class="scroll-mt-24">
      <div v-if="availableResourceTypes.length > 0" class="bg-stone-100 py-12">
        <section class="container mx-auto px-4 sm:px-6 lg:px-8">
          <h5 class="mb-6">Available Resource Types</h5>
          <div class="space-y-6">
            <div
              v-for="roomType in availableResourceTypes"
              :key="roomType.id"
              class="grid gap-6 rounded-2xl border border-gray-400 bg-white p-3 md:grid-cols-3 hover:shadow-md transition-shadow"
            >
              <img
                :src="propertyImages[0]?.image || '/placeholder.jpg'"
                class="aspect-video h-full w-full rounded-lg object-cover"
              />
              <div
                class="flex flex-col justify-between sm:flex-row md:col-span-2 p-4"
              >
                <div class="space-y-4">
                  <h6 class="text-primary font-bold text-xl">
                    {{ roomType.name }}
                  </h6>
                  <div class="font-semibold">
                    <p class="text-lg uppercase text-gray-800">
                      {{ propertyData.propertyName }}
                    </p>
                    <span class="text-gray-500 text-sm">{{
                      propertyData.address
                    }}</span>
                  </div>
                  <div
                    class="flex flex-wrap gap-4 text-gray-400 uppercase text-[10px] font-bold"
                  >
                    <span class="flex items-center gap-1"
                      ><Icon icon="mdi:parking" /> Parking</span
                    >
                    <span class="flex items-center gap-1"
                      ><Icon icon="mdi:shower" /> Attached Bathroom</span
                    >
                    <span class="flex items-center gap-1"
                      ><Icon icon="mdi:video-closed-circuit" /> CCTV</span
                    >
                  </div>
                </div>
                <div
                  class="mt-4 flex flex-col items-start justify-between sm:mt-0 sm:items-end"
                >
                  <div class="text-right">
                    <span
                      class="text-xs text-green-600 font-bold px-2 py-1 bg-green-50 rounded"
                      >20% off</span
                    >
                    <h5 class="ml-2 block mt-2 text-2xl font-bold">
                      £{{ roomType.price }}
                    </h5>
                  </div>
                  <button
                    @click="goToBooking(roomType)"
                    class="mt-4 w-full btn-primary px-10 py-3 text-sm sm:w-auto uppercase tracking-wider"
                  >
                    Book now
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <Teleport to="body">
      <div
        v-if="isCarouselOpen"
        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/95"
      >
        <button
          @click="closeCarousel"
          class="absolute top-6 right-6 text-white hover:text-gray-300 z-[1000]"
        >
          <Icon icon="mdi:close" class="text-4xl" />
        </button>
        <button
          @click="prevImage"
          class="absolute left-4 text-white hover:bg-white/10 p-2 rounded-full z-[1000]"
        >
          <Icon icon="mdi:chevron-left" class="text-5xl" />
        </button>
        <div class="max-w-5xl max-h-[80vh] px-4 select-none text-center">
          <img
            :src="propertyImages[activeImageIndex].image"
            class="max-w-full max-h-[80vh] object-contain rounded shadow-2xl"
          />
          <p class="text-white mt-4 font-semibold">
            Image {{ activeImageIndex + 1 }} of {{ propertyImages.length }}
          </p>
        </div>
        <button
          @click="nextImage"
          class="absolute right-4 text-white hover:bg-white/10 p-2 rounded-full z-[1000]"
        >
          <Icon icon="mdi:chevron-right" class="text-5xl" />
        </button>
      </div>
    </Teleport>
  </main>

  <div v-else class="flex items-center justify-center h-screen">
    <div v-if="loading" class="flex flex-col items-center gap-4">
      <Icon icon="line-md:loading-twotone-loop" class="text-5xl text-primary" />
      <p class="font-medium text-gray-500">Loading property details...</p>
    </div>
    <p v-else class="text-red-500 font-bold text-xl uppercase tracking-widest">
      Property not found.
    </p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Icon } from "@iconify/vue";
import dayjs from "dayjs";
import { userService } from "../../services/userService";
import BaseSelect from "../../components/global/BaseSelect.vue";
import BaseDatePicker from "../../components/global/BaseDatePicker.vue";

// Router & State
const route = useRoute();
const router = useRouter();
const propertyData = ref(null);
const loading = ref(true);
const fetchingResources = ref(false);
const availableResourceTypes = ref([]);
const resourcesSection = ref(null); // Reference for scrolling
const hasSearched = ref(false);
const isAboutExpanded = ref(false);

// Date & Search Details
const details = ref({
  location: "",
  roomGuests: "1room_3adults",
  checkIn: dayjs().format("YYYY-MM-DD"),
  checkOut: dayjs().add(1, "day").format("YYYY-MM-DD"),
});

// Fetch Available Resources & Handle Scroll
const handleShowResources = async () => {
  fetchingResources.value = true;
  hasSearched.value = true;

  try {
    const params = {
      slug: route.params.slug,
      arrivalDateTime: details.value.checkIn,
      departureDateTime: details.value.checkOut,
      totalResources: 1,
    };

    const res = await userService.getAvailableResourcesTypes(params);

    if (res.data.status) {
      availableResourceTypes.value = res.data.data;

      // STEP 1: Wait for Vue to finish updating the Virtual DOM
      await nextTick();

      // STEP 2: Use a slight delay to ensure the browser has actually
      // painted the new HTML and calculated the scroll height.
      setTimeout(() => {
        if (resourcesSection.value && availableResourceTypes.value.length > 0) {
          resourcesSection.value.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }
      }, 150); // 150ms delay is the sweet spot for browser rendering
    }
  } catch (err) {
    console.error("Error fetching resources:", err);
  } finally {
    fetchingResources.value = false;
  }
};

// Booking Redirection
const goToBooking = (roomType) => {
  const bookingData = {
    property: {
      id: propertyData.value.id,
      name: propertyData.value.propertyName,
      address: propertyData.value.address,
      image: propertyImages.value[0]?.image || "",
    },
    room: roomType,
    dates: { checkIn: details.value.checkIn, checkOut: details.value.checkOut },
  };
  localStorage.setItem("pending_booking", JSON.stringify(bookingData));
  router.push({ name: "booking-summary" });
};

// Gallery & Carousel Logic
const propertyImages = computed(() => propertyData.value?.property_image || []);
const expandedImageId = ref(null);
const isCarouselOpen = ref(false);
const activeImageIndex = ref(0);

const setExpanded = (id) => (expandedImageId.value = id);
const resetExpanded = () =>
  (expandedImageId.value = propertyImages.value[0]?.id);
const getFlexGrowClass = (id) =>
  id === expandedImageId.value ? "grow-2" : "grow-1";

const openCarousel = (index) => {
  activeImageIndex.value = index;
  isCarouselOpen.value = true;
  document.body.style.overflow = "hidden";
};
const closeCarousel = () => {
  isCarouselOpen.value = false;
  document.body.style.overflow = "auto";
};
const nextImage = () => {
  activeImageIndex.value =
    (activeImageIndex.value + 1) % propertyImages.value.length;
};
const prevImage = () => {
  activeImageIndex.value =
    (activeImageIndex.value - 1 + propertyImages.value.length) %
    propertyImages.value.length;
};

// Property Details Fetching
const fetchProperty = async () => {
  try {
    const slug = route.params.slug;
    const res = await userService.getPropertyDetails(slug);
    if (res.data.status) {
      propertyData.value = res.data.data;
      if (propertyImages.value.length > 0) {
        expandedImageId.value = propertyImages.value[0].id;
      }
    }
  } catch (err) {
    console.error("Property Fetch Error:", err);
  } finally {
    loading.value = false;
  }
};

// Calendar Helper Logic
const startDate = ref(dayjs().startOf("day"));
const selectedDate = ref(dayjs().startOf("day"));
const dateList = computed(() =>
  Array.from({ length: 7 }, (_, i) => {
    const date = startDate.value.add(i, "day");
    return {
      dateString: date.format("YYYY-MM-DD"),
      dayNumber: date.date(),
      dayName: date.format("ddd"),
    };
  })
);
const navigateBackward = () =>
  (startDate.value = startDate.value.subtract(7, "day"));
const navigateForward = () => (startDate.value = startDate.value.add(7, "day"));
const selectDate = (dateString) => (selectedDate.value = dayjs(dateString));

onMounted(fetchProperty);
</script>

<style scoped>
/* Ensures the section doesn't hide behind a sticky header */
.scroll-mt-24 {
  scroll-margin-top: 6rem;
}

/* Gallery Hover Animation */
.grow-1 {
  flex: 1;
  transition: all 0.5s ease;
}
.grow-2 {
  flex: 2.5;
  transition: all 0.5s ease;
}

.flex-center {
  display: flex;
  align-items: center;
  justify-content: center;
}
.flex-between {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
