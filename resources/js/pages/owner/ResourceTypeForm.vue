<template>
  <div
    class="p-8 bg-white rounded-3xl"
    :class="[inWizard ? '' : 'border border-gray-100 shadow-xl shadow-gray-200/50']"
  >
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-blue-50 rounded-xl">
          <Icon
            icon="mdi:office-building-cog-outline"
            class="text-2xl text-blue-600"
          />
        </div>
        <div>
          <h3 class="text-xl font-bold text-slate-800">Resource Categories</h3>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
            Define Resource Types
          </p>
        </div>
      </div>

      <button
        type="button"
        class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors group"
        @click="addType"
      >
        <Icon
          icon="mdi:plus-circle"
          class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300"
        />
        Add New Resource Type
      </button>
    </div>

    <div class="space-y-4">
      <transition-group name="list-complete">
        <div
          v-for="(type, idx) in types"
          :key="idx"
          class="relative bg-white p-5 rounded-2xl border border-slate-100 hover:border-blue-200 transition-all duration-300"
        >
          <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            <div class="md:col-span-5">
              <BaseInput
                :ref="setInputRef"
                v-model="type.name"
                label="Resource Type Name"
                width="full"
                placeholder="e.g. Deluxe Suite"
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
              />
            </div>

            <div class="md:col-span-1 flex justify-end mt-7">
              <button
                type="button"
                class="w-11 h-11 cursor-pointer text-slate-400 bg-slate-50 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all flex items-center justify-center border border-transparent hover:border-red-100"
                title="Delete type"
                @click="openRemoveTypeModal(idx)"
              >
                <Icon
                  icon="mdi:trash-can-outline"
                  class="w-5 h-5"
                />
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
import { ref, watch, onMounted, onBeforeUpdate, computed } from 'vue';
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
const emits = defineEmits(['success', 'cancel']);

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
const typeNameToRemove = computed(() => {
  const idx = typeIndexToRemove.value;
  return idx !== null && types.value[idx]?.name ? types.value[idx].name : 'this resource type';
});

const initialSnapshot = ref(null);
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
    } else {
      types.value = [
        {
          name: '',
          price: null,
          capacity: 1,
          slot: null,
          adjustedPrice: null,
          adjustedStart: null,
          adjustedEnd: null,
        },
      ];
    }
  } catch (err) {
    toast.error(err);
  }
};

onMounted(() => {
  loadExistingTypes();
});
watch(
  () => props.propertyId,
  (val) => {
    if (val) {
      loadExistingTypes();
    }
  }
);

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
  const isDummyOrBlank = !type.id && (!type.name || type.name.toString().trim() === '');
  if (types.value.length === 1 && !isDummyOrBlank) {
    return;
  }
  if (isDummyOrBlank) {
    typeIndexToRemove.value = idx;
    confirmRemoval();
  } else {
    typeIndexToRemove.value = idx;
    isConfirmationModalVisible.value = true;
  }
};

const confirmRemoval = () => {
  const idx = typeIndexToRemove.value;
  if (idx === null || idx === undefined) {
    isConfirmationModalVisible.value = false;
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
    isConfirmationModalVisible.value = false;
    typeIndexToRemove.value = null;
    return;
  }
  if (t && t.id) {
    deletedTypeIds.value.push(t.id);
    types.value.splice(idx, 1);
  } else {
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
  inputRefs.value.forEach((inputComponent) => {
    if (inputComponent && typeof inputComponent.validate === 'function') {
      const isValid = inputComponent.validate();
      if (!isValid) {
        isInputsValid = false;
      }
    }
  });
  const filled = (types.value || []).filter((t) => t.name && t.name.toString().trim() !== '');
  if (filled.length === 0) {
    toast.error('At least one resource type must be filled.');
    return false;
  }
  for (const t of filled) {
    if (isNaN(t.price) || t.price === null || isNaN(t.capacity) || t.capacity === null) {
      toast.error('Price and Capacity must be valid numbers for filled rows.');
      return false;
    }
  }
  return isInputsValid;
};

const submitting = ref(false);

const handleSubmit = async () => {
  if (!validate()) {
    toast.error('Please ensure all required fields are correctly filled.');
    return;
  }
  if (submitting.value) {
    return;
  }
  submitting.value = true;
  try {
    const serverList = await ownerService.fetchResourceTypes(props.propertyId);
    const serverById = (serverList || []).reduce((acc, r) => {
      acc[r.id] = r;
      return acc;
    }, {});
    const serverByName = (serverList || []).reduce((acc, r) => {
      const key = (r.name || '').toString().trim().toLowerCase();
      if (key) {
        acc[key] = r;
      }
      return acc;
    }, {});
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
        const server = serverById[t.id];
        if (!server) {
          toCreate.push(normalized);
        } else {
          const serverNorm = {
            name: (server.name || '').toString().trim(),
            price: Number(server.price) || 0,
            capacity: Number(server.capacity) || 0,
            slot: server.slot || null,
          };
          if (
            serverNorm.name !== normalized.name ||
            serverNorm.price !== normalized.price ||
            serverNorm.capacity !== normalized.capacity ||
            serverNorm.slot !== normalized.slot
          ) {
            toUpdate.push({ id: t.id, ...normalized });
          }
        }
      } else {
        const key = (normalized.name || '').toLowerCase();
        const existingMatch = key ? serverByName[key] : null;
        if (existingMatch) {
          const serverNorm = {
            name: (existingMatch.name || '').toString().trim(),
            price: Number(existingMatch.price) || 0,
            capacity: Number(existingMatch.capacity) || 0,
            slot: existingMatch.slot || null,
          };
          if (
            serverNorm.name !== normalized.name ||
            serverNorm.price !== normalized.price ||
            serverNorm.capacity !== normalized.capacity ||
            serverNorm.slot !== normalized.slot
          ) {
            toUpdate.push({ id: existingMatch.id, ...normalized });
          }
        } else {
          toCreate.push(normalized);
        }
      }
    }
    if (props.inWizard && props.editMode) {
      return { toCreate, toUpdate, deleted: deletedTypeIds.value.slice() };
    }
    if (deletedTypeIds.value && deletedTypeIds.value.length) {
      for (const id of deletedTypeIds.value) {
        try {
          // eslint-disable-next-line no-await-in-loop
          await ownerService.deleteResourceType(id);
        } catch (err) {
          throw new Error(err);
        }
      }
      deletedTypeIds.value = [];
    }
    if (toUpdate.length > 0) {
      const payload = {
        propertyId: parseInt(props.propertyId, 10),
        ids: toUpdate.map((r) => r.id),
        name: toUpdate.map((r) => r.name),
        price: toUpdate.map((r) => r.price),
        capacity: toUpdate.map((r) => r.capacity),
        slot: toUpdate.map((r) => r.slot),
      };
      await ownerService.resourceTypeMultipleUpdate(payload);
    }
    if (toCreate.length > 0) {
      const payload = {
        propertyId: parseInt(props.propertyId, 10),
        name: toCreate.map((r) => r.name),
        price: toCreate.map((r) => r.price),
        capacity: toCreate.map((r) => r.capacity),
        slot: toCreate.map((r) => r.slot),
      };
      await ownerService.resourceTypeMultipleStore(payload);
    }
    const created = await ownerService.fetchResourceTypes(props.propertyId);
    const ids = (created || []).map((r) => r.id);
    initialSnapshot.value = makeSnapshot(types.value);
    emits('success', { resourceTypes: ids });
  } catch (err) {
    toast.error(err.error || 'An error occurred during submission.');
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
</style>
