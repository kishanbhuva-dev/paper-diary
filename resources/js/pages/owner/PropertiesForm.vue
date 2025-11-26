<template>
    <div class="px-4 sm:px-6 py-8 bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto">

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-indigo-800">
                    {{ isEditing ? 'Edit Property' : 'Add New Property' }}
                </h1>
                <button @click="router.push({ name: 'properties' })"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-300 hover:bg-gray-100 transition duration-150 flex items-center shadow-sm">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 mr-1" />
                    Back to List
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-indigo-200">

                <div class="p-5 bg-indigo-100 flex justify-between items-center border-b border-indigo-200 shrink-0">
                    <h3 class="text-xl font-bold text-indigo-900">
                        Details
                    </h3>
                </div>

                <div v-if="loadingItem" class="p-10 flex justify-center items-center">
                    <Icon icon="eos-icons:loading" class="w-8 h-8 text-indigo-600 animate-spin" />
                    <span class="ml-3 text-indigo-600 font-semibold">Loading data...</span>
                </div>

                <form v-else @submit.prevent="handleSubmit" class="p-6 space-y-4">

                    <div v-if="isEditing">
                        <BaseInput v-model="formData.id" label="Property ID" width="full" variant="gray" disabled />
                    </div>

                    <BaseInput :ref="setInputRef" v-model="formData.propertyName" label="Property Name" width="full"
                        placeholder="e.g. Seaside Villa" required :max-length="50" :show-count="true" />

                    <BaseInput :ref="setInputRef" v-model="formData.address" label="Address" width="full"
                        placeholder="e.g. 123 Ocean Drive" required :max-length="100" />

                    <BaseInput :ref="setInputRef" v-model="formData.email" label="Email" type="email" width="full"
                        placeholder="contact@example.com" required :max-length="50" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <BaseInput :ref="setInputRef" v-model="formData.city" label="City" width="full"
                            placeholder="London" required :max-length="20" />
                        <BaseInput :ref="setInputRef" v-model="formData.country" label="Country" width="full"
                            placeholder="UK" required :max-length="20" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <BaseInput :ref="setInputRef" v-model="formData.postcode" label="Postcode" width="full"
                            placeholder="SW1A 1AA" required :max-length="10" />
                        <BaseInput :ref="setInputRef" v-model="formData.telephone" label="Telephone" width="full"
                            placeholder="+44 7911 123456" :max-length="15" :pattern="/^[0-9+\-\s]*$/"
                            custom-error="Only numbers and + - allowed" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

                    <OwnerImageUploader v-model="formData.images" :max-files="10" />
                </form>

                <div class="flex justify-end pt-4 px-6 pb-6 bg-white border-t border-gray-300 shrink-0">
                    <button type="button" @click="router.push({ name: 'properties' })"
                        class="px-5 py-2 mr-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-150 outline-indigo-400">
                        Cancel
                    </button>
                    <button type="button" @click="handleSubmit"
                        class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition duration-150 flex items-center shadow-md outline-indigo-400 shadow-indigo-200">
                        <Icon icon="ic:round-save" class="w-5 h-5 inline-block mr-1" />
                        {{ isEditing ? 'Update Property' : 'Save New Property' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUpdate } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Icon } from "@iconify/vue";
import { toast } from 'vue-sonner';
import BaseInput from '../../components/global/BaseInput.vue';
import OwnerImageUploader from '../../components/owner/OwnerImageUploader.vue';
import { usePropertiesStore } from '../../stores/propertiesStore';

const route = useRoute();
const router = useRouter();
const propertiesStore = usePropertiesStore();

const defaultFormData = {
    status: 1,
    propertyName: '',
    email: '',
    address: '',
    city: '',
    country: '',
    latitude: '',
    longitude: '',
    postcode: '',
    telephone: '',
    images: [] // <-- NEW: Initialize images array
};
const formData = ref({ ...defaultFormData });
const loadingItem = ref(false);

