import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";
// import api from "../api"; // <--- USE THIS if you created api.js
import Swal from "sweetalert2"; // 👈 ADDED for toast notifications

import { useAuthStore } from "./authStore";

export const usePropertiesStore = defineStore("properties", () => {
  // --- STATE ---
  const properties = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const total = ref(0); // Holds total count for server-side pagination

  // --- API CONFIG ---
  // If using api.js, remove the full domain/prefix if it's in baseURL
  const API_BASE_URL = "/api/owner/property";

  // --- HELPER: TOAST NOTIFICATION ---
  const displayToast = (icon, title) => {
    Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
      },
    }).fire({
      icon: icon,
      title: title,
    });
  };

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
    // We don't throw here to prevent breaking the UI, just set the error state
  };

  // --- ACTIONS ---

  // 1. FETCH (Fixed Syntax & Logic)
  async function fetchProperties(params = {}) {
    loading.value = true;
    error.value = null;

    try {
      // If using api.js, change 'axios.get' to 'api.get'
      const response = await axios.get(API_BASE_URL, { params });

      if (response.data.status) {
        // Adjust this path based on your exact Laravel API response structure
        const paginationData = response.data.data;

        // 1. Get the array of rows
        const fetchedItems = paginationData.data || paginationData;

        // 2. Get the total count (Laravel standard is 'total')
        // If paginationData is just an array, use length. If it's an object, use .total
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

  // 2. CREATE
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
        // Success Toast
        displayToast(
          "success",
          response.data.message || "Property created successfully"
        );
        // Refresh the list (will be done by component after receiving success signal)
        return true;
      } else {
        // Failure Toast
        const errorMessage =
          response.data.message || "Property creation failed";
        displayToast("error", errorMessage);
        error.value = errorMessage;
        return false;
      }
    } catch (err) {
      handleError(err, "createProperty");
      displayToast("error", "API Error: Failed to create property");
      return false;
    } finally {
      loading.value = false;
    }
  }

  // 3. UPDATE
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
        // Success Toast
        displayToast(
          "success",
          response.data.message || "Property updated successfully"
        );
        // await fetchProperties(); // will be done by component
        return true;
      } else {
        // Failure Toast
        const errorMessage = response.data.message || "Property update failed";
        displayToast("error", errorMessage);
        error.value = errorMessage;
        return false;
      }
    } catch (err) {
      handleError(err, "updateProperty");
      displayToast("error", "API Error: Failed to update property");
      return false;
    } finally {
      loading.value = false;
    }
  }

  // 4. DELETE
  async function deleteProperty(id) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`${API_BASE_URL}/${id}`);

      // Keep this log for backend debugging
      console.log("Delete Response Data:", response.data);

      if (response.data.status) {
        // Success Toast
        displayToast(
          "success",
          response.data.message || "Property deleted successfully"
        );

        // Optimistic update: remove immediately from UI
        properties.value = properties.value.filter((item) => item.id !== id);
        // Also update total count locally so pagination doesn't break
        total.value = Math.max(0, total.value - 1);

        return true;
      } else {
        // Failure Toast (This shows the crucial backend error message)
        const errorMessage =
          response.data.message ||
          "Deletion failed due to unknown backend error.";
        displayToast("error", errorMessage);
        error.value = errorMessage;
        return false;
      }
    } catch (err) {
      handleError(err, "deleteProperty");
      displayToast("error", "API Error: Failed to delete property");
      return false;
    } finally {
      loading.value = false;
    }
  }

  return {
    properties,
    loading,
    error,
    total, // Export total so the component can use it
    fetchProperties,
    createProperty,
    updateProperty,
    deleteProperty,
  };
});
