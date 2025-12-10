<template>
  <div class="p-6 bg-white rounded-2xl shadow-inner border border-blue-100">
    <h3 class="text-xl font-bold text-blue-700 mb-4 flex items-center gap-2">
      <Icon icon="mdi:bed-empty-outline" class="text-2xl" /> Resource Types
    </h3>

    <div class="space-y-4">
      <div
        v-for="(type, idx) in types"
        :key="idx"
        class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end p-4 bg-blue-50 rounded-lg border border-blue-200"
      >
        <div class="md:col-span-2">
          <label class="text-sm font-medium text-slate-700">Name</label>
          <input
            v-model="type.name"
            type="text"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-100"
            placeholder="e.g. Deluxe"
          />
        </div>

        <div>
          <label class="text-sm font-medium text-slate-700">Price</label>
          <input
            v-model.number="type.price"
            type="number"
            min="0"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-100"
            placeholder="Base price"
          />
        </div>

        <div>
          <label class="text-sm font-medium text-slate-700">Capacity</label>
          <input
            v-model.number="type.capacity"
            type="number"
            min="1"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-100"
            placeholder="Guests"
          />
        </div>

        <div class="flex gap-2">
          <button
            type="button"
            @click="removeType(idx)"
            class="px-3 py-2 cursor-pointer text-sm text-white bg-red-600 rounded-xl hover:bg-red-700 flex items-center justify-center"
            title="Delete type"
          >
            <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
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

  <div
    v-if="isConfirmationModalVisible"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800/50 bg-opacity-70"
    @click.self="closeModal"
  >
    <div
      class="bg-white rounded-xl shadow-3xl w-full max-w-md transform transition-all overflow-hidden border border-gray-100 animate-slide-in"
    >
      <div class="p-6 flex items-center bg-blue-50 border-b border-blue-200">
        <div class="p-2 mr-4 bg-blue-100 rounded-full">
          <Icon icon="mdi:alert-circle-outline" class="w-6 h-6 text-blue-600" />
        </div>
        <h3 class="text-xl font-semibold text-gray-800">
          Remove Resource Type
        </h3>
      </div>

      <div class="p-6">
        <p class="text-gray-600">
          You are about to remove the resource type. This action is irreversible
          once you save the form. Are you sure you want to proceed with the
          removal?
        </p>
      </div>

      <div
        class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3"
      >
        <button
          @click="closeModal"
          class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 transition duration-150 shadow-sm"
        >
          Cancel
        </button>

        <button
          @click="confirmRemoval"
          class="px-5 py-2 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 shadow-md shadow-red-200"
        >
          Confirm Removal
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import BaseInput from "../../components/global/BaseInput.vue";
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
// track ids removed by the user in the current UI session; actual delete
// will be performed when user submits the form
const deletedTypeIds = ref([]);

// --- CONFIRMATION MODAL STATE ---
const isConfirmationModalVisible = ref(false);
const typeIndexToRemove = ref(null);
// --------------------------------

const initialSnapshot = ref(null);

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

