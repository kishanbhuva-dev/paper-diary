<template>
  <div class="max-w-sm mx-auto mt-10">
    <h2 class="text-2xl font-semibold text-gray-800 mb-1">
      Login to your account
    </h2>
    <p class="text-gray-500 mb-6 text-sm">
      Welcome back! Please enter your details.
    </p>
    <p v-if="errors.general" class="text-red-500 text-sm mb-4 border border-red-500 p-2 rounded-lg bg-red-50">
      {{ errors.general }}
    </p>
    <form @submit.prevent="handleLogin" class="space-y-5">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Your Email
        </label>
        <input v-model="form.email" type="email" @input="clearEmailError"
          class="w-full border border-gray-400 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-300 focus:border-blue-600 focus:outline-none" />
        <p v-if="errors.email" class="text-red-500 text-xs mt-1">
          {{ errors.email }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Password
        </label>
        <div class="relative">
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'" @input="clearPasswordError"
            class="w-full border border-gray-400 rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-blue-300 focus:border-blue-600 focus:outline-none" />
          <button type="button" @click="showPassword = !showPassword"
            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
            <span v-if="showPassword">🙈</span>
            <span v-else>👁️</span>
          </button>
        </div>
        <p v-if="errors.password" class="text-red-500 text-xs mt-1">
          {{ errors.password }}
        </p>
      </div>

      <div class="flex justify-between text-sm text-gray-500">
        <a href="#" class="hover:text-blue-600">Forgot password?</a>
      </div>

      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-800 transition-all">
        Log In
      </button>

      <div class="text-center text-sm text-gray-500 my-4">or login with</div>
      <div class="flex justify-center space-x-4">
        <button v-for="icon in socialIcons" :key="icon"
          class="border border-gray-300 rounded-full p-2 h-10 w-10 hover:bg-gray-100">
          <i :class="`fab fa-${icon} text-gray-600 hover:text-blue-600 text-xl`"></i>
        </button>
      </div>
    </form>

    <p class="text-center text-gray-500 text-sm mt-6">
      Don’t have an account?
      <router-link to="/register" class="text-blue-600 font-medium hover:underline">
        Sign up
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import axios from "axios"; // 1. Import Axios

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
  // Added a general error state for API messages
  general: "",
});

// Email regex
const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

const handleLogin = async () => {
  // Clear previous errors
  errors.email = "";
  errors.password = "";
  errors.general = "";

  // --- START: Client-Side Validation ---
  if (!form.email) {
    errors.email = "Email is required.";
  } else if (!validateEmail(form.email)) {
    errors.email = "Please enter a valid email address.";
  }

  if (!form.password) {
    errors.password = "Password is required.";
  } else if (form.password.length < 6) {
    errors.password = "Password must be at least 6 characters.";
  }
  // --- END: Client-Side Validation ---

  // Proceed only if no errors
  if (!errors.email && !errors.password) {
    try {
      // 2. Call the Laravel API
      const response = await axios.post("/api/login", {
        email: form.email,
        password: form.password,
      });

      const responseData = response.data;
      console.log("API Response Data: ", responseData);

      // 3. Check for successful login based on API response structure
      if (responseData.status === true) {

        // --- START: Success & Token Logic (Points 1 & 2) ---

        // Store Token and User Role
        localStorage.setItem('authToken', responseData.data.token);
        localStorage.setItem('userRole', responseData.data.user.role);

        // Set Authorization header for all future authenticated requests
        axios.defaults.headers.common['Authorization'] = `Bearer ${responseData.data.token}`;

        // Check Role and Redirect
        if (responseData.data.user.role === 'owner') {
          // Redirects to the 'owner-dashboard' path in your routes file
          router.push({ name: 'owner-dashboard' });
        } else {
          // If logged in but not an owner (e.g., admin or basic user),
          // we should still redirect them somewhere, or deny access per your instruction.

          // For now, we'll assume only 'owner' is allowed to proceed and clear the token
          localStorage.removeItem('authToken');
          localStorage.removeItem('userRole');
          errors.general = 'Access denied. You do not have owner privileges.';
        }

        // --- END: Success & Token Logic ---

      } else {
        // Handle API-side error message (e.g., 'The provided credentials are incorrect.')
        errors.general = responseData.message || 'Login failed. Please check your credentials.';
      }

    } catch (error) {
      // Handle server/network errors
      if (error.response) {
        // If server responded with a status outside 2xx (e.g., 401 Unauthorized)
        if (error.response.status === 401) {
          errors.general = 'Invalid email or password.';
        } else if (error.response.data && error.response.data.message) {
          errors.general = error.response.data.message;
        } else {
          errors.general = 'An unexpected error occurred. Please try again.';
        }
      } else {
        errors.general = 'Network error. Could not connect to the server.';
      }
    }
  }
  console.log("display errors ", errors.general);
};

// Clear errors when user corrects input
const clearEmailError = () => {
  if (errors.email && validateEmail(form.email)) {
    errors.email = "";
  }
  errors.general = "";
};

const clearPasswordError = () => {
  if (errors.password && form.password.length >= 6) {
    errors.password = "";
  }
  errors.general = "";
};
</script>

<style scoped>
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css";
</style>