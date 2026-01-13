<template>
  <div :class="[!inWizard ? 'px-4 sm:px-6 py-8 bg-gray-50/50 min-h-screen' : '']">
    <div :class="[!inWizard ? 'max-w-5xl mx-auto' : '']">
      <div
        v-if="!inWizard"
        class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8"
      >
        <div>
          <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
            {{ isEditing ? 'Edit Property' : 'Add New Property' }}
          </h1>
          <p class="text-slate-500 mt-1 font-medium">
            Configure your property details and public profile.
          </p>
        </div>
      </div>

      <div
        class="bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/40 overflow-hidden"
      >
        <div
          v-if="loadingItem"
          class="p-24 flex flex-col justify-center items-center"
        >
          <div class="relative">
            <Icon
              icon="eos-icons:loading"
              class="w-12 h-12 text-blue-600 animate-spin"
            />
            <div class="absolute inset-0 blur-xl bg-blue-400/20 animate-pulse"></div>
          </div>
          <span class="mt-6 text-lg text-slate-600 font-bold tracking-tight"
            >Synchronizing Property Data...</span
          >
        </div>

        <form
          v-else
          @submit.prevent="handleSubmit"
        >
          <div class="p-6 md:p-10">
            <div class="flex items-center gap-3 mb-8">
              <div class="p-2.5 bg-blue-50 rounded-xl">
                <Icon
                  icon="mdi:home-city-outline"
                  class="text-2xl text-blue-600"
                />
              </div>
              <div>
                <h3 class="text-xl font-bold text-slate-800">Property Identity</h3>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
                  General Information
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.propertyName"
                label="Property Name"
                width="full"
                placeholder="e.g. Seaside Luxury Villa"
                required
                :max-length="50"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.description"
                label="Property Description"
                width="full"
                placeholder="Describe your property, its unique features, and surroundings..."
                multiline
                :rows="5"
                :max-length="1000"
                show-count
                required
              />
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <BaseInput
                  :ref="setInputRef"
                  v-model="formData.address"
                  label="Primary Address"
                  width="full"
                  placeholder="e.g. 123 Ocean Drive"
                  required
                  :max-length="100"
                />
                <BaseInput
                  :ref="setInputRef"
                  v-model="formData.email"
                  label="Business Contact Email"
                  type="email"
                  width="full"
                  placeholder="contact@property.com"
                  required
                  :max-length="50"
                />
              </div>
            </div>
          </div>

          <div class="h-px bg-gray-100 mx-10"></div>

          <div class="p-6 md:p-10">
            <div class="flex items-center gap-3 mb-8">
              <div class="p-2.5 bg-emerald-50 rounded-xl">
                <Icon
                  icon="mdi:map-marker-outline"
                  class="text-2xl text-emerald-600"
                />
              </div>
              <div>
                <h3 class="text-xl font-bold text-slate-800">Geolocation & Contact</h3>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
                  Regional Settings
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.city"
                label="City"
                placeholder="London"
                required
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.country"
                label="Country"
                placeholder="United Kingdom"
                required
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.postcode"
                label="Postcode"
                placeholder="SW1A 1AA"
                required
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.telephone"
                label="Phone Number"
                placeholder="+44..."
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.latitude"
                label="Latitude"
                placeholder="51.5072"
                required
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.longitude"
                label="Longitude"
                placeholder="-0.1276"
                required
              />
            </div>
          </div>

          <div class="h-px bg-gray-100 mx-10"></div>

          <div class="p-6 md:p-10 bg-slate-50/50">
            <div class="flex items-center gap-3 mb-8">
              <div class="p-2.5 bg-indigo-50 rounded-xl">
                <Icon
                  icon="mdi:shield-key-outline"
                  class="text-2xl text-indigo-600"
                />
              </div>
              <div>
                <h3 class="text-xl font-bold text-slate-800">Payment Integration</h3>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
                  Stripe Configuration
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-6">
                <BaseInput
                  :ref="setInputRef"
                  v-model="formData.stripePublicKey"
                  label="Stripe Public Key"
                  placeholder="pk_test_..."
                  :pattern="/^pk_(test|live)_[A-Za-z0-9]{24,}$/"
                />
                <BaseInput
                  :ref="setInputRef"
                  v-model="formData.stripeSecretKey"
                  label="Stripe Secret Key"
                  type="password"
                  placeholder="sk_test_..."
                  :pattern="/^sk_(test|live)_[A-Za-z0-9]{24,}$/"
                />
              </div>
              <div
                class="bg-white p-6 rounded-2xl border border-slate-200 border-dashed flex items-center gap-4"
              >
                <Icon
                  icon="logos:stripe"
                  class="text-4xl filter grayscale opacity-50"
                />
                <p class="text-xs text-slate-500 font-medium leading-relaxed">
                  Connect your Stripe account to enable real-time payments. Ensure your keys match
                  the environment (Test vs Live).
                </p>
              </div>
            </div>
          </div>

          <div class="h-px bg-gray-100 mx-10"></div>

          <div class="p-6 md:p-10">
            <div class="flex items-center gap-3 mb-8">
              <div class="p-2.5 bg-amber-50 rounded-xl">
                <Icon
                  icon="mdi:checkbox-multiple-marked-outline"
                  class="text-2xl text-amber-600"
                />
              </div>
              <div>
                <h3 class="text-xl font-bold text-slate-800">Features & Availability</h3>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
                  Amenities & Status
                </p>
              </div>
            </div>

            <div class="space-y-8">
              <div>
                <label class="block text-sm font-bold text-slate-700 mb-3 tracking-tight"
                  >Property Status</label
                >
                <div class="relative max-w-xs">
                  <select
                    v-model="formData.status"
                    class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-blue-500 outline-none appearance-none transition-all font-bold text-slate-700"
                    required
                  >
                    <option :value="1">Active</option>
                    <option :value="0">Inactive</option>
                  </select>
                  <Icon
                    icon="mdi:chevron-down"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-bold text-slate-700 mb-4 tracking-tight"
                  >Included Facilities</label
                >
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                  <label
                    v-for="facility in availableFacilities"
                    :key="facility.id"
                    :class="[
                      'flex items-center gap-3 px-4 py-3 rounded-2xl border transition-all cursor-pointer select-none',
                      formData.facilities.includes(facility.id)
                        ? 'bg-blue-50 border-blue-200 text-blue-700 shadow-sm shadow-blue-100'
                        : 'bg-white border-slate-100 text-slate-600 hover:border-slate-300',
                    ]"
                  >
                    <input
                      v-model="formData.facilities"
                      type="checkbox"
                      :value="facility.id"
                      class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <span class="text-sm font-bold">{{ facility.name }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="h-px bg-gray-100 mx-10"></div>

          <div class="p-6 md:p-10">
            <div class="flex items-center gap-3 mb-8">
              <div class="p-2.5 bg-rose-50 rounded-xl">
                <Icon
                  icon="mdi:image-multiple-outline"
                  class="text-2xl text-rose-600"
                />
              </div>
              <div>
                <h3 class="text-xl font-bold text-slate-800">Visual Gallery</h3>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
                  High Quality Photos
                </p>
              </div>
            </div>

            <div class="bg-slate-50/50 rounded-3xl p-4 border border-slate-100">
              <OwnerImageUploader
                v-model="formData.images"
                :max-files="50"
                @reorder="onImagesReorder"
              />
            </div>
          </div>

          <div
            v-if="!inWizard"
            class="flex items-center justify-between p-8 bg-slate-900 border-t border-slate-800 mt-4"
          >
            <button
              type="button"
              class="px-8 py-3 text-sm font-bold text-slate-400 hover:text-white transition-colors"
              @click="handleCancel"
            >
              Discard Changes
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-10 py-4 bg-blue-600 text-white text-sm font-bold rounded-2xl hover:bg-blue-500 transition-all flex items-center gap-3 shadow-lg shadow-blue-500/20 disabled:opacity-50"
            >
              <Icon
                v-if="submitting"
                icon="eos-icons:loading"
                class="w-5 h-5 animate-spin"
              />
              <Icon
                v-else
                icon="ic:round-save"
                class="w-5 h-5"
              />
              {{ isEditing ? 'Update Property Information' : 'Finalize & Save Property' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUpdate, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Icon } from '@iconify/vue';
import BaseInput from '../../components/global/BaseInput.vue';
import OwnerImageUploader from '../../components/owner/OwnerImageUploader.vue';
import ownerService from '../../services/ownerService';

const props = defineProps({
  inWizard: { type: Boolean, default: false },
  id: { type: [String, Number], default: null },
  editMode: { type: Boolean, default: false },
});
const emits = defineEmits(['success', 'cancel']);

const route = useRoute();
const router = useRouter();

const defaultFormData = {
  status: 1,
  propertyName: '',
  description: '',
  email: '',
  address: '',
  city: '',
  country: '',
  latitude: '',
  longitude: '',
  stripePublicKey: '',
  stripeSecretKey: '',
  postcode: '',
  telephone: '',
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
    if (c && typeof c.validate === 'function') {
      if (!c.validate()) {
        ok = false;
      }
    }
  });
  return ok;
};

