<template>
  <div
    class="base-table bg-white rounded-2xl p-6 w-full box-border border border-slate-100"
  >
    <div
      class="table-header flex flex-wrap gap-4 sm:gap-6 justify-between items-center mb-6 border-b border-gray-200 pb-4 sm:flex-nowrap"
    >
      <h2
        class="text-2xl font-extrabold text-slate-800 tracking-tight shrink-0"
      >
        {{ title }}
      </h2>

      <div
        class="flex flex-wrap gap-3 w-full sm:w-auto sm:ml-auto items-center order-2 sm:order-none"
      >
        <div v-if="showSearch" class="order-2 w-full sm:w-64">
          <div class="relative">
            <input
              type="text"
              v-model="localSearchTerm"
              placeholder="Search..."
              class="block w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500 text-sm pl-10 transition duration-150 shadow-sm"
              title="Search by keyword"
            />
            <Icon
              icon="mdi:magnify"
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5"
            />
          </div>
        </div>

        <div class="table-actions flex gap-3 order-1 shrink-0">
          <button
            v-if="showAdd"
            @click="emit('open-add-modal')"
            class="flex items-center justify-center p-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 cursor-pointer transition duration-150 h-10 w-10 shadow-md shadow-blue-300"
            title="Add New"
          >
            <Icon icon="ic:round-add" class="w-6 h-6" />
          </button>

          <button
            v-if="showDownload"
            @click="emit('download')"
            class="flex items-center justify-center p-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-xl hover:bg-blue-200 cursor-pointer transition duration-150 h-10 w-10"
            title="Download"
          >
            <Icon icon="mdi:microsoft-excel" class="w-6 h-6" />
          </button>
        </div>
      </div>
    </div>

    <div
      class="overflow-x-auto rounded-xl border border-gray-200 table-wrapper"
    >
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-blue-50/70">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap"
            >
              {{ col.label }}
            </th>
            <th
              v-if="showEdit || showDelete"
              class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap"
            >
              Actions
            </th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-100">
          <tr
            v-for="item in paginatedData"
            :key="item.id"
            class="hover:bg-blue-50 transition duration-150"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-6 py-3 whitespace-nowrap text-sm text-gray-700"
            >
              {{ getNestedValue(item, col.key) }}
            </td>

            <td
              v-if="showEdit || showDelete"
              class="actions px-6 py-3 whitespace-nowrap text-center text-sm font-medium"
            >
              <button
                v-if="showEdit"
                @click="emit('open-edit-modal', item)"
                class="text-blue-600 hover:text-white cursor-pointer mr-3 p-2 rounded-full hover:bg-blue-600 transition duration-150"
                title="Edit"
              >
                <Icon icon="mdi:pencil-outline" class="w-5 h-5" />
              </button>

              <button
                v-if="showDelete"
                @click="emit('delete', item.id)"
                class="text-red-600 hover:text-white cursor-pointer p-2 rounded-full font-bold hover:bg-red-500 transition duration-150"
                title="Delete"
              >
                <Icon icon="mdi:delete-forever" class="w-5 h-5" />
              </button>
            </td>
          </tr>

          <tr v-if="!paginatedData.length">
            <td
              :colspan="columns.length + (showEdit || showDelete ? 1 : 0)"
              class="no-data px-6 py-8 text-center text-gray-500 italic"
            >
              <Icon
                icon="mdi:information-outline"
                class="inline-block w-5 h-5 mr-1 text-gray-400"
              />
              No data available matching your criteria.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      class="pagination flex flex-col sm:flex-row items-center justify-between mt-6 pt-4 border-t border-gray-200"
      v-if="totalPages > 0"
    >
      <div class="flex items-center space-x-6 order-2 sm:order-1 mt-4 sm:mt-0">
        <p class="text-sm text-gray-700 whitespace-nowrap">
          Page <span class="font-bold">{{ currentPage }}</span> of
          <span class="font-bold">{{ totalPages }}</span>
        </p>
      </div>
      <div>
        <div class="flex items-center">
          <label
            for="perPage"
            class="text-sm text-gray-700 mr-2 whitespace-nowrap font-medium"
            >Items per page:</label
          >
          <select
            id="perPage"
            v-model="perPageRef"
            class="block w-auto px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 focus:outline-none shadow-sm"
          >
            <option v-for="size in perPageOptions" :key="size" :value="size">
              {{ size }}
            </option>
          </select>
        </div>
        <div v-if="totalPages > 1" class="order-1 sm:order-2">
          <nav
            aria-label="Pagination"
            class="isolate inline-flex -space-x-px rounded-xl shadow-md"
          >
            <button
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
              :class="[
                'relative inline-flex items-center rounded-l-xl px-3 py-2 text-gray-500 border border-gray-300 bg-white',
                'hover:bg-blue-50 hover:text-blue-600 transition duration-150',
                {
                  'cursor-not-allowed text-gray-300 bg-gray-50':
                    currentPage === 1,
                },
              ]"
              aria-label="Previous Page"
            >
              <span class="sr-only">Previous</span>
              <Icon icon="mdi:chevron-left" class="w-5 h-5" />
            </button>

            <template
              v-for="(page, index) in visiblePages"
              :key="`page-${page}-${index}`"
            >
              <span
                v-if="page === '...'"
                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-500 border border-gray-300 bg-white"
              >
                ...
              </span>
              <button
                v-else
                @click="changePage(page)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 text-sm font-semibold transition duration-150 border bg-white',
                  page === currentPage
                    ? 'z-10 bg-blue-600 text-white border-blue-600 hover:bg-blue-600'
                    : 'text-gray-700 border-gray-300 hover:bg-blue-50 hover:text-blue-600',
                ]"
              >
                {{ page }}
              </button>
            </template>

            <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              :class="[
                'relative inline-flex items-center rounded-r-xl px-3 py-2 text-gray-500 border border-gray-300 bg-white',
                'hover:bg-blue-50 hover:text-blue-600 transition duration-150',
                {
                  'cursor-not-allowed text-gray-300 bg-gray-50':
                    currentPage === totalPages,
                },
              ]"
              aria-label="Next Page"
            >
              <span class="sr-only">Next</span>
              <Icon icon="mdi:chevron-right" class="w-5 h-5" />
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onBeforeUnmount } from "vue";
import { Icon } from "@iconify/vue";

