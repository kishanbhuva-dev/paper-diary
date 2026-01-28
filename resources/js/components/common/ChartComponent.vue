<template>
  <div class="chart-container w-full">
    <div
      v-if="title"
      class="mb-4 sm:mb-6 md:mb-8"
    >
      <h3 class="text-base sm:text-lg md:text-xl font-bold text-slate-900 tracking-tight">
        {{ title }}
      </h3>
      <p
        v-if="subtitle"
        class="text-xs sm:text-sm text-slate-500 font-medium leading-relaxed mt-1"
      >
        {{ subtitle }}
      </p>
    </div>

    <div
      class="chart-wrapper"
      :style="{ height: height + 'px' }"
    >
      <VueApexCharts
        :options="chartOptions"
        :series="series"
        :type="type"
        :height="height"
        :width="width"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
  type: { type: String, default: 'bar' },
  series: { type: Array, required: true },
  categories: { type: Array, default: () => [] },
  bookingCountData: { type: Array, default: () => [] },
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  height: { type: Number, default: 300 },
  width: { type: String, default: '100%' },
  colors: { type: Array, default: () => ['#7C3AED'] },
  showLegend: { type: Boolean, default: false },
  showDataLabels: { type: Boolean, default: false },
  showGrid: { type: Boolean, default: true },
  showToolbar: { type: Boolean, default: false },
  animated: { type: Boolean, default: true },
  customOptions: { type: Object, default: () => ({}) },
});

const chartOptions = computed(() => ({
  chart: {
    type: props.type,
    fontFamily: 'Inter, sans-serif',
    toolbar: { show: props.showToolbar },
    animations: {
      enabled: props.animated,
      speed: 600,
      animateGradually: { enabled: true, delay: 150 },
    },
    sparkline: { enabled: false },
  },
  colors: props.colors,
  fill: {
    type: 'gradient',
    gradient: {
      shade: 'light',
      type: 'vertical',
      shadeIntensity: 0.3,
      gradientToColors: ['#A78BFA'], // Fades from Violet to Lavender
      inverseColors: false,
      opacityFrom: 0.95,
      opacityTo: 0.85,
      stops: [0, 100],
    },
  },
  dataLabels: { enabled: props.showDataLabels },
  stroke: { show: true, width: 2, colors: ['transparent'] },
  grid: {
    show: props.showGrid,
    borderColor: '#f1f5f9',
    strokeDashArray: 6,
    xaxis: { lines: { show: false } },
    padding: { top: 0, right: 0, bottom: 0, left: 10 },
  },
  tooltip: {
    theme: 'dark',
    custom({ series, seriesIndex, dataPointIndex, w }) {
      const val = series[seriesIndex][dataPointIndex];
      const count = props.bookingCountData?.[dataPointIndex] ?? 0;
      return `
          <div class="px-4 py-3 bg-slate-900 shadow-2xl rounded-xl border border-slate-800">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">${w.globals.labels[dataPointIndex]}</div>
            <div class="flex items-center gap-3 mb-1">
              <span class="w-2 h-2 rounded-full bg-violet-500"></span>
              <span class="text-xs text-slate-300">Revenue: <b class="text-white text-sm ml-1">£${val.toLocaleString()}</b></span>
            </div>
            <div class="flex items-center gap-3">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              <span class="text-xs text-slate-300">Bookings: <b class="text-white text-sm ml-1">${count}</b></span>
            </div>
          </div>
        `;
    },
  },
  xaxis: {
    categories: props.categories,
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: {
      style: { colors: '#94a3b8', fontSize: '12px', fontWeight: 600 },
      hideOverlappingLabels: true,
      trim: true,
    },
  },
  yaxis: {
    labels: {
      style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 },
      formatter: (val) => (val >= 1000 ? `£${(val / 1000).toFixed(0)}k` : `£${val}`),
    },
  },
  plotOptions: {
    bar: {
      borderRadius: 8,
      columnWidth: '40%',
      endingShape: 'rounded',
      dataLabels: { position: 'top' },
    },
  },
  responsive: [
    {
      breakpoint: 480,
      options: {
        chart: { height: 280 },
        plotOptions: { bar: { columnWidth: '65%', borderRadius: 3 } },
        xaxis: { labels: { rotate: -45, style: { fontSize: '9px' } } },
        yaxis: { labels: { style: { fontSize: '9px' } } },
      },
    },
    {
      breakpoint: 640,
      options: {
        chart: { height: 320 },
        plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
        xaxis: { labels: { rotate: -35, style: { fontSize: '10px' } } },
        yaxis: { labels: { style: { fontSize: '10px' } } },
      },
    },
    {
      breakpoint: 768,
      options: {
        chart: { height: 360 },
        plotOptions: { bar: { columnWidth: '50%', borderRadius: 6 } },
        xaxis: { labels: { rotate: -25, style: { fontSize: '11px' } } },
        yaxis: { labels: { style: { fontSize: '11px' } } },
      },
    },
  ],
  ...props.customOptions,
}));
</script>

<style scoped>
.chart-container {
  padding: 0;
}
.chart-wrapper {
  width: 100%;
}
/* Professional smooth-in animation for bars */
:deep(.apexcharts-bar-area) {
  transition: all 0.3s ease;
}
:deep(.apexcharts-bar-area:hover) {
  filter: brightness(1.1);
}
</style>
