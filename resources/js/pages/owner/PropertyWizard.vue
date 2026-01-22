<template>
  <div class="min-h-screen bg-slate-50/50 p-0 sm:p-4 md:p-8 font-sans">
    <div class="max-w-5xl mx-auto">
      <div class="flex items-center justify-between mb-4 sm:mb-8 p-4 sm:p-0">
        <button
          type="button"
          class="flex items-center gap-2 px-3 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all shadow-sm group"
          @click="handleBackButton"
        >
          <Icon
            icon="mdi:arrow-left"
            class="w-4 h-4 group-hover:-translate-x-1 transition-transform"
          />
          <span class="hidden sm:inline">Back</span>
        </button>

        <h2 class="text-base sm:text-2xl font-black text-slate-800 tracking-tight">
          {{ String(stepTitle) }}
        </h2>

        <button
          type="button"
          class="text-xs font-bold text-slate-400 hover:text-red-500 uppercase tracking-widest transition-colors p-2"
          @click="cancelWizard"
        >
          Exit
        </button>
      </div>

      <div class="mb-8 sm:mb-14 px-6 sm:px-10">
        <div class="relative w-full">
          <div
            class="absolute top-5 sm:top-6 left-[20px] right-[20px] h-1 bg-slate-200 z-0 rounded-full"
          >
            <div
              class="h-full bg-blue-600 transition-all duration-700 ease-in-out rounded-full"
              :style="{ width: progressPercentage }"
            ></div>
          </div>

          <div class="flex items-center justify-between relative z-10">
            <div
              v-for="step in 3"
              :key="step"
              class="flex flex-col items-center group cursor-pointer"
              @click="handleStepClick(step)"
            >
              <div
                :class="[
                  'w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center font-bold transition-all duration-500 border-4 text-sm sm:text-base',
                  currentStep >= step
                    ? 'bg-blue-600 border-blue-100 text-white shadow-lg shadow-blue-200'
                    : 'bg-white border-slate-100 text-slate-300',
                  !startedWithId && step > currentStep ? 'pointer-events-none' : '',
                ]"
              >
                <Icon
                  v-if="currentStep > step"
                  icon="mdi:check-bold"
                  class="w-5 h-5 sm:w-6 sm:h-6"
                />
                <span v-else>{{ step }}</span>
              </div>
              <span
                :class="[
                  'mt-3 text-[10px] sm:text-xs font-black uppercase tracking-tighter sm:tracking-widest whitespace-nowrap transition-colors',
                  currentStep >= step ? 'text-blue-600' : 'text-slate-400',
                ]"
              >
                {{ getStepLabel(step) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div
        class="bg-white rounded-t-3xl sm:rounded-3xl border-x border-t sm:border border-slate-100 shadow-2xl shadow-slate-200/50 overflow-hidden"
      >
        <div class="p-0 sm:p-2">
          <transition
            name="fade-slide"
            mode="out-in"
          >
            <div :key="currentStep">
              <PropertiesForm
                v-if="currentStep === 1"
                :id="normalizedPropertyId"
                ref="step1Ref"
                in-wizard
                :edit-mode="startedWithId"
                @success="handleStep1Success"
              />
              <ResourceTypeForm
                v-if="currentStep === 2"
                ref="step2Ref"
                :property-id="normalizedPropertyId"
                in-wizard
                :edit-mode="startedWithId"
                @success="handleStep2Success"
              />
              <ResourceForm
                v-if="currentStep === 3"
                ref="step3Ref"
                :property-id="normalizedPropertyId"
                :resource-types="resourceTypes"
                in-wizard
                :edit-mode="startedWithId"
                @success="handleStep3Success"
              />
            </div>
          </transition>
        </div>

        <div
          class="bg-slate-50 border-t border-slate-100 p-4 sm:p-8 flex items-center justify-between"
        >
          <div class="hidden sm:flex flex-col">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
              Setup Progress
            </p>
            <div class="flex items-center gap-3">
              <div class="w-32 h-2 bg-slate-200 rounded-full overflow-hidden">
                <div
                  class="h-full bg-blue-600 transition-all duration-500"
                  :style="{ width: `${(currentStep / 3) * 100}%` }"
                ></div>
              </div>
              <span class="text-sm font-black text-blue-600"
                >{{ Math.round((currentStep / 3) * 100) }}%</span
              >
            </div>
          </div>

          <div class="flex items-center justify-between w-full sm:w-auto gap-4">
            <button
              v-if="currentStep > 1"
              type="button"
              class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors"
              @click="prevStep"
            >
              Previous
            </button>
            <div
              v-else
              class="sm:hidden"
            ></div>

            <button
              type="button"
              :disabled="loading"
              class="flex-1 sm:flex-none px-8 sm:px-12 py-3.5 sm:py-4 bg-blue-600 text-white text-sm font-bold rounded-xl sm:rounded-2xl shadow-xl shadow-blue-200 hover:bg-blue-700 active:scale-95 disabled:opacity-70 transition-all flex items-center justify-center"
              @click="submitCurrentStep"
            >
              <Icon
                v-if="loading"
                icon="eos-icons:loading"
                class="w-5 h-5 mr-2 animate-spin"
              />
              <span>{{ currentStep === 3 ? 'Finish Setup' : 'Save & Continue' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Icon } from '@iconify/vue';
import PropertiesForm from './PropertiesForm.vue';
import ResourceTypeForm from './ResourceTypeForm.vue';
import ResourceForm from './ResourceForm.vue';
import ownerService from '../../services/ownerService';

const router = useRouter();
const route = useRoute();

const currentStep = ref(1);
const loading = ref(false);
const propertyId = ref(null);
const resourceTypes = ref([]);
const startedWithId = ref(false);
const pendingChanges = ref({ property: null, resourceTypes: null, resources: null });

const step1Ref = ref(null);
const step2Ref = ref(null);
const step3Ref = ref(null);

onMounted(() => {
  if (route.params?.id) {
    startedWithId.value = true;
    propertyId.value = route.params.id;
  }
});

/**
 * Unified progress calculation
 * Since we have 3 steps, the intervals are 0%, 50%, and 100%.
 * This aligns perfectly with justify-between positioning.
 */
const progressPercentage = computed(() => {
  const percentage = ((currentStep.value - 1) / 2) * 100;
  return `${percentage}%`;
});

const stepTitle = computed(() => {
  const titles = ['Property Details', 'Resource Types', 'Resource Setup'];
  return titles[currentStep.value - 1];
});

const getStepLabel = (step) => ['Details', 'Resource Types', 'Resources'][step - 1];

const normalizedPropertyId = computed(() => {
  if (!propertyId.value) {
    return null;
  }
  try {
    const decoded = atob(String(propertyId.value));
    return !isNaN(Number(decoded)) ? Number(decoded) : propertyId.value;
  } catch {
    return propertyId.value;
  }
});

const handleBackButton = async () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  } else {
    await cancelWizard();
  }
};

const cancelWizard = async () => {
  if (!startedWithId.value && propertyId.value) {
    try {
      await ownerService.deleteProperty(normalizedPropertyId.value);
    } catch (e) {
      console.error('Cleanup failed:', e);
    }
  }
  router.push({ name: 'properties' });
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const submitCurrentStep = async () => {
  let activeRef = null;
  if (currentStep.value === 1) {
    activeRef = step1Ref.value;
  } else if (currentStep.value === 2) {
    activeRef = step2Ref.value;
  } else if (currentStep.value === 3) {
    activeRef = step3Ref.value;
  }

  if (!activeRef?.handleSubmit) {
    return;
  }

  loading.value = true;
  try {
    const result = await activeRef.handleSubmit();
    if (result) {
      if (startedWithId.value) {
        const keys = ['property', 'resourceTypes', 'resources'];
        pendingChanges.value[keys[currentStep.value - 1]] = result;
      }

      if (currentStep.value === 3) {
        router.push({ name: 'properties' });
      } else {
        currentStep.value++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }
  } catch (e) {
    console.error('Wizard error:', e);
  } finally {
    loading.value = false;
  }
};

const handleStepClick = async (step) => {
  if (!startedWithId.value && step > currentStep.value) {
    return;
  }

  if (startedWithId.value && (step === 2 || step === 3) && resourceTypes.value.length === 0) {
    const res = await ownerService.fetchResourceTypes(normalizedPropertyId.value);
    resourceTypes.value = (res || []).map((t) => t.id);
  }
  currentStep.value = step;
};

const handleStep1Success = (data) => {
  if (!startedWithId.value) {
    propertyId.value = data.id;
  }
};

const handleStep2Success = (data) => {
  if (data?.resourceTypes) {
    resourceTypes.value = data.resourceTypes;
  }
};

const handleStep3Success = (data) => {
  if (startedWithId.value) {
    pendingChanges.value.resources = data;
  }
};
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(20px);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

/* iOS zoom prevention and mobile input handling */
@media (max-width: 640px) {
  input,
  select,
  textarea {
    font-size: 16px !important;
  }
}
</style>
