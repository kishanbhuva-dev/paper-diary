<template>
  <div class="property-wizard">
    <div class="stepper-header">
      <h2 class="text-2xl font-semibold mb-4 text-indigo-700">
        Step {{ currentStep }} / 3: {{ stepTitle }}
      </h2>
    </div>

    <div class="form-container">
      <PropertiesForm
        v-if="currentStep === 1"
        ref="step1Ref"
        :id="propertyId"
        @success="handleStep1Success"
      />

      <ResourceSetupForm
        v-if="currentStep === 2"
        ref="step2Ref"
        :property-id="propertyId"
        @success="handleStep2Success"
      />

      <RoomAllocationForm
        v-if="currentStep === 3"
        ref="step3Ref"
        :property-id="propertyId"
        :resource-types="resourceTypes"
        @success="handleStep3Success"
      />
    </div>

    <div class="flex justify-end p-6 border-t border-gray-200 bg-gray-50">
      <button
        v-if="currentStep > 1"
        @click="prevStep"
        class="px-5 py-2 mr-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200"
      >
        Previous
      </button>

      <button
        @click="submitCurrentStep"
        :disabled="loading"
        class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition flex items-center shadow-md"
      >
        <Icon
          v-if="loading"
          icon="eos-icons:loading"
          class="w-5 h-5 mr-2 animate-spin"
        />
        {{ currentStep < 3 ? "Next" : "Submit" }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { Icon } from "@iconify/vue";

// Import your step components
import PropertiesForm from "./PropertiesForm.vue";
// import ResourceSetupForm from "./ResourceSetupForm.vue";
// import RoomAllocationForm from "./RoomAllocationForm.vue";

const router = useRouter();

// --- STATE ---
const currentStep = ref(1);
const loading = ref(false);
const propertyId = ref(null); // Key piece of state carried through steps
const resourceTypes = ref([]); // State from step 2 needed for step 3

// Refs to call child component methods
const step1Ref = ref(null);
const step2Ref = ref(null);
const step3Ref = ref(null);

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
      return "Resource Types & Total Rooms";
    case 3:
      return "Room Allocation Setup";
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
      await currentStepRef.value.handleSubmit();
    } catch (e) {
      console.error("Step submission failed:", e);
    } finally {
      loading.value = false;
    }
  }
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

// --- HANDLERS (Receiving success/data from children) ---

const handleStep1Success = (data) => {
  // Property was created/updated, store the ID
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
  alert("Property setup complete!");
  router.push({ name: "properties" });
};
</script>
