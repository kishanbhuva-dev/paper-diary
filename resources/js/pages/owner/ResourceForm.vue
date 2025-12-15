<template>
  <div
    class="p-6 mb-4 bg-white rounded-2xl shadow-inner border border-blue-100"
  >
    <h3 class="text-xl font-bold text-blue-700 mb-4 flex items-center gap-2">
      <Icon icon="mdi:door-open" class="text-2xl" /> Resources
    </h3>

    <div class="space-y-4">
      <div
        v-for="(resourceItem, idx) in resourceItems"
        :key="idx"
        class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end p-4 bg-blue-50 rounded-lg border border-blue-200"
      >
        <div>
          <BaseInput
            v-model="resourceItem.name"
            label="Name"
            width="full"
            placeholder="e.g. Room 101"
          />
        </div>

        <div>
          <BaseSelect
            v-model="resourceItem.resourceTypeId"
            :label="'Resources Type'"
            :placeholder="'Select type'"
            :options="availableTypeOptions"
          />
        </div>

        <div>
          <BaseSelect
            v-model="resourceItem.status"
            :label="'Status'"
            :options="statusOptions"
          />
        </div>

        <div class="flex items-start self-stretch mt-6">
          <button
            type="button"
            @click="openRemoveResourceItemModal(idx)"
            class="px-2 py-2 cursor-pointer text-sm text-white bg-red-600 rounded-xl hover:bg-red-700 flex items-center justify-center"
            title="Delete resource"
          >
            <Icon icon="mdi:delete-forever" class="w-6 h-6" />
          </button>
        </div>
      </div>

      <div class="pt-2">
        <button
          type="button"
          @click="addResourceItem"
          class="px-4 py-2 cursor-pointer text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700"
        >
          Add Resource
        </button>
      </div>
    </div>

    <div class="flex justify-end mt-6">
      <div v-if="!props.inWizard" class="flex gap-2">
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
          Submit
        </button>
      </div>
    </div>
  </div>

  <DeleteModal
    v-model="isConfirmationModalVisible"
    :title="'Remove Resource'"
    :message="`Are you sure you want to remove the resource: ${resourceItemNameToRemove}?`"
    :warning="
      resourceItemIndexToRemove !== null &&
      resourceItems[resourceItemIndexToRemove]?.id
        ? 'This resource will be permanently deleted from the server upon submission.'
        : 'This is a local change and will be removed from the list.'
    "
    @confirm="confirmRemoval"
  />
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { Icon } from "@iconify/vue";
import BaseInput from "../../components/global/BaseInput.vue";
import BaseSelect from "../../components/global/BaseSelect.vue";
import DeleteModal from "../../components/global/DeleteModal.vue";
import ownerService from "../../services/ownerService";
import { toast } from "vue-sonner";

const props = defineProps({
  propertyId: { type: [String, Number], required: true },
  resourceTypes: { type: Array, default: () => [] },
  inWizard: { type: Boolean, default: false },
  editMode: { type: Boolean, default: false },
});
const emits = defineEmits(["success", "cancel"]);

const availableTypes = ref([]);
const resourceItems = ref([{ name: "", resourceTypeId: "", status: 1 }]);
// track deletions locally and perform deletes on submit
const deletedResourceItemIds = ref([]);
const submitting = ref(false);

// --- CONVERTED OPTIONS FOR BASESELECT ---
const statusOptions = ref([
  { label: "Active", value: 1 },
  { label: "Inactive", value: 0 },
]);

const availableTypeOptions = computed(() => {
  return availableTypes.value.map((rt) => ({
    label: rt.name,
    value: rt.id,
  }));
});
// --- END OPTIONS ---

// --- CONFIRMATION MODAL STATE ---
const isConfirmationModalVisible = ref(false);
const resourceItemIndexToRemove = ref(null);
const resourceItemNameToRemove = computed(() => {
  const idx = resourceItemIndexToRemove.value;
  return idx !== null && resourceItems.value[idx]?.name
    ? resourceItems.value[idx].name
    : "this resource";
});
// --------------------------------

const loadData = async () => {
  try {
    const fetched = await ownerService.fetchResourceTypes(props.propertyId);
    availableTypes.value = fetched || [];
  } catch (err) {
    console.warn("[ResourceForm] failed to fetch resource types", err);
  }

  try {
    const existing = await ownerService.fetchResources(props.propertyId);
    if (Array.isArray(existing) && existing.length) {
      resourceItems.value = existing.map((r) => ({
        id: r.id,
        name: r.name || "",
        resourceTypeId: r.resourceTypeId,
        status: typeof r.status !== "undefined" ? Number(r.status) : 1,
      }));
      // Always keep one empty row to allow quick adding
      resourceItems.value.push({ name: "", resourceTypeId: "", status: 1 });
    } else {
      resourceItems.value = [{ name: "", resourceTypeId: "", status: 1 }];
    }
  } catch (err) {
    console.warn("[ResourceForm] failed to fetch existing resources", err);
  }
};

onMounted(() => {
  loadData();
});

watch(
  () => props.propertyId,
  (val) => {
    if (val) loadData();
  }
);

const addResourceItem = () => {
  resourceItems.value.push({ name: "", resourceTypeId: "", status: 1 });
};

