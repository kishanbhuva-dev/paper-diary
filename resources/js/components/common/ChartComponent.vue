<template>
  <div class="chart-container">
    <div
      v-if="title"
      class="chart-header mb-4"
    >
      <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
      <p
        v-if="subtitle"
        class="text-sm text-gray-600"
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
  // Chart type: 'line', 'bar', 'pie', 'area', 'donut'
  type: {
    type: String,
    default: 'line',
    validator: (value) => ['line', 'bar', 'pie', 'area', 'donut'].includes(value),
  },

  // Chart data
  series: {
    type: Array,
    required: true,
  },

  // Chart categories/labels for x-axis
  categories: {
    type: Array,
    default: () => [],
  },

  // Booking count data for tooltip
  bookingCountData: {
    type: Array,
    default: () => [],
  },

  // Chart title
  title: {
    type: String,
    default: '',
  },

  // Chart subtitle
  subtitle: {
    type: String,
    default: '',
  },

  // Chart dimensions
  height: {
    type: Number,
    default: 300,
  },

  width: {
    type: String,
    default: '100%',
  },

  // Chart colors
  colors: {
    type: Array,
    default: () => ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'],
  },

  // Chart styling options
  showLegend: {
    type: Boolean,
    default: true,
  },

  showDataLabels: {
    type: Boolean,
    default: false,
  },

  showGrid: {
    type: Boolean,
    default: true,
  },

  showToolbar: {
    type: Boolean,
    default: false,
  },

  // Animation options
  animated: {
    type: Boolean,
    default: true,
  },

  // Custom chart options
  customOptions: {
    type: Object,
    default: () => ({}),
  },
});

const chartOptions = computed(() => {
  const baseOptions = {
    chart: {
      type: props.type,
      height: props.height,
      width: props.width,
      toolbar: {
        show: props.showToolbar,
        tools: {
          download: true,
          selection: false,
          zoom: false,
          zoomin: false,
          zoomout: false,
          pan: false,
          reset: false,
        },
      },
      animations: {
        enabled: props.animated,
        easing: 'easeinout',
        speed: 800,
      },
    },
    colors: props.colors,
    dataLabels: {
      enabled: props.showDataLabels,
    },
    legend: {
      show: props.showLegend,
      position: 'top',
      horizontalAlign: 'center',
      fontSize: '12px',
      fontFamily: 'Inter, sans-serif',
      markers: {
        width: 8,
        height: 8,
        radius: 4,
      },
    },
    grid: {
      show: props.showGrid,
      borderColor: '#E5E7EB',
      strokeDashArray: 0,
      xaxis: {
        lines: {
          show: false,
        },
      },
      yaxis: {
        lines: {
          show: true,
        },
      },
    },
    stroke: {
      curve: 'smooth',
      width: 2,
    },
    fill: {
      type: 'solid',
      opacity: 0.8,
    },
    tooltip: {
      enabled: true,
      shared: true,
      intersect: false,
      style: {
        fontSize: '12px',
        fontFamily: 'Inter, sans-serif',
      },
      // y: {
      //   formatter(val) {
      //     return val ? val.toLocaleString() : '0';
      //   },
      // },
      y: {
        formatter(val, opts) {
          // Get booking count for this data point
          const bookingCount =
            props.bookingCountData && props.bookingCountData[opts.dataPointIndex]
              ? props.bookingCountData[opts.dataPointIndex]
              : 0;

          // Return exactly the format you want
          return `Total Booking: ${bookingCount}<br>Total Amount: £${val.toLocaleString()}`;
        },
      },
    },
    xaxis: {
      categories: props.categories,
      axisBorder: {
        show: false,
      },
      axisTicks: {
        show: false,
      },
      labels: {
        style: {
          colors: '#6B7280',
          fontSize: '12px',
          fontFamily: 'Inter, sans-serif',
        },
      },
    },
    yaxis: [
      {
        labels: {
          style: {
            colors: '#6B7280',
            fontSize: '12px',
            fontFamily: 'Inter, sans-serif',
          },
          formatter(val) {
            return val ? val.toLocaleString() : '0';
          },
        },
      },
    ],
    plotOptions: {
      bar: {
        borderRadius: 4,
        columnWidth: '60%',
        dataLabels: {
          position: 'top',
        },
      },
      pie: {
        donut: {
          size: '70%',
          labels: {
            show: true,
            total: {
              show: true,
              label: 'Total',
              formatter(w) {
                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
              },
            },
          },
        },
      },
    },
    responsive: [
      {
        breakpoint: 768,
        options: {
          chart: {
            height: 250,
          },
          legend: {
            position: 'bottom',
          },
        },
      },
    ],
  };

  // Merge with custom options
  return { ...baseOptions, ...props.customOptions };
});
</script>

<style scoped>
.chart-container {
  border-radius: 0.5rem;
  box-shadow: none;
  border: none;
  padding: 1rem;
}

.chart-header {
  border-bottom: none;
  padding-bottom: 0.75rem;
}

.chart-wrapper {
  width: 100%;
}

/* Custom ApexCharts styling */
:deep(.apexcharts-legend) {
  justify-content: center;
}

:deep(.apexcharts-tooltip) {
  box-shadow:
    0 10px 15px -3px rgba(0, 0, 0, 0.1),
    0 4px 6px -2px rgba(0, 0, 0, 0.05);
  border: 0;
}

:deep(.apexcharts-gridline) {
  opacity: 0.3;
}

:deep(.apexcharts-xaxis-label) {
  color: #6b7280;
}

:deep(.apexcharts-yaxis-label) {
  color: #6b7280;
}
</style>

<!-- <style scoped>
.chart-container {
  @apply rounded-lg shadow-none border border-none p-4;
}

.chart-header {
  @apply border-b border-none pb-3;
}

.chart-wrapper {
  @apply w-full;
}

/* Custom ApexCharts styling */
:deep(.apexcharts-legend) {
  @apply justify-center;
}

:deep(.apexcharts-tooltip) {
  @apply shadow-lg border-0;
}

:deep(.apexcharts-gridline) {
  @apply opacity-30;
}

:deep(.apexcharts-xaxis-label) {
  @apply text-gray-500;
}

:deep(.apexcharts-yaxis-label) {
  @apply text-gray-500;
}
</style> -->
