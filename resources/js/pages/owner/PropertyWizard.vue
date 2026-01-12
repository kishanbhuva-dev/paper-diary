<template>
  <div class="min-h-screen bg-gray-50/50 p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
      <div class="flex items-center justify-between mb-8">
        <button
          class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:text-blue-600 transition-all shadow-sm group"
          @click="handleBackButton"
        >
          <Icon
            icon="mdi:arrow-left"
            class="w-5 h-5 group-hover:-translate-x-1 transition-transform"
          />
          Back
        </button>

        <div class="flex items-center gap-3">
          <h2 class="text-xl font-bold text-gray-800">
            {{ stepTitle }}
          </h2>
        </div>
        <button
          class="text-sm font-bold text-gray-400 hover:text-red-500 uppercase tracking-wider transition-colors"
          @click="cancelWizard"
        >
          Exit
        </button>
      </div>

      <div class="mb-10 relative">
        <div class="flex items-center justify-between relative z-10">
          <div v-for="step in 3" :key="step" class="flex flex-col items-center">
            <div
              :class="[
                'w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-500 border-4',
                currentStep >= step
                  ? 'bg-blue-600 border-blue-100 text-white shadow-lg shadow-blue-200'
                  : 'bg-white border-gray-100 text-gray-300',
                startedWithId
                  ? 'cursor-pointer hover:scale-105'
                  : 'cursor-default',
              ]"
              @click="handleStepClick(step)"
            >
              <Icon
                v-if="currentStep > step"
                icon="mdi:check"
                class="w-6 h-6"
              />
              <span v-else>{{ step }}</span>
            </div>
            <span
              :class="[
                'mt-2 text-[11px] font-bold uppercase tracking-tighter',
                currentStep >= step ? 'text-blue-600' : 'text-gray-400',
              ]"
            >
              {{ step === 1 ? "Property Details" : step === 2 ? "Resource Types" : "Resources" }}
            </span>
          </div>
        </div>

        <div
          class="absolute top-5 left-0 w-full h-1 bg-gray-200 -z-0 rounded-full"
        ></div>
        <div
          class="absolute top-5 left-0 h-1 bg-blue-600 transition-all duration-500 ease-in-out rounded-full -z-0"
          :style="{ width: ((currentStep - 1) / 2) * 100 + '%' }"
        ></div>
      </div>

      <div
        class="bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50 overflow-hidden transition-all duration-300"
      >
        <div class="p-6 md:p-10">
          <div class="form-container">
            <transition name="fade-slide" mode="out-in">
              <div :key="currentStep">
                <PropertiesForm
                  v-if="currentStep === 1"
                  :id="normalizedPropertyId"
                  ref="step1Ref"
                  :in-wizard="true"
                  :edit-mode="startedWithId"
                  @success="handleStep1Success"
                />

                <ResourceTypeForm
                  v-if="currentStep === 2"
                  ref="step2Ref"
                  :property-id="normalizedPropertyId"
                  :in-wizard="true"
                  :edit-mode="startedWithId"
                  @success="handleStep2Success"
                />

                <ResourceForm
                  v-if="currentStep === 3"
                  ref="step3Ref"
                  :property-id="normalizedPropertyId"
                  :resource-types="resourceTypes"
                  :in-wizard="true"
                  :edit-mode="startedWithId"
                  @success="handleStep3Success"
                />
              </div>
            </transition>
          </div>
        </div>

        <div
          class="bg-gray-50/80 backdrop-blur-sm border-t border-gray-100 p-6 flex items-center justify-between"
        >
          <div class="flex flex-col">
            <p
              class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1"
            >
              Current Progress
            </p>
            <p class="text-sm font-bold text-blue-600">
              {{ Math.round((currentStep / 3) * 100) }}% Completed
            </p>
          </div>

          <div class="flex items-center gap-4">
            <button
              v-if="currentStep > 1"
              class="px-6 py-2.5 text-sm font-bold text-gray-600 hover:text-gray-900 transition-colors"
              @click="prevStep"
            >
              Previous
            </button>

            <button
              :disabled="loading"
              class="relative px-8 py-3 bg-blue-600 text-white text-sm font-bold rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center group overflow-hidden"
              @click="submitCurrentStep"
            >
              <div v-if="loading" class="mr-3">
                <Icon icon="eos-icons:loading" class="w-5 h-5 animate-spin" />
              </div>

              <span class="relative z-10 flex items-center">
                {{ currentStep < 3 ? "Save & Continue" : "Complete Setup" }}
                <Icon
                  v-if="!loading && currentStep < 3"
                  icon="mdi:arrow-right"
                  class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform"
                />
                <Icon
                  v-else-if="!loading && currentStep === 3"
                  icon="mdi:check-circle"
                  class="w-5 h-5 ml-2"
                />
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { Icon } from "@iconify/vue";

