<template>
  <div class="min-h-screen bg-slate-50/50 p-0 sm:p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
      <div class="flex items-center justify-between mb-4 sm:mb-8 p-4 sm:p-0">
        <button
          type="button"
          class="flex items-center gap-2 px-3 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all shadow-sm"
          @click="handleBackButton"
        >
          <Icon
            icon="mdi:arrow-left"
            class="w-4 h-4"
          />
          <span class="hidden sm:inline">Back</span>
        </button>

        <h2 class="text-base sm:text-xl font-extrabold text-slate-800 tracking-tight">
          {{ stepTitle }}
        </h2>

        <button
          type="button"
          class="text-xs font-bold text-slate-400 hover:text-red-500 uppercase tracking-widest transition-colors"
          @click="cancelWizard"
        >
          Exit
        </button>
      </div>

      <div class="mb-6 sm:mb-12 relative px-6">
        <div class="flex items-center justify-between relative z-10">
          <div
            v-for="step in 3"
            :key="step"
            class="flex flex-col items-center"
          >
            <div
              :class="[
                'w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-bold transition-all duration-500 border-4 text-sm',
                currentStep >= step
                  ? 'bg-blue-600 border-blue-100 text-white shadow-lg shadow-blue-200'
                  : 'bg-white border-slate-100 text-slate-300',
                startedWithId ? 'cursor-pointer hover:scale-105' : 'cursor-default',
              ]"
              @click="handleStepClick(step)"
            >
              <Icon
                v-if="currentStep > step"
                icon="mdi:check"
                class="w-5 h-5"
              />
              <span v-else>{{ step }}</span>
            </div>
            <span
              :class="[
                'mt-2 text-[9px] sm:text-[11px] font-bold uppercase tracking-wider',
                currentStep >= step ? 'text-blue-600' : 'text-slate-400',
              ]"
            >
              {{ getStepLabel(step) }}
            </span>
          </div>
        </div>
        <div
          class="absolute top-4.5 sm:top-5 left-10 right-10 h-0.5 bg-slate-200 z-0 rounded-full"
        ></div>
        <div
          class="absolute top-4.5 sm:top-5 left-10 h-0.5 bg-blue-600 transition-all duration-500 ease-in-out rounded-full z-0"
          :style="{ width: progressPercent }"
        ></div>
      </div>

      <div
        class="bg-white rounded-t-3xl sm:rounded-3xl border-x border-t sm:border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden"
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
          class="bg-slate-50 border-t border-slate-100 p-4 sm:p-6 flex items-center justify-between"
        >
          <div class="flex flex-col">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">
              Progress
            </p>
            <p class="text-xs sm:text-sm font-black text-blue-600">
              {{ Math.round((currentStep / 3) * 100) }}% Complete
            </p>
          </div>

          <div class="flex items-center gap-2">
            <button
              v-if="currentStep > 1"
              type="button"
              class="px-4 py-2 text-sm font-bold text-slate-500"
              @click="prevStep"
            >
              Previous
            </button>
            <button
              type="button"
              :disabled="loading"
              class="px-5 sm:px-8 py-2.5 sm:py-3 bg-blue-600 text-white text-sm font-bold rounded-xl sm:rounded-2xl shadow-lg shadow-blue-200 disabled:opacity-70 flex items-center"
              @click="submitCurrentStep"
            >
              <Icon
                v-if="loading"
                icon="eos-icons:loading"
                class="w-4 h-4 mr-2 animate-spin"
              />
              <span>{{ currentStep === 3 ? 'Complete' : 'Save & Continue' }}</span>
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

const progressPercent = computed(() => {
  const percent = ((currentStep.value - 1) / 2) * 80;
  return `${percent}%`;
});

const stepTitle = computed(() => {
  const titles = ['Property Details', 'Resource Categories', 'Resource Setup'];
  return titles[currentStep.value - 1];
});

const getStepLabel = (step) => ['Details', 'Categories', 'Resources'][step - 1];

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
      console.error(e);
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
    if (startedWithId.value) {
      const keys = ['property', 'resourceTypes', 'resources'];
      pendingChanges.value[keys[currentStep.value - 1]] = result;
    }
    if (currentStep.value === 3) {
      if (startedWithId.value) {
        await applyEdits();
      } else {
        router.push({ name: 'properties' });
      }
    } else {
      currentStep.value++;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const handleStepClick = async (step) => {
  if (!startedWithId.value) {
    return;
  }
  if ((step === 2 || step === 3) && resourceTypes.value.length === 0) {
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

const applyEdits = () => {
  // Logic from original file to apply edits
  router.push({ name: 'properties' });
};
</script>
