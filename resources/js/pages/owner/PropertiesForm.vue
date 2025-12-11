<template>
  <div class="px-4 sm:px-6 py-4 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">
          {{ isEditing ? "Edit Property" : "Add New Property" }}
        </h1>
      </div>

      <div class="bg-white rounded-2xl shadow-inner border border-blue-100">
        <div
          v-if="loadingItem"
          class="p-16 flex flex-col justify-center items-center"
        >
          <Icon
            icon="eos-icons:loading"
            class="w-10 h-10 text-blue-600 animate-spin"
          />
          <span class="mt-4 text-lg text-blue-600 font-semibold"
            >Loading property data...</span
          >
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-8">
          <div class="p-6 md:p-8 border-b border-gray-100">
            <h3
              class="text-xl font-bold text-blue-700 mb-6 flex items-center gap-2"
            >
              <Icon icon="mdi:home-city-outline" class="text-2xl" /> Property
              Details
            </h3>
            <div class="space-y-4">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.propertyName"
                label="Property Name"
                width="full"
                placeholder="e.g. Seaside Villa"
                required
                :max-length="50"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.address"
                label="Primary Address Line"
                width="full"
                placeholder="e.g. 123 Ocean Drive"
                required
                :max-length="100"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.email"
                label="Contact Email"
                type="email"
                width="full"
                placeholder="contact@example.com"
                required
                :max-length="50"
              />
            </div>
          </div>

          <div class="p-6 md:p-8 border-b border-gray-100">
            <h3
              class="text-xl font-bold text-blue-700 mb-6 flex items-center gap-2"
            >
              <Icon icon="mdi:map-marker-outline" class="text-2xl" /> Location &
              Contact
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.city"
                label="City"
                width="full"
                placeholder="London"
                required
                :max-length="20"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.country"
                label="Country"
                width="full"
                placeholder="UK"
                required
                :max-length="20"
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.postcode"
                label="Postcode"
                width="full"
                placeholder="SW1A 1AA"
                required
                :max-length="10"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.telephone"
                label="Telephone"
                width="full"
                placeholder="+44 7911 123456"
                :max-length="15"
                :pattern="/^[0-9+\-\s]*$/"
                custom-error="Only numbers and + - allowed"
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.latitude"
                label="Latitude"
                width="full"
                placeholder="e.g. 51.5072"
                required
                :max-length="15"
                :pattern="/^[-+]?([1-8]?\d(\.\d+)?|90(\.\d+)?)$/"
                custom-error="Must be number -90 to 90"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.longitude"
                label="Longitude"
                width="full"
                placeholder="e.g. -0.1276"
                required
                :max-length="15"
                :pattern="/^[-+]?((1[0-7]\d|0?\d?\d)(\.\d+)?|180(\.\d+)?)$/"
                custom-error="Must be number -180 to 180"
              />
            </div>
          </div>

          <div class="p-6 md:p-8 border-b border-gray-100">
            <h3
              class="text-xl font-bold text-blue-700 mb-6 flex items-center gap-2"
            >
              <Icon
                icon="mdi:checkbox-multiple-marked-outline"
                class="text-2xl"
              />
              Features & Status
            </h3>

            <div class="mb-8">
              <label class="block text-sm font-medium text-slate-700 mb-1.5"
                >Status <span class="text-red-500">*</span></label
              >
              <div class="relative max-w-sm">
                <select
                  v-model="formData.status"
                  class="block w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none appearance-none bg-white shadow-sm font-medium text-gray-700"
                >
                  <option :value="1">Active</option>
                  <option :value="0">Inactive</option>
                </select>
                <Icon
                  icon="mdi:chevron-down"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                />
              </div>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-3"
                >Facilities <span class="text-red-500">*</span></label
              >
              <div
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 bg-blue-50 p-6 rounded-xl border border-blue-200 shadow-inner"
              >
                <div
                  v-for="facility in availableFacilities"
                  :key="facility.id"
                  class="flex items-center"
                >
                  <input
                    :id="`facility-${facility.id}`"
                    type="checkbox"
                    v-model="formData.facilities"
                    :value="facility.id"
                    class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 cursor-pointer transition-colors"
                  />
                  <label
                    :for="`facility-${facility.id}`"
                    class="ml-2 text-sm font-medium text-gray-700 cursor-pointer select-none"
                  >
                    {{ facility.name }}
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 md:p-8">
            <h3
              class="text-xl font-bold text-blue-700 mb-6 flex items-center gap-2"
            >
              <Icon icon="mdi:image-multiple-outline" class="text-2xl" />
              Property Images
            </h3>
            <OwnerImageUploader
              v-model="formData.images"
              :max-files="10"
              @reorder="onImagesReorder"
            />
          </div>

          <div
            v-if="!inWizard"
            class="flex justify-end p-6 border-t border-gray-200 bg-gray-50/50 rounded-b-2xl"
          >
            <button
              type="button"
              @click="handleCancel"
              class="px-6 py-2.5 mr-3 text-sm font-semibold text-gray-700 bg-white rounded-xl border border-gray-300 hover:bg-gray-100 transition duration-150 outline-blue-400 cursor-pointer shadow-sm"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition duration-150 flex items-center shadow-lg outline-blue-400 shadow-blue-300 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed"
            >
              <Icon icon="ic:round-save" class="w-5 h-5 inline-block mr-2" />
              {{ isEditing ? "Update Property" : "Save New Property" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUpdate, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Icon } from "@iconify/vue";
import BaseInput from "../../components/global/BaseInput.vue";
import OwnerImageUploader from "../../components/owner/OwnerImageUploader.vue";
import ownerService from "../../services/ownerService";

// --- 1. PROPS, EMITS, & CORE SETUP ---

const props = defineProps({
  /** True when this component is used as a step in a multi-step wizard. */
  inWizard: { type: Boolean, default: false },
  /** Property ID passed by the wizard (overrides route param). */
  id: { type: [String, Number], default: null },
  /** True when the wizard is in edit mode. */
  editMode: { type: Boolean, default: false },
});

const emits = defineEmits(["success", "cancel"]);

const route = useRoute();
const router = useRouter();

// --- 2. STATE MANAGEMENT & DEFAULTS ---

const defaultFormData = {
  status: 1,
  propertyName: "",
  email: "",
  address: "",
  city: "",
  country: "",
  latitude: "",
  longitude: "",
  postcode: "",
  telephone: "",
  images: [],
  facilities: [],
};

const formData = ref({ ...defaultFormData });
const loadingItem = ref(false);
const submitting = ref(false);
// Stores IDs of images originally loaded from the server for diffing on save/update
const initialImageIds = ref([]);
const availableFacilities = ref([]);

// --- 3. COMPUTED & UTILITY ---

const effectiveId = computed(() => {
  // Prioritize prop.id (from wizard) over route.params.id (from standalone page)
  return props.id || route.params.id || null;
});

const isEditing = computed(() => !!effectiveId.value);
const inWizard = computed(() => !!props.inWizard);

// Component Refs for Validation
const inputRefs = ref([]);
const setInputRef = (el) => {
  if (el) {
    inputRefs.value.push(el);
  }
};
onBeforeUpdate(() => {
  // Clears refs before component updates to ensure we collect only current elements
  inputRefs.value = [];
});

/**
 * Validates all registered form inputs.
 * @returns {boolean} True if all inputs are valid, otherwise false.
 */
const validateForm = () => {
  let isValid = true;
  inputRefs.value.forEach((inputComponent) => {
    if (inputComponent && typeof inputComponent.validate === "function") {
      const isInputValid = inputComponent.validate();
      if (!isInputValid) {
        isValid = false;
      }
    }
  });
  return isValid;
};

/**
 * Decodes base64 ID or ensures ID is numeric for API calls.
 * @param {string | number} val - The raw ID value.
 * @returns {string | number} The resolved numeric ID or original string/number.
 */
const decodeId = (val) => {
  if (!val || typeof val === "number" || /^\d+$/.test(String(val))) return val;
  try {
    const maybe = atob(String(val));
    return !isNaN(Number(maybe)) ? Number(maybe) : val;
  } catch (e) {
    return val;
  }
};

/**
 * Converts image objects from the API response into the local state format.
 * @param {Array<Object>} remoteImages - Images array from the server.
 * @returns {Array<Object>} Formatted images for the form state.
 */
const formatImages = (remoteImages) => {
  return (remoteImages || []).map((img) => ({
    id: img.id,
    // Build URL from image (filename) or use existing url property
    url: img.url ? img.url : `/storage/property/images/${img.image}`,
    file: null,
  }));
};

// --- 4. DATA LOADING & WATCHERS ---

/**
 * Loads property data and associated images/facilities for editing.
 * @param {string | number} id - The property ID (may be base64 encoded).
 */
const loadPropertyForEdit = async (id) => {
  loadingItem.value = true;
  try {
    const resolvedId = decodeId(id);
    const item = await ownerService.fetchPropertyById(resolvedId);

    if (item) {
      // 1. Fetch & format Images
      const remoteImages = await ownerService.fetchPropertyImages(item.id);
      const loadedImages = formatImages(remoteImages);

      // 2. Prepare Facilities
      const loadedFacilityIds = (item.facilities || []).map((f) => f.id);

      // 3. Update State
      const loadedData = JSON.parse(JSON.stringify(item));
      loadedData.images = loadedImages;
      loadedData.facilities = loadedFacilityIds;

      initialImageIds.value = loadedImages.map((i) => i.id).filter(Boolean);
      formData.value = loadedData;
    } else {
      // If fetching fails for a standalone edit, redirect to the list
      if (!inWizard.value) router.push({ name: "properties" });
    }
  } catch (e) {
    console.error("Failed to load property:", e);
  } finally {
    loadingItem.value = false;
  }
};

onMounted(() => {
  // Load all available facilities (STATIC DATA - always needed for the form)
  (async () => {
    try {
      const facs = await ownerService.fetchFacilities();
      if (Array.isArray(facs) && facs.length) {
        availableFacilities.value = facs.map((f) => ({
          id: f.id,
          name: f.name,
        }));
      }
    } catch (e) {
      console.error("Failed to load facilities:", e);
    }
  })();

  // The initial logic to check if we are in CREATE mode on mount.
  if (!isEditing.value) {
    formData.value = { ...defaultFormData };
  }
  // Data loading for edit mode is now entirely handled by the watch below.
});

// Watch the effectiveId:
// 1. If it has a value, load data (happens on initial load in edit mode, or when returning to step 1)
// 2. If it is null, reset the form (happens if you clear the ID)
watch(
  () => effectiveId.value,
  (val) => {
    if (val) {
      // Loads data for edit mode
      loadPropertyForEdit(val);
    } else {
      // Resets form for create mode
      formData.value = { ...defaultFormData };
    }
  },
  { immediate: true } // This ensures initial loading happens if an ID exists
);

// --- 5. DEPENDENT DATA HANDLERS (Images & Facilities) ---

/**
 * Processes images: calculates removed IDs, prepares new files for upload.
 * @returns {{ newFiles: Array<File>, removedIds: Array<number> }}
 */
const processImages = () => {
  const newFiles = formData.value.images
    .filter((img) => img.file instanceof File)
    .map((f) => f.file);

  const existingIds = formData.value.images
    .filter((img) => img.id)
    .map((i) => i.id);

  const removedIds = initialImageIds.value.filter(
    (id) => !existingIds.includes(id)
  );

  return { newFiles, removedIds };
};

/**
 * Saves/Updates images and facilities to the API after the property data is saved.
 * @param {number} propertyId - The ID of the saved property.
 * @param {Array<File>} newFiles - Files to upload.
 * @param {Array<number>} removedIds - IDs of images to delete.
 */
const handleDependentDataUpdates = async (propertyId, newFiles, removedIds) => {
  // A. Delete removed images
  if (removedIds.length) {
    for (const imgId of removedIds) {
      await ownerService.deletePropertyImage(imgId).catch((err) => {});
    }
  }

  // B. Upload new images
  if (newFiles.length) {
    await ownerService.addPropertyImages(propertyId, newFiles).catch((err) => {
      console.error("Image upload failed:", err);
    });
  }

  // C. Update image order (requires re-fetching final state)
  const remoteImages = await ownerService.fetchPropertyImages(propertyId);
  formData.value.images = formatImages(remoteImages); // Update state

  const orderedIds = formData.value.images.map((i) => i.id).filter(Boolean);
  if (orderedIds.length > 1) {
    await ownerService
      .changePropertyImagePosition(propertyId, orderedIds)
      .catch((err) => {
        console.debug("[PropertiesForm] Failed to update image order:", err);
      });
  }

  // D. Update facilities
  const facilityIds = formData.value.facilities
    .map((id) => parseInt(id, 10))
    .filter((id) => Number.isInteger(id) && id > 0);

  if (facilityIds.length > 0) {
    await ownerService
      .setPropertyFacilities({
        propertyId: propertyId,
        facilityId: facilityIds,
      })
      .catch((err) => {});
  }
};

/**
 * Handles image reordering when emitted from the uploader component.
 * @param {Array<Object>} newImages - The new order of image objects.
 */
const onImagesReorder = async (newImages) => {
  formData.value.images = [...newImages];

  // Only perform API call if in standalone edit mode
  if (isEditing.value && formData.value.id && !inWizard.value) {
    const ids = formData.value.images.map((i) => i.id).filter(Boolean);
    if (ids.length > 1) {
      await ownerService
        .changePropertyImagePosition(formData.value.id, ids)
        .catch((err) => {
          console.error(
            "[PropertiesForm] changePropertyImagePosition error:",
            err
          );
        });
    }
  }
};

// --- 6. SUBMISSION HANDLERS ---

/**
 * Saves or creates the property data and returns the saved ID.
 * Includes fallback logic to find the ID if the API doesn't return it.
 * @param {object} apiPayload - The property data payload.
 * @returns {number | null} The ID of the created or updated property.
 */
const savePropertyData = async (apiPayload) => {
  let savedPropertyId = null;

  if (isEditing.value) {
    await ownerService.updateProperty(formData.value.id, apiPayload);
    savedPropertyId = formData.value.id;
  } else {
    const response = await ownerService.createProperty(apiPayload);
    savedPropertyId = response?.data?.id || response?.id;

    // Fallback: fetch properties and get the latest one by name
    if (!savedPropertyId) {
      const paginatedResponse = await ownerService.fetchProperties({
        limit: 1,
      });
      const createdProperty = paginatedResponse?.data?.find(
        (p) => p.propertyName === apiPayload.propertyName
      );
      savedPropertyId = createdProperty?.id;
    }
  }

  return savedPropertyId;
};

/**
 * Main function called on form submit.
 */
const handleSubmit = async () => {
  if (submitting.value) return;
  submitting.value = true;

  if (!validateForm()) {
    submitting.value = false;
    return;
  }

  try {
    // A. Prepare Data: Separate files/facilities from property payload
    const { newFiles, removedIds } = processImages();
    const apiPayload = JSON.parse(JSON.stringify(formData.value));
    delete apiPayload.images;
    delete apiPayload.facilities;

    // B. WIZARD EDIT MODE: Return payload for parent component to apply later
    if (inWizard.value && props.editMode) {
      const facilityIds = formData.value.facilities
        .map((id) => parseInt(id, 10))
        .filter((id) => Number.isInteger(id) && id > 0);

      return {
        propertyPayload: apiPayload,
        newFiles,
        removedImageIds: removedIds,
        facilityIds,
      };
    }

    // C. STANDALONE/WIZARD CREATE MODE: Execute API calls
    const savedPropertyId = await savePropertyData(apiPayload);

    if (savedPropertyId) {
      // D. Handle Dependent Data (Images & Facilities)
      await handleDependentDataUpdates(savedPropertyId, newFiles, removedIds);

      // E. Final Action
      if (inWizard.value) {
        emits("success", { id: savedPropertyId });
      } else {
        router.push({ name: "properties" });
      }
    }
  } catch (e) {
    console.error("[PropertiesForm] Submission Error:", e);
  } finally {
    submitting.value = false;
  }
};

/**
 * Handles the cancel action, routing/emitting based on wizard mode.
 */
const handleCancel = () => {
  if (inWizard.value) {
    emits("cancel");
  } else {
    router.push({ name: "properties" });
  }
};

// --- 7. EXPOSE FOR WIZARD USE ---
defineExpose({ handleSubmit });
</script>
