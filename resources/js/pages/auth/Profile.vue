<template>
  <div class="w-full mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-600 to-indigo-700">
        <h3 class="text-xl font-bold text-white flex items-center gap-2">
          <Icon
            icon="mdi:account-circle-outline"
            class="text-2xl"
          />
          Personal Information
        </h3>
        <p class="text-blue-100 text-sm mt-1">
          Manage your account details and contact information
        </p>
      </div>

      <form
        class="p-6 md:p-10 space-y-8"
        @submit.prevent="handleUpdateProfile"
      >
        <div class="space-y-2">
          <div class="flex items-center gap-2 text-blue-600 font-bold border-b border-gray-50 pb-2">
            <Icon icon="mdi:badge-account-horizontal-outline" />
            <span>Basic Details</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <BaseInput
              ref="input_firstName"
              v-model="form.firstName"
              label="First Name"
              width="full"
              required
              icon="mdi:account-outline"
              placeholder="John"
            />
            <BaseInput
              ref="input_lastName"
              v-model="form.lastName"
              label="Last Name"
              width="full"
              required
              icon="mdi:account-outline"
              placeholder="Doe"
            />
            <BaseInput
              v-model="form.email"
              label="Email Address"
              width="full"
              variant="gray"
              disabled
              icon="mdi:email-outline"
            />
          </div>
        </div>

        <div class="space-y-2">
          <div class="flex items-center gap-2 text-blue-600 font-bold border-b border-gray-50 pb-2">
            <Icon icon="mdi:phone-outline" />
            <span>Contact Information</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <BaseInput
              ref="input_phone"
              v-model="form.phone"
              label="Phone Number"
              width="full"
              icon="mdi:phone"
              placeholder="+44 123 456 7890"
            />
            <BaseInput
              ref="input_telephone"
              v-model="form.telephone"
              label="Secondary Telephone"
              width="full"
              icon="mdi:phone-classic"
              placeholder="Home or office number"
            />
          </div>
        </div>

        <div class="space-y-2">
          <div class="flex items-center gap-2 text-blue-600 font-bold border-b border-gray-50 pb-2">
            <Icon icon="mdi:map-marker-outline" />
            <span>Address Details</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <BaseInput
              ref="input_address"
              v-model="form.address"
              label="Address Line 1"
              width="full"
              icon="mdi:home-outline"
              placeholder="123 Main Street"
            />
            <BaseInput
              ref="input_address2"
              v-model="form.address2"
              label="Address Line 2"
              width="full"
              icon="mdi:home-plus-outline"
              placeholder="Apartment, suite, etc."
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <BaseInput
              ref="input_city"
              v-model="form.city"
              label="City"
              width="full"
              icon="mdi:city-variant-outline"
              placeholder="London"
            />
            <BaseInput
              ref="input_postcode"
              v-model="form.postcode"
              label="Postcode"
              width="full"
              icon="mdi:mailbox-outline"
              placeholder="E1 6AN"
            />
            <BaseInput
              ref="input_country"
              v-model="form.country"
              label="Country"
              width="full"
              icon="mdi:earth"
              placeholder="United Kingdom"
            />
          </div>
        </div>

        <div
          v-if="userRole === 'owner'"
          class="space-y-2"
        >
          <div class="flex items-center gap-2 text-blue-600 font-bold border-b border-gray-50 pb-2">
            <Icon icon="mdi:shield-key-outline" />
            <span>Stripe Configuration</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <BaseInput
              ref="input_stripePublicKey"
              v-model="form.stripePublicKey"
              label="Stripe Public Key"
              width="full"
              icon="mdi:key"
              placeholder="pk_test_..."
              :pattern="/^pk_(test|live)_[A-Za-z0-9]{24,}$/"
              custom-error="Invalid public key"
              required
            />
            <BaseInput
              ref="input_stripeSecretKey"
              v-model="form.stripeSecretKey"
              label="Stripe Secret Key"
              width="full"
              icon="mdi:lock-outline"
              type="password"
              placeholder="sk_test_..."
              :pattern="/^sk_(test|live)_[A-Za-z0-9]{24,}$/"
              custom-error="Invalid secret key"
              required
            />
          </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-4 pt-10 border-t border-gray-100">
          <button
            type="button"
            class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-all"
            @click="$router.back()"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-10 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 active:transform active:scale-95 transition-all flex items-center justify-center gap-2"
          >
            <Icon
              v-if="loading"
              icon="line-md:loading-twotone-loop"
            />
            <Icon
              v-else
              icon="mdi:check-circle-outline"
            />
            {{ loading ? 'Updating...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Icon } from '@iconify/vue';
import BaseInput from '../../components/global/BaseInput.vue';
import authService from '../../services/authService';
import ownerService from '../../services/ownerService';

const router = useRouter();
const loading = ref(false);
const userRole = ref('');

// Input Refs for Validation
const input_firstName = ref(null);
const input_lastName = ref(null);
const input_phone = ref(null);
const input_telephone = ref(null);
const input_address = ref(null);
const input_address2 = ref(null);
const input_city = ref(null);
const input_postcode = ref(null);
const input_country = ref(null);
const input_stripePublicKey = ref(null);
const input_stripeSecretKey = ref(null);

const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  address: '',
  address2: '',
  country: '',
  city: '',
  postcode: '',
  phone: '',
  telephone: '',
  stripePublicKey: '',
  stripeSecretKey: '',
});
const fetchProfileDetails = async () => {
  try {
    const storedUser = localStorage.getItem('user');
    const user = JSON.parse(storedUser);
    userRole.value = user.role;

    if (storedUser) {
      let ownerData = null;
      if (userRole.value === 'owner') {
        const res = await ownerService.getOwnerDetails();
        ownerData = res.data.data;
      }
      form.value = {
        firstName: user.firstName || '',
        lastName: user.lastName || '',
        email: user.email || '',
        address: user.address || '',
        address2: user.address2 || '',
        country: user.country || '',
        city: user.city || '',
        postcode: user.postcode || '',
        phone: user.phone || '',
        telephone: user.telephone || '',

        stripePublicKey: ownerData?.stripePublicKey || '',
        stripeSecretKey: ownerData?.stripeSecretKey || '',
      };
    }
  } catch (error) {
    throw new Error(error);
  }
};

