<template>
  <div class="px-2 sm:px-3 py-4 bg-gray-50 min-h-screen">
    <div
      v-if="loading && !properties.length"
      class="fixed inset-0 bg-white/80 z-60 flex flex-col justify-center items-center backdrop-blur-sm transition-opacity duration-300"
    >
      <Icon
        icon="eos-icons:loading"
        class="w-12 h-12 text-blue-600 animate-spin"
      />
      <span class="mt-4 text-xl text-blue-700 font-semibold tracking-tight"
        >Loading properties...</span
      >
    </div>

    <div
      v-if="error"
      class="max-w-7xl mx-auto mb-6 p-4 bg-red-50 text-red-700 border border-red-100 rounded-xl flex items-start font-medium shadow-sm"
    >
      <Icon
        icon="mdi:alert-circle"
        class="w-6 h-6 mr-3 shrink-0 text-red-500"
      />
      <span class="whitespace-pre-wrap text-sm md:text-base">{{ error }}</span>
    </div>

    <Basetable
      title="Property Management"
      :columns="tableColumns"
      :rows="properties"
      server-side
      :total-items="total"
      :per-page="perPage"
      show-delete
      show-view
      show-search
      show-add
      show-download
      show-edit
      :admin-login="false"
      @view="showResources"
      @search="handleSearch"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
      @sort="handleSort"
      @open-add-modal="navigateToAdd"
      @open-edit-modal="navigateToEdit"
      @delete="confirmDelete"
    />
  </div>

  <DeleteModal
    v-model="isConfirmationModalVisible"
    title="Delete Property"
    :message="`Are you sure you want to delete the property: ${propertyNameToDelete} ?`"
    warning="This action cannot be undone and will permanently delete the property, all associated resource types, and images!"
    @confirm="handleDeleteConfirmation"
  />

  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isViewModalVisible"
      class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4"
    >
      <div
        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
        @click="isViewModalVisible = false"
      ></div>

      <div
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-200 flex flex-col max-h-[90vh] sm:max-h-[85vh] transform transition-all"
      >
        <div
          class="px-5 py-4 sm:px-8 sm:py-6 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 flex justify-between items-center shrink-0"
        >
          <div class="flex items-center gap-3 sm:gap-4">
            <div
              class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200"
            >
              <Icon
                icon="heroicons:rss-20-solid"
                class="text-xl sm:text-2xl"
              />
            </div>
            <div>
              <h3 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">
                Resource Synchronization
              </h3>
              <p class="text-[11px] sm:text-sm font-medium text-slate-500">
                iCalendar Feed Management
              </p>
            </div>
          </div>
          <button
            type="button"
            class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors active:scale-90"
            @click="isViewModalVisible = false"
          >
            <Icon
              icon="mdi:close"
              class="text-2xl"
            />
          </button>
        </div>

        <div class="p-4 sm:p-8 overflow-y-auto bg-white modal-inner-content min-h-0">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
              <span class="w-1.5 h-6 bg-blue-600 rounded-full"></span>
              <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                Available Resources
              </h4>
            </div>
            <span
              class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-full border border-slate-200 uppercase"
            >
              {{ viewModalResources.length }} Linked
            </span>
          </div>

          <div
            v-if="viewModalResources.length"
            class="grid gap-4"
          >
            <div
              v-for="res in viewModalResources"
              :key="res.id"
              class="group relative bg-slate-50/50 border border-slate-200 rounded-2xl p-4 sm:p-5 hover:border-blue-400 hover:bg-white transition-all duration-300"
            >
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                  <div
                    class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-blue-600 group-hover:border-blue-100 transition-colors shadow-sm shrink-0"
                  >
                    <Icon
                      icon="heroicons:building-office-2"
                      class="text-2xl"
                    />
                  </div>
                  <div class="min-w-0 flex-1">
                    <h5
                      class="text-[15px] font-bold text-slate-800 group-hover:text-blue-700 transition-colors truncate"
                    >
                      {{ res.name }}
                    </h5>
                    <div class="flex items-center gap-3 mt-1">
                      <div class="flex items-center gap-1">
                        <div
                          class="w-2 h-2 rounded-full animate-pulse"
                          :class="res.status ? 'bg-emerald-500' : 'bg-red-500'"
                        ></div>
                        <span class="text-xs font-semibold text-slate-500 uppercase">{{
                          res.resource_type?.name || 'Standard'
                        }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="w-full md:w-auto">
                  <button
                    type="button"
                    :class="[
                      'w-full md:w-auto px-5 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 transition-all duration-300',
                      copiedId === res.id
                        ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200'
                        : 'bg-white text-blue-600 border border-blue-200 hover:bg-blue-600 hover:text-white shadow-sm',
                    ]"
                    @click="copyIcalLink(res.id)"
                  >
                    <Icon
                      :icon="
                        copiedId === res.id
                          ? 'heroicons:check-circle-20-solid'
                          : 'heroicons:clipboard-document-list-20-solid'
                      "
                      class="text-lg"
                    />
                    <span>{{ copiedId === res.id ? 'Copied Link' : 'Copy Feed' }}</span>
                  </button>
                </div>
              </div>

              <div
                class="mt-4 border-t border-slate-100 pt-3 flex items-center gap-2 overflow-hidden"
              >
                <span class="text-[10px] font-bold text-slate-600 uppercase shrink-0"
                  >Endpoint:</span
                >
                <span class="text-[10px] font-mono text-slate-500 truncate opacity-70">
                  {{ hostOrigin }}/api/icalendar/{{ res.id }}/feed.ics
                </span>
              </div>
            </div>
          </div>

          <div
            v-else
            class="py-12 sm:py-20 flex flex-col items-center justify-center bg-slate-50/50 border-2 border-dashed border-slate-200 rounded-[2rem]"
          >
            <div
              class="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-full flex items-center justify-center shadow-sm mb-4"
            >
              <Icon
                icon="heroicons:circle-stack"
                class="text-3xl sm:text-4xl text-slate-200"
              />
            </div>
            <h5 class="text-slate-800 font-bold">No Resources Found</h5>
            <p class="text-slate-500 text-sm mt-1">There are no resources linked.</p>
          </div>
        </div>

        <div
          class="px-5 py-4 sm:px-8 sm:py-5 bg-slate-50 border-t border-slate-100 flex justify-end items-center gap-3 shrink-0"
        >
          <span
            class="mr-auto text-[10px] text-slate-400 font-medium italic hidden sm:flex items-center"
          >
            <Icon
              icon="heroicons:information-circle"
              class="mr-1 text-sm"
            />
            Feeds sync every 15 minutes.
          </span>
          <button
            type="button"
            class="w-full sm:w-auto px-8 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-all active:scale-95 shadow-sm"
            @click="isViewModalVisible = false"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import Basetable from '../../components/global/Basetable.vue';
