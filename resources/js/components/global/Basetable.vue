<template>
  <div
    class="base-table bg-white rounded-2xl p-6 w-full box-border border border-slate-100 shadow-sm"
  >
    <div
      class="table-header flex flex-wrap gap-4 sm:gap-6 justify-between items-center mb-6 border-b border-gray-200 pb-5 sm:flex-nowrap"
    >
      <div class="flex flex-col gap-1">
        <h2
          class="text-2xl font-extrabold text-slate-800 tracking-tight shrink-0"
        >
          {{ title }}
        </h2>
      </div>

      <div
        class="flex flex-wrap gap-3 w-full sm:w-auto sm:ml-auto items-center order-2 sm:order-none"
      >
        <div v-if="showSearch" class="order-2 w-full sm:w-64">
          <div class="relative group">
            <input
              type="text"
              v-model="localSearchTerm"
              placeholder="Search..."
              class="block w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 text-sm pl-10 transition duration-150 shadow-sm"
              title="Search by keyword"
            />
            <Icon
              icon="mdi:magnify"
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 w-5 h-5 transition-colors"
            />
          </div>
        </div>

        <div class="table-actions flex gap-3 order-1 shrink-0 relative">
          <div class="relative">
            <button
              v-if="availableFilters.length"
              @click="toggleFilterDropdown"
              :class="[
                'flex items-center justify-center p-2 rounded-xl border transition-all duration-150 h-10 w-10 active:scale-95 cursor-pointer',
                isFilterDropdownOpen || hasActiveFilters
                  ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200'
                  : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50 shadow-sm',
              ]"
              title="Toggle Filters"
            >
              <Icon
                :icon="
                  isFilterDropdownOpen ? 'mdi:filter-off' : 'mdi:filter-variant'
                "
                class="w-6 h-6"
              />
              <span
                v-if="activeFilterCount > 0"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white"
              >
                {{ activeFilterCount }}
              </span>
            </button>

            <div
              v-if="isFilterDropdownOpen"
              class="absolute right-0 mt-3 w-72 sm:w-80 bg-white border border-gray-200 rounded-2xl shadow-2xl z-[100] p-5 origin-top-right animate-in fade-in zoom-in duration-150"
            >
              <div
                class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100"
              >
                <span
                  class="text-sm font-black text-slate-800 uppercase tracking-widest"
                  >Filter Options</span
                >
                <button
                  @click="clearAllFilters"
                  class="text-[15px] font-bold text-red-500 hover:text-red-700 hover:bg-red-100 p-3 rounded-xl uppercase cursor-pointer"
                >
                  Reset
                </button>
              </div>

              <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">
                <div
                  v-for="filter in availableFilters"
                  :key="filter.key"
                  class="flex flex-col gap-1.5"
                >
                  <label
                    class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1"
                    >{{ filter.label }}</label
                  >

                  <div
                    v-if="!filter.type || filter.type === 'select'"
                    class="relative"
                  >
                    <select
                      @change="
                        (e) => handleFilterChange(filter.key, e.target.value)
                      "
                      :value="appliedFilters[filter.key] || ''"
                      class="appearance-none w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-xs font-semibold text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition-all cursor-pointer"
                    >
                      <option value="">All {{ filter.label }}</option>
                      <option
                        v-for="opt in filter.options"
                        :key="opt.value"
                        :value="opt.value"
                      >
                        {{ opt.label }}
                      </option>
                    </select>
                    <Icon
                      icon="tabler:chevron-down"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none w-4 h-4"
                    />
                  </div>

                  <input
                    v-else-if="filter.type === 'text'"
                    type="text"
                    :placeholder="`Enter ${filter.label}...`"
                    :value="appliedFilters[filter.key] || ''"
                    @input="
                      (e) => handleFilterChange(filter.key, e.target.value)
                    "
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-xs font-semibold focus:ring-4 focus:ring-blue-50 outline-none transition-all"
                  />

                  <input
                    v-else-if="filter.type === 'date'"
                    type="date"
                    :value="appliedFilters[filter.key] || ''"
                    @input="
                      (e) => handleFilterChange(filter.key, e.target.value)
                    "
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-xs font-semibold focus:ring-4 focus:ring-blue-50 outline-none transition-all"
                  />
                </div>
              </div>

              <div class="mt-5">
                <button
                  @click="isFilterDropdownOpen = false"
                  class="w-full bg-slate-800 text-white cursor-pointer text-xs font-bold py-2.5 rounded-xl hover:bg-slate-700 transition-colors uppercase tracking-widest"
                >
                  Close Filters
                </button>
              </div>
            </div>
          </div>

          <button
            v-if="showAdd"
            @click="emit('open-add-modal')"
            class="flex items-center justify-center p-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 cursor-pointer transition duration-150 h-10 w-10 shadow-md shadow-blue-300 active:scale-95"
            title="Add New"
          >
            <Icon icon="ic:round-add" class="w-6 h-6" />
          </button>

          <button
            v-if="showDownload"
            @click="exportToExcel"
            class="flex items-center justify-center p-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-xl hover:bg-blue-200 cursor-pointer transition duration-150 h-10 w-10 active:scale-95"
            title="Download"
          >
            <Icon icon="mdi:microsoft-excel" class="w-6 h-6" />
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="hasActiveFilters"
      class="mb-6 flex flex-wrap items-center gap-2 animate-in slide-in-from-top-2 duration-200"
    >
      <span
        class="text-[9px] font-black text-slate-400 uppercase tracking-widest mr-1"
        >Active:</span
      >
      <template v-for="(value, key) in appliedFilters" :key="key">
        <div
          v-if="value"
          class="flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg border border-blue-200 text-[10px] font-bold shadow-sm hover:border-blue-300 transition-all"
        >
          <span class="opacity-50 uppercase text-[8px]"
            >{{ getFilterLabel(key) }}:</span
          >
          <span>{{ getOptionLabel(key, value) }}</span>
          <button
            @click="handleFilterChange(key, '')"
            class="p-0.5 rounded-md hover:bg-red-50 hover:text-red-500 transition-colors cursor-pointer"
          >
            <Icon icon="mdi:close" class="w-3.5 h-3.5" />
          </button>
        </div>
      </template>
    </div>

    <div
      class="overflow-x-auto rounded-xl border border-gray-200 table-wrapper shadow-sm"
    >
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-blue-50/70">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">
              No.
            </th>
            <th
              v-for="col in columns"
              :key="col.key"
              @click="col.sortable !== false ? handleSort(col) : null"
              :class="[
                'px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap',
                col.sortable !== false
                  ? 'cursor-pointer hover:bg-blue-100 transition duration-150'
                  : '',
              ]"
            >
              <div class="flex items-center gap-1">
                {{ col.label }}
                <template v-if="col.sortable !== false">
                  <Icon
                    v-if="
                      sortKey ===
                      (typeof col.key === 'function' ? col.label : col.key)
                    "
                    :icon="
                      sortOrder === 'asc'
                        ? 'mdi:sort-ascending'
                        : 'mdi:sort-descending'
                    "
                    class="w-4 h-4 text-blue-600"
                  />
                  <Icon
                    v-else
                    icon="mdi:unfold-more-horizontal"
                    class="w-4 h-4 text-gray-400 opacity-40"
                  />
                </template>
              </div>
            </th>
            <th
              v-if="showView || showEdit || showDelete || adminLogin"
              class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap"
            >
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <tr
            v-for="(item, index) in paginatedData"
            :key="item.id"
            class="hover:bg-blue-50/40 transition duration-150 group"
          >
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold italic">
              {{ (currentPage - 1) * perPageRef + index + 1 }}
            </td>
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium"
            >
              <template v-if="$slots[col.key]"
                ><slot :name="col.key" :row="item"
              /></template>
              <template v-else-if="col.key === 'icon'">
                <div v-if="item[col.key]"
                  class="w-9 h-9 flex items-center justify-center bg-blue-50 rounded-xl border border-blue-100"
                >
                  <Icon :icon="item[col.key]" class="w-5 h-5 text-blue-600" />
                </div>
                <span v-else class="text-gray-300">-</span>
              </template>
              <template v-else-if="col.key === 'status'">
                <span
                  v-if="getCellValue(item, col)"
                  class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border"
                  :class="getStatusClasses(getCellValue(item, col))"
                >
                  {{ formatStatusDisplay(getCellValue(item, col)) }}
                </span>
                <span v-else class="text-gray-300">-</span>
              </template>
              <template v-else>
                <span v-if="getCellValue(item, col) !== null && getCellValue(item, col) !== undefined && getCellValue(item, col) !== ''">
                  {{ getCellValue(item, col) }}
                </span>
                <span v-else class="text-gray-300">-</span>
              </template>
            </td>
            <td
              v-if="showView || showEdit || showDelete || adminLogin"
              class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
            >
              <div class="flex items-center justify-center gap-1">
                <button
                  v-if="showView"
                  @click="emit('view', item)"
                  class="text-emerald-600 hover:text-white cursor-pointer p-2 rounded-xl hover:bg-emerald-600 transition duration-150"
                  title="View Details"
                >
                  <Icon icon="mdi:eye-outline" class="w-5 h-5" />
                </button>

                <button
                  v-if="showEdit"
                  @click="emit('open-edit-modal', item)"
                  class="text-blue-600 hover:text-white cursor-pointer p-2 rounded-xl hover:bg-blue-600 transition duration-150"
                  title="Edit"
                >
                  <Icon icon="mdi:pencil-outline" class="w-5 h-5" />
                </button>
                <button
                  v-if="showDelete"
                  @click="emit('delete', item.id)"
                  class="text-red-600 hover:text-white cursor-pointer p-2 rounded-xl font-bold hover:bg-red-500 transition duration-150"
                  title="Delete"
                >
                  <Icon icon="mdi:delete-forever" class="w-5 h-5" />
                </button>
                <button
                  v-if="adminLogin"
                  @click="emit('admin-login', item.id)"
                  class="text-blue-600 hover:text-white cursor-pointer p-2 rounded-xl font-bold hover:bg-green-500 transition duration-150"
                  :title="adminLoginTitle"
                >
                  <Icon icon="lucide:user-pen" class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!paginatedData.length">
            <td
              :colspan="
                columns.length + 1 + (showView || showEdit || showDelete || adminLogin ? 1 : 0)
              "
              class="no-data px-6 py-12 text-center text-gray-500 italic"
            >
              <div class="flex flex-col items-center justify-center gap-2">
                <Icon
                  icon="mdi:database-search-outline"
                  class="w-10 h-10 text-gray-200"
                />
                <span>No data available matching your criteria.</span>
              </div>
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
            class="block w-auto px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 shadow-sm outline-none"
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
              class="relative inline-flex items-center rounded-l-xl px-3 py-2 text-gray-500 border border-gray-300 bg-white hover:bg-blue-50 hover:text-blue-600 transition duration-150 disabled:bg-gray-50 disabled:text-gray-300 disabled:cursor-not-allowed cursor-pointer"
            >
              <Icon icon="mdi:chevron-left" class="w-5 h-5" />
            </button>
            <template v-for="(page, index) in visiblePages" :key="index">
              <span
                v-if="page === '...'"
                class="hidden md:inline-flex relative items-center px-4 py-2 text-sm font-semibold text-gray-500 border border-gray-300 bg-white"
                >...</span
              >
              <button
                v-else
                @click="changePage(page)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 text-sm font-semibold transition duration-150 border cursor-pointer',
                  page === currentPage
                    ? 'z-10 bg-blue-600 text-white border-blue-600'
                    : 'text-gray-700 border-gray-300 hover:bg-blue-50 hover:text-blue-600',
                ]"
              >
                {{ page }}
              </button>
            </template>
            <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="relative inline-flex items-center rounded-r-xl px-3 py-2 text-gray-500 border border-gray-300 bg-white hover:bg-blue-50 hover:text-blue-600 transition duration-150 disabled:bg-gray-50 disabled:text-gray-300 disabled:cursor-not-allowed cursor-pointer"
            >
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
  "sort",
  "filter-change",
  "view",
]);

