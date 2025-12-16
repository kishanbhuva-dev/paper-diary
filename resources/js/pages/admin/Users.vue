<template>
  <div>
    <Basetable
      title="User Management"
      :columns="tableColumns"
      :rows="users"
      :server-side="true"
      :total-items="total"
      :per-page="perPage"
      @search="handleSearch"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
      @open-add-modal="handleAdd"
      @open-edit-modal="handleEdit"
      @delete="handleDelete"
      :showDelete="true"
      :show-search="true"
      :showAdd="true"
      :showDownload="true"
      :showEdit="true"
    />
    <BaseModal
      :title="form?.id ? 'Edit User' : 'Add User'"
      v-model="isOpen"
      width="max-w-xl"
      @save="handleSubmit"
    >
      <div class="grid grid-cols-2 gap-4">
        <BaseInput
          ref="firstNameInput"
          label="First Name"
          v-model="form.firstName"
          width="full"
          required
          icon="lucide:user"
          placeholder="John"
        />
        <BaseInput
          ref="lastNameInput"
          label="Last Name"
          v-model="form.lastName"
          width="full"
          required
          icon="lucide:user"
          placeholder="Doe"
        />
        <BaseInput
          ref="emailInput"
          label="Your Email"
          v-model="form.email"
          type="email"
          autocomplete="email"
          width="full"
          required
          icon="lucide:mail"
          placeholder="name@example.com"
        />
        <BaseInput
          ref="passwordInput"
          label="Password"
          v-model="form.password"
          type="password"
          autocomplete="new-password"
          width="full"
          :required="!form.id"
          :minLength="6"
          icon="lucide:lock"
          placeholder="••••••••"
        />
        <BaseInput
          ref="addressInput"
          label="Address"
          v-model="form.address"
          width="full"
          required
          class="col-span-2"
          icon="lucide:map-pin"
          placeholder="123 Main St"
        />
        <BaseInput
          label="Address 2"
          v-model="form.address2"
          width="full"
          class="col-span-2"
          icon="lucide:map-pin"
          placeholder="Apt/Suite"
        />
        <BaseInput
          label="City"
          v-model="form.city"
          width="full"
          icon="mdi:city"
          placeholder="City"
        />
        <BaseInput
          label="Country"
          v-model="form.country"
          width="full"
          icon="lucide:globe"
          placeholder="United States"
        />
        <BaseInput
          label="Postcode"
          v-model="form.postcode"
          width="full"
          class="col-span-2"
          icon="lucide:map-pin"
          placeholder="12345"
        />
        <BaseInput
          label="Phone"
          v-model="form.phone"
          type="phone"
          width="full"
          icon="lucide:phone"
          placeholder="(123) 456-7890"
        />
        <BaseInput
          label="telephone"
          v-model="form.telephone"
          type="phone"
          width="full"
          icon="lucide:phone"
          placeholder="(123) 456-7890"
        />
      </div>
    </BaseModal>

    <DeleteModal
      v-model="isOpenDelete"
      title="Delete User"
      message="Are you sure you want to delete this user?"
      warning="This action cannot be undone. Please confirm that you want to delete this user."
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

const isOpen = ref(false);
const perPage = ref(10);
const currentPage = ref(1);
const currentSearch = ref("");
const total = ref(0);

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

const fetchAllUsers = async () => {
  const res = await adminService.fetchAllUsers({
    page: currentPage.value,
    per_page: perPage.value,
    search: currentSearch.value,
  });
  if (res.status) {
    users.value = res.data.data;
    total.value = res.data.total || 0;
  }
};

const handleAdd = () => {
  isOpen.value = true;
  form.value = {};
};

const handleEdit = (user) => {
  isOpen.value = true;
  form.value = { ...user };
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

  if (!allValid) return;

  let res;

  if (form.value.id) {
    res = await adminService.updateUser(form.value);
  } else {
    res = await adminService.createUser(form.value);
  }

  if (res?.status) {
    isOpen.value = false;
    fetchAllUsers();
  }
};

const isOpenDelete = ref(false);
const deleteUser = ref(null);

const handleDelete = async (user) => {
  deleteUser.value = user;
  isOpenDelete.value = true;
};

const handleDeleteData = async () => {
  const res = await adminService.deleteUser({ id: deleteUser.value });
  if (res.status) {
    fetchAllUsers();
    isOpenDelete.value = false;
  }
};

const handleSearch = (term) => {
  currentSearch.value = term;
  currentPage.value = 1;
  fetchAllUsers();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  fetchAllUsers();
};

const handlePerPageChange = (size) => {
  perPage.value = size;
  currentPage.value = 1;
  fetchAllUsers();
};

const users = ref([]);

const tableColumns = [
  { label: "ID", key: "id" },
  { label: "Name", key: (row) => row?.firstName + " " + row?.lastName },
  { label: "Email", key: "email" },
  { label: "Address", key: "address" },
  { label: "Phone", key: "phone" },
  { label: "Role", key: "role" },
];

onMounted(() => {
  fetchAllUsers();
});
</script>
