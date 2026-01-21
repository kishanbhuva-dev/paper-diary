<template>
  <div :class="[!inWizard ? 'px-4 sm:px-10 py-8 min-h-screen bg-slate-50/50' : 'w-full bg-white']">
    <div :class="[!inWizard ? 'max-w-5xl mx-auto' : 'w-full']">
      <div
        :class="[
          !inWizard ? 'bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden' : '',
        ]"
      >
        <div
          v-if="loadingItem"
          class="p-20 flex flex-col items-center"
        >
          <Icon
            icon="eos-icons:loading"
            class="w-12 h-12 text-blue-600 animate-spin"
          />
        </div>

        <form
          v-else
          class="divide-y divide-slate-100"
          @submit.prevent="handleSubmit"
        >
          <div class="p-4 sm:p-10">
            <div class="flex items-center gap-3 mb-6 sm:mb-8">
              <div class="p-2 bg-blue-50 rounded-lg">
                <Icon
                  icon="mdi:home-city-outline"
                  class="text-xl sm:text-2xl text-blue-600"
                />
              </div>
              <h3 class="text-lg sm:text-xl font-bold text-slate-800">Property Identity</h3>
            </div>

            <div class="space-y-6">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.propertyName"
                label="Property Name"
                width="full"
                placeholder="e.g. Seaside Luxury Villa"
                required
                class="text-base"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.description"
                label="Property Description"
                width="full"
                multiline
                :rows="5"
                show-count
                :max-length="1000"
                required
                class="text-base"
              />
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <BaseInput
                  :ref="setInputRef"
                  v-model="formData.address"
                  label="Primary Address"
                  width="full"
                  required
                  class="text-base"
                />
                <BaseInput
                  :ref="setInputRef"
                  v-model="formData.email"
                  label="Contact Email"
                  type="email"
                  width="full"
                  required
                  class="text-base"
                />
              </div>
            </div>
          </div>

          <div class="p-4 sm:p-10">
            <div class="flex items-center gap-3 mb-6 sm:mb-8">
              <div class="p-2 bg-emerald-50 rounded-lg">
                <Icon
                  icon="mdi:map-marker-outline"
                  class="text-xl sm:text-2xl text-emerald-600"
                />
              </div>
              <h3 class="text-lg sm:text-xl font-bold text-slate-800">Geolocation & Contact</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.city"
                label="City"
                required
                class="text-base"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.country"
                label="Country"
                required
                class="text-base"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.postcode"
                label="Postcode"
                required
                class="text-base"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.phone"
                label="Mobile Phone"
                restrict="phone"
                required
                class="text-base"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.telephone"
                label="Landline Telephone"
                restrict="phone"
                required
                class="text-base"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.latitude"
                label="Latitude"
                required
                class="text-base"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.longitude"
                label="Longitude"
                required
                class="text-base"
              />
            </div>
          </div>

          <div class="p-4 sm:p-10 bg-slate-50/30">
            <div class="flex items-center gap-3 mb-6 sm:mb-8">
              <div class="p-2 bg-indigo-50 rounded-lg">
                <Icon
                  icon="mdi:shield-key-outline"
                  class="text-xl sm:text-2xl text-indigo-600"
                />
              </div>
              <h3 class="text-lg sm:text-xl font-bold text-slate-800">Payment Integration</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <BaseInput
                :ref="setInputRef"
                v-model="formData.stripePublicKey"
                label="Stripe Public Key"
                placeholder="pk_test_..."
                class="text-base"
              />
              <BaseInput
                :ref="setInputRef"
                v-model="formData.stripeSecretKey"
                label="Stripe Secret Key"
                type="password"
                placeholder="sk_test_..."
                class="text-base"
              />
            </div>
          </div>

          <div class="p-4 sm:p-10">
            <div class="flex items-center gap-3 mb-6 sm:mb-8">
              <div class="p-2 bg-amber-50 rounded-lg">
                <Icon
                  icon="mdi:checkbox-multiple-marked-outline"
                  class="text-xl sm:text-2xl text-amber-600"
                />
              </div>
              <h3 class="text-lg sm:text-xl font-bold text-slate-800">Amenities</h3>
            </div>

            <div class="mb-8 w-full sm:max-w-xs">
              <label class="block text-sm font-bold text-slate-700 mb-2">Status</label>
              <select
                v-model="formData.status"
                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:border-blue-500 outline-none font-bold text-base"
              >
                <option :value="1">Active</option>
                <option :value="0">Inactive</option>
              </select>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <label
                v-for="facility in availableFacilities"
                :key="facility.id"
                :class="[
                  'flex items-center gap-2 px-3 py-3 rounded-xl border transition-all cursor-pointer',
                  formData.facilities.includes(facility.id)
                    ? 'bg-blue-50 border-blue-200 text-blue-700'
                    : 'bg-white border-slate-100 shadow-sm',
                ]"
              >
                <input
                  v-model="formData.facilities"
                  type="checkbox"
                  :value="facility.id"
                  class="w-4 h-4 rounded text-blue-600 border-slate-300"
                />
                <span class="text-xs font-bold truncate">{{ facility.name }}</span>
              </label>
            </div>
          </div>

          <div class="p-4 sm:p-10">
            <div class="flex items-center gap-3 mb-6 sm:mb-8">
              <div class="p-2 bg-rose-50 rounded-lg">
                <Icon
                  icon="mdi:image-multiple-outline"
                  class="text-xl sm:text-2xl text-rose-600"
                />
              </div>
              <h3 class="text-lg sm:text-xl font-bold text-slate-800">Visual Gallery</h3>
            </div>
            <div class="bg-slate-50/50 rounded-2xl p-2 sm:p-6 border border-slate-100">
              <OwnerImageUploader
                v-model="formData.images"
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
import { ref, computed, onMounted, onBeforeUpdate } from 'vue';
import { useRoute } from 'vue-router';
import { Icon } from '@iconify/vue';
import BaseInput from '../../components/global/BaseInput.vue';
import OwnerImageUploader from '../../components/owner/OwnerImageUploader.vue';
import ownerService from '../../services/ownerService';