const props = defineProps({
  title: { type: String, default: "Data Table" },
  columns: { type: Array, required: true },
  rows: { type: Array, required: true, default: () => [] },
  perPage: { type: Number, default: 10 },
  serverSide: { type: Boolean, default: false },
  totalItems: { type: Number, default: 0 },
  showAdd: { type: Boolean, default: true },
  showView: { type: Boolean, default: true },
  showEdit: { type: Boolean, default: true },
  adminLogin: { type: Boolean, default: true },
  adminLoginTitle: { type: String, default: "Login as User" },
  showDelete: { type: Boolean, default: true },
  showDownload: { type: Boolean, default: true },
  showSearch: { type: Boolean, default: true },
  availableFilters: { type: Array, default: () => [] },
});

// --- FILTER UI ---
const isFilterDropdownOpen = ref(false);
const appliedFilters = ref({});
const activeFilterCount = computed(
  () =>
    Object.values(appliedFilters.value).filter((v) => v !== "" && v !== null)
      .length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const toggleFilterDropdown = () => {
  isFilterDropdownOpen.value = !isFilterDropdownOpen.value;
};

const handleFilterChange = (key, value) => {
  appliedFilters.value = { ...appliedFilters.value, [key]: value };
  currentPage.value = 1;
  emit("filter-change", appliedFilters.value);
};

const clearAllFilters = () => {
  appliedFilters.value = {};
  currentPage.value = 1;
  emit("filter-change", {});
};

const getFilterLabel = (key) =>
  props.availableFilters.find((f) => f.key === key)?.label || key;
const getOptionLabel = (key, value) => {
  const filter = props.availableFilters.find((f) => f.key === key);
  if (!filter || !filter.options) return value;
  return (
    filter.options.find((o) => String(o.value) === String(value))?.label ||
    value
  );
};

// --- STATUS LOGIC ---
const getStatusClasses = (val) => {
  const s = String(val).toLowerCase();
  const success = ["1", "active", "confirm", "paid", "success", "confirmed"];
  const danger = ["0", "inactive", "cancelled", "unpaid", "failed"];
  if (success.includes(s)) return "bg-green-50 text-green-700 border-green-200";
  if (danger.includes(s)) return "bg-red-50 text-red-700 border-red-200";
  return "bg-gray-50 text-gray-600 border-gray-200";
};

const formatStatusDisplay = (val) => {
  if (val === 1 || String(val).toLowerCase() === "active") return "Active";
  if (val === 0 || String(val).toLowerCase() === "inactive") return "Inactive";
  return val;
};

const getCellValue = (item, col) => {
  if (typeof col.key === "function") return col.key(item);
  if (!col.key) return "";
  return col.key.split(".").reduce((acc, part) => acc && acc[part], item);
};

// --- SORT, SEARCH & PAGINATION ---
const sortKey = ref("");
const sortOrder = ref("asc");
const localSearchTerm = ref("");
const debouncedSearchTerm = ref("");
let debounceTimeout = null;

const handleSort = (col) => {
  const key = typeof col.key === "function" ? col.label : col.key;
  sortOrder.value =
    sortKey.value === key && sortOrder.value === "asc" ? "desc" : "asc";
  sortKey.value = key;
  emit("sort", { key: sortKey.value, order: sortOrder.value });
};

watch(localSearchTerm, (newVal) => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    debouncedSearchTerm.value = newVal;
    currentPage.value = 1;
    if (props.serverSide) emit("search", newVal);
  }, 400);
});

