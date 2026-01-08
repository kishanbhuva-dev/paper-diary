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
            @click="exportToExcel"
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
              @click="col.sortable !== false ? handleSort(col) : null"
              :class="[
                'px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap',
                col.sortable !== false ? 'cursor-pointer hover:bg-blue-100 transition duration-150' : ''
              ]"
            >
              <div class="flex items-center gap-1">
                {{ col.label }}
                <template v-if="col.sortable !== false">
                  <Icon 
                    v-if="sortKey === (typeof col.key === 'function' ? col.label : col.key)" 
                    :icon="sortOrder === 'asc' ? 'mdi:sort-ascending' : 'mdi:sort-descending'" 
                    class="w-4 h-4 text-blue-600"
                  />
                  <Icon v-else icon="mdi:unfold-more-horizontal" class="w-4 h-4 text-gray-400 opacity-40" />
                </template>
              </div>
            </th>
            <th
              v-if="showEdit || showDelete || adminLogin"
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

              <template v-if="$slots[col.key]">
                <slot :name="col.key" :row="item" />
              </template>

                <template v-else-if="col.key === 'icon'">
                  <div class="w-9 h-9 flex items-center justify-center bg-blue-50 rounded-xl border border-blue-100">
                    <Icon :icon="item[col.key]" class="w-5 h-5 text-blue-600" />
                  </div>
                </template>

                <template v-else-if="col.key === 'status'">
                  <span 
                    class="px-3 py-1 rounded-full text-xs font-bold border"
                    :class="(item.status == 1 || item.status === 'Active') 
                      ? 'bg-green-50 text-green-700 border-green-200' 
                      : 'bg-red-50 text-red-700 border-red-200'"
                  >
                    {{ (item.status == 1 || item.status === 'Active') ? 'Active' : 'Inactive' }}
                  </span>
                </template>

                <template v-else>
                  {{ getCellValue(item, col) }}
                </template>
              </td>

            <td
              v-if="showEdit || showDelete || adminLogin"
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

              <button
                v-if="adminLogin"
                @click="emit('admin-login', item.id)"
                class="text-blue-600 hover:text-white cursor-pointer p-2 rounded-full font-bold hover:bg-green-500 transition duration-150"
                :title="adminLoginTitle"
              >
                <Icon icon="lucide:user-pen" class="w-5 h-5" />
              </button>
            </td>
          </tr>

          <tr v-if="!paginatedData.length">
            <td
              :colspan="columns.length + (showEdit || showDelete || adminLogin ? 1 : 0)"
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
      class="pagination flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6 pt-4 border-t border-gray-200 px-1 sm:px-0 mb-2"
      v-if="totalPages > 0"
    >
      <div
        class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-6 w-full sm:w-auto mt-2 sm:mt-0"
      >
        <p class="text-sm text-gray-700 whitespace-nowrap font-medium">
          Page <span class="font-bold">{{ currentPage }}</span> of
          <span class="font-bold">{{ totalPages }}</span>
        </p>
      </div>

      <div
        class="flex flex-col md:flex-col lg:flex-row items-center gap-4 sm:mt-0 w-full justify-center sm:justify-end"
      >
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
        <div v-if="totalPages > 1" class="w-full sm:w-auto">
          <nav
            aria-label="Pagination"
            class="isolate inline-flex -space-x-px rounded-xl md:shadow-md w-full sm:w-auto justify-center"
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
                class="hidden md:inline-flex relative items-center px-4 py-2 text-sm font-semibold text-gray-500 border border-gray-300 bg-white"
              >
                ...
              </span>
              <button
                v-else
                @click="changePage(page)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 text-sm font-semibold transition duration-150 border',
                  {
                    'hidden sm:inline-flex':
                      page !== currentPage && Math.abs(page - currentPage) > 1,
                  },
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
import * as XLSX from "xlsx";

const emit = defineEmits([
  "open-add-modal",
  "download",
  "delete",
  "open-edit-modal",
  "search",
  "page-change",
  "per-page-change",
  "admin-login",
  "sort"
]);