import PropertiesForm from "./PropertiesForm.vue";
import ResourceTypeForm from "./ResourceTypeForm.vue";
import ResourceForm from "./ResourceForm.vue";
import ownerService from "../../services/ownerService";

const router = useRouter();
const route = useRoute();

// --- STATE ---
const currentStep = ref(1);
const loading = ref(false);
const propertyId = ref(null);
const resourceTypes = ref([]);
const createdInWizard = ref(false);
const startedWithId = ref(false);

const pendingChanges = ref({
  property: null,
  resourceTypes: null,
  resources: null,
});

// Component Refs
const step1Ref = ref(null);
const step2Ref = ref(null);
const step3Ref = ref(null);

onMounted(() => {
  if (route.params && route.params.id) {
    startedWithId.value = true;
    propertyId.value = route.params.id;
  }
});

// --- COMPUTED ---

const normalizedPropertyId = computed(() => {
  if (!propertyId.value) {return null;}
  try {
    const decoded = atob(String(propertyId.value));
    return !isNaN(Number(decoded)) ? Number(decoded) : propertyId.value;
  } catch (e) {
    return propertyId.value;
  }
});

const currentStepRef = computed(() => {
  if (currentStep.value === 1) {return step1Ref.value;}
  if (currentStep.value === 2) {return step2Ref.value;}
  if (currentStep.value === 3) {return step3Ref.value;}
  return null;
});

const stepTitle = computed(() => {
  const titles = ["Property Details", "Resource Categories", "Resource Setup"];
  return titles[currentStep.value - 1] || "Wizard";
});

// --- NAVIGATION ---
const submitCurrentStep = async () => {
  if (!currentStepRef.value?.handleSubmit) {return;}
  
  loading.value = true;
  try {
    const result = await currentStepRef.value.handleSubmit();
    
    // If in Edit mode, we collect the returned payload for final batch update
    if (startedWithId.value) {
      if (currentStep.value === 1) {pendingChanges.value.property = result;}
      if (currentStep.value === 2) {pendingChanges.value.resourceTypes = result;}
      if (currentStep.value === 3) {pendingChanges.value.resources = result;}
      
      if (currentStep.value === 3) {
        await applyEdits();
      } else {
        nextStep();
      }
    } else {
      // Create mode: Success is handled by child success emits
    }
  } catch (e) {
    console.error("Step submission failed:", e);
  } finally {
    loading.value = false;
  }
};

