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
                :pattern="/^[0-9+\-\s()]*$/"
                custom-error="Only numbers and + - () allowed"
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
                  required
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
                >Facilities 
                <!-- <span class="text-red-500">*</span> -->
                </label
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
              :max-files="50"
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

const props = defineProps({
  inWizard: { type: Boolean, default: false },
  id: { type: [String, Number], default: null },
  editMode: { type: Boolean, default: false },
});
const emits = defineEmits(["success", "cancel"]);

const route = useRoute();
const router = useRouter();

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
const initialImageIds = ref([]);
const availableFacilities = ref([]);

const effectiveId = computed(() => props.id || route.params.id || null);
const isEditing = computed(() => !!effectiveId.value);
const inWizard = computed(() => !!props.inWizard);

const inputRefs = ref([]);
const setInputRef = (el) => el && inputRefs.value.push(el);
onBeforeUpdate(() => (inputRefs.value = []));

const validateForm = () => {
  let ok = true;
  inputRefs.value.forEach((c) => {
    if (c && typeof c.validate === "function") {
      if (!c.validate()) ok = false;
    }
  });
  return ok;
};

const decodeId = (val) => {
  if (!val) return val;
  if (typeof val === "number" || /^\d+$/.test(String(val))) return Number(val);
  try {
    const maybe = atob(String(val));
    return !isNaN(Number(maybe)) ? Number(maybe) : val;
  } catch (e) {
    return val;
  }
};

const formatImages = (images = []) => {
  return (images || []).map((img) => ({
    id: img.id,
    url: img.url ? img.url : `/storage/property/images/${img.image}`,
    file: null,
    image: img.image,
    position: img.position,
  }));
};

const loadPropertyForEdit = async (id) => {
  loadingItem.value = true;
  try {
    const resolved = decodeId(id);
    const item = await ownerService.fetchPropertyById(resolved);
    if (!item) {
      if (!inWizard.value) router.push({ name: "properties" });
      return;
    }

    const remoteImages = await ownerService.fetchPropertyImages(item.id);
    const loadedImages = formatImages(remoteImages);
    const loadedFacilityIds = (item.facilities || []).map((f) => f.id);

    const loadedData = JSON.parse(JSON.stringify(item));
    loadedData.images = loadedImages;
    loadedData.facilities = loadedFacilityIds;

    initialImageIds.value = loadedImages.map((i) => i.id).filter(Boolean);
    formData.value = loadedData;
  } catch (err) {
    console.error("Failed to load property:", err);
    if (!inWizard.value) router.push({ name: "properties" });
  } finally {
    loadingItem.value = false;
  }
};

onMounted(async () => {
  try {
    const facs = await ownerService.fetchFacilities();
    availableFacilities.value = Array.isArray(facs)
      ? facs.map((f) => ({ id: f.id, name: f.name }))
      : [];
  } catch (e) {
    console.error("Failed to load facilities:", e);
  }

  if (!isEditing.value) formData.value = { ...defaultFormData };
});

watch(
  () => effectiveId.value,
  (val) => {
    if (val) loadPropertyForEdit(val);
    else formData.value = { ...defaultFormData };
  },
  { immediate: true }
);

// Images helper: get list of File objects to upload and ids removed
const processImages = () => {
  const newFiles = formData.value.images
    .filter((img) => img.file instanceof File)
    .map((i) => i.file);
  const existingIds = formData.value.images
    .filter((img) => img.id)
    .map((i) => i.id);
  const removedIds = initialImageIds.value.filter(
    (id) => !existingIds.includes(id)
  );
  return { newFiles, removedIds };
};

