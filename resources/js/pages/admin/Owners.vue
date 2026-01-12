<template>
  <div>
    <Basetable
      title="Owner Management"
      :columns="tableColumns"
      :rows="owners"
      :server-side="true"
      :total-items="total"
      :per-page="perPage"
      :show-delete="true"
      :show-search="true"
      :show-add="true"
      @search="handleSearch"
      :show-download="true"
      @page-change="handlePageChange"
      :show-edit="true"
      @per-page-change="handlePerPageChange"
      :show-view="false"
      @open-add-modal="handleAdd"
      admin-login-title="Login as Owner"
      @open-edit-modal="handleEdit"
      @delete="handleDelete"
      @admin-login="handleLoginAsUser"
      @sort="handleSort"
    />
    <BaseModal
      v-model="isOpen"
      :title="form?.id ? 'Edit Owner' : 'Add Owner'"
      width="max-w-xl"
      @save="handleSubmit"
    >
      <div class="grid grid-cols-2 gap-4">
        <BaseInput
          ref="firstNameInput"
          v-model="form.firstName"
          label="First Name"
          width="full"
          required
          icon="lucide:user"
          placeholder="John"
        />
        <BaseInput
          ref="lastNameInput"
          v-model="form.lastName"
          label="Last Name"
          width="full"
          required
          icon="lucide:user"
          placeholder="Doe"
        />
        <BaseInput
          ref="emailInput"
          v-model="form.email"
          label="Your Email"
          type="email"
          autocomplete="email"
          width="full"
          required
          icon="lucide:mail"
          placeholder="name@example.com"
        />
        <BaseInput
          ref="passwordInput"
          v-model="form.password"
          label="Password"
          type="password"
          autocomplete="new-password"
          width="full"
          :required="!form.id"
          :min-length="6"
          icon="lucide:lock"
          placeholder="••••••••"
        />
        <BaseInput
          ref="addressInput"
          v-model="form.address"
          label="Address"
          width="full"
          required
          class="col-span-2"
          icon="lucide:map-pin"
          placeholder="123 Main St"
        />
        <BaseInput
          v-model="form.address2"
          label="Address 2"
          width="full"
          class="col-span-2"
          icon="lucide:map-pin"
          placeholder="Apt/Suite"
        />
        <BaseInput
          v-model="form.city"
          label="City"
          width="full"
          icon="mdi:city"
          placeholder="City"
        />
        <BaseInput
          v-model="form.country"
          label="Country"
          width="full"
          icon="lucide:globe"
          placeholder="United States"
        />
        <BaseInput
          v-model="form.postcode"
          label="Postcode"
          width="full"
          class="col-span-2"
          icon="lucide:map-pin"
          placeholder="12345"
        />
        <BaseInput
          v-model="form.phone"
          label="Phone"
          type="phone"
          width="full"
          icon="lucide:phone"
          placeholder="(123) 456-7890"
        />
        <BaseInput
          v-model="form.telephone"
          label="telephone"
          type="phone"
          width="full"
          icon="lucide:phone"
          placeholder="(123) 456-7890"
        />
      </div>
    </BaseModal>

    <DeleteModal
      v-model="isOpenDelete"
      title="Delete Owner"
      message="Are you sure you want to delete this owner?"
      warning="This action cannot be undone. Please confirm that you want to delete this owner."
      action="delete"
      @confirm="handleDeleteData"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Basetable from "../../components/global/Basetable.vue";
import BaseModal from "../../components/global/BaseModal.vue";
import BaseInput from "../../components/global/BaseInput.vue";
import adminService from "../../services/adminService";
import DeleteModal from "../../components/global/DeleteModal.vue";
import { useRouter } from "vue-router";

const isOpen = ref(false);
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref("");
const total = ref(0);
const router = useRouter();
const orderBy = ref("id");
const orderDirection = ref("asc");

const form = ref({
  firstName: "",
  lastName: "",
  email: "",
  password: "",
  address: "",
});

const firstNameInput = ref(null);
const lastNameInput = ref(null);
const emailInput = ref(null);
const passwordInput = ref(null);
const addressInput = ref(null);

const fetchAllOwners = async () => {
  const res = await adminService.fetchAllOwners({
    page: currentPage.value,
    per_page: perPage.value,
    search: currentSearch.value,
    descending: orderDirection.value,       
    sortBy: orderBy.value
  });
  if (res.status) {
    owners.value = res.data.data;
    total.value = res.data.total || 0;
  }
};

const handleSort = (sortData) => {
  orderBy.value = sortData.key;
  orderDirection.value = sortData.order;
  fetchAllOwners();
}

const handleAdd = () => {
  isOpen.value = true;
  form.value = {};
};

const handleEdit = (owner) => {
  isOpen.value = true;
  form.value = { ...owner };
};

const handleSubmit = async () => {
  const inputs = [
    firstNameInput,
    lastNameInput,
    emailInput,
    passwordInput,
    addressInput,
  ];

  const allValid = inputs.every((input) => input.value.validate());

  if (!allValid) {return;}

  let res;

  if (form.value.id) {
    res = await adminService.updateOwner(form.value);
  } else {
    res = await adminService.createOwner(form.value);
  }

  if (res?.status) {
    isOpen.value = false;
    fetchAllOwners();
  }
};

const isOpenDelete = ref(false);
const deleteOwner = ref(null);

const handleDelete = async (owner) => {
  deleteOwner.value = owner;
  isOpenDelete.value = true;
};

const handleDeleteData = async () => {
  const res = await adminService.deleteOwner({ id: deleteOwner.value });
  if (res.status) {
    fetchAllOwners();
    isOpenDelete.value = false;
  }
};

const handleSearch = (term) => {
  currentSearch.value = term;
  currentPage.value = 1;
  fetchAllOwners();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  fetchAllOwners();
};

const handlePerPageChange = (size) => {
  perPage.value = size;
  currentPage.value = 1;
  fetchAllOwners();
};

const owners = ref([]);

const tableColumns = [
  { label: "Name", key: (row) => `${row?.firstName  } ${  row?.lastName}` },
  { label: "Email", key: "email" },
  { label: "Address", key: "address" },
  { label: "Phone", key: "phone" },
  { label: "Role", key: "role" },
];

const handleLoginAsUser = async (item) => {
      const {email} = item;

  if (!email) {
      console.warn("Admin login attempted without email");
      return;
  }

  const adminToken = localStorage.getItem('authToken');
  const adminUser = localStorage.getItem('user');

  const response = await adminService.loginAsOwner({ email });

  if (!response?.data?.status) {
  console.warn("Login as user failed", response);
  return;
  }
  
  const { token, user } = response.data.data;
  
  localStorage.setItem('authToken', token);
  localStorage.setItem('user', JSON.stringify(user));
  localStorage.setItem('adminToken', adminToken);
  localStorage.setItem('adminUser', adminUser);

  // router.push({ name: 'owner-dashboard' });
  // setTimeout(() => location.reload(), 1000);
   window.location.href = '/owner';
};

onMounted(() => {
  fetchAllOwners();
});
</script>
