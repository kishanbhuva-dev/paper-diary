<template>
  <div
    class="p-6 mb-4 bg-white rounded-2xl shadow-inner border border-blue-100"
  >
    <h3 class="text-xl font-bold text-blue-700 mb-4 flex items-center gap-2">
      <Icon icon="mdi:bed-empty-outline" class="text-2xl" /> Resource Types
    </h3>

    <div class="space-y-4">
      <div
        v-for="(type, idx) in types"
        :key="idx"
        class="grid grid-cols-1 md:grid-cols-5 gap-3 p-4 bg-blue-50 rounded-lg border border-blue-200 items-start"
      >
        <div class="md:col-span-2 self-stretch">
          <BaseInput
            v-model="type.name"
            label="Name"
            width="full"
            placeholder="e.g. Deluxe"
            :ref="setInputRef"
          />
        </div>

        <div class="self-stretch">
          <BaseInput
            v-model.number="type.price"
            label="Price"
            type="number"
            width="full"
            placeholder="Base price"
            :min="0"
            :ref="setInputRef"
          />
        </div>

        <div class="self-stretch">
          <BaseInput
            v-model.number="type.capacity"
            label="Capacity"
            type="number"
            width="full"
            placeholder="Guests"
            :min="1"
            :ref="setInputRef"
          />
        </div>

        <div class="flex items-start self-stretch mt-6">
          <button
            type="button"
            @click="openRemoveTypeModal(idx)"
            class="px-2 py-2 cursor-pointer text-sm text-white bg-red-600 rounded-xl hover:bg-red-700 flex items-center justify-center"
            title="Delete type"
          >
            <Icon icon="mdi:delete-forever" class="w-6 h-6" />
          </button>
        </div>
      </div>

      <div class="pt-2">
        <button
          type="button"
          @click="addType"
          class="px-4 py-2 cursor-pointer text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700"
        >
          Add Type
        </button>
      </div>
    </div>

    <div class="flex justify-end mt-6">
      <div v-if="!props.inWizard">
        <button
          type="button"
          @click="cancel"
          class="px-5 py-2 mr-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="handleSubmit"
          class="px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700"
        >
          Next
        </button>
      </div>
    </div>
  </div>

  <DeleteModal
    v-model="isConfirmationModalVisible"
    :title="'Remove Resource Type'"
    :message="`Are you sure you want to remove the resource type: ${typeNameToRemove}?`"
    :warning="
      typeIndexToRemove !== null && types[typeIndexToRemove]?.id
        ? 'This type will be permanently deleted from the server upon submission.'
        : 'This is a local change and will be removed from the list.'
    "
    @confirm="confirmRemoval"
  />
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUpdate, computed } from "vue";
import BaseInput from "../../components/global/BaseInput.vue";
import DeleteModal from "../../components/global/DeleteModal.vue";
import { Icon } from "@iconify/vue";
import ownerService from "../../services/ownerService";
import { toast } from "vue-sonner";

const props = defineProps({
  propertyId: { type: [String, Number], required: true },
  inWizard: { type: Boolean, default: false },
  editMode: { type: Boolean, default: false },
});
const emits = defineEmits(["success", "cancel"]);

