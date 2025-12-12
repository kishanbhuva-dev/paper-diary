<template>
  <div class="max-w-lg sm:mx-auto mx-4 my-10 rounded-lg p-6 shadow-md">
    <h2 class="text-2xl font-semibold text-gray-800 mb-1">Reset Password</h2>
    <p class="text-gray-500 mb-6 text-sm">
      Enter your new password below to reset your password.
    </p>

    <form @submit.prevent="handleResetPassword" class="space-y-5">
      <BaseInput
        ref="passwordInput"
        label="New Password"
        v-model="form.password"
        type="password"
        autocomplete="new-password"
        width="full"
        required
        placeholder="••••••••"
        icon="lucide:lock"
      />

      <BaseInput
        ref="confirmPasswordInput"
        label="Confirm New Password"
        v-model="form.confirmPassword"
        type="password"
        autocomplete="new-password"
        width="full"
        required
        placeholder="••••••••"
        icon="lucide:lock"
        :pattern="new RegExp(`^${form.password}$`)"
        customError="Passwords do not match."
      />

      <button type="submit" class="btn-primary w-full py-1.5">
        Reset Password
      </button>
    </form>
    <p class="text-center text-gray-500 text-sm mt-6">
      Back to login?
      <router-link to="/login" class="text-primary font-medium hover:underline">
        Log in
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import authService from "../../services/authService";
import BaseInput from "../../components/global/BaseInput.vue";

const route = useRoute();
const router = useRouter();

const form = ref({
  password: "",
  confirmPassword: "",
  token: route.query.token,
});

const passwordInput = ref(null);
const confirmPasswordInput = ref(null);

const handleResetPassword = async () => {
  if (passwordInput.value.validate() && confirmPasswordInput.value.validate()) {
    const res = await authService.resetPassword(form.value);
    if (res.status) {
      router.push({ name: "login" });
    }
  }
};
</script>