// 1. DEFINE EMITS
const emit = defineEmits([
  "open-add-modal",
  "download",
  "delete",
  "open-edit-modal",
  "search",
  "page-change",
  "per-page-change",
]);

// 2. PROPS
const props = defineProps({
  title: { type: String, default: "Data Table" },
  columns: { type: Array, required: true },
  rows: { type: Array, required: true, default: () => [] },

  // Configuration
  perPage: { type: Number, default: 10 },

  // Server Side specific
  serverSide: { type: Boolean, default: false },
  totalItems: { type: Number, default: 0 },

  // Visibility
  showAdd: { type: Boolean, default: true },
  showEdit: { type: Boolean, default: true },
  showDelete: { type: Boolean, default: true },
  showDownload: { type: Boolean, default: true },
  showSearch: { type: Boolean, default: true },
});

// --- HELPERS ---
// Solves the 'owner.name' issue by traversing objects safely
const getNestedValue = (obj, path) => {
  if (!obj || !path) return "";
  return path.split(".").reduce((acc, part) => acc && acc[part], obj);
};

// --- SEARCH & DEBOUNCE ---
const localSearchTerm = ref("");
const debouncedSearchTerm = ref("");
let debounceTimeout = null;

watch(localSearchTerm, (newVal) => {
  if (debounceTimeout) clearTimeout(debounceTimeout);

  debounceTimeout = setTimeout(() => {
    debouncedSearchTerm.value = newVal;
    currentPage.value = 1; // Always reset to page 1 on search

    // If Server Side, emit search event to parent
    if (props.serverSide) {
      emit("search", newVal);
    }
  }, 300); // 300ms delay for performance
});

