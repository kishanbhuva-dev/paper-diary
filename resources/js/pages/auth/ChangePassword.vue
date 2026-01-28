<template>
  <div class="min-h-screen bg-[#f8fafc] px-0 py-4 sm:px-6 sm:py-8 lg:px-8 font-sans text-slate-800">
    <div class="mx-auto w-full max-w-5xl">
      <div
        class="bg-white rounded-2xl border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden"
      >
        <div class="px-5 py-5 sm:px-8 sm:py-6 bg-gradient-to-r from-blue-600 to-indigo-700">
          <h3 class="text-lg sm:text-xl font-bold text-white flex items-center gap-2.5">
            <Icon
              icon="mdi:lock-reset"
              class="text-2xl sm:text-3xl shrink-0"
            />
            <span>Security Settings</span>
          </h3>
          <p class="text-blue-100 text-xs sm:text-sm mt-1.5 ml-0.5 sm:ml-1">
            Update your password to keep your account secure
          </p>
        </div>

        <form
          class="p-4 sm:p-8 lg:p-10 space-y-8 sm:space-y-10"
          @submit.prevent="openConfirmation"
        >
          <div class="space-y-6">
            <div
              class="flex items-center gap-2 text-blue-700 font-bold border-b border-slate-100 pb-2.5"
            >
              <div class="p-1.5 bg-blue-50 rounded-lg text-blue-600">
                <Icon
                  icon="mdi:shield-key-outline"
                  class="w-5 h-5"
                />
              </div>
              <span class="text-sm sm:text-base">Change Password</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
              <div class="w-full group">
                <label class="block text-sm font-bold mb-1.5 text-slate-600">
                  New Password <span class="text-red-500">*</span>
                </label>

                <div
                  :class="[
                    'flex gap-2 border px-3 sm:px-4 py-2.5 transition-all duration-200 rounded-xl items-center bg-white',
                    touched.password && errors.password
                      ? 'border-red-500 ring-2 ring-red-100 bg-red-50/10'
                      : 'border-slate-200 hover:border-slate-300 focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-500/10',
                  ]"
                >
                  <Icon
                    icon="mdi:lock-outline"
                    class="text-xl shrink-0 text-slate-400 group-focus-within:text-blue-500 transition-colors"
                  />
                  <input
                    v-model="form.password"
                    :type="showPass1 ? 'text' : 'password'"
                    placeholder="••••••••"
                    class="flex-1 min-w-0 outline-none border-none bg-transparent placeholder:text-slate-300 text-sm sm:text-base text-slate-900 w-full"
                    @blur="touched.password = true"
                  />
                  <button
                    type="button"
                    class="p-1 -mr-1 rounded-lg focus:outline-none text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"
                    title="Toggle password visibility"
                    @click="showPass1 = !showPass1"
                  >
                    <Icon
                      :icon="showPass1 ? 'mdi:eye-off-outline' : 'mdi:eye-outline'"
                      class="text-xl"
                    />
                  </button>
                </div>

                <div class="h-5 mt-1">
                  <span
                    class="text-xs font-medium block truncate"
                    :class="touched.password && errors.password ? 'text-red-500' : 'text-slate-400'"
                  >
                    {{
                      touched.password && errors.password
                        ? errors.password
                        : 'Minimum 8 characters required'
                    }}
                  </span>
                </div>
              </div>

              <div class="w-full group">
                <label class="block text-sm font-bold mb-1.5 text-slate-600">
                  Confirm New Password <span class="text-red-500">*</span>
                </label>

                <div
                  :class="[
                    'flex gap-2 border px-3 sm:px-4 py-2.5 transition-all duration-200 rounded-xl items-center bg-white',
                    touched.confirmPassword && errors.confirmPassword
                      ? 'border-red-500 ring-2 ring-red-100 bg-red-50/10'
                      : 'border-slate-200 hover:border-slate-300 focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-500/10',
                  ]"
                >
                  <Icon
                    icon="mdi:lock-check-outline"
                    class="text-xl shrink-0 text-slate-400 group-focus-within:text-blue-500 transition-colors"
                  />
                  <input
                    v-model="form.confirmPassword"
                    :type="showPass2 ? 'text' : 'password'"
                    placeholder="••••••••"
                    class="flex-1 min-w-0 outline-none border-none bg-transparent placeholder:text-slate-300 text-sm sm:text-base text-slate-900 w-full"
                    @blur="touched.confirmPassword = true"
                  />
                  <button
                    type="button"
                    class="p-1 -mr-1 rounded-lg focus:outline-none text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"
                    title="Toggle password visibility"
                    @click="showPass2 = !showPass2"
                  >
                    <Icon
                      :icon="showPass2 ? 'mdi:eye-off-outline' : 'mdi:eye-outline'"
                      class="text-xl"
                    />
                  </button>
                </div>

                <div class="h-5 mt-1">
                  <span
                    v-if="touched.confirmPassword && errors.confirmPassword"
                    class="text-xs font-medium text-red-500 block truncate"
                  >
                    {{ errors.confirmPassword }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div
            class="bg-blue-50/80 border border-blue-100 rounded-xl p-4 sm:p-5 flex items-start gap-4"
          >
            <div class="bg-blue-100 text-blue-600 rounded-full p-1 mt-0.5 shrink-0">
              <Icon
                icon="mdi:information-variant"
                class="text-lg"
              />
            </div>
            <p class="text-xs sm:text-sm text-blue-800 leading-relaxed">
              Changing your password will sign you out of all other active sessions. Please ensure
              your new password is <strong>unique</strong> and has not been used for other services
              recently.
            </p>
          </div>

          <div
            class="flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4 pt-8 sm:pt-10 border-t border-slate-100"
          >
            <button
              type="button"
              class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all text-sm sm:text-base"
              @click="resetForm"
            >
              Clear Fields
            </button>

            <button
              type="submit"
              :disabled="loading"
              class="w-full sm:w-auto px-8 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 active:transform active:scale-[0.98] transition-all flex items-center justify-center gap-2 text-sm sm:text-base disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <Icon
                v-if="loading"
                icon="line-md:loading-twotone-loop"
                class="text-xl"
              />
              <Icon
                v-else
                icon="mdi:update"
                class="text-xl"
              />
              {{ loading ? 'Update Password' : 'Update Password' }}
            </button>
          </div>
        </form>
      </div>

      <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showModal"
          class="fixed inset-0 z-[9999] flex items-center justify-center px-4 py-6"
        >
          <div
            class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
            @click="!loading && (showModal = false)"
          ></div>

          <div
            class="relative bg-white rounded-2xl p-6 sm:p-8 max-w-[400px] w-full shadow-2xl border border-slate-100 scale-100 transform transition-all"
          >
            <div class="text-center">
              <div
                class="bg-blue-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-5 shadow-sm"
              >
                <Icon
                  icon="mdi:shield-check-outline"
                  class="text-3xl text-blue-600"
                />
              </div>
              <h4 class="text-lg sm:text-xl font-bold text-slate-800">Confirm Change</h4>
              <p class="text-slate-500 mt-2 text-sm leading-relaxed">
                Are you sure you want to update your password? You will need to use this new
                password for your next login.
              </p>
            </div>

            <div class="flex gap-3 mt-6 sm:mt-8">
              <button
                class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition-all text-sm"
                :disabled="loading"
                @click="showModal = false"
              >
                Cancel
              </button>
              <button
                :disabled="loading"
                class="flex-1 py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all text-sm flex items-center justify-center gap-2"
                @click="handlePasswordUpdate"
              >
                <Icon
                  v-if="loading"
                  icon="line-md:loading-twotone-loop"
                />
                {{ loading ? 'Updating...' : 'Yes, Update' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Icon } from '@iconify/vue';
import authService from '../../services/authService';

const router = useRouter();
const loading = ref(false);
const showModal = ref(false);

const showPass1 = ref(false);
const showPass2 = ref(false);

const form = ref({
  password: '',
  confirmPassword: '',
});

const touched = ref({
  password: false,
  confirmPassword: false,
});

// Logic to replace BaseInput validation
const errors = computed(() => {
  const errs = { password: '', confirmPassword: '' };

  // New Password Validation
  if (!form.value.password) {
    errs.password = 'This field is required';
  } else if (form.value.password.length < 8) {
    errs.password = 'Minimum 8 characters required';
  }

  // Confirm Password & Mismatch Validation
  if (!form.value.confirmPassword) {
    errs.confirmPassword = 'This field is required';
  } else if (form.value.password !== form.value.confirmPassword) {
    errs.confirmPassword = 'Passwords do not match';
  }

  return errs;
});

const resetForm = () => {
  form.value.password = '';
  form.value.confirmPassword = '';
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

      const storedUser = JSON.parse(localStorage.getItem('user') || '{}');
      const { role } = storedUser;

      if (role === 'admin') {
        router.push({ name: 'admin-dashboard' });
      } else if (role === 'owner') {
        router.push({ name: 'owner-dashboard' });
      } else {
        router.push({ name: 'home' });
      }
    } else {
      showModal.value = false;
    }
  } catch (error) {
    showModal.value = false;
    throw new Error(error);
  } finally {
    loading.value = false;
  }
};
</script>
