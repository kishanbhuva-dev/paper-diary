<template>
    <div v-if="show" class="fixed inset-0 z-50 bg-gray-900/50 flex items-center justify-center p-4">
        <div
            class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-auto overflow-hidden border border-indigo-300 flex flex-col max-h-[90vh]">

            <div class="p-5 bg-indigo-100 flex justify-between items-center border-b border-indigo-300 flex-shrink-0">
                <h3 class="text-xl font-bold text-gray-800">
                    {{ isEditing ? 'Edit' : 'Add New' }} Property
                </h3>
                <button @click="$emit('close')"
                    class="text-indigo-600 hover:text-red-500 transition duration-150 p-1 rounded-full hover:bg-white/50">
                    <Icon icon="mdi:close" class="w-6 h-6" />
                </button>
            </div>

            <div class="p-6 overflow-y-auto modal-inner-content">
                <form @submit.prevent="handleSubmit" class="space-y-5">

                    <div v-if="isEditing" class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">ID</label>
                        <input type="text" :value="formData.id"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                            readonly>
                    </div>

                    <div>
                        <!-- <label class="block text-sm font-semibold text-gray-700 mb-1">Property Name</label>
                        <input type="text" v-model="formData.propertyName"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
                            required> -->
                        <BaseInput v-model="formData.propertyName" lnm="Property Name" type="text" width="full"
                            variant="light" roundedlg="" helpertext="Type Property name" placeholder="" require="" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Address</label>
                        <input type="text" v-model="formData.address"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
                            required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">City</label>
                            <input type="text" v-model="formData.city"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Country</label>
                            <input type="text" v-model="formData.country"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                        </div>
                    </div>
                    <!-- postcode & telephone -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Postcode</label>
                            <input type="text" v-model="formData.postcode"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Telephone</label>
                            <input type="text" v-model="formData.telephone"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Latitude</label>
                            <input type="text" v-model="formData.latitude"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Longitude</label>
                            <input type="text" v-model="formData.longitude"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" v-model="formData.email"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                        <select v-model="formData.status"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                            <option :value="1">Active</option>
                            <option :value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="flex justify-end pt-4 px-6 pb-6 bg-white border-t border-gray-200 flex-shrink-0">
                <button type="button" @click="$emit('close')"
                    class="px-5 py-2 mr-3 text-sm font-medium text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400 transition duration-150">
                    Cancel
                </button>
                <button type="button" @click="handleSubmit"
                    class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition duration-150 flex items-center">
                    <Icon icon="ic:round-save" class="w-5 h-5 inline-block mr-1" />
                    {{ isEditing ? 'Update' : 'Save' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Icon } from "@iconify/vue";
import BaseInput from '../global/BaseInput.vue';

const props = defineProps({
    show: Boolean,
    item: Object
});

const emit = defineEmits(['close', 'submit']);

const formData = ref({});
const isEditing = computed(() => !!formData.value.id);

// Initialize form data when the modal opens
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        if (props.item) {
            // Clone the object to avoid editing the table row directly before saving
            formData.value = JSON.parse(JSON.stringify(props.item));
        } else {
            // Default empty state for "Add New"
            formData.value = {
                status: 1,
                propertyName: '',
                email: '',
                address: '',
                city: '',
                country: '',
                latitude: '',
                longitude: '',
                postcode: '',
                telephone: ''
            };
        }
    }
});

const handleSubmit = () => {
    emit('submit', formData.value);
};
</script>

<style scoped>
.modal-inner-content::-webkit-scrollbar {
    width: 6px;
}

.modal-inner-content::-webkit-scrollbar-track {
    background-color: transparent;
}

.modal-inner-content::-webkit-scrollbar-thumb {
    background-color: #b9b8b8;
    border-radius: 50px;
}

.modal-inner-content::-webkit-scrollbar-thumb:hover {
    cursor: grab;
    background-color: #a9a8a8;


}
</style>