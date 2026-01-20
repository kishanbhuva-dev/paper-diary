<template>
  <div :class="[!inWizard ? 'px-4 sm:px-6 py-8 min-h-screen' : '']">
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
        class="bg-white rounded-3xl overflow-hidden"
        :class="[inWizard ? '' : 'border border-gray-100 shadow-xl shadow-gray-200/40 ']"
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
                placeholder="Describe your property..."
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
                />
                <BaseInput
                  :ref="setInputRef"
                  v-model="formData.email"
                  label="Business Contact Email"
                  type="email"
                  width="full"
                  placeholder="contact@property.com"
                  required
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

            <div class="space-y-8">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <BaseInput
                  :ref="setInputRef"
                  v-model="formData.phone"
                  label="Mobile Phone"
                  placeholder="+447123456789"
                  restrict="phone"
                  :max-length="15"
                  required
                />
                <BaseInput
                  :ref="setInputRef"
                  v-model="formData.telephone"
                  label="Landline Telephone"
                  restrict="phone"
                  placeholder="+44 20 7946 0958"
                  :max-length="15"
                  required
                />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                />
                <BaseInput
                  :ref="setInputRef"
                  v-model="formData.stripeSecretKey"
                  label="Stripe Secret Key"
                  type="password"
                  placeholder="sk_test_..."
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
                  Connect your Stripe account to enable real-time payments.
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
                      class="w-4 h-4 text-blue-600 border-gray-300 rounded"
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
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUpdate, watch } from 'vue';
import { useRoute } from 'vue-router';
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
  phone: '',
  telephone: '',
  images: [],
  facilities: [],
};

const formData = ref({ ...defaultFormData });
const loadingItem = ref(false);
const submitting = ref(false);
const initialImageIds = ref([]);
const availableFacilities = ref([]);
const ownerStripeData = ref({ publicKey: '', secretKey: '' });

const effectiveId = computed(() => props.id || route.params.id || null);
const isEditing = computed(() => !!effectiveId.value);

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
      return;
    }

    const remoteImages = await ownerService.fetchPropertyImages(item.id);
    const loadedImages = formatImages(remoteImages);

    const loadedData = JSON.parse(JSON.stringify(item));
    loadedData.images = loadedImages;
    loadedData.facilities = (item.facilities || []).map((f) => f.id);

    if (!loadedData.stripePublicKey) {
      loadedData.stripePublicKey = ownerStripeData.value.publicKey;
    }
    if (!loadedData.stripeSecretKey) {
      loadedData.stripeSecretKey = ownerStripeData.value.secretKey;
    }

    initialImageIds.value = loadedImages.map((i) => i.id).filter(Boolean);
    formData.value = loadedData;
  } finally {
    loadingItem.value = false;
  }
};

onMounted(async () => {
  try {
    const res = await ownerService.getOwnerDetails();
    if (res?.data?.data) {
      ownerStripeData.value = {
        publicKey: res.data.data.stripePublicKey || '',
        secretKey: res.data.data.stripeSecretKey || '',
      };
    }
    const facs = await ownerService.fetchFacilities();
    availableFacilities.value = Array.isArray(facs) ? facs : [];
  } catch (e) {
    console.error(e);
  }

  if (!isEditing.value) {
    formData.value = {
      ...defaultFormData,
      stripePublicKey: ownerStripeData.value.publicKey,
      stripeSecretKey: ownerStripeData.value.secretKey,
    };
  } else {
    loadPropertyForEdit(effectiveId.value);
  }
});

watch(
  () => effectiveId.value,
  (val) => {
    if (val) {
      loadPropertyForEdit(val);
    } else {
      formData.value = {
        ...defaultFormData,
        stripePublicKey: ownerStripeData.value.publicKey,
        stripeSecretKey: ownerStripeData.value.secretKey,
      };
    }
  }
);

const onImagesReorder = (newImages) => {
  formData.value.images = [...newImages];
};

const handleDependentDataUpdates = async (propertyId, newFiles, removedIds) => {
  if (removedIds.length) {
    await Promise.all(removedIds.map((id) => ownerService.deletePropertyImage(id)));
  }
  let uploaded = [];
  if (newFiles.length) {
    uploaded = await ownerService.addPropertyImages(propertyId, newFiles);
  }
  const uploadedQueue = [...(uploaded || [])];
  const orderedIds = formData.value.images
    .map((img) => img.id || uploadedQueue.shift()?.id)
    .filter(Boolean);
  const tasks = [];
  if (orderedIds.length) {
    tasks.push(ownerService.changePropertyImagePosition(propertyId, orderedIds));
  }
  const facilityIds = Array.isArray(formData.value.facilities)
    ? formData.value.facilities.map((id) => parseInt(id, 10)).filter((n) => n > 0)
    : [];
  if (facilityIds.length) {
    tasks.push(ownerService.setPropertyFacilities({ propertyId, facilityId: facilityIds }));
  }
  await Promise.all(tasks);
};

const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }
  const newFiles = formData.value.images
    .filter((img) => img.file instanceof File)
    .map((i) => i.file);
  const existingIds = formData.value.images.filter((img) => img.id).map((i) => i.id);
  const removedIds = initialImageIds.value.filter((id) => !existingIds.includes(id));
  const apiPayload = JSON.parse(JSON.stringify(formData.value));
  delete apiPayload.images;
  delete apiPayload.facilities;

  if (props.inWizard && props.editMode) {
    return {
      propertyPayload: apiPayload,
      newFiles,
      removedImageIds: removedIds,
      orderedImageIds: formData.value.images.map((img) => img.id).filter(Boolean),
      facilityIds: formData.value.facilities.map((id) => parseInt(id, 10)).filter((n) => n > 0),
    };
  }

  submitting.value = true;
  try {
    let savedId = formData.value.id;
    if (isEditing.value) {
      await ownerService.updateProperty(savedId, apiPayload);
    } else {
      const response = await ownerService.createProperty(apiPayload);
      savedId = response?.data;
    }
    await handleDependentDataUpdates(savedId, newFiles, removedIds);
    emits('success', { id: savedId });
    return { id: savedId };
  } finally {
    submitting.value = false;
  }
};

defineExpose({ handleSubmit, onImagesReorder });
</script>
