<template>
  <div class="px-4 sm:px-6 py-8 bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-indigo-800">
          {{ isEditing ? "Edit Property" : "Add New Property" }}
        </h1>
        <button
          @click="router.push({ name: 'properties' })"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-300 hover:bg-gray-200 transition cursor-pointer duration-150 flex items-center"
        >
          <Icon icon="mdi:arrow-left" class="w-5 h-5 mr-1" />
          Back to List
        </button>
      </div>

      <div class="bg-white rounded-xl overflow-hidden border border-indigo-300">
        <div
          class="p-5 bg-indigo-100 flex justify-between items-center border-b border-indigo-200 shrink-0"
        >
          <h3 class="text-xl font-bold text-indigo-900">Details</h3>
        </div>

        <div v-if="loadingItem" class="p-10 flex justify-center items-center">
          <Icon
            icon="eos-icons:loading"
            class="w-8 h-8 text-indigo-600 animate-spin"
          />
          <span class="ml-3 text-indigo-600 font-semibold"
            >Loading data...</span
          >
        </div>

        <form v-else @submit.prevent="handleSubmit" class="p-6 space-y-4">
          <BaseInput
            :ref="setInputRef"
            v-model="formData.propertyName"
            label="Property Name"
            width="full"
            placeholder="e.g. Seaside Villa"
            required
            :max-length="50"
            :show-count="true"
          />

          <BaseInput
            :ref="setInputRef"
            v-model="formData.address"
            label="Address"
            width="full"
            placeholder="e.g. 123 Ocean Drive"
            required
            :max-length="100"
          />

          <BaseInput
            :ref="setInputRef"
            v-model="formData.email"
            label="Email"
            type="email"
            width="full"
            placeholder="contact@example.com"
            required
            :max-length="50"
          />

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

          <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1"
              >Status <span class="text-red-500">*</span></label
            >
            <div class="relative">
              <select
                v-model="formData.status"
                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none appearance-none bg-white"
              >
                <option :value="1">Active</option>
                <option :value="0">Inactive</option>
              </select>
              <Icon
                icon="mdi:chevron-down"
                class="absolute right-3 top-3 text-gray-400 pointer-events-none"
              />
            </div>
          </div>

          <!-- Facilities -->
          <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1"
              >Facilities <span class="text-red-500">*</span></label
            >
            <div
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 bg-indigo-50 p-4 rounded-lg border border-indigo-200"
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
                  class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                />
                <label
                  :for="`facility-${facility.id}`"
                  class="ml-2 text-sm font-medium text-gray-700 cursor-pointer"
                >
                  {{ facility.name }}
                </label>
              </div>
            </div>
          </div>

          <OwnerImageUploader
            v-model="formData.images"
            :max-files="10"
            @reorder="onImagesReorder"
          />
        </form>

        <div
          class="flex justify-end pt-4 px-6 pb-6 bg-white border-t border-gray-300 shrink-0"
        >
          <button
            type="button"
            @click="router.push({ name: 'properties' })"
            class="px-5 py-2 mr-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-150 outline-indigo-400 cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="handleSubmit"
            class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition duration-150 flex items-center shadow-md outline-indigo-400 shadow-indigo-200 cursor-pointer"
          >
            <Icon icon="ic:round-save" class="w-5 h-5 inline-block mr-1" />
            {{ isEditing ? "Update Property" : "Save New Property" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUpdate } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Icon } from "@iconify/vue";
import { toast } from "vue-sonner";
import BaseInput from "../../components/global/BaseInput.vue";
import OwnerImageUploader from "../../components/owner/OwnerImageUploader.vue";
import ownerService from "../../services/ownerService";

const route = useRoute();
const router = useRouter();
const availableFacilities = ref([]);

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
const initialImageIds = ref([]);

const propertyId = computed(() => route.params.id);
const isEditing = computed(() => !!propertyId.value);

// --- Component Refs for Validation ---
const inputRefs = ref([]);
const setInputRef = (el) => {
  if (el) {
    inputRefs.value.push(el);
  }
};
onBeforeUpdate(() => {
  inputRefs.value = [];
});

// --- Data Initialization ---
const loadPropertyForEdit = async (id) => {
  loadingItem.value = true;
  try {
    const item = await ownerService.fetchPropertyById(id);
    if (item) {
      // Fetch images for this property
      const remoteImages = await ownerService.fetchPropertyImages(item.id);
      const loadedImages = (remoteImages || []).map((img) => ({
        id: img.id,
        url: img.url,
        file: null,
      }));
      const loadedData = JSON.parse(JSON.stringify(item));
      loadedData.images = loadedImages;
      // Prefill facilities from API if available
      const loadedFacilityIds = (item.facilities || []).map((f) => f.id);
      loadedData.facilities = loadedFacilityIds;
      initialImageIds.value = loadedImages.map((i) => i.id).filter(Boolean);
      formData.value = loadedData;
    } else {
      toast.error(`Property with ID ${id} not found or failed to load.`);
      router.push({ name: "properties" });
    }
  } catch (e) {
    toast.error("Failed to load property data.");
    console.error("Error loading property:", e);
    router.push({ name: "properties" });
  } finally {
    loadingItem.value = false;
  }
};