onMounted(() => {
  fetchProfileDetails();
});

const handleUpdateProfile = async () => {
  const inputsToValidate = [
    input_firstName.value,
    input_lastName.value,
    input_phone.value,
    input_telephone.value,
    input_address.value,
    input_address2.value,
    input_city.value,
    input_postcode.value,
    input_country.value,
  ];

  if (userRole.value === 'owner') {
    inputsToValidate.push(input_stripePublicKey.value, input_stripeSecretKey.value);
  }

  const isFormValid = inputsToValidate
    .filter((input) => input !== null)
    .every((input) => input.validate());

  if (!isFormValid) {
    return;
  }

  loading.value = true;

  try {
    const payload = { ...form.value };

    if (userRole.value !== 'owner') {
      delete payload.stripePublicKey;
      delete payload.stripeSecretKey;
    }
    const response = await authService.updateProfile(payload);

    if (response.status) {
      const storedUser = JSON.parse(localStorage.getItem('user'));
      const { ...safeUserData } = form.value;
      const updatedUser = { ...storedUser, ...safeUserData };
      localStorage.setItem('user', JSON.stringify(updatedUser));

      if (userRole.value === 'admin') {
        router.push({ name: 'admin-dashboard' });
      } else if (userRole.value === 'owner') {
        router.push({ name: 'owner-dashboard' });
      } else {
        router.push({ name: 'home' });
      }
    }
  } catch (error) {
    throw new Error(error);
  } finally {
    loading.value = false;
  }
};
</script>