const nextStep = () => {
  if (currentStep.value < 3) {
    currentStep.value++;
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const handleStepClick = async (step) => {
  if (!startedWithId.value) {return;}
  await ensureStepDataLoaded(step);
  currentStep.value = step;
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const ensureStepDataLoaded = async (step) => {
  if (!normalizedPropertyId.value) {return;}
  if ((step === 2 || step === 3) && resourceTypes.value.length === 0) {
    await loadResourceTypes(normalizedPropertyId.value);
  }
};

const loadResourceTypes = async (id) => {
  try {
    const types = await ownerService.fetchResourceTypes(id);
    resourceTypes.value = (types || []).map((t) => t.id);
  } catch (e) {
    console.error("Failed to load resource types:", e);
  }
};

// --- HANDLERS ---
const handleStep1Success = async (data) => {
  if (!startedWithId.value) {
    if (!propertyId.value) {createdInWizard.value = true;}
    propertyId.value = data.id;
  }
  nextStep();
};

const handleStep2Success = (data) => {
  if (data?.resourceTypes) {
    resourceTypes.value = data.resourceTypes;
  }
  nextStep();
};

const handleStep3Success = async (data) => {
  if (startedWithId.value) {
    pendingChanges.value.resources = data;
    await applyEdits();
  } else {
    router.push({ name: "properties" });
  }
};

const cancelWizard = async () => {
  if (!startedWithId.value && createdInWizard.value && normalizedPropertyId.value) {
    try {
      const images = await ownerService.fetchPropertyImages(normalizedPropertyId.value);
      const imageIds = (images || []).map((i) => i.id).filter(Boolean);
      if (imageIds.length) {await ownerService.deletePropertyImages(imageIds);}
      await ownerService.deleteProperty(normalizedPropertyId.value);
    } catch (err) {
      console.debug("Cleanup failed", err);
    }
  }
  router.push({ name: "properties" });
};

const handleBackButton = async () => {
  if (currentStep.value > 1) {
    prevStep();
  } else {
    await cancelWizard();
  }
};

const applyEdits = async () => {
  loading.value = true;
  try {
    const numericId = normalizedPropertyId.value;

    // 1. Process Property Changes
    if (pendingChanges.value.property) {
      const p = pendingChanges.value.property;
      if (p.propertyPayload) {await ownerService.updateProperty(numericId, p.propertyPayload);}
      if (p.removedImageIds?.length) {await ownerService.deletePropertyImages(p.removedImageIds);}
      
      let uploadedNewIds = [];
      if (p.newFiles?.length) {
        const uploaded = await ownerService.addPropertyImages(numericId, p.newFiles);
        uploadedNewIds = (uploaded || []).map((u) => u.id).filter(Boolean);
      }

      const finalOrder = [...(p.orderedImageIds || [])];
      if (uploadedNewIds.length) {finalOrder.push(...uploadedNewIds);}
      if (finalOrder.length > 0) {
        await ownerService.changePropertyImagePosition(numericId, finalOrder).catch(() => {});
      }

      if (Array.isArray(p.facilityIds)) {
        await ownerService.setPropertyFacilities({ propertyId: numericId, facilityId: p.facilityIds });
      }
    }

    // 2. Process Resource Types
    if (pendingChanges.value.resourceTypes) {
      const rt = pendingChanges.value.resourceTypes;
      if (rt.deleted?.length) {
        for (const id of rt.deleted) {await ownerService.deleteResourceType(id);}
      }
      if (rt.toUpdate?.length) {
        await ownerService.resourceTypeMultipleUpdate({
          propertyId: numericId,
          ids: rt.toUpdate.map(r => r.id),
          name: rt.toUpdate.map(r => r.name),
          price: rt.toUpdate.map(r => r.price),
          capacity: rt.toUpdate.map(r => r.capacity),
          slot: rt.toUpdate.map(r => r.slot),
        });
      }
      if (rt.toCreate?.length) {
        await ownerService.resourceTypeMultipleStore({
          propertyId: numericId,
          name: rt.toCreate.map(r => r.name),
          price: rt.toCreate.map(r => r.price),
          capacity: rt.toCreate.map(r => r.capacity),
          slot: rt.toCreate.map(r => r.slot),
        });
      }
    }

    // 3. Process Resources
    if (pendingChanges.value.resources) {
      const rs = pendingChanges.value.resources;
      if (rs.deleted?.length) {
        for (const id of rs.deleted) {await ownerService.deleteResource(id);}
      }
      if (rs.toUpdate?.length) {
        await ownerService.resourceMultipleUpdate({
          ids: rs.toUpdate.map(r => r.id),
          name: rs.toUpdate.map(r => r.name),
          status: rs.toUpdate.map(r => r.status),
          resourceTypeId: rs.toUpdate.map(r => r.resourceTypeId),
        });
      }
      if (rs.toCreate?.length) {
        await ownerService.resourceMultipleStore({
          name: rs.toCreate.map(r => r.name),
          status: rs.toCreate.map(r => r.status),
          resourceTypeId: rs.toCreate.map(r => r.resourceTypeId),
        });
      }
    }

    router.push({ name: "properties" });
  } catch (err) {
    console.error("Failed to apply edits", err);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}
.fade-slide-enter-from { opacity: 0; transform: translateX(20px); }
.fade-slide-leave-to { opacity: 0; transform: translateX(-20px); }
</style>