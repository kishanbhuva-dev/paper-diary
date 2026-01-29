<template>
  <div class="max-w-3xl sm:shadow-md rounded-lg p-2 sm:p-6 my-10 mx-4 sm:mx-auto">
    <h2 class="text-2xl font-semibold text-gray-800 mb-1">Create an Account</h2>
    <p class="text-gray-500 mb-8 text-sm">Register now to access your Paper Diary account.</p>

    <form
      class="sm:grid sm:grid-cols-2 gap-5"
      @submit.prevent="handleRegister"
    >
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
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1"> Role </label>
        <div class="relative">
          <Icon
            icon="lucide:users"
            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
          />
          <select
            v-model="form.role"
            class="w-full appearance-none rounded-lg border border-gray-300 py-[8.3px] pl-10 pr-4 text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 text-sm"
          >
            <option
              class=""
              value="user"
            >
              User
            </option>
            <option value="owner">Owner</option>
          </select>
          <Icon
            icon="lucide:chevron-down"
            class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"
          />
        </div>
      </div>
      <BaseInput
        ref="passwordInput"
        v-model="form.password"
        label="Password"
        type="password"
        autocomplete="new-password"
        width="full"
        required
        :min-length="6"
        icon="lucide:lock"
        placeholder="••••••••"
      />
      <BaseInput
        ref="confirmPasswordInput"
        v-model="form.confirm_password"
        label="Confirm Password"
        type="password"
        autocomplete="new-password"
        width="full"
        required
        :pattern="new RegExp(`^${form.password}$`)"
        custom-error="Passwords do not match."
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
        ref="cityInput"
        v-model="form.city"
        label="City"
        width="full"
        required
        icon="mdi:city"
        placeholder="City"
      />
      <BaseInput
        ref="countryInput"
        v-model="form.country"
        label="Country"
        width="full"
        required
        icon="lucide:globe"
        placeholder="United States"
      />
      <BaseInput
        ref="postcodeInput"
        v-model="form.postcode"
        label="Postcode"
        width="full"
        required
        class="col-span-2"
        icon="lucide:map-pin"
        placeholder="12345"
      />
      <BaseInput
        ref="phoneInput"
        v-model="form.phone"
        label="Phone"
        type="phone"
        width="full"
        required
        icon="lucide:phone"
        placeholder="(123) 456-7890"
      />
      <BaseInput
        ref="telephoneInput"
        v-model="form.telephone"
        label="telephone"
        type="phone"
        width="full"
        icon="lucide:phone"
        placeholder="(123) 456-7890"
      />

      <div class="col-span-2 pt-1">
        <button
          type="submit"
          class="btn-primary w-full py-1.5"
        >
          Register
        </button>
      </div>
    </form>
    <p class="text-center text-gray-500 text-sm mt-6">
      Already have an account?
      <router-link
        to="/login"
        class="text-primary font-medium hover:underline"
      >
        Log in
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import authService from '../../services/authService';
import BaseInput from '../../components/global/BaseInput.vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  password: '',
  confirm_password: '',
  role: 'user',
  address: '',
  address2: '',
  city: '',
  country: '',
  postcode: '',
  phone: '',
});

const firstNameInput = ref(null);
const lastNameInput = ref(null);
const emailInput = ref(null);
const passwordInput = ref(null);
const confirmPasswordInput = ref(null);
const addressInput = ref(null);
const cityInput = ref(null);
const countryInput = ref(null);
const postcodeInput = ref(null);
const phoneInput = ref(null);
const telephoneInput = ref(null);

const handleRegister = async () => {
  const inputs = [
    firstNameInput,
    lastNameInput,
    emailInput,
    passwordInput,
    confirmPasswordInput,
    addressInput,
    cityInput,
    countryInput,
    postcodeInput,
    phoneInput,
  ];

  const allValid = inputs.every((input) => input.value.validate());

  if (allValid) {
    const res = await authService.register(form.value);
    if (res.status) {
      router.push({ name: 'login' });
    }
  }
};
</script>