onBeforeUnmount(() => {
  if (debounceTimeout) clearTimeout(debounceTimeout);
});

// --- PAGINATION STATE ---
const perPageRef = ref(props.perPage || 10);
const perPageOptions = [10, 20, 50, 100];

watch(perPageRef, (newVal) => {
  currentPage.value = 1;
  if (props.serverSide) {
    emit("per-page-change", newVal);
  }
});

// --- DATA PROCESSING (HYBRID LOGIC) ---
const processedData = computed(() => {
  // IF SERVER SIDE: The API has already filtered data. Return rows as is.
  if (props.serverSide) {
    return props.rows;
  }

  // IF CLIENT SIDE: Filter locally
  if (!debouncedSearchTerm.value) {
    return props.rows;
  }
  const term = debouncedSearchTerm.value.toLowerCase().trim();
  return props.rows.filter((item) => {
    return props.columns.some((col) => {
      // --- THIS IS THE CRITICAL CHANGE ---
      let rawValue;

      if (typeof col.key === "function") {
        // If the 'key' is a function, CALL it with the current row data (item)
        // to get the computed value (e.g., "Firstname Lastname").
        rawValue = col.key(item);
      } else {
        // If the 'key' is a string (like "id" or "address.main"),
        // use the helper function to safely get the nested value.
        rawValue = getNestedValue(item, col.key);
      }
      // ------------------------------------

      if (rawValue === null || rawValue === undefined) return false;
      return String(rawValue).toLowerCase().includes(term);
    });
  });
});

// --- PAGINATION CALCULATIONS ---
const currentPage = ref(1);

const totalPages = computed(() => {
  // IF SERVER SIDE: Use totalItems from DB
  if (props.serverSide) {
    return Math.ceil(props.totalItems / perPageRef.value);
  }
  // IF CLIENT SIDE: Use local array length
  if (processedData.value.length === 0) return 0;
  return Math.ceil(processedData.value.length / perPageRef.value);
});

const paginatedData = computed(() => {
  // IF SERVER SIDE: Return all rows (since it's already a slice)
  if (props.serverSide) {
    return props.rows;
  }

  // IF CLIENT SIDE: Slice the local array
  if (processedData.value.length === 0) return [];
  const start = (currentPage.value - 1) * perPageRef.value;
  const end = start + perPageRef.value;
  return processedData.value.slice(start, end);
});

// --- NAVIGATION HANDLER ---
const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    if (props.serverSide) {
      emit("page-change", page);
    }
  }
};

// --- VISIBLE PAGES LOGIC (Standard 1 ... 5 6 7 ... 10) ---
const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const delta = 1;
  const range = [];
  const rangeWithDots = [];
  let l;

  for (let i = 1; i <= total; i++) {
    if (
      i === 1 ||
      i === total ||
      (i >= current - delta && i <= current + delta)
    ) {
      range.push(i);
    }
  }
  range.forEach((i) => {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1);
      } else if (i - l !== 1) {
        rangeWithDots.push("...");
      }
    }
    rangeWithDots.push(i);
    l = i;
  });
  return rangeWithDots;
});

// Watch for external row updates to fix potential empty page issues
watch(
  () => props.rows,
  () => {
    if (!props.serverSide) {
      const newTotal = Math.ceil(props.rows.length / perPageRef.value);
      if (currentPage.value > newTotal && newTotal > 0) {
        currentPage.value = newTotal;
      }
    }
  },
  { deep: true }
);
</script>

<style scoped>
/*Fallback for Chrome  */
.table-wrapper::-webkit-scrollbar {
  height: 10px;
  width: 10px;
}

.table-wrapper::-webkit-scrollbar-thumb {
  background-color: #a5b4fc;
  border-radius: 5px;
  border: 2px solid transparent;
  background-clip: content-box;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
  background-color: #6366f1;
}

/* Fallback for Firefox */
.table-wrapper {
  scrollbar-color: #a5b4fc #f1f5f9; /* thumb color track color */
  scrollbar-width: thin; /* makes it thinner than auto */
}
</style>
