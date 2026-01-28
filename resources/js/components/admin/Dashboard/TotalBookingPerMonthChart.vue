<template>
  <div class="w-full space-y-6">
    <div
      class="relative overflow-hidden bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 sm:gap-6 transition-all"
    >
      <div
        class="absolute -right-8 sm:-right-10 -top-8 sm:-top-10 w-32 sm:w-40 h-32 sm:h-40 bg-violet-50 rounded-full blur-3xl opacity-50 pointer-events-none"
      ></div>

      <div class="flex items-center gap-3 sm:gap-5 relative z-10 flex-shrink-0">
        <div
          class="flex h-10 w-10 sm:h-12 sm:w-12 md:h-14 md:w-14 items-center justify-center rounded-xl sm:rounded-2xl bg-violet-600 text-white shadow-lg shadow-violet-200 flex-shrink-0"
        >
          <Icon
            icon="heroicons:presentation-chart-bar"
            class="h-5 w-5 sm:h-6 sm:w-6 md:h-7 md:w-7"
          />
        </div>
        <div class="min-w-0">
          <h3
            class="text-xs sm:text-sm font-black uppercase tracking-[0.1em] text-slate-400 truncate"
          >
            Monthly Performance
          </h3>
          <p class="text-sm sm:text-lg md:text-xl font-bold text-slate-900 truncate">
            Booking Total Per Month
          </p>
        </div>
      </div>

      <div class="flex flex-col items-start md:items-end relative z-10 mt-2 md:mt-0">
        <span
          class="inline-flex items-center rounded-full bg-emerald-50 px-2 sm:px-2.5 py-0.5 text-[10px] sm:text-xs font-bold text-emerald-600 mb-1 sm:mb-2 whitespace-nowrap"
        >
          <Icon
            icon="heroicons:arrow-trending-up"
            class="mr-0.5 sm:mr-1 h-2.5 w-2.5 sm:h-3 sm:w-3"
          />
          Live Revenue
        </span>
        <span
          class="text-base sm:text-xl md:text-2xl lg:text-3xl font-black text-slate-900 tracking-tighter truncate"
        >
          £{{
            totalAmount.toLocaleString('en-GB', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            })
          }}
        </span>
      </div>
    </div>

    <div
      class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-3 sm:p-4 md:p-6 overflow-hidden"
    >
      <ChartComponent
        :series="chartSeries"
        :categories="chartCategories"
        :booking-count-data="bookingCountData"
        type="bar"
        :height="chartHeight"
        :colors="['#7C3AED']"
        :show-legend="false"
        :show-data-labels="false"
        animated
        :width="chartCategories.length === 1 ? '30%' : '100%'"
      />
    </div>
  </div>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import ChartComponent from '../../common/ChartComponent.vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
  chartData: { type: Array, required: true },
});

const chartHeight = ref(400);
const updateHeight = () => {
  if (window.innerWidth < 480) {
    chartHeight.value = 280;
  } else if (window.innerWidth < 640) {
    chartHeight.value = 320;
  } else if (window.innerWidth < 768) {
    chartHeight.value = 360;
  } else if (window.innerWidth < 1024) {
    chartHeight.value = 380;
  } else {
    chartHeight.value = 420;
  }
};

onMounted(() => {
  updateHeight();
  window.addEventListener('resize', updateHeight);
});
onUnmounted(() => window.removeEventListener('resize', updateHeight));

const chartSeries = computed(() => {
  if (!props.chartData || !Array.isArray(props.chartData)) {
    return [];
  }
  const sortedCategories = chartCategories.value;
  const amountDataMap = {};
  props.chartData.forEach((item) => {
    amountDataMap[item.x] = item.y;
  });
  return [
    { name: 'Monthly Revenue', data: sortedCategories.map((date) => amountDataMap[date] || 0) },
  ];
});

const bookingCountData = computed(() => {
  if (!props.chartData || !Array.isArray(props.chartData)) {
    return [];
  }
  const sortedCategories = chartCategories.value;
  const countDataMap = {};
  props.chartData.forEach((item) => {
    countDataMap[item.x] = item.z;
  });
  return sortedCategories.map((date) => countDataMap[date] || 0);
});

const chartCategories = computed(() => {
  if (!props.chartData || !Array.isArray(props.chartData)) {
    return [];
  }
  return props.chartData
    .map((item) => item.x)
    .sort((a, b) => {
      const dateA = new Date(a.split('-').reverse().join('-'));
      const dateB = new Date(b.split('-').reverse().join('-'));
      return dateA - dateB;
    });
});

const totalAmount = computed(() => {
  if (!props.chartData || !Array.isArray(props.chartData)) {
    return 0;
  }
  return props.chartData.reduce((total, item) => total + (parseFloat(item.y) || 0), 0);
});
</script>