const decodeId = (val) => {
  if (!val) {
    return val;
  }
  if (typeof val === 'number' || /^\d+$/.test(String(val))) {
    return Number(val);
  }
  try {
    const maybe = atob(String(val));
    return !isNaN(Number(maybe)) ? Number(maybe) : val;
  } catch {
    return val;
  }
};

const formatImages = (images = []) =>
  (images || []).map((img) => ({
    id: img.id,
    url: img.url ? img.url : `/storage/property/images/${img.image}`,
    file: null,
    image: img.image,
    position: img.position,
  }));

const loadPropertyForEdit = async (id) => {
  loadingItem.value = true;
  try {
    const resolved = decodeId(id);
    const item = await ownerService.fetchPropertyById(resolved);
    if (!item) {
      if (!inWizard.value) {
        router.push({ name: 'properties' });
      }
      return;
    }

    const remoteImages = await ownerService.fetchPropertyImages(item.id);
    const loadedImages = formatImages(remoteImages);
    const loadedFacilityIds = (item.facilities || []).map((f) => f.id);

    const loadedData = JSON.parse(JSON.stringify(item));
    loadedData.images = loadedImages;
    loadedData.facilities = loadedFacilityIds;
    if (loadedData.stripePublicKey === null && loadedData.stripeSecretKey === null) {
      const res = await ownerService.getOwnerDetails();
      const ownerData = res.data.data;
      loadedData.stripePublicKey = ownerData?.stripePublicKey || '';
      loadedData.stripeSecretKey = ownerData?.stripeSecretKey || '';
    }

    initialImageIds.value = loadedImages.map((i) => i.id).filter(Boolean);
    formData.value = loadedData;
  } catch {
    if (!inWizard.value) {
      router.push({ name: 'properties' });
    }
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
    throw new Error(e);
  }

  if (!isEditing.value) {
    formData.value = { ...defaultFormData };
  }
});

