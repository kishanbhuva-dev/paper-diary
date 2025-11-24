<template>
    <div v-if="show" class="fixed inset-0 z-50 bg-gray-900/50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div
            class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-auto overflow-hidden border border-indigo-200 flex flex-col max-h-[90vh]">

            <div class="p-5 bg-indigo-100 flex justify-between items-center border-b border-indigo-200 shrink-0">
                <h3 class="text-xl font-bold text-indigo-900">
                    {{ isEditing ? 'Edit' : 'Add New' }} Property
                </h3>
                <button @click="$emit('close')"
                    class="text-indigo-400 hover:text-red-500 transition duration-150 p-1 rounded-full hover:bg-white/50">
                    <Icon icon="mdi:close" class="w-6 h-6" />
                </button>
            </div>

            <div class="p-6 overflow-y-auto modal-inner-content">
                <form @submit.prevent="handleSubmit" class="space-y-1">

                    <div v-if="isEditing">
                        <BaseInput v-model="formData.id" label="Property ID" width="full" variant="gray" disabled />
                    </div>

                    <BaseInput :ref="setInputRef" v-model="formData.propertyName" label="Property Name" width="full"
                        placeholder="e.g. Seaside Villa" required :max-length="50" :show-count="true" />

                    <BaseInput :ref="setInputRef" v-model="formData.address" label="Address" width="full"
                        placeholder="e.g. 123 Ocean Drive" required :max-length="100" />

                    <BaseInput :ref="setInputRef" v-model="formData.email" label="Email" type="email" width="full"
                        placeholder="contact@example.com" required :max-length="50" />

                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput :ref="setInputRef" v-model="formData.city" label="City" width="full"
                            placeholder="London" required :max-length="20" />
                        <BaseInput :ref="setInputRef" v-model="formData.country" label="Country" width="full"
                            placeholder="UK" required :max-length="20" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput :ref="setInputRef" v-model="formData.postcode" label="Postcode" width="full"
                            placeholder="SW1A 1AA" required :max-length="10" />
                        <BaseInput :ref="setInputRef" v-model="formData.telephone" label="Telephone" width="full"
                            placeholder="+44 7911 123456" :max-length="15" :pattern="/^[0-9+\-\s]*$/"
                            custom-error="Only numbers and + - allowed" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput :ref="setInputRef" v-model="formData.latitude" label="Latitude" width="full"
                            placeholder="e.g. 51.5072" required :max-length="15"
                            :pattern="/^[-+]?([1-8]?\d(\.\d+)?|90(\.\d+)?)$/" custom-error="Must be number -90 to 90" />
                        <BaseInput :ref="setInputRef" v-model="formData.longitude" label="Longitude" width="full"
                            placeholder="e.g. -0.1276" required :max-length="15"
                            :pattern="/^[-+]?((1[0-7]\d|0?\d?\d)(\.\d+)?|180(\.\d+)?)$/"
                            custom-error="Must be number -180 to 180" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <select v-model="formData.status"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none appearance-none bg-white">
                                <option :value="1">Active</option>
                                <option :value="0">Inactive</option>
                            </select>
                            <Icon icon="mdi:chevron-down"
                                class="absolute right-3 top-3 text-gray-400 pointer-events-none" />
                        </div>
                    </div>
                </form>
            </div>

            <div class="flex justify-end pt-4 px-6 pb-6 bg-white border-t border-gray-300 shrink-0">
                <button type="button" @click="$emit('close')"
                    class="px-5 py-2 mr-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-150 outline-indigo-400">
                    Cancel
                </button>
                <button type="button" @click="handleSubmit"
                    class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition duration-150 flex items-center shadow-md outline-indigo-400 shadow-indigo-200">
                    <Icon icon="ic:round-save" class="w-5 h-5 inline-block mr-1" />
                    {{ isEditing ? 'Update' : 'Save' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onBeforeUpdate } from 'vue';
import { Icon } from "@iconify/vue";
import BaseInput from '../global/BaseInput.vue';

const props = defineProps({
    show: Boolean,
    item: Object
});

const emit = defineEmits(['close', 'submit']);

const formData = ref({});
const isEditing = computed(() => !!formData.value.id);

// --- 1. CRITICAL FIX: Function Ref for Multiple Inputs ---
const inputRefs = ref([]);

// This function runs for EVERY component with :ref="setInputRef"
// It collects the actual component instances so we can call .validate() on them
const setInputRef = (el) => {
    if (el) {
        inputRefs.value.push(el);
    }
};

// Reset the array before Vue updates the DOM to prevent duplicates
onBeforeUpdate(() => {
    inputRefs.value = [];
});

// Initialize form data
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        if (props.item) {
            formData.value = JSON.parse(JSON.stringify(props.item));
        } else {
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

// --- 2. SUBMIT & VALIDATE ---
const handleSubmit = () => {
    let isFormValid = true;

    // Loop through all collected input components
    inputRefs.value.forEach((inputComponent) => {
        // Double check that the component exists and has the validate function
        if (inputComponent && typeof inputComponent.validate === 'function') {
            const isValid = inputComponent.validate();
            if (!isValid) {
                isFormValid = false;
            }
        }
    });

    // 3. BLOCK SUBMISSION IF INVALID
    if (!isFormValid) {
        console.log("Validation failed: Check fields highlighted in red.");
        return; // Stop execution here. Do not emit submit.
    }

    // 4. If valid, emit data
    emit('submit', formData.value);
};
</script>

<style scoped>
/* Clean Scrollbar */
.modal-inner-content::-webkit-scrollbar {
    width: 8px;
}

.modal-inner-content::-webkit-scrollbar-track {
    background-color: transparent;
}

.modal-inner-content::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
    border: 2px solid transparent;
    background-clip: content-box;
}

.modal-inner-content::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}
</style>