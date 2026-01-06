<template>
    <div>
        <Basetable
            title="Facilites"
            :columns="tableColumns"
            :rows="facilites"
            :server-side="true"
            :total-items="total"
            :per-page="perPage"
            @search="handleSearch"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
            @open-add-modal="handleAdd"
            @open-edit-modal="handleEdit"
            @delete="handleDelete"
            :showDelete="true"
            :show-search="true"
            :showAdd="true"
            :showDownload="true"
            :showEdit="true"
        />

        <BaseModal
            :title="form?.id ? 'Edit Facilies' : 'Facility'"
            v-model="isOpen"
            width="max-w-xl"
            @save="handleSubmit"
        >
            <div class="grid grid-cols-2 gap-4">
                <BaseInput
                    ref="nameInput"
                    label="Facility Name"
                    v-model="form.name"
                    width="full"
                    required
                    class="col-span-2"
                    placeholder="Enter Name"
                />

                <div class="col-span-2">
                    <label class="text-xs font-bold mt-4 mb-1">
                        Pick an Icon
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative flex items-center" @click="showMenu = !showMenu">
                        <input
                            v-model="editingWidget.icon"
                            type="text"
                            :class="[
                                'flex-1 outline-none border p-2.5 rounded-md text-sm placeholder:text-xs focus:outline-none focus:ring-0 transition duration-300 ease focus:border-blue-900',
                                errors.icon ? 'border-red-500' : 'border-gray-300',
                            ]"
                            @focus="clearError('icon')"
                            readonly
                        />
                        <Icon
                            v-if="editingWidget.icon"
                            :icon="editingWidget.icon"
                            class="ml-3 text-xl text-blue-900"
                        />


                    </div>
                    <p v-if="errors.icon" class="text-xs text-red-500 mt-1">{{ errors.icon }}</p>

                    <div v-if="showMenu" class="z-10 w-full mt-2 bg-white border rounded-lg shadow-lg">
                        <IconPicker @select-icon="handleIconSelect" />
                    </div>
                </div>
                <BaseInput
                    ref="descriptionInput"
                    label="Description"
                    v-model="form.description"
                    width="full"
                    required
                    class="col-span-2"
                    placeholder="Description"
                />
                <BaseSelect
                    ref="statusInput"
                    label="Status"
                    v-model="form.status"
                    :options="[
                        { label: 'Active', value: 1 },
                        { label: 'Inactive', value: 0 }
                    ]"
                    width="full"
                    required
                    class="col-span-2"
                    placeholder="Select status"
                />
            </div>
        </BaseModal>
        <DeleteModal
            v-model="isOpenDelete"
            title="Delete Facility"
            message="Are you sure you want to delete this facility?"
            warning="This action cannot be undone. Please confirm that you want to delete this facility."
            action="delete"
            @confirm="handleDeleteData"
        />
    </div>
</template>

<script setup>
    import { ref } from 'vue';
    import { onMounted } from 'vue';
    import Basetable from '../../../components/global/Basetable.vue';
    import BaseModal from "../../../components/global/BaseModal.vue";
    import BaseInput from "../../../components/global/BaseInput.vue";
    import BaseSelect from '../../../components/global/BaseSelect.vue';
    import DeleteModal from '../../../components/global/DeleteModal.vue';
    import adminService from "../../../services/adminService";
    import IconPicker from '../../../components/admin/Facility/IconPicker.vue';

    const isOpen = ref(false);
    const total = ref(0);
    const perPage = ref(10);
    const currentPage = ref(1);
    const currentSearch = ref("");
    const facilites = ref([]);
    const editingWidget = ref({});
    const errors = ref({})
    const showMenu = ref(false);

    const tableColumns = [
        { label: "ID", key: "id" },
        { label: "name", key: (row) => row?.name },
        { label: "icon", key: "icon" },
        { label: "description", key: "description" },
        { label: "status", key: "status" }
    ];

    const fetchFacilites = async () => {
        const res = await adminService.fetchFacilites({
            page: currentPage.value,
            per_page: perPage.value,
            search: currentSearch.value,
        });
        if (res.status) {
            facilites.value = res.data.data;
            total.value = res.data.total || 0;
        }
    }

    const handleSearch = (term) => {
        currentSearch.value = term;
        currentPage.value = 1;
        fetchFacilites();
    };

    const handlePageChange = (page) => {
        currentPage.value = page;
        fetchFacilites();
    };

    const handleEdit = (facility) => {
        isOpen.value = true;
        form.value = { ...facility };
        editingWidget.value.icon = facility.icon;
    };

    const handleAdd = () => {
        isOpen.value = true;
        form.value = {};
    };

    const handlePerPageChange = (size) => {
        perPage.value = size;
        currentPage.value = 1;
        fetchFacilites();
    };

    const form = ref({
        name: "",
        icon: null,
        description: "",
        status: "",
    });

    const nameInput = ref(null);
    const descriptionInput = ref(null);
    const statusInput = ref(null);

    const handleSubmit = async () => {
        const inputs = [
            nameInput,
            descriptionInput,
            statusInput
        ];

        const allValid = inputs.every((input) => input.value.validate());

        if (!validateForm()) return;
        if (!allValid) return;

        let res;

        if (form.value.id) {
            res = await adminService.updateFacility(form.value);
        } else {
            res = await adminService.addFacility(form.value);
        }

        if (res?.status) {
            isOpen.value = false;
            fetchFacilites();
        }
    }

    const isOpenDelete = ref(false)
    const deleteFacility = ref(null)

    const handleDelete = async (facility) => {
        deleteFacility.value = facility;
        isOpenDelete.value = true;
    };

    const handleDeleteData = async () => {
        const id = deleteFacility.value;
        const res = await adminService.deleteFacility(id);
        if (res.status) {
            fetchFacilites();
            isOpenDelete.value = false;
        }
    };

    const handleIconSelect = (icon) => {
        const iconString = `fa-${icon.style}:${icon.id}`;

        form.value.icon = iconString;
        editingWidget.value.icon = iconString;

        showMenu.value = false;
    };

    function clearError(field) {
        errors.value[field] = '';
    }

    const validateForm = () => {
        errors.value = { icon: '' };
        let valid = true;

        if (!form.value.icon) {
            errors.value.icon = 'Icon is required';
            valid = false;
        }

        return valid;
    };

    // const parseFaIcon = (iconString) => {
    //     // "fa-solid:asterisk"
    //     const [style, name] = iconString.split(':');

    //     return [
    //         style.replace('fa-', ''), // solid
    //         name                         // asterisk
    //     ];
    // };

    onMounted(() => {
        fetchFacilites();
    });
</script>