const props = defineProps({
  title: { type: String, default: "Data Table" },
  columns: { type: Array, required: true },
  rows: { type: Array, required: true, default: () => [] },
  perPage: { type: Number, default: 10 },
  serverSide: { type: Boolean, default: false },
  totalItems: { type: Number, default: 0 },
  showAdd: { type: Boolean, default: true },
  showEdit: { type: Boolean, default: true },
  adminLogin: { type: Boolean, default: true },
  adminLoginTitle: { type: String, default: "Login as User" },
  showDelete: { type: Boolean, default: true },
  showDownload: { type: Boolean, default: true },
  showSearch: { type: Boolean, default: true },
});

// --- SORTING ---
const sortKey = ref("");
const sortOrder = ref("asc");

const handleSort = (col) => {
  const key = typeof col.key === 'function' ? col.label : col.key;
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
  } else {
    sortKey.value = key;
    sortOrder.value = "asc";
  }
  emit("sort", { key: sortKey.value, order: sortOrder.value });
};

// --- HELPERS ---
const getNestedValue = (obj, path) => {
  if (!obj || !path) return "";
  if (typeof path !== "string") return "";
  return path.split(".").reduce((acc, part) => acc && acc[part], obj);
};

const getCellValue = (item, col) => {
  if (typeof col.key === "function") {
    const rawValue = col.key(item);
    return rawValue === null || rawValue === undefined ? "" : rawValue;
  }
  return getNestedValue(item, col.key);
};

// --- EXPORT ---
const exportToExcel = () => {
  const exportData = props.rows.map(item => {
    const row = {};
    props.columns.forEach(col => {
      if (col.key !== 'icon') {
        row[col.label] = getCellValue(item, col);
      }
    });
    return row;
  });
  const worksheet = XLSX.utils.json_to_sheet(exportData);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, "Data");
  XLSX.writeFile(workbook, `${props.title.replace(/\s+/g, '_')}.xlsx`);
  emit("download");
};

// --- SEARCH ---
const localSearchTerm = ref("");
const debouncedSearchTerm = ref("");
let debounceTimeout = null;

watch(localSearchTerm, (newVal) => {
  if (debounceTimeout) clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    debouncedSearchTerm.value = newVal;
    currentPage.value = 1;
    if (props.serverSide) emit("search", newVal);
  }, 300);
});

onBeforeUnmount(() => { if (debounceTimeout) clearTimeout(debounceTimeout); });

// --- PAGINATION ---
const perPageRef = ref(props.perPage || 10);
const perPageOptions = [10, 20, 50, 100];
const currentPage = ref(1);

watch(perPageRef, (newVal) => {
  currentPage.value = 1;
  if (props.serverSide) emit("per-page-change", newVal);
});

const processedData = computed(() => {
  if (props.serverSide) return props.rows;
  let data = [...props.rows];
  if (debouncedSearchTerm.value) {
    const term = debouncedSearchTerm.value.toLowerCase().trim();
    data = data.filter(item => props.columns.some(col => String(getCellValue(item, col)).toLowerCase().includes(term)));
  }
  if (sortKey.value) {
    data.sort((a, b) => {
      const col = props.columns.find(c => (typeof c.key === 'function' ? c.label : c.key) === sortKey.value);
      let vA = getCellValue(a, col);
      let vB = getCellValue(b, col);
      if (vA < vB) return sortOrder.value === "asc" ? -1 : 1;
      if (vA > vB) return sortOrder.value === "asc" ? 1 : -1;
      return 0;
    });
  }
  return data;
});

const totalPages = computed(() => {
  if (props.serverSide) return Math.ceil(props.totalItems / perPageRef.value);
  return Math.ceil(processedData.value.length / perPageRef.value);
});

const paginatedData = computed(() => {
  if (props.serverSide) return props.rows;
  const start = (currentPage.value - 1) * perPageRef.value;
  return processedData.value.slice(start, start + perPageRef.value);
});

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    if (props.serverSide) emit("page-change", page);
  }
};

const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const delta = 1;
  const range = [];
  const rangeWithDots = [];
  let l;
  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) range.push(i);
  }
  range.forEach((i) => {
    if (l) {
      if (i - l === 2) rangeWithDots.push(l + 1);
      else if (i - l !== 1) rangeWithDots.push("...");
    }
    rangeWithDots.push(i);
    l = i;
  });
  return rangeWithDots;
});

watch(totalPages, (newTotal) => {
  if (newTotal > 0 && currentPage.value > newTotal) currentPage.value = newTotal;
});
</script>

<style scoped>
.table-wrapper {
  scrollbar-color: #a5b4fc #f1f5f9;
  scrollbar-width: thin;
}
</style>