// Load existing resource types for this property when available
const loadExistingTypes = async () => {
  if (!props.propertyId) return;
  try {
    const existing = await ownerService.fetchResourceTypes(props.propertyId);
    console.debug("[ResourceSetupForm] loadExistingTypes fetched:", existing);
    if (Array.isArray(existing) && existing.length) {
      types.value = existing.map((r) => ({
        name: r.name || "",
        price: r.price || null,
        capacity: r.capacity || 1,
        id: r.id,
      }));
      // Always keep one empty row at the end for quick entry
      types.value.push({
        name: "",
        price: null,
        capacity: 1,
        slot: null,
        adjustedPrice: null,
        adjustedStart: null,
        adjustedEnd: null,
      });
      // record initial snapshot to detect changes
      initialSnapshot.value = makeSnapshot(types.value);
    } else {
      // reset to at least one empty row if none found
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
  } catch (err) {
    console.debug(
      "[ResourceSetupForm] failed to load existing resource types",
      err
    );
  }
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

// --- MODIFIED removeType to open Modal ---
const removeType = (idx) => {
  if (types.value.length === 1) return;
  typeIndexToRemove.value = idx;
  isConfirmationModalVisible.value = true;
};

const closeModal = () => {
  isConfirmationModalVisible.value = false;
  typeIndexToRemove.value = null;
};

// --- NEW: Confirmation Logic ---
const confirmRemoval = () => {
  const idx = typeIndexToRemove.value;
  if (idx === null || idx === undefined) {
    closeModal();
    return;
  }

  const t = types.value[idx];

  if (t && t.id) {
    // mark for deletion and remove from UI
    deletedTypeIds.value.push(t.id);
    types.value.splice(idx, 1);
  } else {
    // local-only row, just remove
    types.value.splice(idx, 1);
  }

  // Reset state after deletion
  closeModal();
};
// -----------------------------

const validate = () => {
  if (!props.propertyId) return false;
  // Ignore completely empty rows (no name) — they are the persistent blank.
  const filled = (types.value || []).filter(
    (t) => t.name && t.name.toString().trim() !== ""
  );
  if (filled.length === 0) return false; // require at least one type
  for (const t of filled) {
    if (t.price === null || isNaN(t.price)) return false;
  }
  return true;
};
const submitting = ref(false);

const handleSubmit = async () => {
  if (!validate()) {
    toast.error("Fill the Resource Type");
    return;
  }

  if (submitting.value) return;
  submitting.value = true;

  try {
    // Fetch latest server list to determine which rows are new vs existing
    const serverList = await ownerService.fetchResourceTypes(props.propertyId);
    const serverById = (serverList || []).reduce((acc, r) => {
      acc[r.id] = r;
      return acc;
    }, {});

    // Also map by normalized name to handle cases where an existing server row
    // matches a client-added row by name (avoid creating a duplicate)
    const serverByName = (serverList || []).reduce((acc, r) => {
      const key = (r.name || "").toString().trim().toLowerCase();
      if (key) acc[key] = r;
      return acc;
    }, {});

    const toCreate = [];
    const toUpdate = [];

    for (const t of types.value) {
      // Normalize values for comparison
      const normalized = {
        name: (t.name || "").toString().trim(),
        price: Number(t.price) || 0,
        capacity: Number(t.capacity) || 0,
        slot: t.slot || null,
      };

      // Skip empty placeholder rows (no name provided)
      if (!normalized.name) continue;

      if (t.id) {
        const server = serverById[t.id];
        if (!server) {
          // Not present on server (unexpected) - treat as create
          toCreate.push(normalized);
        } else {
          const serverNorm = {
            name: (server.name || "").toString().trim(),
            price: Number(server.price) || 0,
            capacity: Number(server.capacity) || 0,
            slot: server.slot || null,
          };
          // If any field changed, schedule update
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
        // No id -> new row on client. But check if name matches an existing server row
        const key = (normalized.name || "").toLowerCase();
        const existingMatch = key ? serverByName[key] : null;
        if (existingMatch) {
          // If the client added a row with a name that already exists on server,
          // treat it as an update to that id if fields differ; otherwise skip.
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
            // unchanged duplicate by name, skip entirely
          }
        } else {
          // Truly new row, create
          toCreate.push(normalized);
        }
      }
    }

    // If we're editing inside the wizard, do not perform API calls now.
    // Instead return the computed diffs so the parent can apply them on final Submit.
    if (props.inWizard && props.editMode) {
      return {
        toCreate,
        toUpdate,
        deleted: deletedTypeIds.value.slice(),
      };
    }

    // Perform deletes first (if any): user removed rows in UI; remove them server-side
    if (deletedTypeIds.value && deletedTypeIds.value.length) {
      for (const id of deletedTypeIds.value) {
        try {
          await ownerService.deleteResourceType(id);
        } catch (err) {
          console.debug(
            "[ResourceSetupForm] failed to delete resource type",
            id,
            err
          );
        }
      }
      // clear deleted list after attempting deletes
      deletedTypeIds.value = [];
    }

    // Perform updates first (if any)
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

    // Then create new ones (if any)
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

    // Finally fetch created/updated resource types to get fresh ids
    const created = await ownerService.fetchResourceTypes(props.propertyId);
    const ids = (created || []).map((r) => r.id);
    // update snapshot after successful save
    initialSnapshot.value = makeSnapshot(types.value);
    emits("success", { resourceTypes: ids });
  } catch (err) {
    // Handle error logging or user feedback here
  } finally {
    submitting.value = false;
  }
};

// expose handleSubmit and helpers to parent

const cancel = () => {
  emits("cancel");
};

// expose a quick helper to check if current types differ from initial snapshot
const hasChanges = () => {
  return initialSnapshot.value !== makeSnapshot(types.value);
};

defineExpose({ handleSubmit, hasChanges });
</script>
