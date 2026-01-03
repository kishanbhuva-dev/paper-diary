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

      <form @submit.prevent="openConfirmation" class="p-6 md:p-10 space-y-8">
        <div class="space-y-2">
          <div
            class="flex items-center gap-2 text-blue-600 font-bold border-b border-gray-50 pb-2"
          >
            <Icon icon="mdi:shield-key-outline" />
            <span>Change Password</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="max-w-6xl w-full">
              <label class="block text-sm font-semibold mb-1 text-slate-400">
                New Password <span class="text-red-500">*</span>
              </label>

              <div
                :class="[
                  'flex gap-2 border px-3 py-2 transition-all duration-200 rounded-xl items-center bg-white',
                  touched.password && errors.password
                    ? 'border-red-500 ring-1 ring-red-100'
                    : 'border-gray-300 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100',
                ]"
              >
                <Icon
                  icon="mdi:lock-outline"
                  class="text-md shrink-0 text-slate-400"
                />
                <input
                  v-model="form.password"
                  :type="showPass1 ? 'text' : 'password'"
                  placeholder="••••••••"
                  class="flex-1 min-w-0 outline-none border-none bg-transparent placeholder:text-slate-400 text-sm text-gray-900"
                  @blur="touched.password = true"
                />
                <button
                  type="button"
                  @click="showPass1 = !showPass1"
                  class="focus:outline-none text-slate-400 hover:text-slate-600"
                >
                  <Icon
                    :icon="
                      showPass1 ? 'mdi:eye-off-outline' : 'mdi:eye-outline'
                    "
                    class="text-lg"
                  />
                </button>
              </div>

              <div class="flex justify-between items-start mt-1">
                <span
                  class="text-xs font-medium"
                  :class="
                    touched.password && errors.password
                      ? 'text-red-500'
                      : 'text-slate-500'
                  "
                >
                  {{
                    touched.password && errors.password
                      ? errors.password
                      : "Minimum 8 characters required"
                  }}
                </span>
              </div>
            </div>

            <div class="max-w-6xl w-full">
              <label class="block text-sm font-semibold mb-1 text-slate-400">
                Confirm New Password <span class="text-red-500">*</span>
              </label>

              <div
                :class="[
                  'flex gap-2 border px-3 py-2 transition-all duration-200 rounded-xl items-center bg-white',
                  touched.confirmPassword && errors.confirmPassword
                    ? 'border-red-500 ring-1 ring-red-100'
                    : 'border-gray-300 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100',
                ]"
              >
                <Icon
                  icon="mdi:lock-check-outline"
                  class="text-md shrink-0 text-slate-400"
                />
                <input
                  v-model="form.confirmPassword"
                  :type="showPass2 ? 'text' : 'password'"
                  placeholder="••••••••"
                  class="flex-1 min-w-0 outline-none border-none bg-transparent placeholder:text-slate-400 text-sm text-gray-900"
                  @blur="touched.confirmPassword = true"
                />
                <button
                  type="button"
                  @click="showPass2 = !showPass2"
                  class="focus:outline-none text-slate-400 hover:text-slate-600"
                >
                  <Icon
                    :icon="
                      showPass2 ? 'mdi:eye-off-outline' : 'mdi:eye-outline'
                    "
                    class="text-lg"
                  />
                </button>
              </div>

              <div class="flex justify-between items-start mt-1">
                <span
                  v-if="touched.confirmPassword && errors.confirmPassword"
                  class="text-xs font-medium text-red-500"
                >
                  {{ errors.confirmPassword }}
                </span>
              </div>
            </div>
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
            {{ loading ? "Update Password" : "Update Password" }}
          </button>
        </div>
      </form>
    </div>

    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    >
      <div
        class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl border border-gray-100"
      >
        <div class="text-center">
          <div
            class="bg-blue-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
          >
            <Icon
              icon="mdi:help-circle-outline"
              class="text-3xl text-blue-600"
            />
          </div>
          <h4 class="text-lg font-bold text-gray-800">
            Confirm Password Change
          </h4>
          <p class="text-gray-500 mt-2 text-sm leading-relaxed">
            Are you sure you want to update your password? You will need to use
            this new password for your next login.
          </p>
        </div>

        <div class="flex gap-3 mt-6">
          <button
            @click="showModal = false"
            class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-all"
          >
            Cancel
          </button>
          <button
            @click="handlePasswordUpdate"
            :disabled="loading"
            class="flex-1 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow-md transition-all disabled:opacity-50"
          >
            {{ loading ? "Updating..." : "Yes, Update" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { Icon } from "@iconify/vue";
import authService from "../../services/authService";

const router = useRouter();
const loading = ref(false);
const showModal = ref(false);

const showPass1 = ref(false);
const showPass2 = ref(false);

const form = ref({
  password: "",
  confirmPassword: "",
});

const touched = ref({
  password: false,
  confirmPassword: false,
});

// Logic to replace BaseInput validation
const errors = computed(() => {
  const errs = { password: "", confirmPassword: "" };

  // New Password Validation
  if (!form.value.password) {
    errs.password = "This field is required";
  } else if (form.value.password.length < 8) {
    errs.password = "Minimum 8 characters required";
  }

  // Confirm Password & Mismatch Validation
  if (!form.value.confirmPassword) {
    errs.confirmPassword = "This field is required";
  } else if (form.value.password !== form.value.confirmPassword) {
    errs.confirmPassword = "Passwords do not match";
  }

  return errs;
});

const resetForm = () => {
  form.value.password = "";
  form.value.confirmPassword = "";
  touched.value.password = false;
  touched.value.confirmPassword = false;
};

const openConfirmation = () => {
  touched.value.password = true;
  touched.value.confirmPassword = true;

  if (errors.value.password || errors.value.confirmPassword) {
    return;
  }

  showModal.value = true;
};

const handlePasswordUpdate = async () => {
  loading.value = true;

  try {
    const response = await authService.changePassword(form.value);

    if (response.status) {
      showModal.value = false;
      resetForm();

      const storedUser = JSON.parse(localStorage.getItem("user") || "{}");
      const role = storedUser.role;

      if (role === "admin") {
        router.push({ name: "admin-dashboard" });
      } else if (role === "owner") {
        router.push({ name: "owner-dashboard" });
      } else {
        router.push({ name: "home" });
      }
    } else {
      showModal.value = false;
    }
  } catch (error) {
    showModal.value = false;
  } finally {
    loading.value = false;
  }
};
</script>
