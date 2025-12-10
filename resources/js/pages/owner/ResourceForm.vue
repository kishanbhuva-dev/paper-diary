<template>
  <div class="p-6 bg-white rounded-2xl shadow-inner border border-blue-100">
    <h3 class="text-xl font-bold text-blue-700 mb-4 flex items-center gap-2">
      <Icon icon="mdi:door-open" class="text-2xl" /> Resources
    </h3>

    <div class="space-y-4">
      <div
        v-for="(room, idx) in rooms"
        :key="idx"
        class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end p-4 bg-blue-50 rounded-lg border border-blue-200"
      >
        <div>
          <label class="text-sm font-medium text-slate-700">Name</label>
          <input
            v-model="room.name"
            type="text"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-100"
            placeholder="e.g. Room 101"
          />
        </div>

        <div>
          <label class="text-sm font-medium text-slate-700"
            >Resources Type</label
          >
          <select
            v-model="room.resourceTypeId"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-100"
          >
            <option value="">Select type</option>
            <option v-for="rt in availableTypes" :key="rt.id" :value="rt.id">
              {{ rt.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-sm font-medium text-slate-700">Status</label>
          <select
            v-model="room.status"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-100"
          >
            <option :value="1">Active</option>
            <option :value="0">Inactive</option>
          </select>
        </div>

        <div class="flex gap-2">
          <button
            type="button"
            @click="removeRoom(idx)"
            class="px-3 py-2 text-sm text-white bg-red-600 rounded-xl hover:bg-red-700 flex items-center justify-center"
            title="Delete room"
          >
            <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
          </button>
        </div>
      </div>

      <div class="pt-2">
        <button
          type="button"
          @click="addRoom"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700"
        >
          Add Room
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
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { Icon } from "@iconify/vue";
import ownerService from "../../services/ownerService";

const props = defineProps({
  propertyId: { type: [String, Number], required: true },
  resourceTypes: { type: Array, default: () => [] },
  inWizard: { type: Boolean, default: false },
});
const emits = defineEmits(["success", "cancel"]);

const availableTypes = ref([]);
const rooms = ref([{ name: "", resourceTypeId: "", status: 1 }]);
// track deletions locally and perform deletes on submit
const deletedRoomIds = ref([]);
const submitting = ref(false);

const loadData = async () => {
  try {
    const fetched = await ownerService.fetchResourceTypes(props.propertyId);
    availableTypes.value = fetched || [];
  } catch (err) {
    console.warn("[RoomAllocationForm] failed to fetch resource types", err);
  }

  try {
    const existing = await ownerService.fetchResources(props.propertyId);
    if (Array.isArray(existing) && existing.length) {
      rooms.value = existing.map((r) => ({
        id: r.id,
        name: r.name || "",
        resourceTypeId: r.resourceTypeId,
        status: typeof r.status !== "undefined" ? Number(r.status) : 1,
      }));
      // Always keep one empty row to allow quick adding
      rooms.value.push({ name: "", resourceTypeId: "", status: 1 });
    } else {
      rooms.value = [{ name: "", resourceTypeId: "", status: 1 }];
    }
  } catch (err) {
    console.warn(
      "[RoomAllocationForm] failed to fetch existing resources",
      err
    );
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

const addRoom = () => {
  rooms.value.push({ name: "", resourceTypeId: "", status: 1 });
};
const removeRoom = (idx) => {
  if (rooms.value.length === 1) return;
  const r = rooms.value[idx];
  if (r && r.id) {
    deletedRoomIds.value.push(r.id);
    rooms.value.splice(idx, 1);
  } else {
    rooms.value.splice(idx, 1);
  }
};

const validate = () => {
  if (!props.propertyId) return false;
  // Ignore empty placeholder rows (no name). Require at least one filled room.
  const filled = (rooms.value || []).filter(
    (r) => r.name && r.name.toString().trim() !== ""
  );
  if (filled.length === 0) return false; // require at least one room
  for (const r of filled) {
    if (!r.resourceTypeId) return false;
  }
  return true;
};

const handleSubmit = async () => {
  if (!validate()) {
    alert("Please provide a name and type for every room before submitting.");
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

    for (const rm of rooms.value) {
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
      return { toCreate, toUpdate, deleted: deletedRoomIds.value.slice() };
    }

    // Perform deletes first for any rooms the user removed in the UI
    if (deletedRoomIds.value && deletedRoomIds.value.length) {
      for (const id of deletedRoomIds.value) {
        try {
          await ownerService.deleteResource(id);
        } catch (err) {
          console.debug("[RoomAllocationForm] failed to delete room", id, err);
        }
      }
      deletedRoomIds.value = [];
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
  } finally {
    submitting.value = false;
  }
};

defineExpose({ handleSubmit });

const cancel = () => emits("cancel");
</script>
