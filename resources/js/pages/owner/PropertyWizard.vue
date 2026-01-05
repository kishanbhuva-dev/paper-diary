<template>
  <div class="min-h-screen bg-gray-50/50 p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
      <div class="flex items-center justify-between mb-8">
        <button
          @click="handleBackButton"
          class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:text-blue-600 transition-all shadow-sm group"
        >
          <Icon
            icon="mdi:arrow-left"
            class="w-5 h-5 group-hover:-translate-x-1 transition-transform"
          />
          Back
        </button>

        <div class="flex items-center gap-3">
          <span
            class="hidden md:block text-sm font-medium text-gray-400 uppercase tracking-widest"
            >Property Wizard</span
          >
          <div class="h-4 w-px bg-gray-300 hidden md:block"></div>
          <h2 class="text-xl font-bold text-gray-800">
            {{ stepTitle }}
          </h2>
        </div>

        <button
          @click="cancelWizard"
          class="text-sm font-bold text-gray-400 hover:text-red-500 uppercase tracking-wider transition-colors"
        >
          Exit Wizard
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
              ]"
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
              {{ step === 1 ? "Details" : step === 2 ? "Types" : "Setup" }}
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
                  ref="step1Ref"
                  :id="propertyId"
                  :in-wizard="true"
                  @success="handleStep1Success"
                />

                <ResourceTypeForm
                  v-if="currentStep === 2"
                  ref="step2Ref"
                  :property-id="propertyId"
                  :in-wizard="true"
                  @success="handleStep2Success"
                />

                <ResourceForm
                  v-if="currentStep === 3"
                  ref="step3Ref"
                  :property-id="propertyId"
                  :resource-types="resourceTypes"
                  :in-wizard="true"
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
              @click="prevStep"
              class="px-6 py-2.5 text-sm font-bold text-gray-600 hover:text-gray-900 transition-colors"
            >
              Previous
            </button>

            <button
              @click="submitCurrentStep"
              :disabled="loading"
              class="relative px-8 py-3 bg-blue-600 text-white text-sm font-bold rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center group overflow-hidden"
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

// Import your step components
import PropertiesForm from "./PropertiesForm.vue";
import ResourceTypeForm from "./ResourceTypeForm.vue";
import ResourceForm from "./ResourceForm.vue";
import ownerService from "../../services/ownerService";

const router = useRouter();

// --- STATE ---
const currentStep = ref(1);
const loading = ref(false);
const propertyId = ref(null);
const resourceTypes = ref([]);
const createdInWizard = ref(false);
const pendingChanges = ref({
  property: null,
  resourceTypes: null,
  resources: null,
});
// Refs to call child component methods
const startedWithId = ref(false);
const step1Ref = ref(null);
const step2Ref = ref(null);
const step3Ref = ref(null);

const route = useRoute();
onMounted(() => {
  if (route.params && route.params.id) {
    startedWithId.value = true;
    propertyId.value = route.params.id;
  }
});

// --- COMPUTED ---
const currentStepRef = computed(() => {
  switch (currentStep.value) {
    case 1:
      return step1Ref.value;
    case 2:
      return step2Ref.value;
    case 3:
      return step3Ref.value;
    default:
      return null;
  }
});

const stepTitle = computed(() => {
  switch (currentStep.value) {
    case 1:
      return "Property Details";
    case 2:
      return "Resource Types";
    case 3:
      return "Resource Setup";
    default:
      return "Wizard";
  }
});

// --- NAVIGATION ---
const submitCurrentStep = async () => {
  if (currentStepRef.value && currentStepRef.value.handleSubmit) {
    loading.value = true;
    try {
      const result = await currentStepRef.value.handleSubmit();
      if (startedWithId.value && result) {
        if (currentStep.value === 1) pendingChanges.value.property = result;
        if (currentStep.value === 2)
          pendingChanges.value.resourceTypes = result;
        if (currentStep.value === 3) pendingChanges.value.resources = result;
        nextStep();
        return;
      }
    } catch (e) {
      console.error("Step submission failed:", e);
    } finally {
      loading.value = false;
    }
  }
};

