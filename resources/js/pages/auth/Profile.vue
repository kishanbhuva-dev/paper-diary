<template>
  <div class="w-full mx-auto px-4 py-8">
    <div
      class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
    >
      <div
        class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-600 to-indigo-700"
      >
        <h3 class="text-xl font-bold text-white flex items-center gap-2">
          <Icon icon="mdi:account-circle-outline" class="text-2xl" />
          Personal Information
        </h3>
        <p class="text-blue-100 text-sm mt-1">
          Manage your account details and contact information
        </p>
      </div>

      <form @submit.prevent="handleUpdateProfile" class="p-6 md:p-10 space-y-8">
        <div class="space-y-2">
          <div
            class="flex items-center gap-2 text-blue-600 font-bold border-b border-gray-50 pb-2"
          >
            <Icon icon="mdi:badge-account-horizontal-outline" />
            <span>Basic Details</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <BaseInput
              label="First Name"
              v-model="form.firstName"
              width="full"
              required
              icon="mdi:account-outline"
              placeholder="John"
            />
            <BaseInput
              label="Last Name"
              v-model="form.lastName"
              width="full"
              required
              icon="mdi:account-outline"
              placeholder="Doe"
            />
            <BaseInput
              label="Email Address"
              v-model="form.email"
              width="full"
              variant="gray"
              disabled
              icon="mdi:email-outline"
            />
          </div>
        </div>

        <div class="space-y-2">
          <div
            class="flex items-center gap-2 text-blue-600 font-bold border-b border-gray-50 pb-2"
          >
            <Icon icon="mdi:phone-outline" />
            <span>Contact Information</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <BaseInput
              label="Phone Number"
              v-model="form.phone"
              width="full"
              icon="mdi:phone"
              placeholder="+44 123 456 7890"
            />
            <BaseInput
              label="Secondary Telephone"
              v-model="form.telephone"
              width="full"
              icon="mdi:phone-classic"
              placeholder="Home or office number"
            />
          </div>
        </div>

        <div class="space-y-2">
          <div
            class="flex items-center gap-2 text-blue-600 font-bold border-b border-gray-50 pb-2"
          >
            <Icon icon="mdi:map-marker-outline" />
            <span>Address Details</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <BaseInput
              label="Address Line 1"
              v-model="form.address"
              width="full"
              icon="mdi:home-outline"
              placeholder="123 Main Street"
            />
            <BaseInput
              label="Address Line 2"
              v-model="form.address2"
              width="full"
              icon="mdi:home-plus-outline"
              placeholder="Apartment, suite, etc."
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <BaseInput
              label="City"
              v-model="form.city"
              width="full"
              icon="mdi:city-variant-outline"
              placeholder="London"
            />
            <BaseInput
              label="Postcode"
              v-model="form.postcode"
              width="full"
              icon="mdi:mailbox-outline"
              placeholder="E1 6AN"
            />
            <BaseInput
              label="Country"
              v-model="form.country"
              width="full"
              icon="mdi:earth"
              placeholder="United Kingdom"
            />
          </div>
        </div>

        <div v-if="userRole === 'owner'" class="space-y-2">
          <div
            class="flex items-center gap-2 text-blue-600 font-bold border-b border-gray-50 pb-2"
          >
            <Icon icon="mdi:shield-key-outline" />
            <span>Stripe Configuration</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <BaseInput
              label="Stripe Public Key"
              v-model="form.stripePublicKey"
              width="full"
              icon="mdi:key"
              placeholder="pk_test_..."
            />
            <BaseInput
              label="Stripe Secret Key"
              v-model="form.stripeSecretKey"
              width="full"
              icon="mdi:lock-outline"
              type="password"
              placeholder="sk_test_..."
            />
          </div>
        </div>

        <div
          class="flex flex-col sm:flex-row justify-end gap-4 pt-10 border-t border-gray-100"
        >
          <button
            type="button"
            @click="$router.back()"
            class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-all"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-10 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 active:transform active:scale-95 transition-all flex items-center justify-center gap-2"
          >
            <Icon v-if="loading" icon="line-md:loading-twotone-loop" />
            <Icon v-else icon="mdi:check-circle-outline" />
            {{ loading ? "Updating..." : "Save Changes" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { Icon } from "@iconify/vue";
import BaseInput from "../../components/global/BaseInput.vue";
import authService from "../../services/authService";

const router = useRouter();
const loading = ref(false);
const userRole = ref("");

const form = ref({
  firstName: "",
  lastName: "",
  email: "",
  address: "",
  address2: "",
  country: "",
  city: "",
  postcode: "",
  phone: "",
  telephone: "",
  stripePublicKey: "",
  stripeSecretKey: "",
});

onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    const user = JSON.parse(storedUser);
    userRole.value = user.role;

    // Populate form with stored data
    form.value = {
      firstName: user.firstName || "",
      lastName: user.lastName || "",
      email: user.email || "",
      address: user.address || "",
      address2: user.address2 || "",
      country: user.country || "",
      city: user.city || "",
      postcode: user.postcode || "",
      phone: user.phone || "",
      telephone: user.telephone || "",
      stripePublicKey: user.stripePublicKey || "",
      stripeSecretKey: user.stripeSecretKey || "",
    };
  }
});

const handleUpdateProfile = async () => {
  loading.value = true;

  try {
    const response = await authService.updateProfile(form.value);

    if (response.status) {
      // Sync Local Storage
      const storedUser = JSON.parse(localStorage.getItem("user"));
      const updatedUser = { ...storedUser, ...form.value };
      localStorage.setItem("user", JSON.stringify(updatedUser));

      // Role-based Redirection
      if (userRole.value === "admin") {
        router.push({ name: "admin-dashboard" });
      } else if (userRole.value === "owner") {
        router.push({ name: "owner-dashboard" });
      } else {
        router.push({ name: "home" });
      }
    }
  } catch (error) {
    console.error("Update failed", error);
  } finally {
    loading.value = false;
  }
};
</script>
