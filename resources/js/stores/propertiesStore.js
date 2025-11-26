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
      const response = await axios.get(`${API_BASE_URL}/${id}`);
      if (response.data.status) {
        // Assuming the API returns the single property data directly under 'data'
        return response.data.data;
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
  };
});
