<template>
  <div class="max-w-lg sm:mx-auto mx-4 my-10 rounded-lg p-2 sm:p-6 sm:shadow-md">
    <h2 class="text-2xl font-semibold text-gray-800 mb-1">Login to your account</h2>
    <p class="text-gray-500 mb-6 text-sm">Welcome back! Please enter your details.</p>

    <form
      class="space-y-5"
      @submit.prevent="handleLogin"
    >
      <BaseInput
        ref="emailInput"
        v-model="form.email"
        label="Your Email"
        type="email"
        autocomplete="email"
        width="full"
        required
        placeholder="name@example.com"
        icon="lucide:mail"
      />

      <BaseInput
        ref="passwordInput"
        v-model="form.password"
        label="Password"
        type="password"
        autocomplete="current-password"
        width="full"
        required
        placeholder="••••••••"
        icon="lucide:lock"
      />

      <div class="flex justify-between text-sm text-primary">
        <a
          href="/forgot-password"
          class="hover:text-primary hover:underline"
        >
          Forgot password?
        </a>
      </div>

      <button
        type="submit"
        class="btn-primary w-full py-1.5"
      >
        Log In
      </button>
    </form>

    <p class="text-center text-gray-500 text-sm mt-6">
      Don’t have an account?
      <router-link
        to="/register"
        class="text-primary font-medium hover:underline"
      >
        Create one
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue';

import BaseInput from '../../components/global/BaseInput.vue';
import { useAuth } from '../../composables/useAuth';

const { login } = useAuth();

const form = ref({
  email: '',
  password: '',
});

const emailInput = ref(null);
const passwordInput = ref(null);

const handleLogin = async () => {
  const isEmailValid = emailInput.value.validate();
  const isPasswordValid = passwordInput.value.validate();

  if (isEmailValid && isPasswordValid) {
    await login(form.value);
  }
};
</script>
