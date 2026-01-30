<template>
  <div>
    <Basetable
      title="Facilites"
      :columns="tableColumns"
      :rows="facilites"
      server-side
      :total-items="total"
      :per-page="perPage"
      show-delete
      show-search
      show-add
      :show-download="false"
      show-edit
      :admin-login="false"
      :show-view="false"
      @search="handleSearch"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
      @open-add-modal="handleAdd"
      @open-edit-modal="handleEdit"
      @delete="handleDelete"
      @sort="handleSort"
    />

    <BaseModal
      v-model="isOpen"
      :title="form?.id ? 'Edit Facilies' : 'Facility'"
      width="max-w-xl"
      @save="handleSubmit"
    >
      <div class="grid grid-cols-2 gap-4">
        <BaseInput
          ref="nameInput"
          v-model="form.name"
          label="Facility Name"
          width="full"
          required
          class="col-span-2"
          placeholder="Enter Name"
        />

        <div class="col-span-2">
          <label class="text-xs font-bold mt-4 mb-1">
            Pick an Icon
            <span class="text-red-500">*</span>
          </label>
          <div
            class="relative flex items-center"
            @click="showMenu = !showMenu"
          >
            <input
              v-model="editingWidget.icon"
              type="text"
              :class="[
                'flex-1 outline-none border p-2.5 rounded-md text-sm placeholder:text-xs focus:outline-none focus:ring-0 transition duration-300 ease focus:border-blue-900',
                errors.icon ? 'border-red-500' : 'border-gray-300',
              ]"
              readonly
              @focus="clearError('icon')"
            />
            <Icon
              v-if="editingWidget.icon"
              :icon="editingWidget.icon"
              class="ml-3 text-xl text-blue-900"
            />
          </div>
          <p
            v-if="errors.icon"
            class="text-xs text-red-500 mt-1"
          >
            {{ errors.icon }}
          </p>

          <div
            v-if="showMenu"
            class="z-10 w-full mt-2 bg-white border rounded-lg shadow-lg"
          >
            <IconPicker @select-icon="handleIconSelect" />
          </div>
        </div>
        <BaseInput
          ref="descriptionInput"
          v-model="form.description"
          label="Description"
          width="full"
          required
          multiline=""
          :rows="3"
          class="col-span-2"
          placeholder="Description"
        />
        <BaseSelect
          ref="statusInput"
          v-model="form.status"
          label="Status"
          :options="[
            { label: 'Active', value: 1 },
            { label: 'Inactive', value: 0 },
          ]"
          width="full"
          required
          class="col-span-2"
          placeholder="Select status"
        />
      </div>
    </BaseModal>
    <DeleteModal
      v-model="isOpenDelete"
      title="Delete Facility"
      message="Are you sure you want to delete this facility?"
      warning="This action cannot be undone. Please confirm that you want to delete this facility."
      action="delete"
      @confirm="handleDeleteData"
    />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Basetable from '../../../components/global/Basetable.vue';
import BaseModal from '../../../components/global/BaseModal.vue';
import BaseInput from '../../../components/global/BaseInput.vue';
import BaseSelect from '../../../components/global/BaseSelect.vue';
import DeleteModal from '../../../components/global/DeleteModal.vue';
import adminService from '../../../services/adminService';
import IconPicker from '../../../components/admin/Facility/IconPicker.vue';

const isOpen = ref(false);
const total = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref('');
const facilites = ref([]);
const editingWidget = ref({});
const errors = ref({});
const showMenu = ref(false);
const orderBy = ref('id');
const orderDirection = ref('asc');

const tableColumns = [
  { label: 'name', key: (row) => row?.name, sortable: true },
  { label: 'icon', key: 'icon', sortable: true },
  { label: 'description', key: 'description', sortable: true },
  { label: 'status', key: 'status', sortable: true },
];

const fetchFacilites = async () => {
  const res = await adminService.fetchFacilites({
    page: currentPage.value,
    per_page: perPage.value,
    search: currentSearch.value,
    orderBy: orderBy.value,
    sort: orderDirection.value,
  });
  if (res.status) {
    facilites.value = res.data.data;
    total.value = res.data.total || 0;
  }
};

const handleSort = (sortData) => {
  orderBy.value = sortData.key;
  orderDirection.value = sortData.order;
  fetchFacilites();
};

const handleSearch = (term) => {
  currentSearch.value = term;
  currentPage.value = 1;
  fetchFacilites();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  fetchFacilites();
};

const handleEdit = (facility) => {
  isOpen.value = true;
  form.value = { ...facility };
  editingWidget.value.icon = facility.icon;
};

const handleAdd = () => {
  isOpen.value = true;
  form.value = {};
};

const handlePerPageChange = (size) => {
  perPage.value = size;
  currentPage.value = 1;
  fetchFacilites();
};

const form = ref({
  name: '',
  icon: null,
  description: '',
  status: '',
});

const nameInput = ref(null);
const descriptionInput = ref(null);
const statusInput = ref(null);

const handleSubmit = async () => {
  const inputs = [nameInput, descriptionInput, statusInput];

  const allValid = inputs.every((input) => input.value.validate());

  if (!validateForm()) {
    return;
  }
  if (!allValid) {
    return;
  }

  let res;

  if (form.value.id) {
    res = await adminService.updateFacility(form.value);
  } else {
    res = await adminService.addFacility(form.value);
  }

  if (res?.status) {
    isOpen.value = false;
    fetchFacilites();
  }
};

const isOpenDelete = ref(false);
const deleteFacility = ref(null);

const handleDelete = (facility) => {
  deleteFacility.value = facility;
  isOpenDelete.value = true;
};

const handleDeleteData = async () => {
  const id = deleteFacility.value;
  const res = await adminService.deleteFacility(id);
  if (res.status) {
    fetchFacilites();
    isOpenDelete.value = false;
  }
};

const handleIconSelect = (icon) => {
  const iconString = `fa-${icon.style}:${icon.id}`;

  form.value.icon = iconString;
  editingWidget.value.icon = iconString;

  showMenu.value = false;
};

function clearError(field) {
  errors.value[field] = '';
}

const validateForm = () => {
  errors.value = { icon: '' };
  let valid = true;

  if (!form.value.icon) {
    errors.value.icon = 'Icon is required';
    valid = false;
  }

  return valid;
};

onMounted(() => {
  fetchFacilites();
});
</script>
