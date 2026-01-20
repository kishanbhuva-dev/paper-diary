<template>
  <div>
    <!-- Total Summary Box -->
    <div
      class="bg-white border border-teal-200 rounded-lg p-4 mb-4 flex justify-between items-center"
    >
      <span class="text-teal-600 font-medium">Booking Total Per Month</span>
      <span class="text-teal-600 font-semibold"
        >Total: £{{
          totalAmount.toLocaleString('en-GB', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          })
        }}</span
      >
    </div>

    <ChartComponent
      :series="chartSeries"
      :categories="chartCategories"
      :booking-count-data="bookingCountData"
      type="bar"
      :height="400"
      :colors="['#8B5CF6']"
      :show-legend="false"
      :show-data-labels="false"
      animated
      :width="chartCategories.length === 1 ? '10%' : '100%'"
    />
  </div>
</template>

<script setup>
import ChartComponent from '../../common/ChartComponent.vue';
import { computed } from 'vue';

const props = defineProps({
  chartData: {
    type: Array,
    required: true,
  },
});

const chartSeries = computed(() => {
  if (!props.chartData || !Array.isArray(props.chartData)) {
    return [];
  }

  // Get sorted categories first
  const sortedCategories = chartCategories.value;

  // Create data map for easy lookup
  const amountDataMap = {};
  props.chartData.forEach((item) => {
    amountDataMap[item.x] = item.y;
  });

  // Map data according to sorted categories
  const chartData = sortedCategories.map((date) => amountDataMap[date] || 0);

  return [
    {
      name: '',
      data: chartData,
    },
  ];
});

// Add this computed property after chartSeries
const bookingCountData = computed(() => {
  if (!props.chartData || !Array.isArray(props.chartData)) {
    return [];
  }

  // Get sorted categories first
  const sortedCategories = chartCategories.value;

  // Create data map for booking counts
  const countDataMap = {};
  props.chartData.forEach((item) => {
    countDataMap[item.x] = item.z;
  });

  // Map data according to sorted categories
  return sortedCategories.map((date) => countDataMap[date] || 0);
});

const chartCategories = computed(() => {
  if (!props.chartData || !Array.isArray(props.chartData)) {
    return [];
  }

  // Extract all x values (dates) from the data
  const allDates = props.chartData.map((item) => item.x);

  // Sort dates properly (convert to Date objects for accurate sorting)
  return allDates.sort((a, b) => {
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
