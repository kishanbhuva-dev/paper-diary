<template>
  <div
    :class="[
      'bg-white transition-all duration-300',
      inWizard
        ? 'p-4 sm:p-8 rounded-t-3xl sm:rounded-3xl'
        : 'p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50',
    ]"
  >
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-blue-50 rounded-xl">
          <Icon
            icon="mdi:office-building-cog-outline"
            class="text-2xl text-blue-600"
          />
        </div>
        <div>
          <h3 class="text-xl font-bold text-slate-800">Resource Categories</h3>
          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            Define Resource Types
          </p>
        </div>
      </div>

      <button
        type="button"
        class="flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors group w-full sm:w-auto"
        @click="addType"
      >
        <Icon
          icon="mdi:plus-circle"
          class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300"
        />
        Add Type
      </button>
    </div>

    <div class="space-y-4">
      <transition-group name="list-complete">
        <div
          v-for="(type, idx) in types"
          :key="idx"
          class="relative bg-white p-2 sm:p-4 rounded-2xl border border-slate-100 hover:border-blue-200 transition-all duration-300 group/card"
        >
          <div class="grid grid-cols-1 md:grid-cols-12 gap-1 sm:gap-5 items-start">
            <div class="md:col-span-5">
              <BaseInput
                :ref="setInputRef"
                v-model="type.name"
                label="Resource Type Name"
                width="full"
                placeholder="e.g. Deluxe Suite"
                class="text-base sm:text-sm"
              />
            </div>

            <div class="md:col-span-3">
              <BaseInput
                :ref="setInputRef"
                v-model.number="type.price"
                label="Base Price"
                type="number"
                width="full"
                placeholder="0.00"
                :min="0"
                prefix="£"
                class="text-base sm:text-sm"
              />
            </div>

            <div class="md:col-span-3">
              <BaseInput
                :ref="setInputRef"
                v-model.number="type.capacity"
                label="Max Occupancy"
                type="number"
                width="full"
                placeholder="Guests"
                :min="1"
                class="text-base sm:text-sm"
              />
            </div>

            <div class="md:col-span-1 flex justify-end md:mt-7">
              <button
                type="button"
                class="w-full md:w-11 h-11 cursor-pointer text-slate-400 bg-slate-50 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all flex items-center justify-center border border-slate-100 md:border-transparent hover:border-red-100"
                title="Delete type"
                @click="openRemoveTypeModal(idx)"
              >
                <Icon
                  icon="mdi:trash-can-outline"
                  class="w-5 h-5"
                />
                <span class="md:hidden ml-2 font-bold text-sm">Remove Category</span>
              </button>
            </div>
          </div>
        </div>
      </transition-group>
    </div>
  </div>

  <DeleteModal
    v-model="isConfirmationModalVisible"
    title="Remove Category"
    :message="`Are you sure you want to remove the '${typeNameToRemove}' category?`"
    :warning="
      typeIndexToRemove !== null && types[typeIndexToRemove]?.id
        ? 'This will permanently delete this category from the server.'
        : 'This will remove the category from your local list.'
    "
    @confirm="confirmRemoval"
  />
</template>

<script setup>
import { ref, onMounted, onBeforeUpdate, computed } from 'vue';
import BaseInput from '../../components/global/BaseInput.vue';
import DeleteModal from '../../components/global/DeleteModal.vue';
import { Icon } from '@iconify/vue';
import ownerService from '../../services/ownerService';
import { toast } from 'vue-sonner';

const props = defineProps({
  propertyId: { type: [String, Number], required: true },
  inWizard: { type: Boolean, default: false },
  editMode: { type: Boolean, default: false },
});
const emits = defineEmits(['success']);

const types = ref([
  {
    name: '',
    price: null,
    capacity: 1,
    slot: null,
    adjustedPrice: null,
    adjustedStart: null,
    adjustedEnd: null,
  },
]);

const deletedTypeIds = ref([]);
const isConfirmationModalVisible = ref(false);
const typeIndexToRemove = ref(null);
const submitting = ref(false);
const initialSnapshot = ref(null);

const typeNameToRemove = computed(() => {
  const idx = typeIndexToRemove.value;
  if (idx === null || !types.value[idx]) {
    return 'this resource type';
  }
  return types.value[idx].name || 'this resource type';
});

const inputRefs = ref([]);
const setInputRef = (el) => {
  if (el) {
    inputRefs.value.push(el);
  }
};
onBeforeUpdate(() => {
  inputRefs.value = [];
});

const makeSnapshot = (list) =>
  JSON.stringify(
    (list || []).map((t) => ({
      name: t.name || '',
      price: Number(t.price) || 0,
      capacity: Number(t.capacity) || 0,
      slot: t.slot || null,
    }))
  );

