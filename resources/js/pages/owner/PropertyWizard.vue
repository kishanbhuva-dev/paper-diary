<template>
  <div class="property-wizard">
    <div class="stepper-header">
      <div class="flex items-center justify-between mb-4">
        <div>
          <button
            @click="handleBackButton"
            class="px-3 py-1 cursor-pointer text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-md hover:bg-gray-50 mr-3"
          >
            <Icon icon="mdi:arrow-left" class="w-4 h-4 inline-block mr-2" />
            Back
          </button>
        </div>
        <h2 class="text-2xl font-semibold text-blue-700">
          Step {{ currentStep }} / 3: {{ stepTitle }}
        </h2>
        <div></div>
      </div>
    </div>

    <div class="form-container">
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

    <div class="flex justify-end p-6 border-t border-gray-200 bg-gray-50">
      <button
        v-if="currentStep > 0"
        @click="cancelWizard"
        class="px-5 py-2 cursor-pointer mr-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200"
      >
        Cancel
      </button>

      <button
        @click="submitCurrentStep"
        :disabled="loading"
        class="px-5 py-2 cursor-pointer text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition flex items-center shadow-md"
      >
        <Icon
          v-if="loading"
          icon="eos-icons:loading"
          class="w-5 h-5 mr-3 animate-spin"
        />
        {{ currentStep < 3 ? "Save & Continue" : "Complete Setup" }}
        <Icon
          v-if="!loading && currentStep < 3"
          icon="mdi:arrow-right"
          class="w-5 h-5 ml-2"
        />
        <Icon
          v-else-if="!loading && currentStep === 3"
          icon="mdi:check-bold"
          class="w-5 h-5 ml-2"
        />
      </button>
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
const propertyId = ref(null); // Key piece of state carried through steps
const resourceTypes = ref([]); // State from step 2 needed for step 3
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
// --- COMPUTED ---
onMounted(() => {
  // If wizard opened with an id (edit-mode), remember that so we don't
  // treat subsequent saves as 'created in wizard' and we avoid deleting
  // the property on Cancel.
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
  // Call the handleSubmit method exposed by the current child component
  if (currentStepRef.value && currentStepRef.value.handleSubmit) {
    loading.value = true;
    try {
      // The child form will call its API and emit 'success'
      const result = await currentStepRef.value.handleSubmit();
      // If we're editing an existing property (edit-mode), children return
      // a payload describing pending changes instead of performing API calls.
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
  // If a property was created during this wizard, delete cascade it
  try {
    if (!startedWithId.value && createdInWizard.value && propertyId.value) {
      // Resolve potential base64 id
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
    window.scrollTo(0, 0); // Scroll to top on new step
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const handleBackButton = async () => {
  // If we're not on the first step, behave like previous.
  if (currentStep.value > 1) {
    prevStep();
    return;
  }

  // We're on the first step. Behavior should mirror Cancel but with
  // a distinction: when creating a new property (createdInWizard) we must
  // delete any partial data stored during the wizard. When editing an
  // existing property (startedWithId) we should only discard pending
  // changes and NOT delete the property.
  try {
    // If creation happened in this wizard, remove created data.
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

      // delete property which should cascade on backend
      await ownerService.deleteProperty(id);
    } else if (startedWithId.value) {
      // Editing: discard any pending changes collected during the wizard.
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

// --- HANDLERS (Receiving success/data from children) ---

const handleStep1Success = (data) => {
  // Property was created/updated, store the ID
  // If the wizard was opened without an existing id, then this creation
  // happened in this wizard and should be deleted on Cancel.
  if (!startedWithId.value) {
    if (!propertyId.value) createdInWizard.value = true;
  }
  propertyId.value = data.id;
  nextStep();
};

const handleStep2Success = (data) => {
  // Resource types were created, store their IDs
  resourceTypes.value = data.resourceTypes;
  nextStep();
};

const handleStep3Success = () => {
  // Final step complete, navigate to properties list
  if (startedWithId.value) {
    // If editing, apply the pending changes (property/resource types/resources)
    applyEdits();
    return;
  }
  router.push({ name: "properties" });
};

const applyEdits = async () => {
  loading.value = true;
  try {
    const id = propertyId.value;
    // Ensure numeric id if base64 was used in route
    let numericId = id;
    try {
      // attempt base64 decode
      const maybe = atob(String(id));
      if (!isNaN(Number(maybe))) numericId = Number(maybe);
    } catch (e) {}

    // 1) Property update + images + facilities
    if (pendingChanges.value.property) {
      const p = pendingChanges.value.property;
      if (p.propertyPayload) {
        await ownerService.updateProperty(numericId, p.propertyPayload);
      }
      if (p.removedImageIds && p.removedImageIds.length) {
        await ownerService.deletePropertyImages(p.removedImageIds);
      }
      if (p.newFiles && p.newFiles.length) {
        await ownerService.addPropertyImages(numericId, p.newFiles);
      }
      if (Array.isArray(p.facilityIds) && p.facilityIds.length > 0) {
        await ownerService.setPropertyFacilities({
          propertyId: numericId,
          facilityId: p.facilityIds,
        });
      }
    }

    // 2) Resource types changes
    if (pendingChanges.value.resourceTypes) {
      const rt = pendingChanges.value.resourceTypes;
      // deletes
      if (rt.deleted && rt.deleted.length) {
        for (const id of rt.deleted) {
          await ownerService.deleteResourceType(id);
        }
      }
      // updates
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
      // creates
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

    // 3) Resources (rooms)
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
  } finally {
    loading.value = false;
  }
};
</script>
