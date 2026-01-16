<template>
  <div
    class="p-8 bg-white rounded-3xl"
    :class="[inWizard ? '' : 'border border-gray-100 shadow-xl shadow-gray-200/50']"
  >
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-indigo-50 rounded-xl">
          <Icon
            icon="mdi:door-open"
            class="text-2xl text-indigo-600"
          />
        </div>
        <div>
          <h3 class="text-xl font-bold text-slate-800">Individual Units</h3>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
            Inventory & Specific Resources
          </p>
        </div>
      </div>

      <button
        type="button"
        class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-all group active:scale-95"
        @click="addResourceItem"
      >
        <Icon
          icon="mdi:plus"
          class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300"
        />
        Add Resources
      </button>
    </div>

    <div class="space-y-3 relative">
      <transition-group name="resource-list">
        <div
          v-for="(resourceItem, idx) in resourceItems"
          :key="idx"
          class="group bg-white p-5 rounded-2xl border border-slate-100 hover:border-indigo-200 transition-all duration-300"
        >
          <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
            <div class="md:col-span-4">
              <BaseInput
                v-model="resourceItem.name"
                label="Resource Name"
                width="full"
                placeholder="e.g. Room 101"
              />
            </div>

            <div class="md:col-span-4">
              <BaseSelect
                v-model="resourceItem.resourceTypeId"
                label="Assign Resource Type"
                placeholder="Select Resource Type..."
                :options="availableTypeOptions"
              />
            </div>

            <div class="md:col-span-3">
              <BaseSelect
                v-model="resourceItem.status"
                label="Availability Status"
                :options="statusOptions"
              />
            </div>

            <div class="md:col-span-1 flex justify-end mt-7">
              <button
                type="button"
                class="w-11 h-11 cursor-pointer text-slate-300 bg-slate-50 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all flex items-center justify-center border border-transparent hover:border-red-100"
                title="Remove resource"
                @click="openRemoveResourceItemModal(idx)"
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
    title="Remove Individual Unit"
    :message="`Are you sure you want to remove '${resourceItemNameToRemove}'?`"
    :warning="
      resourceItemIndexToRemove !== null && resourceItems[resourceItemIndexToRemove]?.id
        ? 'This unit will be permanently removed from the server.'
        : 'This will remove the draft unit from your list.'
    "
    @confirm="confirmRemoval"
  />
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { Icon } from '@iconify/vue';
import BaseInput from '../../components/global/BaseInput.vue';
import BaseSelect from '../../components/global/BaseSelect.vue';
import DeleteModal from '../../components/global/DeleteModal.vue';
import ownerService from '../../services/ownerService';
import { toast } from 'vue-sonner';

const props = defineProps({
  propertyId: { type: [String, Number], required: true },
  resourceTypes: { type: Array, default: () => [] },
  inWizard: { type: Boolean, default: false },
  editMode: { type: Boolean, default: false },
});
const emits = defineEmits(['success', 'cancel']);

const availableTypes = ref([]);
const resourceItems = ref([{ name: '', resourceTypeId: '', status: 1 }]);
const deletedResourceItemIds = ref([]);
const submitting = ref(false);
const statusOptions = ref([
  { label: 'Active', value: 1 },
  { label: 'Inactive', value: 0 },
]);
const availableTypeOptions = computed(() =>
  availableTypes.value.map((rt) => ({ label: rt.name, value: rt.id }))
);

const isConfirmationModalVisible = ref(false);
const resourceItemIndexToRemove = ref(null);
const resourceItemNameToRemove = computed(() => {
  const idx = resourceItemIndexToRemove.value;
  return idx !== null && resourceItems.value[idx]?.name
    ? resourceItems.value[idx].name
    : 'this resource';
});

