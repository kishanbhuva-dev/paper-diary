import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";
// import api from "../api"; // Use this if you have a global API instance
import { useAuthStore } from "./authStore";

export const usePropertiesStore = defineStore("properties", () => {
  // --- STATE ---
  const properties = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const total = ref(0);

  // --- API CONFIG ---
  const API_BASE_URL = "/api/owner/property";

  // --- HELPER: ERROR HANDLER ---
  const handleError = (err, actionName) => {
    const authStore = useAuthStore();
    let errorMessage = "An error occurred";

    if (err.response) {
      if (err.response.status === 401) {
        console.error("Authentication Required. Logging out.");
        authStore.logout();
        return;
      }
      if (err.response.data && err.response.data.message) {
        errorMessage = err.response.data.message;
      }
      if (err.response.data && err.response.data.errors) {
        const validationErrors = err.response.data.errors;
        errorMessage +=
          ":\n" + Object.values(validationErrors).flat().join("\n");
      }
    } else {
      errorMessage = err.message;
    }

    error.value = errorMessage;
    console.error(`Error in ${actionName}:`, errorMessage);
  };

  // --- ACTIONS ---

  // 1. FETCH ALL PROPERTIES
  async function fetchProperties(params = {}) {
    loading.value = true;
    error.value = null;

    try {
      const response = await axios.get(API_BASE_URL, { params });

      if (response.data.status) {
        const paginationData = response.data.data;
        const fetchedItems = paginationData.data || paginationData;

        total.value =
          paginationData.total !== undefined
            ? paginationData.total
            : fetchedItems.length;

        properties.value = fetchedItems.map((item) => ({
          ...item,
          statusDisplay: item.status === 1 ? "Active" : "Inactive",
        }));
      }
    } catch (err) {
      handleError(err, "fetchProperties");
    } finally {
      loading.value = false;
    }
  }

  // 2. FETCH PROPERTY BY ID (NEW ACTION FOR EDIT PAGE)
  async function fetchPropertyById(id) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`${API_BASE_URL}/${atob(id)}`);
      if (response.data.status) {
        const prop = response.data.data;
        // attach images (if backend provides a separate endpoint)
        try {
          const images = await fetchPropertyImages(prop.id);
          prop.images = (images || []).map((img) => ({
            id: img.id,
            url: img.url,
            position: img.position,
          }));
        } catch (e) {
          prop.images = [];
        }

        // Normalize facilities if backend includes them on the property
        if (prop && Array.isArray(prop.facilities)) {
          prop.facilities = (prop.facilities || []).map((f) => ({
            id: f.id,
            name: f.name,
          }));
        }
        return prop;
      }
      // If status is false, set error and return null
      error.value =
        response.data.message || `Property with ID ${id} not found.`;
      return null;
    } catch (err) {
      handleError(err, `fetchPropertyById(${id})`);
      return null;
    } finally {
      loading.value = false;
    }
  }

  // --- IMAGES: CRUD OPERATIONS (Owner property images) ---
  async function fetchPropertyImages(propertyId) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/owner/property-wise-image`, {
        params: { propertyId },
      });
      if (response.data.status) {
        const base = window.location.origin || "";
        const normalized = (response.data.data || []).map((img) => ({
          id: img.id,
          url: img.image
            ? `${base}/storage/property/images/${img.image}`
            : img.url || img.path || "",
          position: img.position || 0,
        }));
        return normalized;
      }
      error.value = response.data.message || "Failed to fetch property images";
      return [];
    } catch (err) {
      handleError(err, "fetchPropertyImages");
      return [];
    } finally {
      loading.value = false;
    }
  }

  // --- FACILITIES ---
  async function fetchFacilities(params = {}) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/owner/facility`, { params });
      if (response.data.status) {
        // Return array of facilities
        return response.data.data || [];
      }
      error.value = response.data.message || "Failed to fetch facilities";
      return [];
    } catch (err) {
      handleError(err, "fetchFacilities");
      return [];
    } finally {
      loading.value = false;
    }
  }

  async function setPropertyFacilities(propertyId, facilityIds = []) {
    loading.value = true;
    error.value = null;
    try {
      const payload = { data: { propertyId, facilityId: facilityIds } };
      const response = await axios.post(
        `/api/owner/add-facility-property`,
        payload
      );
      if (response.data.status) return true;
      error.value =
        response.data.message || "Setting property facilities failed";
      return false;
    } catch (err) {
      handleError(err, "setPropertyFacilities");
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function addPropertyImages(propertyId, files) {
    loading.value = true;
    error.value = null;
    try {
      console.debug(
        "[propertiesStore] addPropertyImages called for propertyId=",
        propertyId,
        "files=",
        files.map((f) => f.name)
      );

      // Fetch the current list of images before uploading so we can
      // determine which images were newly created after upload. This
      // makes the function's return value reliable even if the backend
      // responses don't include created objects for each upload.
      const beforeImages = await fetchPropertyImages(propertyId).catch(
        () => []
      );
      const beforeIds = new Set((beforeImages || []).map((i) => i.id));

      const createdImages = [];
      for (const file of files) {
        try {
          const formData = new FormData();
          formData.append("propertyId", propertyId);
          formData.append("images[]", file);
          const response = await axios.post(
            `/api/owner/property-image-store`,
            formData,
            {
              headers: { "Content-Type": "multipart/form-data" },
            }
          );
          console.debug(
            "[propertiesStore] addPropertyImages response:",
            response.data
          );
          if (response.data.status) {
            // backend returns image records for this upload or nothing;
            // we try to push if present but don't rely solely on it.
            if (
              Array.isArray(response.data.data) &&
              response.data.data.length
            ) {
              createdImages.push(...response.data.data);
            } else if (response.data.data) {
              createdImages.push(response.data.data);
            }
          }
        } catch (err) {
          if (err && err.response && err.response.status === 413) {
            error.value =
              "One or more images are too large. Please upload smaller files.";
          } else {
            handleError(err, "addPropertyImages");
          }
          console.error(
            "[propertiesStore] addPropertyImages file upload failed for",
            file.name,
            err
          );
        }
      }
      // Fetch images after uploads and derive the newly created ones by
      // comparing their ids with `beforeIds`. This handles backend
      // implementations that don't return created objects on upload.
      const afterImages = await fetchPropertyImages(propertyId).catch(() => []);
      const newlyCreated = (afterImages || []).filter(
        (i) => !beforeIds.has(i.id)
      );

      // If we couldn't detect newly created images from server fetch,
      // fall back to any objects we collected directly from upload
      // responses (createdImages). This keeps previous behavior.
      if (newlyCreated && newlyCreated.length) {
        console.debug(
          "[propertiesStore] addPropertyImages newly created count =",
          newlyCreated.length
        );
        return newlyCreated;
      }

      console.debug(
        "[propertiesStore] addPropertyImages falling back to response-collected createdImages count =",
        createdImages.length
      );
      return createdImages;
    } catch (err) {
      handleError(err, "addPropertyImages");
      return [];
    } finally {
      loading.value = false;
    }
  }

  async function deletePropertyImage(imageId) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(
        `/api/owner/single-property-image-delete`,
        { id: imageId }
      );
      if (response.data.status) return true;
      error.value = response.data.message || "Image delete failed";
      return false;
    } catch (err) {
      handleError(err, "deletePropertyImage");
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function deleteMultiplePropertyImages(ids) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/owner/property-image-delete`, {
        ids,
      });
      if (response.data.status) return true;
      error.value = response.data.message || "Multi image delete failed";
      return false;
    } catch (err) {
      handleError(err, "deleteMultiplePropertyImages");
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function updatePropertyImage(imageId, file) {
    loading.value = true;
    error.value = null;
    try {
      const formData = new FormData();
      formData.append("imageId", imageId);
      formData.append("image", file);
      const response = await axios.post(
        `/api/owner/property-image-update`,
        formData,
        {
          headers: { "Content-Type": "multipart/form-data" },
        }
      );
      if (response.data.status) return response.data.data || true;
      error.value = response.data.message || "Image update failed";
      return false;
    } catch (err) {
      handleError(err, "updatePropertyImage");
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function changePropertyImagePosition(propertyId, positions) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(
        `/api/owner/property-image-position-change`,
        { propertyId, ids: positions }
      );
      if (response.data.status) return true;
      error.value = response.data.message || "Image position change failed";
      return false;
    } catch (err) {
      handleError(err, "changePropertyImagePosition");
      return false;
    } finally {
      loading.value = false;
    }
  }

  // 3. CREATE
  async function createProperty(newProperty) {
    loading.value = true;
    error.value = null;

    const dataToSend = {};
    for (const key in newProperty) {
      if (key !== "id" && newProperty[key] !== null) {
        dataToSend[key] = newProperty[key];
      }
    }

    try {
      const response = await axios.post(API_BASE_URL, dataToSend);
      if (response.data.status) {
        // If data returned is a property object, return it; otherwise return true
        if (
          response.data.data &&
          typeof response.data.data === "object" &&
          response.data.data.id
        ) {
          return response.data.data;
        }
        return true; // Success
      } else {
        error.value = response.data.message || "Property creation failed";
        return false;
      }
    } catch (err) {
      handleError(err, "createProperty");
      return false;
    } finally {
      loading.value = false;
    }
  }

  // 4. UPDATE
  async function updateProperty(updatedProperty) {
    loading.value = true;
    error.value = null;

    const dataToSend = {};
    for (const key in updatedProperty) {
      if (
        key !== "id" &&
        key !== "statusDisplay" &&
        updatedProperty[key] !== null
      ) {
        dataToSend[key] = updatedProperty[key];
      }
    }

    try {
      const response = await axios.put(
        `${API_BASE_URL}/${updatedProperty.id}`,
        dataToSend
      );
      if (response.data.status) {
        return true; // Success
      } else {
        error.value = response.data.message || "Property update failed";
        return false;
      }
    } catch (err) {
      handleError(err, "updateProperty");
      return false;
    } finally {
      loading.value = false;
    }
  }

  // 5. DELETE
  async function deleteProperty(id) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`${API_BASE_URL}/${id}`);

      if (response.data.status) {
        properties.value = properties.value.filter((item) => item.id !== id);
        total.value = Math.max(0, total.value - 1);
        return true; // Success
      } else {
        error.value = response.data.message || "Deletion failed.";
        return false;
      }
    } catch (err) {
      handleError(err, "deleteProperty");
      return false;
    } finally {
      loading.value = false;
    }
  }

  return {
    properties,
    loading,
    error,
    total,
    fetchProperties,
    fetchPropertyById, // This is correctly exported
    createProperty,
    updateProperty,
    deleteProperty,
    // Image operations
    fetchPropertyImages,
    addPropertyImages,
    deletePropertyImage,
    deleteMultiplePropertyImages,
    updatePropertyImage,
    changePropertyImagePosition,
    // Facilities
    fetchFacilities,
    setPropertyFacilities,
  };
});
