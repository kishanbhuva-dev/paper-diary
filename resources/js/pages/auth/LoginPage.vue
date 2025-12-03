<template>
  <div class="max-w-lg mx-auto border border-gray-300 rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-1">
      Login to your account
    </h2>
    <p class="text-gray-500 mb-6 text-sm">
      Welcome back! Please enter your details.
    </p>
    <p
      v-if="errors.general"
      class="text-red-500 text-sm mb-4 border border-red-500 p-2 rounded-lg bg-red-50"
    >
      {{ errors.general }}
    </p>
    <form @submit.prevent="handleLogin" class="space-y-5">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Your Email
        </label>
        <input
          v-model="form.email"
          type="email"
          @input="clearEmailError"
          autocomplete="email"
          class="w-full border border-gray-400 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-300 focus:border-blue-600 focus:outline-none"
        />
        <p v-if="errors.email" class="text-red-500 text-xs mt-1">
          {{ errors.email }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Password
        </label>
        <div class="relative">
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            @input="clearPasswordError"
            class="w-full border border-gray-400 rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-blue-300 focus:border-blue-600 focus:outline-none"
            autocomplete="current-password"
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

      <div class="flex justify-between text-sm text-primary">
        <a href="#" class="hover:text-primary hover:underline"
          >Forgot password?</a
        >
      </div>

      <button
        type="submit"
        class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-800 transition-all"
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
        Sign up
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useAuth } from "../../composables/useAuth";
import { toast } from "vue-sonner";

const { login } = useAuth();
const showPassword = ref(false);

const form = reactive({
  email: "",
  password: "",
});

const errors = reactive({
  email: "",
  password: "",
  general: "",
});

const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

const handleLogin = async () => {
  errors.email = "";
  errors.password = "";
  errors.general = "";

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

  if (!errors.email && !errors.password) {
    const result = await login({
      email: form.email,
      password: form.password,
    });

    if (!result.success) {
      errors.general = result.message;
    } else {
      toast.success("Login Success");
    }
  }
};

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