const perPageRef = ref(props.perPage || 10);
const perPageOptions = [10, 20, 50, 100];
const currentPage = ref(1);

const processedData = computed(() => {
  if (props.serverSide) return props.rows;
  let data = [...props.rows];

  Object.keys(appliedFilters.value).forEach((key) => {
    if (appliedFilters.value[key]) {
      data = data.filter((item) => {
        const itemVal = String(item[key] || "").toLowerCase();
        const filterVal = String(appliedFilters.value[key]).toLowerCase();
        return itemVal.includes(filterVal);
      });
    }
  });

  if (sortKey.value) {
    data.sort((a, b) => {
      const col = props.columns.find(
        (c) => (typeof c.key === "function" ? c.label : c.key) === sortKey.value
      );
      let vA = String(getCellValue(a, col) || "");
      let vB = String(getCellValue(b, col) || "");
      return sortOrder.value === "asc"
        ? vA.localeCompare(vB)
        : vB.localeCompare(vA);
    });
  }
  return data;
});

const totalPages = computed(() => {
  const count = props.serverSide
    ? props.totalItems
    : processedData.value.length;
  return Math.ceil(count / perPageRef.value) || 0;
});

const paginatedData = computed(() => {
  if (props.serverSide) return props.rows;
  const start = (currentPage.value - 1) * perPageRef.value;
  return processedData.value.slice(start, start + perPageRef.value);
});

