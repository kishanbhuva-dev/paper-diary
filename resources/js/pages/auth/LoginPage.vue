<template>
  <div class="max-w-sm mx-auto mt-10">
    <h2 class="text-2xl font-semibold text-gray-800 mb-1">
      Login to your account
    </h2>
    <p class="text-gray-500 mb-6 text-sm">
      Welcome back! Please enter your details.
    </p>

    <form @submit.prevent="handleLogin" class="space-y-5">
      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Your Email
        </label>
        <input
          v-model="form.email"
          type="email"
          @input="clearEmailError"
          class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
        />
        <p v-if="errors.email" class="text-red-500 text-xs mt-1">
          {{ errors.email }}
        </p>
      </div>

      <!-- Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Password
        </label>
        <div class="relative">
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            @input="clearPasswordError"
            class="w-full border rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          />
          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
          >
            <span v-if="showPassword">🙈</span>
            <span v-else>👁️</span>
          </button>
        </div>
        <p v-if="errors.password" class="text-red-500 text-xs mt-1">
          {{ errors.password }}
        </p>
      </div>

      <!-- Forgot Password -->
      <div class="flex justify-between text-sm text-gray-500">
        <a href="#" class="hover:text-indigo-600">Forgot password?</a>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition-all"
      >
        Log In
      </button>

      <!-- Social Login -->
      <div class="text-center text-sm text-gray-500 my-4">or login with</div>
      <div class="flex justify-center space-x-4">
        <button
          v-for="icon in socialIcons"
          :key="icon"
          class="border border-gray-300 rounded-full p-2 hover:bg-gray-100"
        >
          <i :class="`fab fa-${icon} text-gray-600`"></i>
        </button>
      </div>
    </form>

    <p class="text-center text-gray-500 text-sm mt-6">
      Don’t have an account?
      <router-link
        to="/register"
        class="text-indigo-600 font-medium hover:underline"
      >
        Sign up
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const showPassword = ref(false);
const socialIcons = ["facebook-f", "google", "linkedin-in"];

const form = reactive({
  email: "",
  password: "",
});

const errors = reactive({
  email: "",
  password: "",
});

// Email regex
const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

// Run validation only on Login click
const handleLogin = () => {
  errors.email = "";
  errors.password = "";

  // Email validation
  if (!form.email) {
    errors.email = "Email is required.";
  } else if (!validateEmail(form.email)) {
    errors.email = "Please enter a valid email address.";
  }

  // Password validation
  if (!form.password) {
    errors.password = "Password is required.";
  } else if (form.password.length < 6) {
    errors.password = "Password must be at least 6 characters.";
  }

  // Proceed only if no errors
  if (!errors.email && !errors.password) {
    alert("Login successful (you can now call API or redirect)");
    router.push("/dashboard");
  }
};

// ✅ Clear errors when user corrects input
const clearEmailError = () => {
  if (errors.email && validateEmail(form.email)) {
    errors.email = "";
  }
};

const clearPasswordError = () => {
  if (errors.password && form.password.length >= 6) {
    errors.password = "";
  }
};
</script>

<style scoped>
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css";
</style>
