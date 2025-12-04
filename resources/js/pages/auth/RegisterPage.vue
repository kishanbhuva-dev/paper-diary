<template>
  <div>
    <h2 class="text-2xl font-semibold text-gray-800 mb-1">Create an Account</h2>
    <p class="text-gray-500 mb-8 text-sm">
      Register now to access your Paper Diary account.
    </p>

    <form @submit.prevent="handleRegister" class="grid grid-cols-2 gap-5">
      <!-- First Name -->
      <div>
        <label class="block text-sm text-gray-700 mb-1">First Name</label>
        <input
          v-model="form.firstName"
          @input="clearError('firstName')"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
        <p v-if="errors.firstName" class="text-red-500 text-xs mt-1">
          {{ errors.firstName }}
        </p>
      </div>

      <!-- Last Name -->
      <div>
        <label class="block text-sm text-gray-700 mb-1">Last Name</label>
        <input
          v-model="form.lastName"
          @input="clearError('lastName')"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
        <p v-if="errors.lastName" class="text-red-500 text-xs mt-1">
          {{ errors.lastName }}
        </p>
      </div>

      <!-- Email -->
      <div class="col-span-2">
        <label class="block text-sm text-gray-700 mb-1">Email Address</label>
        <input
          v-model="form.email"
          type="email"
          @input="clearError('email')"
          autocomplete="email"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
        <p v-if="errors.email" class="text-red-500 text-xs mt-1">
          {{ errors.email }}
        </p>
      </div>

      <!-- Password -->
      <div class="col-span-2">
        <label class="block text-sm text-gray-700 mb-1">Password</label>
        <input
          v-model="form.password"
          type="password"
          @input="clearError('password')"
          autocomplete="new-password"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
        <p v-if="errors.password" class="text-red-500 text-xs mt-1">
          {{ errors.password }}
        </p>
      </div>

      <!-- Confirm Password -->
      <div class="col-span-2">
        <label class="block text-sm text-gray-700 mb-1">Confirm Password</label>
        <input
          v-model="form.confirm_password"
          type="password"
          @input="clearError('confirm_password')"
          autocomplete="new-password"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
        <p v-if="errors.confirm_password" class="text-red-500 text-xs mt-1">
          {{ errors.confirm_password }}
        </p>
      </div>

      <!-- Role -->
      <div class="col-span-2">
        <label class="block text-sm text-gray-700 mb-1">Role</label>
        <select
          v-model="form.role"
          @change="clearError('role')"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        >
          <option class="" value="user">User</option>
          <option value="owner">Owner</option>
        </select>
        <p v-if="errors.role" class="text-red-500 text-xs mt-1">
          {{ errors.role }}
        </p>
      </div>

      <!-- Address -->
      <div class="col-span-2">
        <label class="block text-sm text-gray-700 mb-1">Address</label>
        <input
          v-model="form.address"
          @input="clearError('address')"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
        <p v-if="errors.address" class="text-red-500 text-xs mt-1">
          {{ errors.address }}
        </p>
      </div>

      <!-- Address 2 -->
      <div class="col-span-2">
        <label class="block text-sm text-gray-700 mb-1">Address 2</label>
        <input
          v-model="form.address2"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
      </div>

      <!-- City -->
      <div>
        <label class="block text-sm text-gray-700 mb-1">City</label>
        <input
          v-model="form.city"
          @input="clearError('city')"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
        <p v-if="errors.city" class="text-red-500 text-xs mt-1">
          {{ errors.city }}
        </p>
      </div>

      <!-- Country -->
      <div>
        <label class="block text-sm text-gray-700 mb-1">Country</label>
        <input
          v-model="form.country"
          @input="clearError('country')"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
        <p v-if="errors.country" class="text-red-500 text-xs mt-1">
          {{ errors.country }}
        </p>
      </div>

      <!-- Postcode -->
      <div>
        <label class="block text-sm text-gray-700 mb-1">Postcode</label>
        <input
          v-model="form.postcode"
          @input="clearError('postcode')"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
        <p v-if="errors.postcode" class="text-red-500 text-xs mt-1">
          {{ errors.postcode }}
        </p>
      </div>

      <!-- Phone -->
      <div>
        <label class="block text-sm text-gray-700 mb-1">Phone</label>
        <input
          v-model="form.phone"
          @input="clearError('phone')"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200 focus:outline-none"
        />
        <p v-if="errors.phone" class="text-red-500 text-xs mt-1">
          {{ errors.phone }}
        </p>
      </div>

      <!-- Submit -->
      <div class="col-span-2">
        <button
          type="submit"
          class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-800 transition-colors"
        >
          Register
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive } from "vue";
import { useAuth } from "../../composables/useAuth";
import { toast } from "vue-sonner";
import authService from "../../services/authService";

const { login } = useAuth();

const form = reactive({
  firstName: "",
  lastName: "",
  email: "",
  password: "",
  confirm_password: "",
  role: "user",
  address: "",
  address2: "",
  city: "",
  country: "",
  postcode: "",
  phone: "",
});

const errors = reactive({});

// Simple email + phone regex
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const phoneRegex = /^[0-9]{7,15}$/;

const handleRegister = async () => {
  Object.keys(errors).forEach((key) => (errors[key] = "")); // clear old errors

  // Basic required fields
  if (!form.firstName) errors.firstName = "First name is required.";
  if (!form.lastName) errors.lastName = "Last name is required.";
  if (!form.email) errors.email = "Email is required.";
  else if (!emailRegex.test(form.email))
    errors.email = "Please enter a valid email.";

  if (!form.password) errors.password = "Password is required.";
  else if (form.password.length < 6)
    errors.password = "Password must be at least 6 characters.";

  if (!form.confirm_password)
    errors.confirm_password = "Confirm password is required.";
  else if (form.password !== form.confirm_password)
    errors.confirm_password = "Passwords do not match.";

  if (!form.role) errors.role = "Please select a role.";
  if (!form.address) errors.address = "Address is required.";
  if (!form.city) errors.city = "City is required.";
  if (!form.country) errors.country = "Country is required.";
  if (!form.postcode) errors.postcode = "Postcode is required.";
  if (!form.phone) errors.phone = "Phone is required.";
  else if (!phoneRegex.test(form.phone))
    errors.phone = "Enter a valid phone number.";

  // stop if any errors exist
  if (Object.keys(errors).some((key) => errors[key])) return;

  // If all valid, send request
  try {
    const res = await authService.register(form);
    if (res) {
      toast.success("Registered successfully!");
      // Auto-login after registration
      await login({
        email: form.email,
        password: form.password,
      });
    }
  } catch (error) {
    const message =
      error.response?.data?.message ||
      error.message ||
      "Server error during registration";
    errors.general = message;
    toast.error(message);
  }
};

// ✅ clear error as soon as user types correctly
const clearError = (field) => {
  if (!errors[field]) return;
  if (form[field]) errors[field] = "";
};
</script>

<style scoped lang="postcss">
.input {
  @apply w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none;
}
</style>