const propertyId = computed(() => route.params.id);
const isEditing = computed(() => !!propertyId.value);

// --- Component Refs for Validation ---
const inputRefs = ref([]);
const setInputRef = (el) => {
    if (el) {
        inputRefs.value.push(el);
    }
};
onBeforeUpdate(() => {
    inputRefs.value = [];
});

// --- Data Initialization ---
const loadPropertyForEdit = async (id) => {
    if (typeof propertiesStore.fetchPropertyById !== 'function') {
        console.error("Store error: fetchPropertyById is missing.");
        toast.error('Initialization error: Required data function is missing.');
        router.push({ name: 'properties' });
        return;
    }

    loadingItem.value = true;
    try {
        const item = await propertiesStore.fetchPropertyById(id);

        if (item) {
            // Success: Load data
            // Ensure `item.images` exists and map it to a format the Uploader understands
            const loadedImages = (item.images || []).map(img => ({
                id: img.id, // Existing image ID
                url: img.url, // Existing image URL
                file: null // No file object for existing images
            }));

            // Deep clone item, then override/add the images property
            const loadedData = JSON.parse(JSON.stringify(item));
            loadedData.images = loadedImages;
            formData.value = loadedData;

        } else {
            toast.error(`Property with ID ${id} not found or failed to load.`);
            router.push({ name: 'properties' });
        }
    } catch (e) {
        // Critical Error: Network/Server down
        toast.error('Failed to load property data.');
        console.error("Error loading property:", e);
        router.push({ name: 'properties' });
    } finally {
        loadingItem.value = false;
    }
};

onMounted(() => {
    if (isEditing.value) {
        loadPropertyForEdit(propertyId.value);
    } else {
        formData.value = { ...defaultFormData };
    }
});

// --- Submission Logic ---
const handleSubmit = async () => {
    let isFormValid = true;

    // Run validation on standard inputs
    inputRefs.value.forEach((inputComponent) => {
        if (inputComponent && typeof inputComponent.validate === 'function') {
            const isValid = inputComponent.validate();
            if (!isValid) {
                isFormValid = false;
            }
        }
    });

    if (!isFormValid) {
        toast.warning("Please correct the validation errors before submitting.");
        return;
    }

    const action = isEditing.value ? 'Update' : 'Create';
    const loadingToastId = toast.loading(`${action} property...`);

    try {
        let success = false;

        // --- API INTEGRATION PREPARATION (For when you implement the API) ---
        // 1. Separate new files from existing/deleted files
        const dataToSend = { ...formData.value };
        const newFiles = dataToSend.images.filter(img => img.file instanceof File);
        const existingImages = dataToSend.images.filter(img => img.id !== null);

        // You would typically use a FormData object to send files
        // For now, we'll just log the structure you need:
        console.log("New files to upload:", newFiles.map(f => f.file.name));
        console.log("Existing images to keep:", existingImages.map(f => f.id));

        // In your future API integration:
        // Use a FormData object to send 'newFiles' for upload.
        // Send a list of 'existingImages' IDs to confirm which images to keep.
        // Any existing images not in this list were "deleted" by the user.
        // --- END API INTEGRATION PREPARATION ---


        if (isEditing.value) {
            // Placeholder: Assume updateProperty handles the files structure you send
            success = await propertiesStore.updateProperty(dataToSend);
        } else {
            // Placeholder: Assume createProperty handles the files structure you send
            success = await propertiesStore.createProperty(dataToSend);
        }

        toast.dismiss(loadingToastId);

        if (success) {
            toast.success(`Property ${action.toLowerCase()}d successfully`, { duration: 4000 });
            router.push({ name: 'properties' });
        } else {
            const msg = propertiesStore.error || `${action} failed due to an unknown error.`;
            toast.error(msg);
        }
    } catch (e) {
        console.error(e);
        toast.dismiss(loadingToastId);
        toast.error(`An unexpected error occurred during ${action.toLowerCase()}.`);
    }
};
</script>