const cancelWizard = async () => {
  try {
    if (!startedWithId.value && createdInWizard.value && propertyId.value) {
      let id = propertyId.value;
      try {
        const maybe = atob(String(id));
        if (!isNaN(Number(maybe))) id = Number(maybe);
      } catch (e) {}

      try {
        const images = await ownerService.fetchPropertyImages(id);
        const imageIds = (images || []).map((i) => i.id).filter(Boolean);
        if (imageIds.length) {
          await ownerService.deletePropertyImages(imageIds);
        }
      } catch (err) {
        console.debug("[PropertyWizard] cleanup images failed:", err);
      }

      await ownerService.deleteProperty(id);
    }
  } catch (err) {
    console.error("Failed to cleanup property on cancel:", err);
  }
  router.push({ name: "properties" });
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

const handleBackButton = async () => {
  if (currentStep.value > 1) {
    prevStep();
    return;
  }

  try {
    if (!startedWithId.value && createdInWizard.value && propertyId.value) {
      let id = propertyId.value;
      try {
        const maybe = atob(String(id));
        if (!isNaN(Number(maybe))) id = Number(maybe);
      } catch (e) {}

      try {
        const images = await ownerService.fetchPropertyImages(id);
        const imageIds = (images || []).map((i) => i.id).filter(Boolean);
        if (imageIds.length) {
          await ownerService.deletePropertyImages(imageIds);
        }
      } catch (err) {
        console.debug("[PropertyWizard] cleanup images failed:", err);
      }

      await ownerService.deleteProperty(id);
    } else if (startedWithId.value) {
      pendingChanges.value = {
        property: null,
        resourceTypes: null,
        resources: null,
      };
    }
  } catch (err) {
    console.error("Failed to perform back-from-first-step cleanup:", err);
  }

  router.push({ name: "properties" });
};

// --- HANDLERS ---
const loadResourceTypes = async (id) => {
  try {
    const types = await ownerService.fetchResourceTypes(id);
    resourceTypes.value = (types || []).map((t) => t.id);
  } catch (e) {
    console.error("Failed to load resource types:", e);
    resourceTypes.value = [];
  }
};

const handleStep1Success = async (data) => {
  if (!startedWithId.value) {
    if (!propertyId.value) createdInWizard.value = true;
  }
  propertyId.value = data.id;

  if (startedWithId.value) {
    let id = data.id;
    try {
      const maybe = atob(String(id));
      if (!isNaN(Number(maybe))) id = Number(maybe);
    } catch (e) {}
    await loadResourceTypes(id);
  }

  nextStep();
};

const handleStep2Success = (data) => {
  resourceTypes.value = data.resourceTypes;
  nextStep();
};

const handleStep3Success = () => {
  if (startedWithId.value) {
    applyEdits();
    return;
  }
  router.push({ name: "properties" });
};

const applyEdits = async () => {
  loading.value = true;
  try {
    const id = propertyId.value;
    let numericId = id;
    try {
      const maybe = atob(String(id));
      if (!isNaN(Number(maybe))) numericId = Number(maybe);
    } catch (e) {}

    if (pendingChanges.value.property) {
      const p = pendingChanges.value.property;
      if (p.propertyPayload) {
        await ownerService.updateProperty(numericId, p.propertyPayload);
      }

      if (p.removedImageIds && p.removedImageIds.length) {
        await ownerService.deletePropertyImages(p.removedImageIds);
      }

      let uploadedNewIds = [];
      if (p.newFiles && p.newFiles.length) {
        const uploaded = await ownerService.addPropertyImages(
          numericId,
          p.newFiles
        );
        uploadedNewIds = (uploaded || []).map((u) => u.id).filter(Boolean);
      }

      if (p.orderedImageIds && p.orderedImageIds.length) {
        const finalOrder = [...p.orderedImageIds];
        if (uploadedNewIds.length) finalOrder.push(...uploadedNewIds);
        if (finalOrder.length > 1) {
          await ownerService
            .changePropertyImagePosition(numericId, finalOrder)
            .catch((err) => {
              console.debug(
                "[PropertyWizard] Failed to update image order:",
                err
              );
            });
        }
      } else {
        const remoteImages = await ownerService.fetchPropertyImages(numericId);
        const orderedIds = (remoteImages || [])
          .map((i) => i.id)
          .filter(Boolean);
        if (orderedIds.length > 1) {
          await ownerService
            .changePropertyImagePosition(numericId, orderedIds)
            .catch((err) => {
              console.debug(
                "[PropertyWizard] Failed to update image order:",
                err
              );
            });
        }
      }

      if (Array.isArray(p.facilityIds) && p.facilityIds.length >= 0) {
        await ownerService.setPropertyFacilities({
          propertyId: numericId,
          facilityId: p.facilityIds,
        });
      }
    }

    if (pendingChanges.value.resourceTypes) {
      const rt = pendingChanges.value.resourceTypes;
      if (rt.deleted && rt.deleted.length) {
        for (const id of rt.deleted) {
          await ownerService.deleteResourceType(id);
        }
      }
      if (rt.toUpdate && rt.toUpdate.length) {
        const payload = {
          propertyId: numericId,
          ids: rt.toUpdate.map((r) => r.id),
          name: rt.toUpdate.map((r) => r.name),
          price: rt.toUpdate.map((r) => r.price),
          capacity: rt.toUpdate.map((r) => r.capacity),
          slot: rt.toUpdate.map((r) => r.slot),
        };
        await ownerService.resourceTypeMultipleUpdate(payload);
      }
      if (rt.toCreate && rt.toCreate.length) {
        const payload = {
          propertyId: numericId,
          name: rt.toCreate.map((r) => r.name),
          price: rt.toCreate.map((r) => r.price),
          capacity: rt.toCreate.map((r) => r.capacity),
          slot: rt.toCreate.map((r) => r.slot),
        };
        await ownerService.resourceTypeMultipleStore(payload);
      }
    }

    if (pendingChanges.value.resources) {
      const rs = pendingChanges.value.resources;
      if (rs.deleted && rs.deleted.length) {
        for (const id of rs.deleted) {
          await ownerService.deleteResource(id);
        }
      }
      if (rs.toUpdate && rs.toUpdate.length) {
        const payload = {
          ids: rs.toUpdate.map((r) => r.id),
          name: rs.toUpdate.map((r) => r.name),
          status: rs.toUpdate.map((r) => r.status),
          resourceTypeId: rs.toUpdate.map((r) => r.resourceTypeId),
        };
        await ownerService.resourceMultipleUpdate(payload);
      }
      if (rs.toCreate && rs.toCreate.length) {
        const payload = {
          name: rs.toCreate.map((r) => r.name),
          status: rs.toCreate.map((r) => r.status),
          resourceTypeId: rs.toCreate.map((r) => r.resourceTypeId),
        };
        await ownerService.resourceMultipleStore(payload);
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

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}
</style>