const props = defineProps({
  inWizard: { type: Boolean, default: false },
  id: { type: [String, Number], default: null },
});
const emits = defineEmits(['success']);
const route = useRoute();

const formData = ref({
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
});

const loadingItem = ref(false);
const availableFacilities = ref([]);
const initialImageIds = ref([]);
const inputRefs = ref([]);
const setInputRef = (el) => {
  if (el) {
    inputRefs.value.push(el);
  }
};
onBeforeUpdate(() => {
  inputRefs.value = [];
});

const effectiveId = computed(() => props.id || route.params.id || null);
const isEditing = computed(() => !!effectiveId.value);

const validateForm = () => {
  let ok = true;
  inputRefs.value.forEach((c) => {
    if (c?.validate && !c.validate()) {
      ok = false;
    }
  });
  return ok;
};

const loadPropertyForEdit = async (id) => {
  loadingItem.value = true;
  try {
    const item = await ownerService.fetchPropertyById(id);
    if (item) {
      const imgs = await ownerService.fetchPropertyImages(item.id);
      formData.value = {
        ...item,
        facilities: (item.facilities || []).map((f) => f.id),
        images: imgs.map((i) => ({ ...i, url: `/storage/property/images/${i.image}` })),
      };
      initialImageIds.value = imgs.map((i) => i.id);
    }
  } catch (err) {
    console.error(err);
  } finally {
    loadingItem.value = false;
  }
};

onMounted(async () => {
  try {
    const res = await ownerService.getOwnerDetails();
    if (res?.data?.data) {
      formData.value.stripePublicKey = res.data.data.stripePublicKey || '';
      formData.value.stripeSecretKey = res.data.data.stripeSecretKey || '';
    }
    const facs = await ownerService.fetchFacilities();
    availableFacilities.value = Array.isArray(facs) ? facs : [];
    if (effectiveId.value) {
      await loadPropertyForEdit(effectiveId.value);
    }
  } catch (e) {
    console.error(e);
  }
});

const onImagesReorder = (imgs) => {
  formData.value.images = imgs;
};

const handleSubmit = async () => {
  if (!validateForm()) {
    return null;
  }
  const newFiles = formData.value.images.filter((i) => i.file instanceof File).map((i) => i.file);
  const existingIds = formData.value.images.filter((i) => i.id).map((i) => i.id);
  const removedIds = initialImageIds.value.filter((id) => !existingIds.includes(id));
  const payload = { ...formData.value };
  delete payload.images;
  delete payload.facilities;

  if (props.inWizard) {
    return {
      propertyPayload: payload,
      newFiles,
      removedImageIds: removedIds,
      orderedImageIds: existingIds,
      facilityIds: formData.value.facilities,
    };
  }

  try {
    const id = isEditing.value
      ? effectiveId.value
      : (await ownerService.createProperty(payload)).data;
    await ownerService.addPropertyImages(id, newFiles);
    emits('success', { id });
    return { id };
  } catch (err) {
    console.error(err);
    throw err;
  }
};

defineExpose({ handleSubmit });
</script>

<style scoped>
@media (max-width: 640px) {
  input,
  select,
  textarea {
    font-size: 16px !important;
  }
}
</style>