// --- MODAL HANDLERS (UPDATED) ---
const openRemoveResourceItemModal = (idx) => {
  const resourceItem = resourceItems.value[idx];

  // Checks if the row is new (no id) AND if the name field is empty/blank
  const isDummyOrBlank =
    !resourceItem.id &&
    (!resourceItem.name || resourceItem.name.toString().trim() === "");

  // Prevent deletion if it's the only remaining item AND it has content/ID
  if (resourceItems.value.length === 1 && !isDummyOrBlank) {
    return;
  }

  if (isDummyOrBlank) {
    // If it's a dummy/blank row, skip the modal and proceed straight to removal
    resourceItemIndexToRemove.value = idx;
    confirmRemoval();
  } else {
    // If it has an ID or a name (user has started filling it out), show the modal
    resourceItemIndexToRemove.value = idx;
    isConfirmationModalVisible.value = true;
  }
};

const confirmRemoval = () => {
  const idx = resourceItemIndexToRemove.value;
  if (idx === null || idx === undefined) {
    isConfirmationModalVisible.value = false;
    return;
  }

  const r = resourceItems.value[idx];

  // If only one item remains, reset it instead of deleting the array item
  if (resourceItems.value.length === 1) {
    resourceItems.value[0] = {
      name: "",
      resourceTypeId: "",
      status: 1,
    };
    isConfirmationModalVisible.value = false;
    resourceItemIndexToRemove.value = null;
    return;
  }

  // Mark for deletion if it has an ID
  if (r && r.id) {
    deletedResourceItemIds.value.push(r.id);
    resourceItems.value.splice(idx, 1);
  } else {
    // Local-only row, just remove
    resourceItems.value.splice(idx, 1);
  }

  // Reset state after deletion
  isConfirmationModalVisible.value = false;
  resourceItemIndexToRemove.value = null;
};
// --- END MODAL HANDLERS ---

const validate = () => {
  if (!props.propertyId) return false;

  // Ignore empty placeholder rows (no name). Require at least one filled resourceItem.
  const filled = (resourceItems.value || []).filter(
    (r) => r.name && r.name.toString().trim() !== ""
  );
  if (filled.length === 0) {
    toast.error("At least one resource must be filled.");
    return false;
  }

  // Ensure all filled rows have a selected resource type
  for (const r of filled) {
    if (!r.resourceTypeId) {
      toast.error(`Resource "${r.name}" requires a Resource Type.`);
      return false;
    }
  }
  return true;
};

const handleSubmit = async () => {
  if (!validate()) {
    return;
  }

  if (submitting.value) return;
  submitting.value = true;

  try {
    const serverList = await ownerService.fetchResources(props.propertyId);
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

    for (const rm of resourceItems.value) {
      const normalized = {
        name: (rm.name || "").toString().trim(),
        status: typeof rm.status !== "undefined" ? Number(rm.status) : 1,
        resourceTypeId: parseInt(rm.resourceTypeId, 10) || null,
      };
      // ignore empty placeholder rows
      if (!normalized.name) continue;
      if (rm.id) {
        const server = serverById[rm.id];
        if (!server) {
          toCreate.push(normalized);
        } else {
          const serverNorm = {
            name: (server.name || "").toString().trim(),
            status:
              typeof server.status !== "undefined" ? Number(server.status) : 1,
            resourceTypeId: server.resourceTypeId,
          };
          if (
            serverNorm.name !== normalized.name ||
            serverNorm.status !== normalized.status ||
            serverNorm.resourceTypeId !== normalized.resourceTypeId
          ) {
            toUpdate.push({ id: rm.id, ...normalized });
          }
        }
      } else {
        const key = normalized.name.toLowerCase();
        const existingMatch = key ? serverByName[key] : null;
        if (existingMatch) {
          const serverNorm = {
            name: (existingMatch.name || "").toString().trim(),
            status:
              typeof existingMatch.status !== "undefined"
                ? Number(existingMatch.status)
                : 1,
            resourceTypeId: existingMatch.resourceTypeId,
          };
          if (
            serverNorm.name !== normalized.name ||
            serverNorm.status !== normalized.status ||
            serverNorm.resourceTypeId !== normalized.resourceTypeId
          ) {
            toUpdate.push({ id: existingMatch.id, ...normalized });
          } else {
            // unchanged duplicate, skip
          }
        } else {
          toCreate.push(normalized);
        }
      }
    }

    // If editing in the wizard, return diffs and deletions to parent
    if (props.inWizard && props.editMode) {
      return {
        toCreate,
        toUpdate,
        deleted: deletedResourceItemIds.value.slice(),
      };
    }

    // Perform deletes first for any resources the user removed in the UI
    if (deletedResourceItemIds.value && deletedResourceItemIds.value.length) {
      for (const id of deletedResourceItemIds.value) {
        try {
          await ownerService.deleteResource(id);
        } catch (err) {
          console.debug("[ResourceForm] failed to delete resource", id, err);
        }
      }
      deletedResourceItemIds.value = [];
    }

    if (toUpdate.length > 0) {
      const payload = {
        ids: toUpdate.map((r) => r.id),
        name: toUpdate.map((r) => r.name),
        status: toUpdate.map((r) => r.status),
        resourceTypeId: toUpdate.map((r) => r.resourceTypeId),
      };
      await ownerService.resourceMultipleUpdate(payload);
    }

    if (toCreate.length > 0) {
      const payload = {
        name: toCreate.map((r) => r.name),
        status: toCreate.map((r) => r.status),
        resourceTypeId: toCreate.map((r) => r.resourceTypeId),
      };
      await ownerService.resourceMultipleStore(payload);
    }

    emits("success");
  } catch (err) {
    toast.error("An error occurred during submission.");
  } finally {
    submitting.value = false;
  }
};

defineExpose({ handleSubmit });

const cancel = () => emits("cancel");
</script>