// Handle image deletes/uploads and ordering, then facility sync
const handleDependentDataUpdates = async (
  propertyId,
  newFiles,
  removedIds,
  uiOrder = null
) => {
  // Delete removed images
  if (removedIds && removedIds.length) {
    for (const id of removedIds) {
      try {
        await ownerService.deletePropertyImage(id);
      } catch (e) {
        console.debug("Failed to delete image:", id, e);
      }
    }
  }

  // Upload new files in same order as provided
  let uploaded = [];
  if (newFiles && newFiles.length) {
    try {
      // newFiles is an array of File objects
      uploaded = await ownerService.addPropertyImages(propertyId, newFiles);
    } catch (e) {
      console.debug("Failed to upload images:", e);
    }
  }

  // Compute ordered IDs according to uiOrder when provided, otherwise use current UI state
  const uiOrdered = uiOrder || formData.value.images;
  const uploadedIdsQueue = (uploaded || []).map((u) => u.id || null);
  const orderedIds = [];
  for (const slot of uiOrdered) {
    if (slot.id) orderedIds.push(slot.id);
    else if (slot.file) {
      const next = uploadedIdsQueue.shift();
      if (next) orderedIds.push(next);
    }
  }

  // Apply order if more than one id
  if (orderedIds.length > 1) {
    try {
      await ownerService.changePropertyImagePosition(propertyId, orderedIds);
    } catch (e) {
      console.debug("Failed to change image positions:", e);
    }
  }

  // Refresh images and set local state
  try {
    const remote = await ownerService.fetchPropertyImages(propertyId);
    formData.value.images = formatImages(remote);
    initialImageIds.value = formData.value.images
      .map((i) => i.id)
      .filter(Boolean);
  } catch (e) {
    console.debug("Failed to refresh images:", e);
  }

  // Sync facilities
  const facilityIds = Array.isArray(formData.value.facilities)
    ? formData.value.facilities
        .map((id) => parseInt(id, 10))
        .filter((n) => Number.isInteger(n) && n > 0)
    : [];
  if (facilityIds.length) {
    try {
      await ownerService.setPropertyFacilities({
        propertyId,
        facilityId: facilityIds,
      });
    } catch (e) {
      console.debug("Failed to sync facilities:", e);
    }
  }
};

const onImagesReorder = async (newImages) => {
  formData.value.images = [...newImages];
  // Only apply immediate reorder for non-wizard inline edit (keep wizard changes deferred)
  if (isEditing.value && formData.value.id && !inWizard.value) {
    const ids = formData.value.images.map((i) => i.id).filter(Boolean);
    if (ids.length > 1) {
      try {
        await ownerService.changePropertyImagePosition(formData.value.id, ids);
      } catch (e) {
        console.error("[PropertiesForm] changePropertyImagePosition error:", e);
      }
    }
  }
};

const savePropertyData = async (apiPayload) => {
  let savedPropertyId = null;
  if (isEditing.value) {
    await ownerService.updateProperty(formData.value.id, apiPayload);
    savedPropertyId = formData.value.id;
  } else {
    // FIX APPLIED HERE: The backend now correctly returns the ID in the 'data' field.
    const response = await ownerService.createProperty(apiPayload);
    // Directly retrieve the ID from the response data field.
    savedPropertyId = response?.data;

    // Removed the fragile fallback logic that attempted to fetch the newest property by name/limit.
  }
  return savedPropertyId;
};

const handleSubmit = async () => {
  if (submitting.value) return;
  if (!validateForm()) return;
  submitting.value = true;
  try {
    const { newFiles, removedIds } = processImages();
    const apiPayload = JSON.parse(JSON.stringify(formData.value));
    delete apiPayload.images;
    delete apiPayload.facilities;

    // Edit-in-wizard: return diffs to parent for final apply
    if (inWizard.value && props.editMode) {
      const facilityIds = Array.isArray(formData.value.facilities)
        ? formData.value.facilities
            .map((id) => parseInt(id, 10))
            .filter((n) => Number.isInteger(n) && n > 0)
        : [];
      const orderedImageIds = formData.value.images
        .map((img) => img.id)
        .filter(Boolean);
      return {
        propertyPayload: apiPayload,
        newFiles,
        removedImageIds: removedIds,
        orderedImageIds,
        facilityIds,
      };
    }

    const savedPropertyId = await savePropertyData(apiPayload);
    if (!savedPropertyId)
      throw new Error("Could not determine saved property id");

    // Apply dependent updates using UI order preserved
    await handleDependentDataUpdates(
      savedPropertyId,
      newFiles,
      removedIds,
      formData.value.images
    );

    if (inWizard.value) emits("success", { id: savedPropertyId });
    else router.push({ name: "properties" });
  } catch (e) {
    console.error("[PropertiesForm] Submission Error:", e);
  } finally {
    submitting.value = false;
  }
};

const handleCancel = () => {
  if (inWizard.value) emits("cancel");
  else router.push({ name: "properties" });
};

defineExpose({ handleSubmit, onImagesReorder });
</script>