watch(
  () => effectiveId.value,
  (val) => {
    if (val) {
      loadPropertyForEdit(val);
    } else {
      formData.value = { ...defaultFormData };
    }
  },
  { immediate: true }
);

const processImages = () => {
  const newFiles = formData.value.images
    .filter((img) => img.file instanceof File)
    .map((i) => i.file);
  const existingIds = formData.value.images.filter((img) => img.id).map((i) => i.id);
  const removedIds = initialImageIds.value.filter((id) => !existingIds.includes(id));
  return { newFiles, removedIds };
};

const handleDependentDataUpdates = async (propertyId, newFiles, removedIds, uiOrder = null) => {
  if (removedIds && removedIds.length) {
    for (const id of removedIds) {
      try {
        // eslint-disable-next-line no-await-in-loop
        await ownerService.deletePropertyImage(id);
      } catch (e) {
        throw new Error(e);
      }
    }
  }

  let uploaded = [];
  if (newFiles && newFiles.length) {
    try {
      uploaded = await ownerService.addPropertyImages(propertyId, newFiles);
    } catch (e) {
      throw new Error(e);
    }
  }

  const uiOrdered = uiOrder || formData.value.images;
  const uploadedIdsQueue = (uploaded || []).map((u) => u.id || null);
  const orderedIds = [];
  for (const slot of uiOrdered) {
    if (slot.id) {
      orderedIds.push(slot.id);
    } else if (slot.file) {
      const next = uploadedIdsQueue.shift();
      if (next) {
        orderedIds.push(next);
      }
    }
  }

  if (orderedIds.length > 1) {
    try {
      await ownerService.changePropertyImagePosition(propertyId, orderedIds);
    } catch (e) {
      throw new Error(e);
    }
  }

  try {
    const remote = await ownerService.fetchPropertyImages(propertyId);
    formData.value.images = formatImages(remote);
    initialImageIds.value = formData.value.images.map((i) => i.id).filter(Boolean);
  } catch (e) {
    throw new Error(e);
  }

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
      throw new Error(e);
    }
  }
};

const onImagesReorder = async (newImages) => {
  formData.value.images = [...newImages];
  if (isEditing.value && formData.value.id && !inWizard.value) {
    const ids = formData.value.images.map((i) => i.id).filter(Boolean);
    if (ids.length > 1) {
      try {
        await ownerService.changePropertyImagePosition(formData.value.id, ids);
      } catch (e) {
        throw new Error(e);
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
    const response = await ownerService.createProperty(apiPayload);
    savedPropertyId = response?.data;
  }
  return savedPropertyId;
};

const handleSubmit = async () => {
  if (submitting.value) {
    return;
  }
  if (!validateForm()) {
    return;
  }
  submitting.value = true;
  try {
    const { newFiles, removedIds } = processImages();
    const apiPayload = JSON.parse(JSON.stringify(formData.value));
    delete apiPayload.images;
    delete apiPayload.facilities;

    if (inWizard.value && props.editMode) {
      const facilityIds = Array.isArray(formData.value.facilities)
        ? formData.value.facilities
            .map((id) => parseInt(id, 10))
            .filter((n) => Number.isInteger(n) && n > 0)
        : [];
      const orderedImageIds = formData.value.images.map((img) => img.id).filter(Boolean);
      return {
        propertyPayload: apiPayload,
        newFiles,
        removedImageIds: removedIds,
        orderedImageIds,
        facilityIds,
      };
    }

    const savedPropertyId = await savePropertyData(apiPayload);
    if (!savedPropertyId) {
      throw new Error('Could not determine saved property id');
    }

    await handleDependentDataUpdates(savedPropertyId, newFiles, removedIds, formData.value.images);

    if (inWizard.value) {
      emits('success', { id: savedPropertyId });
    } else {
      router.push({ name: 'properties' });
    }
  } catch (e) {
    throw new Error(e);
  } finally {
    submitting.value = false;
  }
};

const handleCancel = () => {
  if (inWizard.value) {
    emits('cancel');
  } else {
    router.push({ name: 'properties' });
  }
};

defineExpose({ handleSubmit, onImagesReorder });
</script>
