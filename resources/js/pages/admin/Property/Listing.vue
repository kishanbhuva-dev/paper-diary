<template>
    <div>
        <Basetable
            title="Lisings"
            :columns="tableColumns"
            :rows="propertLisings"
            :server-side="true"
            :total-items="total"
            :per-page="perPage"
            :show-search="true"
            :show-add="false"
            :show-download="true"
            :show-edit="false"
            :show-delete="false"
            :show-view="false"
            :admin-login="true"
            admin-login-title="Login as User"
            @search="handleSearch"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
            @delete="handleDelete"
            @admin-login="handleLoginAsUser"
            @sort="handleSort"
        >
            <template #toggle-status="{ row }">
                <div class="flex items-center">
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input
                            v-model="row.status"
                            type="checkbox"
                            class="sr-only peer"
                            :true-value="1"
                            :false-value="0"
                            @change="ChangeStatus(row.id, row.status)"
                        />
                        <div class="w-16 h-5 bg-red-500 rounded-full transition-colors peer-checked:bg-green-500"></div>
                        <span
                            class="absolute right-2 text-white text-[0.75em] font-bold pointer-events-none peer-checked:hidden"
                        >
                            Offline
                        </span>
                        <span
                            class="absolute left-2 text-white text-[0.75em] font-bold pointer-events-none hidden peer-checked:inline"
                        >
                            Online
                        </span>
                        <div
                            class="absolute left-0.5 top-0.5 size-4 bg-white rounded-full transition-transform transform peer-checked:translate-x-11"
                        ></div>
                    </label>
                </div>
            </template>
        </Basetable>
    </div>
</template>

<script setup>
    import { onMounted, ref } from 'vue';
    import adminService from '../../../services/adminService'
    import Basetable from '../../../components/global/Basetable.vue';
    import { useRouter } from 'vue-router';

    const isOpen = ref(false);
    const total = ref(0);
    const perPage = ref(10);
    const currentPage = ref(1);
    const currentSearch = ref("");
    const propertLisings = ref([]);
    const router = useRouter();
    const orderBy = ref("id");
    const orderDirection = ref("asc");

    const tableColumns = [
        { label: "S.N", key: "id", sortable: true},
        { label: "propertyName", key: "propertyName", sortable: true},
        { label: "ownerName", key: "ownerName", sortable: true},
        { label: "ownerEmail", key: "ownerEmail", sortable: true},
        { label: "ownerPhone", key: "ownerPhone", sortable: true},
        { label: "ownerTelephone", key: "ownerTelephone", sortable: true},
        { label: "totalRevenue", key: "totalRevenue", sortable: true},
        { label: "lostAmount", key: "lostAmount", sortable: true},
        { label: "status", key: "status", sortable: true},
        { label: 'CHANGE STATUS', key: 'toggle-status'}
    ]

    const ChangeStatus = async (propertyId, status) => {
        const payload = {
            id: propertyId,
            status
        }
        const res = await adminService.ChangePropertyStatus(payload);
        console.log('res', res);
    }


    const fetchPropertLisings = async () => {
        const res = await adminService.fetchPropertLisings({
            page: currentPage.value,
            per_page: perPage.value,
            search: currentSearch.value,
            sortBy: orderBy.value,       
            sortOrder: orderDirection.value
        });

        console.log('res for status', res);

        if (res.status) {
            propertLisings.value = res.data.data;
            total.value = res.data.total || 0;
        }
    }

    const handleSort = (sortData) => {
        orderBy.value = sortData.key;
        orderDirection.value = sortData.order;
        fetchPropertLisings();
    };

    const handleSearch = (term) => {
        currentSearch.value = term;
        currentPage.value = 1;
        fetchPropertLisings();
    };

    const handlePageChange = (page) => {
        currentPage.value = page;
        fetchPropertLisings();
    };

    const handlePerPageChange = (size) => {
        perPage.value = size;
        currentPage.value = 1;
        fetchPropertLisings();
    };

    const isOpenDelete = ref(false)
    const deleteFacility = ref(null)

    const handleDelete = async (facility) => {
        deleteFacility.value = facility;
        isOpenDelete.value = true;
    };

    const handleLoginAsUser = async (item) => {
        const email = item.ownerEmail;

        if (!email) {
            console.warn("Admin login attempted without email");
            return;
        }
        const adminToken = localStorage.getItem('authToken');
        const adminUser = localStorage.getItem('user');

        const response = await adminService.loginAsOwner({ email });

        if (!response?.data?.status) {
        console.warn("Login as user failed", response);
        return;
        }
        
        const { token, user } = response.data.data;
        
        localStorage.setItem('authToken', token);
        localStorage.setItem('user', JSON.stringify(user));
        localStorage.setItem('adminToken', adminToken);
        localStorage.setItem('adminUser', adminUser);

        // router.push({ name: 'owner-dashboard' });
        // setTimeout(() => location.reload(), 1000);
        window.location.href = '/owner';
    };

    onMounted(() => {
        fetchPropertLisings();
    })
</script>