const loadData = async () => {
  try {
    const fetched = await ownerService.fetchResourceTypes(props.propertyId);
    availableTypes.value = fetched || [];
  } catch (err) {
    throw new Error(err);
  }
  try {
    const existing = await ownerService.fetchResources(props.propertyId);
    if (Array.isArray(existing) && existing.length) {
      resourceItems.value = existing.map((r) => ({
        id: r.id,
        name: r.name || '',
        resourceTypeId: r.resourceTypeId,
        status: typeof r.status !== 'undefined' ? Number(r.status) : 1,
      }));
      resourceItems.value.push({ name: '', resourceTypeId: '', status: 1 });
    } else {
      resourceItems.value = [{ name: '', resourceTypeId: '', status: 1 }];
    }
  } catch (err) {
    throw new Error(err);
  }
};

onMounted(() => {
  loadData();
});
watch(
  () => props.propertyId,
  (val) => {
    if (val) {
      loadData();
    }
  }
);

const addResourceItem = () => {
  resourceItems.value.push({ name: '', resourceTypeId: '', status: 1 });
};

const openRemoveResourceItemModal = (idx) => {
  const resourceItem = resourceItems.value[idx];
  const isDummyOrBlank =
    !resourceItem.id && (!resourceItem.name || resourceItem.name.toString().trim() === '');
  if (resourceItems.value.length === 1 && !isDummyOrBlank) {
    return;
  }
  if (isDummyOrBlank) {
    resourceItemIndexToRemove.value = idx;
    confirmRemoval();
  } else {
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
  if (resourceItems.value.length === 1) {
    resourceItems.value[0] = { name: '', resourceTypeId: '', status: 1 };
    isConfirmationModalVisible.value = false;
    resourceItemIndexToRemove.value = null;
    return;
  }
  if (r && r.id) {
    deletedResourceItemIds.value.push(r.id);
    resourceItems.value.splice(idx, 1);
  } else {
    resourceItems.value.splice(idx, 1);
  }
  isConfirmationModalVisible.value = false;
  resourceItemIndexToRemove.value = null;
};

const validate = () => {
  if (!props.propertyId) {
    return false;
  }
  const filled = (resourceItems.value || []).filter(
    (r) => r.name && r.name.toString().trim() !== ''
  );
  if (filled.length === 0) {
    toast.error('At least one resource must be filled.');
    return false;
  }
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
  if (submitting.value) {
    return;
  }
  submitting.value = true;
  try {
    const serverList = await ownerService.fetchResources(props.propertyId);
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
    for (const rm of resourceItems.value) {
      const normalized = {
        name: (rm.name || '').toString().trim(),
        status: typeof rm.status !== 'undefined' ? Number(rm.status) : 1,
        resourceTypeId: parseInt(rm.resourceTypeId, 10) || null,
      };
      if (!normalized.name) {
        continue;
      }
      if (rm.id) {
        const server = serverById[rm.id];
        if (!server) {
          toCreate.push(normalized);
        } else {
          const serverNorm = {
            name: (server.name || '').toString().trim(),
            status: typeof server.status !== 'undefined' ? Number(server.status) : 1,
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
            name: (existingMatch.name || '').toString().trim(),
            status: typeof existingMatch.status !== 'undefined' ? Number(existingMatch.status) : 1,
            resourceTypeId: existingMatch.resourceTypeId,
          };
          if (
            serverNorm.name !== normalized.name ||
            serverNorm.status !== normalized.status ||
            serverNorm.resourceTypeId !== normalized.resourceTypeId
          ) {
            toUpdate.push({ id: existingMatch.id, ...normalized });
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
        deleted: deletedResourceItemIds.value.slice(),
      };
    }
    if (deletedResourceItemIds.value && deletedResourceItemIds.value.length) {
      for (const id of deletedResourceItemIds.value) {
        try {
          // eslint-disable-next-line no-await-in-loop
          await ownerService.deleteResource(id);
        } catch (err) {
          toast.error(err);
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
    emits('success');
  } catch (err) {
    toast.error(err || 'An error occurred during submission.');
  } finally {
    submitting.value = false;
  }
};

defineExpose({ handleSubmit });
</script>

<style scoped>
.resource-list-enter-from,
.resource-list-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
.resource-list-leave-active {
  position: absolute;
  width: 100%;
}
</style>
