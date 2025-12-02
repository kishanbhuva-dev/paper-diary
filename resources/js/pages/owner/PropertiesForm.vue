<template>
    <div class="px-4 sm:px-6 py-8 bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto">

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-indigo-800">
                    {{ isEditing ? 'Edit Property' : 'Add New Property' }}
                </h1>
                <button @click="router.push({ name: 'properties' })"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-300 hover:bg-gray-200 transition cursor-pointer duration-150 flex items-center">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 mr-1" />
                    Back to List
                </button>
            </div>

            <div class="bg-white rounded-xl overflow-hidden border border-indigo-300">

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

                    <!-- <div v-if="isEditing">
                        <BaseInput v-model="formData.id" label="Property ID" width="full" variant="gray" disabled />
                    </div> -->

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
                    <!-- check Facilities -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Facilities <span
                                class="text-red-500">*</span></label>
                        <div
                            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                            <div v-for="facility in availableFacilities" :key="facility.id" class="flex items-center">
                                <input :id="`facility-${facility.id}`" type="checkbox" v-model="formData.facilities"
                                    :value="facility.id"
                                    class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" />
                                <label :for="`facility-${facility.id}`"
                                    class="ml-2 text-sm font-medium text-gray-700 cursor-pointer">
                                    {{ facility.name }}
                                </label>
                            </div>
                        </div>

                    </div>
                    <OwnerImageUploader v-model="formData.images" :max-files="10" @reorder="onImagesReorder" />
                </form>

                <div class="flex justify-end pt-4 px-6 pb-6 bg-white border-t border-gray-300 shrink-0">
                    <button type="button" @click="router.push({ name: 'properties' })"
                        class="px-5 py-2 mr-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-150 outline-indigo-400 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" @click="handleSubmit"
                        class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition duration-150 flex items-center shadow-md outline-indigo-400 shadow-indigo-200 cursor-pointer">
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
const availableFacilities = ref([]);
// const fallbackFacilities = [
//     { id: 1, name: 'Free Wi-Fi' },
//     { id: 2, name: 'Swimming Pool' },
//     { id: 3, name: 'Free Parking' },
//     { id: 4, name: 'Air Conditioning' },
//     { id: 5, name: 'Pet Friendly' },
//     { id: 6, name: 'Gym/Fitness Center' },
//     { id: 7, name: '24-Hour Security' },
//     { id: 8, name: 'On-site Restaurant' },
//     { id: 9, name: 'Laundry Service' },
//     { id: 10, name: 'Wheelchair Access' },
// ];

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
    ,
    facilities: []
};
const formData = ref({ ...defaultFormData });
const loadingItem = ref(false);
const initialImageIds = ref([]);

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
            // Prefill facilities if API returned them on the property; otherwise, try cached values
            const loadedFacilityIds = (item.facilities || []).map(f => f.id);
            if (loadedFacilityIds.length) {
                loadedData.facilities = loadedFacilityIds;
            } else {
                // Fallback: use cached property facilities if available (from prior saves in this session)
                try {
                    const cached = propertiesStore.getPropertyCachedFacilities(item.id);
                    loadedData.facilities = Array.isArray(cached) ? cached : [];
                } catch (err) {
                    loadedData.facilities = [];
                }
            }
            initialImageIds.value = loadedImages.map(i => i.id).filter(Boolean);
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
    // Load all available facilities so the UI can render checkboxes
    (async () => {
        const facs = await propertiesStore.fetchFacilities();
        if (Array.isArray(facs) && facs.length) {
            availableFacilities.value = facs.map(f => ({ id: f.id, name: f.name }));
        } else {
            // Fallback to static list if API returns no facilities
            availableFacilities.value = fallbackFacilities;
        }
    })();
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
            // Determine saved property ID
            let savedPropertyId = null;
            if (isEditing.value) {
                savedPropertyId = formData.value.id;
            } else {
                // If backend returned the created object, use it
                if (typeof success === 'object' && success.id) {
                    savedPropertyId = success.id;
                } else {
                    // Fallback: try to find freshly created property using name (increase per_page)
                    await propertiesStore.fetchProperties({ page: 1, per_page: 100, search: formData.value.propertyName });
                    const found = propertiesStore.properties.find(p => p.propertyName === formData.value.propertyName);
                    if (found) {
                        savedPropertyId = found.id;
                    }
                }
            }

            // If savedPropertyId is found, handle images (new files and deletions)
            if (savedPropertyId) {
                // new files
                const newFiles = formData.value.images.filter(img => img.file instanceof File).map(i => i.file);
                // existing image IDs preserved in the form
                const existingIds = formData.value.images.filter(img => img.id).map(i => i.id);
                // deleted: existed previously but no longer present in the form
                const removedIds = initialImageIds.value.filter(id => !existingIds.includes(id));

                // Snapshot images on server before we apply changes, so we can
                // determine how many new images are actually created.
                const beforeRemoteImages = await propertiesStore.fetchPropertyImages(savedPropertyId);
                const beforeRemoteIds = new Set((beforeRemoteImages || []).map(i => i.id));

                if (removedIds.length) {
                    console.debug('[PropertiesForm] deleting removed image ids =', removedIds);
                    const deleted = await propertiesStore.deleteMultiplePropertyImages(removedIds);
                    if (!deleted && propertiesStore.error) {
                        toast.error(propertiesStore.error);
                    }
                }

                if (newFiles.length) {
                    // Debug logs
                    console.debug('[PropertiesForm] uploading newFiles for propertyId =', savedPropertyId, newFiles.map(f => f.name));
                    const created = await propertiesStore.addPropertyImages(savedPropertyId, newFiles);
                    console.debug('[PropertiesForm] addPropertyImages created =', created);

                    // Re-fetch remote images and determine how many were added relative
                    // to the snapshot we took before changes. This avoids false
                    // negative warnings if the API didn't return created objects in
                    // the upload response but the images exist on the server.
                    const afterRemoteImages = await propertiesStore.fetchPropertyImages(savedPropertyId);
                    const newlyOnServer = (afterRemoteImages || []).filter(i => !beforeRemoteIds.has(i.id));
                    const newlyCount = newlyOnServer.length;

                    if (newlyCount > 0) {
                        toast.success(`${newlyCount} image(s) uploaded successfully`);
                    }
                    if (newlyCount < newFiles.length) {
                        // Only warn the user if we’re sure the server did not save all files.
                        // If newlyCount < newFiles.length and the store shows an error, surface it.
                        if (propertiesStore.error) {
                            toast.error(propertiesStore.error);
                        } else if (newlyCount === 0) {
                            // nothing uploaded but no error message means something strange happened
                            toast.warning('Some images did not upload successfully. Check server error or try smaller files.');
                        } else {
                            // Partial success: still warn, but be explicit
                            toast.warning(`${newlyCount} of ${newFiles.length} images uploaded. Try smaller files or re-upload the failed ones.`);
                        }
                    }
                }

                // refresh images from backend
                const remoteImages = await propertiesStore.fetchPropertyImages(savedPropertyId);
                formData.value.images = (remoteImages || []).map(img => ({ id: img.id, url: img.url, file: null }));

                // Ensure server position matches UI order; persist if multiple images present
                const orderedIdsAfter = formData.value.images.map(i => i.id).filter(Boolean);
                if (orderedIdsAfter.length > 1) {
                    const resPos = await propertiesStore.changePropertyImagePosition(savedPropertyId, orderedIdsAfter);
                    if (!resPos && propertiesStore.error) {
                        console.debug('[PropertiesForm] changePropertyImagePosition failed after upload:', propertiesStore.error);
                    }
                }

                // Persist selected facilities (owner endpoint expects { data: { propertyId, facilityId: [] } })
                if (Array.isArray(formData.value.facilities)) {
                    // sanitize facility IDs - ensure only integers
                    const facilityIds = formData.value.facilities
                        .map(id => parseInt(id, 10))
                        .filter(id => Number.isInteger(id) && id > 0);
                    // Always call the endpoint, even with an empty array, so
                    // previously set facilities can be cleared if the user
                    // unchecks all boxes (sync([]) behavior on backend).
                    const successFacilities = await propertiesStore.setPropertyFacilities(savedPropertyId, facilityIds);
                    if (successFacilities) {
                        toast.success('Facilities updated');
                    } else if (propertiesStore.error) {
                        toast.error(propertiesStore.error);
                    }
                }
            }

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

// Handler fired when uploader emits a reorder event (images array updated)
const onImagesReorder = async (newImages) => {
    // Update local form data (already done by v-model) just ensure it's consistent
    formData.value.images = [...newImages];

    // If editing and we have a saved property id, persist order to server
    if (isEditing.value && formData.value.id) {
        // keep only existing ids
        const ids = formData.value.images.map(i => i.id).filter(Boolean);
        if (ids.length) {
            const success = await propertiesStore.changePropertyImagePosition(formData.value.id, ids);
            if (success) {
                toast.success('Image order updated');
            } else {
                toast.error(propertiesStore.error || 'Failed to update image order');
            }
        }
    }
};
</script>