/*
Central Axios client for the app.

Features:
- baseURL default (reads from environment variable or falls back to '/api').
- default headers (JSON/Accept)
- request interceptor attaches auth token from localStorage (key: 'authToken')
- response interceptor handles error messages and toast notifications
- request debouncing / cancellation via AbortController and a pendingRequests Map

This follows the service-based architecture pattern with centralized API communication.
*/

import axios from "axios";
import { toast } from "vue-sonner";

// Create axios instance with base configuration
const apiClient = axios.create({
  baseURL: "/api",
  timeout: 30000, // 30 second timeout for API calls
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

// pendingRequests map: requestKey -> AbortController
// Store AbortController for each in-flight request to cancel duplicates (debounce)
const pendingRequests = new Map();

// Build a stable key for each request using URL (simple approach for deduplication)
function makeRequestKey(config) {
  try {
    const method = (config.method || "get").toUpperCase();
    const url = config.url || "";
    return `${method}::${url}`;
  } catch (e) {
    return `${config.method}::${config.url}`;
  }
}

// Request interceptor: add auth token and handle request cancellation
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("authToken");

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Use request key for debouncing/cancellation
    const requestKey = config.url;

    // If a pending request with the same key exists, abort it
    if (pendingRequests.has(requestKey)) {
      const previousController = pendingRequests.get(requestKey);
      previousController.abort();
      pendingRequests.delete(requestKey);
    }

    // Create a new AbortController and store it
    const newController = new AbortController();
    config.signal = newController.signal;
    pendingRequests.set(requestKey, newController);

    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor: handle success responses and errors with toast notifications
apiClient.interceptors.response.use(
  (response) => {
    // Removed automatic success toast - let individual operations handle toasts as needed
    // const { status, message } = response.data || {};
    // if (message !== undefined && message !== "") {
    //   if (status === true) {
    //     toast(message, { type: "success" });
    //   }
    // }

    // Clean up the pending request after a successful response
    const requestKey = response.config.url;
    if (pendingRequests.has(requestKey)) {
      pendingRequests.delete(requestKey);
    }

    return response;
  },
  (error) => {
    // Handle timeout errors specifically
    if (error.code === "ECONNABORTED" || error.message.includes("timeout")) {
      toast("Request timed out. Please try again.", { type: "error" });
      return Promise.reject(error);
    }

    // Handle cancelled requests (don't show toast)
    if (axios.isCancel(error)) {
      console.log("Request was automatically aborted:", error.message);
      return Promise.reject(error);
    }

    // Handle specific HTTP status codes
    if (error.response?.status === 401) {
      toast("Authentication required. Please login.", { type: "error" });
    } else if (error.response?.status === 403) {
      toast("Access denied.", { type: "error" });
    } else if (error.response?.status === 404) {
      toast("Resource not found.", { type: "error" });
    } else if (error.response?.status === 413) {
      toast("File upload too large.", { type: "error" });
    } else if (error.response?.status >= 500) {
      const message =
        error.response?.data?.message || "Server error. Try again later.";
      toast(message, { type: "error" });
    } else {
      const message =
        error.response?.data?.message || error.message || "An error occurred";
      toast(message, { type: "error" });
    }

    // Clean up the pending request even if there's an error
    if (error.config?.url) {
      const requestKey = error.config.url;
      if (pendingRequests.has(requestKey)) {
        pendingRequests.delete(requestKey);
      }
    }

    console.error("API error:", error);
    return Promise.reject(error);
  }
);

export default apiClient;
export { pendingRequests, makeRequestKey };