const changePage = (p) => {
  if (p >= 1 && p <= totalPages.value) {
    currentPage.value = p;
    if (props.serverSide) emit("page-change", p);
  }
};

const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const range = [];
  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || (i >= current - 1 && i <= current + 1))
      range.push(i);
  }
  const withDots = [];
  let last;
  range.forEach((i) => {
    if (last && i - last > 1) withDots.push("...");
    withDots.push(i);
    last = i;
  });
  return withDots;
});

const exportToExcel = () => {
  const data = props.rows.map((item) => {
    const row = {};
    props.columns.forEach((col) => {
      if (col.key !== "icon") row[col.label] = getCellValue(item, col) || "-";
    });
    return row;
  });
  const ws = XLSX.utils.json_to_sheet(data);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, "Data");
  XLSX.writeFile(wb, `${props.title.replace(/\s+/g, "_")}.xlsx`);
  emit("download");
};

onBeforeUnmount(() => clearTimeout(debounceTimeout));
watch(perPageRef, (v) => {
  currentPage.value = 1;
  if (props.serverSide) emit("per-page-change", v);
});
</script>

<style scoped>
.table-wrapper {
  scrollbar-color: #cbd5e1 #f8fafc;
  scrollbar-width: thin;
}

@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
@keyframes zoom-in {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
.animate-in {
  animation: fade-in forwards, zoom-in forwards;
}
</style>