import DeleteModal from '../../components/global/DeleteModal.vue';
import { ref, onMounted, computed } from 'vue';
import { Icon } from '@iconify/vue';
import { useRouter } from 'vue-router';
import ownerService from '../../services/ownerService';

const router = useRouter();
const loading = ref(false);
const error = ref(null);
const properties = ref([]);
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref('');

const orderBy = ref('id');
const orderDirection = ref('asc');

const isConfirmationModalVisible = ref(false);
const propertyIdToDelete = ref(null);
const propertyNameToDelete = ref('');
const total = ref(0);

const isViewModalVisible = ref(false);
const viewModalResources = ref([]);
const copiedId = ref(null);

const hostOrigin = computed(() => window.location.origin);

const truncateString = (str, maxLen = 15) => {
  if (!str) {
    return '';
  }
  const s = String(str);
  return s.length > maxLen ? `${s.substring(0, maxLen)}...` : s;
};

const tableColumns = [
  { label: 'Name', key: 'propertyName', sortable: true },
  { label: 'Address', key: (item) => truncateString(item.address, 15), sortable: true },
  { label: 'City', key: 'city', sortable: true },
  { label: 'Country', key: 'country', sortable: true },
  { label: 'Postcode', key: 'postcode', sortable: true },
  { label: 'Telephone', key: 'telephone', sortable: true },
  { label: 'Email', key: 'email', sortable: true },
  { label: 'Status', key: 'status', sortable: true },
];