const loadExistingTypes = async () => {
  if (!props.propertyId) {
    return;
  }
  try {
    const existing = await ownerService.fetchResourceTypes(props.propertyId);
    if (Array.isArray(existing) && existing.length) {
      types.value = existing.map((r) => ({
        name: r.name || '',
        price: r.price || null,
        capacity: r.capacity || 1,
        id: r.id,
      }));
      types.value.push({
        name: '',
        price: null,
        capacity: 1,
        slot: null,
        adjustedPrice: null,
        adjustedStart: null,
        adjustedEnd: null,
      });
      initialSnapshot.value = makeSnapshot(types.value);
    }
  } catch (err) {
    toast.error(err.message || 'Failed to load types');
  }
};

onMounted(async () => {
  await loadExistingTypes();
});

const addType = () => {
  types.value.push({
    name: '',
    price: null,
    capacity: 1,
    slot: null,
    adjustedPrice: null,
    adjustedStart: null,
    adjustedEnd: null,
  });
};

const openRemoveTypeModal = (idx) => {
  const type = types.value[idx];
  const isBlank = !type.id && (!type.name || type.name.toString().trim() === '');

  if (types.value.length === 1 && !isBlank) {
    return;
  }

  typeIndexToRemove.value = idx;
  if (isBlank) {
    confirmRemoval();
  } else {
    isConfirmationModalVisible.value = true;
  }
};

const confirmRemoval = () => {
  const idx = typeIndexToRemove.value;
  if (idx === null) {
    return;
  }

  const t = types.value[idx];
  if (types.value.length === 1) {
    types.value[0] = {
      name: '',
      price: null,
      capacity: 1,
      slot: null,
      adjustedPrice: null,
      adjustedStart: null,
      adjustedEnd: null,
    };
  } else {
    if (t.id) {
      deletedTypeIds.value.push(t.id);
    }
    types.value.splice(idx, 1);
  }

  isConfirmationModalVisible.value = false;
  typeIndexToRemove.value = null;
};

const validate = () => {
  if (!props.propertyId) {
    return false;
  }
  let isInputsValid = true;

  inputRefs.value.forEach((input) => {
    if (input?.validate && !input.validate()) {
      isInputsValid = false;
    }
  });

  const filled = types.value.filter((t) => t.name?.toString().trim() !== '');
  if (filled.length === 0) {
    toast.error('At least one resource type must be filled.');
    return false;
  }
  return isInputsValid;
};

const handleSubmit = async () => {
  if (!validate() || submitting.value) {
    return null;
  }

  submitting.value = true;
  try {
    const serverList = await ownerService.fetchResourceTypes(props.propertyId);
    const toCreate = [];
    const toUpdate = [];

    for (const t of types.value) {
      const normalized = {
        name: (t.name || '').toString().trim(),
        price: Number(t.price) || 0,
        capacity: Number(t.capacity) || 0,
        slot: t.slot || null,
      };
      if (!normalized.name) {
        continue;
      }

      if (t.id) {
        toUpdate.push({ id: t.id, ...normalized });
      } else {
        const match = (serverList || []).find(
          (s) => s.name?.toLowerCase() === normalized.name.toLowerCase()
        );
        if (match) {
          toUpdate.push({ id: match.id, ...normalized });
        } else {
          toCreate.push(normalized);
        }
      }
    }

    if (props.inWizard && props.editMode) {
      return { toCreate, toUpdate, deleted: [...deletedTypeIds.value] };
    }

    // Process Deletions
    if (deletedTypeIds.value.length) {
      await Promise.all(deletedTypeIds.value.map((id) => ownerService.deleteResourceType(id)));
      deletedTypeIds.value = [];
    }

    // Process Updates
    if (toUpdate.length) {
      await ownerService.resourceTypeMultipleUpdate({
        propertyId: parseInt(props.propertyId, 10),
        ids: toUpdate.map((r) => r.id),
        name: toUpdate.map((r) => r.name),
        price: toUpdate.map((r) => r.price),
        capacity: toUpdate.map((r) => r.capacity),
        slot: toUpdate.map((r) => r.slot),
      });
    }

    // Process Creates
    if (toCreate.length) {
      await ownerService.resourceTypeMultipleStore({
        propertyId: parseInt(props.propertyId, 10),
        name: toCreate.map((r) => r.name),
        price: toCreate.map((r) => r.price),
        capacity: toCreate.map((r) => r.capacity),
        slot: toCreate.map((r) => r.slot),
      });
    }

    const created = await ownerService.fetchResourceTypes(props.propertyId);
    initialSnapshot.value = makeSnapshot(types.value);
    emits('success', { resourceTypes: (created || []).map((r) => r.id) });
    return { resourceTypes: (created || []).map((r) => r.id) };
  } catch (err) {
    toast.error(err.message || 'An error occurred during submission.');
    return null;
  } finally {
    submitting.value = false;
  }
};

const hasChanges = () => initialSnapshot.value !== makeSnapshot(types.value);
defineExpose({ handleSubmit, hasChanges });
</script>

<style scoped>
.list-complete-enter-from,
.list-complete-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
.list-complete-leave-active {
  position: absolute;
  width: 100%;
}
@media (max-width: 640px) {
  input {
    font-size: 16px !important;
  }
}
</style>
