<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div
      class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
    >
      <div
        class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-600 to-indigo-700"
      >
        <h3 class="text-xl font-bold text-white flex items-center gap-2">
          <Icon icon="mdi:lock-reset" class="text-2xl" />
          Security Settings
        </h3>
        <p class="text-blue-100 text-sm mt-1">
          Update your password to keep your account secure
        </p>
      </div>

      <form
        @submit.prevent="handlePasswordUpdate"
        class="p-6 md:p-10 space-y-8"
      >
        <div class="space-y-2">
          <div
            class="flex items-center gap-2 text-blue-600 font-bold border-b border-gray-50 pb-2"
          >
            <Icon icon="mdi:shield-key-outline" />
            <span>Change Password</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <BaseInput
              label="New Password"
              v-model="form.password"
              type="password"
              width="full"
              required
              :minLength="8"
              icon="mdi:lock-outline"
              placeholder="••••••••"
              helperText="Minimum 8 characters required"
            />

            <BaseInput
              label="Confirm New Password"
              v-model="form.password_confirmation"
              type="password"
              width="full"
              required
              icon="mdi:lock-check-outline"
              placeholder="••••••••"
              :customError="passwordError"
            />
          </div>
        </div>

        <div
          class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex gap-3"
        >
          <Icon
            icon="mdi:information-outline"
            class="text-blue-600 text-xl shrink-0 mt-0.5"
          />
          <p class="text-sm text-blue-700 leading-relaxed">
            Changing your password will sign you out of all other active
            sessions. Make sure your new password is <strong>unique</strong> and
            not used for other services.
          </p>
        </div>

        <div
          class="flex flex-col sm:flex-row justify-end gap-4 pt-10 border-t border-gray-100"
        >
          <button
            type="button"
            @click="resetForm"
            class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-all"
          >
            Clear Fields
          </button>

          <button
            type="submit"
            :disabled="loading"
            class="px-10 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 active:transform active:scale-95 transition-all flex items-center justify-center gap-2"
          >
            <Icon v-if="loading" icon="line-md:loading-twotone-loop" />
            <Icon v-else icon="mdi:update" />
            {{ loading ? "Updating..." : "Update Password" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Icon } from "@iconify/vue";
import BaseInput from "../../components/global/BaseInput.vue"; // Adjust path based on your folder structure

const loading = ref(false);

const form = ref({
  password: "",
  password_confirmation: "",
});

// Validation for matching passwords
const passwordError = computed(() => {
  if (
    form.value.password_confirmation &&
    form.value.password !== form.value.password_confirmation
  ) {
    return "Passwords do not match";
  }
  return "";
});

const resetForm = () => {
  form.value.password = "";
  form.value.password_confirmation = "";
};

const handlePasswordUpdate = async () => {
  // Final check before submission
  if (form.value.password !== form.value.password_confirmation) return;
  if (form.value.password.length < 8) return;

  loading.value = true;

  try {
    console.log("Submitting Password Update:", {
      password: form.value.password,
    });

    /* API CALL START
    const response = await axios.post('/api/auth/change-password', {
        password: form.value.password,
        password_confirmation: form.value.password_confirmation
    });
    
    if(response.status === 200) {
       // handle success (e.g., notify user, redirect, or clear form)
       resetForm();
    }
    API CALL END
    */

    // Simulating API delay
    await new Promise((resolve) => setTimeout(resolve, 1500));
    alert("Password updated successfully!");
    resetForm();
  } catch (error) {
    console.error("Password update failed", error);
    // alert(error.response?.data?.message || "An error occurred");
  } finally {
    loading.value = false;
  }
};
</script>