const types = ref([
  {
    name: "",
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
  return idx !== null && types.value[idx]?.name
    ? types.value[idx].name
    : "this resource type";
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

const makeSnapshot = (list) => {
  return JSON.stringify(
    (list || []).map((t) => ({
      name: t.name || "",
      price: Number(t.price) || 0,
      capacity: Number(t.capacity) || 0,
      slot: t.slot || null,
    }))
  );
};

const loadExistingTypes = async () => {
  if (!props.propertyId) return;
  try {
    const existing = await ownerService.fetchResourceTypes(props.propertyId);
    if (Array.isArray(existing) && existing.length) {
      types.value = existing.map((r) => ({
        name: r.name || "",
        price: r.price || null,
        capacity: r.capacity || 1,
        id: r.id,
      }));
      types.value.push({
        name: "",
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
          name: "",
          price: null,
          capacity: 1,
          slot: null,
          adjustedPrice: null,
          adjustedStart: null,
          adjustedEnd: null,
        },
      ];
    }
  } catch (err) {}
};

onMounted(() => {
  loadExistingTypes();
});

watch(
  () => props.propertyId,
  (val) => {
    if (val) loadExistingTypes();
  }
);

const addType = () => {
  types.value.push({
    name: "",
    price: null,
    capacity: 1,
    slot: null,
    adjustedPrice: null,
    adjustedStart: null,
    adjustedEnd: null,
  });
};

const openRemoveTypeModal = (idx) => {
  // We remove the length check here to allow removing the last one if it's a dummy/blank row.
  // The logic inside confirmRemoval prevents the list from becoming completely empty, if needed.

  const type = types.value[idx];

  // Checks if the row is new (no id) AND if the name field is empty/blank
  const isDummyOrBlank =
    !type.id && (!type.name || type.name.toString().trim() === "");

  if (types.value.length === 1 && !isDummyOrBlank) {
    // If only one remains AND it has content/ID, we prevent deletion.
    return;
  }

  if (isDummyOrBlank) {
    // If it's a dummy/blank row, skip the modal and proceed straight to removal
    typeIndexToRemove.value = idx;
    confirmRemoval();
  } else {
    // If it has an ID or a name (user has started filling it out), show the modal
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
    // If only one item remains, reset it instead of deleting the array item
    // This is safer than having an empty array of resource types
    types.value[0] = {
      name: "",
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
  if (!props.propertyId) return false;

  let isInputsValid = true;
  inputRefs.value.forEach((inputComponent) => {
    if (inputComponent && typeof inputComponent.validate === "function") {
      const isValid = inputComponent.validate();
      if (!isValid) isInputsValid = false;
    }
  });

  const filled = (types.value || []).filter(
    (t) => t.name && t.name.toString().trim() !== ""
  );

  if (filled.length === 0) {
    toast.error("At least one resource type must be filled.");
    return false;
  }

  for (const t of filled) {
    if (
      isNaN(t.price) ||
      t.price === null ||
      isNaN(t.capacity) ||
      t.capacity === null
    ) {
      toast.error("Price and Capacity must be valid numbers for filled rows.");
      return false;
    }
  }

  return isInputsValid;
};
const submitting = ref(false);

const handleSubmit = async () => {
  if (!validate()) {
    toast.error("Please ensure all required fields are correctly filled.");
    return;
  }

  if (submitting.value) return;
  submitting.value = true;

  try {
    const serverList = await ownerService.fetchResourceTypes(props.propertyId);
    const serverById = (serverList || []).reduce((acc, r) => {
      acc[r.id] = r;
      return acc;
    }, {});

    const serverByName = (serverList || []).reduce((acc, r) => {
      const key = (r.name || "").toString().trim().toLowerCase();
      if (key) acc[key] = r;
      return acc;
    }, {});

    const toCreate = [];
    const toUpdate = [];

    for (const t of types.value) {
      const normalized = {
        name: (t.name || "").toString().trim(),
        price: Number(t.price) || 0,
        capacity: Number(t.capacity) || 0,
        slot: t.slot || null,
      };

      if (!normalized.name) continue;

      if (t.id) {
        const server = serverById[t.id];
        if (!server) {
          toCreate.push(normalized);
        } else {
          const serverNorm = {
            name: (server.name || "").toString().trim(),
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
        const key = (normalized.name || "").toLowerCase();
        const existingMatch = key ? serverByName[key] : null;
        if (existingMatch) {
          const serverNorm = {
            name: (existingMatch.name || "").toString().trim(),
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
          } else {
          }
        } else {
          toCreate.push(normalized);
        }
      }
    }

    if (props.inWizard && props.editMode) {
      return {
        toCreate,
        toUpdate,
        deleted: deletedTypeIds.value.slice(),
      };
    }

    if (deletedTypeIds.value && deletedTypeIds.value.length) {
      for (const id of deletedTypeIds.value) {
        try {
          await ownerService.deleteResourceType(id);
        } catch (err) {}
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
    emits("success", { resourceTypes: ids });
  } catch (err) {
    toast.error("An error occurred during submission.");
  } finally {
    submitting.value = false;
  }
};

const hasChanges = () => {
  return initialSnapshot.value !== makeSnapshot(types.value);
};

const cancel = () => {
  emits("cancel");
};

defineExpose({ handleSubmit, hasChanges });
</script>
