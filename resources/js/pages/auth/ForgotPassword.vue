<template>
  <div class="max-w-lg sm:mx-auto mx-4 my-10 rounded-lg p-6 shadow-md">
    <h2 class="text-2xl font-semibold text-gray-800 mb-1">
      Forgot your password?
    </h2>
    <p class="text-gray-500 mb-6 text-sm">
      Enter your email address and we will send you a link to reset your
      password.
    </p>

    <form @submit.prevent="handleForgotPassword" class="space-y-5">
      <BaseInput
        ref="emailInput"
        label="Your Email"
        v-model="form.email"
        type="email"
        autocomplete="email"
        width="full"
        required
        placeholder="name@example.com"
        icon="lucide:mail"
      />

      <button type="submit" class="btn-primary w-full py-1.5">
        Send Password Reset Link
      </button>
    </form>

    <p class="text-center text-gray-500 text-sm mt-6">
      Remember your password?
      <router-link to="/login" class="text-primary font-medium hover:underline">
        Log in
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from "vue";
import BaseInput from "../../components/global/BaseInput.vue";
import authService from "../../services/authService";
import { useRouter } from "vue-router";
const router = useRouter();

const form = ref({
  email: "",
});

const emailInput = ref(null);

const handleForgotPassword = async () => {
  if (emailInput.value.validate()) {
    const res = await authService.forgotPassword(form.value);
    if (res.status) {
      router.push({ name: "login" });
    }
  }
};
</script>
