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
        <div class="p-2.5 bg-indigo-50 rounded-xl">
          <Icon
            icon="mdi:door-open"
            class="text-2xl text-indigo-600"
          />
        </div>
        <div>
          <h3 class="text-xl font-bold text-slate-800">Individual Units</h3>
          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            Inventory & Specific Resources
          </p>
        </div>
      </div>

      <button
        type="button"
        class="flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-all group active:scale-95 w-full sm:w-auto"
        @click="addResourceItem"
      >
        <Icon
          icon="mdi:plus"
          class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300"
        />
        Add Unit
      </button>
    </div>

    <div class="space-y-4 relative">
      <transition-group name="resource-list">
        <div
          v-for="(resourceItem, idx) in resourceItems"
          :key="idx"
          class="group bg-white p-4 sm:p-6 rounded-2xl border border-slate-100 hover:border-indigo-200 transition-all duration-300"
        >
          <div class="grid grid-cols-1 md:grid-cols-12 gap-0 sm:gap-5 items-start">
            <div class="md:col-span-4 mb-0">
              <BaseInput
                v-model="resourceItem.name"
                label="Resource Name"
                width="full"
                placeholder="e.g. Room 101"
              />
            </div>

            <div class="md:col-span-4 mb-4 md:mb-0">
              <BaseSelect
                v-model="resourceItem.resourceTypeId"
                label="Assign Type"
                placeholder="Select Type..."
                :options="availableTypeOptions"
                class="text-base sm:text-sm"
              />
            </div>

            <div class="md:col-span-3 mb-4 md:mb-0">
              <BaseSelect
                v-model="resourceItem.status"
                label="Availability"
                :options="statusOptions"
                class="text-base sm:text-sm"
              />
            </div>

            <div class="md:col-span-1 flex justify-end md:mt-7">
              <button
                type="button"
                class="w-full md:w-11 h-11 cursor-pointer text-slate-300 bg-slate-50 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all flex items-center justify-center border border-slate-100 md:border-transparent hover:border-red-100"
                title="Remove resource"
                @click="openRemoveResourceItemModal(idx)"
              >
                <Icon
                  icon="mdi:trash-can-outline"
                  class="w-5 h-5"
                />
                <span class="md:hidden ml-2 font-bold text-sm">Remove Unit</span>
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
import { ref, onMounted, computed } from 'vue';
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
const emits = defineEmits(['success']);

const availableTypes = ref([]);
const resourceItems = ref([{ name: '', resourceTypeId: '', status: 1 }]);
const deletedResourceItemIds = ref([]);
const submitting = ref(false);
const statusOptions = ref([
  { label: 'Active', value: 1 },
  { label: 'Inactive', value: 0 },
]);

const isConfirmationModalVisible = ref(false);
const resourceItemIndexToRemove = ref(null);

const availableTypeOptions = computed(() =>
  availableTypes.value.map((rt) => ({ label: rt.name, value: rt.id }))
);

const resourceItemNameToRemove = computed(() => {
  const idx = resourceItemIndexToRemove.value;
  if (idx === null || !resourceItems.value[idx]) {
    return 'this resource';
  }
  return resourceItems.value[idx].name || 'this resource';
});

const loadData = async () => {
  if (!props.propertyId) {
    return;
  }
  try {
    const [fetchedTypes, existingResources] = await Promise.all([
      ownerService.fetchResourceTypes(props.propertyId),
      ownerService.fetchResources(props.propertyId),
    ]);

    availableTypes.value = fetchedTypes || [];

    if (Array.isArray(existingResources) && existingResources.length) {
      resourceItems.value = existingResources.map((r) => ({
        id: r.id,
        name: r.name || '',
        resourceTypeId: r.resourceTypeId,
        status: typeof r.status !== 'undefined' ? Number(r.status) : 1,
      }));
      resourceItems.value.push({ name: '', resourceTypeId: '', status: 1 });
    }
  } catch (err) {
    toast.error(err || 'Failed to load resource data');
  }
};

onMounted(async () => {
  await loadData();
});

const addResourceItem = () => {
  resourceItems.value.push({ name: '', resourceTypeId: '', status: 1 });
};

const openRemoveResourceItemModal = (idx) => {
  const item = resourceItems.value[idx];
  const isBlank = !item.id && (!item.name || item.name.toString().trim() === '');

  if (resourceItems.value.length === 1 && !isBlank) {
    return;
  }

  resourceItemIndexToRemove.value = idx;
  if (isBlank) {
    confirmRemoval();
  } else {
    isConfirmationModalVisible.value = true;
  }
};

const confirmRemoval = () => {
  const idx = resourceItemIndexToRemove.value;
  if (idx === null) {
    return;
  }

  const r = resourceItems.value[idx];
  if (resourceItems.value.length === 1) {
    resourceItems.value[0] = { name: '', resourceTypeId: '', status: 1 };
  } else {
    if (r.id) {
      deletedResourceItemIds.value.push(r.id);
    }
    resourceItems.value.splice(idx, 1);
  }

  isConfirmationModalVisible.value = false;
  resourceItemIndexToRemove.value = null;
};

const validate = () => {
  if (!props.propertyId) {
    return false;
  }
  const filled = resourceItems.value.filter((r) => r.name?.toString().trim() !== '');

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
  if (!validate() || submitting.value) {
    return null;
  }

  submitting.value = true;
  try {
    const serverList = await ownerService.fetchResources(props.propertyId);
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
        toUpdate.push({ id: rm.id, ...normalized });
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
      return { toCreate, toUpdate, deleted: [...deletedResourceItemIds.value] };
    }

    // Deletions
    if (deletedResourceItemIds.value.length) {
      await Promise.all(deletedResourceItemIds.value.map((id) => ownerService.deleteResource(id)));
      deletedResourceItemIds.value = [];
    }

    // Updates
    if (toUpdate.length) {
      await ownerService.resourceMultipleUpdate({
        ids: toUpdate.map((r) => r.id),
        name: toUpdate.map((r) => r.name),
        status: toUpdate.map((r) => r.status),
        resourceTypeId: toUpdate.map((r) => r.resourceTypeId),
      });
    }

    // Creates
    if (toCreate.length) {
      await ownerService.resourceMultipleStore({
        name: toCreate.map((r) => r.name),
        status: toCreate.map((r) => r.status),
        resourceTypeId: toCreate.map((r) => r.resourceTypeId),
      });
    }

    emits('success');
    return true;
  } catch (err) {
    toast.error(err || 'An error occurred during submission.');
    return null;
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
@media (max-width: 640px) {
  input,
  select {
    font-size: 16px !important;
  }
}
</style>