const loadData = async () => {
  error.value = null;
  loading.value = true;
  try {
    const data = await ownerService.fetchProperties({
      page: currentPage.value,
      pagination: perPage.value,
      search: currentSearch.value,
      orderBy: orderBy.value,
      orderDirection: orderDirection.value,
    });
    total.value = data.total || 0;
    properties.value = data.data || [];
  } catch (err) {
    error.value =
      err.response?.status === 401
        ? 'Unauthorized. Please log in again.'
        : err.message || 'Failed to load properties';
  } finally {
    loading.value = false;
  }
};

const navigateToAdd = () => router.push({ name: 'property-wizard' });
const navigateToEdit = (item) => {
  if (item && item.id) {
    router.push({ name: 'property-wizard', params: { id: btoa(item.id) } });
  }
};

const handleSearch = (term) => {
  currentSearch.value = term;
  currentPage.value = 1;
  loadData();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  loadData();
};

const handlePerPageChange = (size) => {
  perPage.value = size;
  currentPage.value = 1;
  loadData();
};

const handleSort = (sortData) => {
  orderBy.value = sortData.key;
  orderDirection.value = sortData.order;
  loadData();
};

const handleDeleteConfirmation = async () => {
  isConfirmationModalVisible.value = false;
  if (propertyIdToDelete.value !== null) {
    await handleDelete(propertyIdToDelete.value);
    propertyIdToDelete.value = null;
    propertyNameToDelete.value = '';
  }
};

const handleDelete = async (id) => {
  error.value = null;
  try {
    const images = await ownerService.fetchPropertyImages(id);
    const imageIds = (images || []).map((i) => i.id).filter(Boolean);
    if (imageIds.length) {
      await ownerService.deletePropertyImages(imageIds);
    }
    await ownerService.deleteProperty(id);
    if (properties.value.length === 1 && currentPage.value > 1) {
      currentPage.value--;
    }
    await loadData();
  } catch (err) {
    error.value = err.response?.status === 401 ? 'Unauthorized' : err.message;
  }
};

const confirmDelete = (id) => {
  const property = properties.value.find((p) => p.id === id);
  propertyIdToDelete.value = id;
  propertyNameToDelete.value = property ? property.propertyName : 'this property';
  isConfirmationModalVisible.value = true;
};

const showResources = async (item) => {
  if (item && item.id) {
    try {
      loading.value = true;
      const existing = await ownerService.fetchResources(item.id);
      viewModalResources.value = existing || [];
      isViewModalVisible.value = true;
    } catch (err) {
      console.error(err);
      error.value = 'Could not fetch resources.';
    } finally {
      loading.value = false;
    }
  }
};

const copyIcalLink = (resourceId) => {
  const link = `${hostOrigin.value}/api/icalendar/${resourceId}/feed.ics`;
  navigator.clipboard.writeText(link).then(() => {
    copiedId.value = resourceId;
    setTimeout(() => {
      copiedId.value = null;
    }, 2000);
  });
};

onMounted(() => {
  loadData();
});
</script>

<style scoped>
/* Standard professional scrollbar for modal content */
.modal-inner-content {
  scrollbar-width: thin;
  scrollbar-color: #e2e8f0 transparent;
}
.modal-inner-content::-webkit-scrollbar {
  width: 5px;
}
.modal-inner-content::-webkit-scrollbar-track {
  background: transparent;
}
.modal-inner-content::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.modal-inner-content::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

@keyframes pulse-soft {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.6;
  }
}
.animate-pulse {
  animation: pulse-soft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