onMounted(() => {
  if (isEditing.value) {
    loadPropertyForEdit(propertyId.value);
  } else {
    formData.value = { ...defaultFormData };
  }

  // Load all available facilities
  (async () => {
    try {
      const facs = await ownerService.fetchFacilities();
      if (Array.isArray(facs) && facs.length) {
        availableFacilities.value = facs.map((f) => ({
          id: f.id,
          name: f.name,
        }));
      }
    } catch (err) {
      console.error("Failed to load facilities:", err);
      toast.error("Failed to load facilities");
    }
  })();
});

// --- Submission Logic ---
const handleSubmit = async () => {
  let isFormValid = true;

  // Run validation on standard inputs
  inputRefs.value.forEach((inputComponent) => {
    if (inputComponent && typeof inputComponent.validate === "function") {
      const isValid = inputComponent.validate();
      if (!isValid) {
        isFormValid = false;
      }
    }
  });

  if (!isFormValid) {
    toast.warning("Please correct the validation errors before submitting.");
    return;
  }

  const action = isEditing.value ? "Update" : "Create";
  const loadingToastId = toast.loading(`${action} property...`);

  try {
    let savedPropertyId = null;

    // Separate files from form data
    const newFiles = formData.value.images.filter(
      (img) => img.file instanceof File
    );
    const apiPayload = JSON.parse(JSON.stringify(formData.value));
    delete apiPayload.images;

    // Create or update property
    if (isEditing.value) {
      await ownerService.updateProperty(formData.value.id, apiPayload);
      savedPropertyId = formData.value.id;
    } else {
      const result = await ownerService.createProperty(apiPayload);
      savedPropertyId = result.data?.id || result.id;
    }

    // Handle images and facilities if property was saved
    if (savedPropertyId) {
      // Calculate which images were removed
      const existingIds = formData.value.images
        .filter((img) => img.id)
        .map((i) => i.id);
      const removedIds = initialImageIds.value.filter(
        (id) => !existingIds.includes(id)
      );

      // Delete removed images
      if (removedIds.length) {
        console.debug("[PropertiesForm] Deleting images:", removedIds);
        try {
          for (const imgId of removedIds) {
            await ownerService.deletePropertyImage(imgId);
          }
        } catch (err) {
          toast.error("Failed to delete some images");
        }
      }

      // Upload new images
      if (newFiles.length) {
        console.debug(
          "[PropertiesForm] Uploading images for property:",
          savedPropertyId
        );
        const filesToUpload = newFiles.map((f) => f.file);
        const uploaded = await ownerService.addPropertyImages(
          savedPropertyId,
          filesToUpload
        );
        if (uploaded && Array.isArray(uploaded)) {
          toast.success(`${uploaded.length} image(s) uploaded`);
        }
      }

      // Refresh images from server
      const remoteImages = await ownerService.fetchPropertyImages(
        savedPropertyId
      );
      // remoteImages is now directly the array (after ownerService fix)
      formData.value.images = (remoteImages || []).map((img) => ({
        id: img.id,
        url: img.url,
        file: null,
      }));

      // Update image order if multiple images
      const orderedIds = formData.value.images.map((i) => i.id).filter(Boolean);
      if (orderedIds.length > 1) {
        try {
          await ownerService.changePropertyImagePosition(
            savedPropertyId,
            orderedIds
          );
        } catch (err) {
          console.debug("[PropertiesForm] Failed to update image order:", err);
        }
      }

      // Update facilities
      if (Array.isArray(formData.value.facilities)) {
        const facilityIds = formData.value.facilities
          .map((id) => parseInt(id, 10))
          .filter((id) => Number.isInteger(id) && id > 0);
        try {
          await ownerService.setPropertyFacilities(
            savedPropertyId,
            facilityIds
          );
          // toast.success("Facilities updated");
        } catch (err) {
          toast.error("Failed to update facilities");
        }
      }

      toast.dismiss(loadingToastId);
      toast.success(`Property ${action.toLowerCase()}d successfully`);
      router.push({ name: "properties" });
    }
  } catch (e) {
    console.error(e);
    toast.dismiss(loadingToastId);
    toast.error(`An unexpected error occurred during ${action.toLowerCase()}.`);
  }
};

// Handle image reordering
const onImagesReorder = async (newImages) => {
  formData.value.images = [...newImages];

  if (isEditing.value && formData.value.id) {
    const ids = formData.value.images.map((i) => i.id).filter(Boolean);
    if (ids.length) {
      try {
        await ownerService.changePropertyImagePosition(formData.value.id, ids);
      } catch (err) {
        console.error(
          "[PropertiesForm] changePropertyImagePosition error:",
          err
        );
        toast.error("Failed to update image order");
      }
    }
  }
};
</script>
