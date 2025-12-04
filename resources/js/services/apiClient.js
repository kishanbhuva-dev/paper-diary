import axios from "axios";
import { toast } from "vue-sonner";
import {
  formatValidationErrors,
  isValidationError,
} from "../utils/errorHandler";

// Create axios instance with default config
const apiClient = axios.create({
  baseURL: "/api",
  timeout: 30000, // Increased timeout to 30 seconds for complex dashboard queries
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

const pendingRequests = new Map();

// Request interceptor for adding auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Use a unique key for each request, e.g., the URL
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

// Response interceptor for handling errors
apiClient.interceptors.response.use(
  (response) => {
    const { status, message } = response.data || {};

    if (message !== undefined && message !== "") {
      toast(message, { type: status ? "success" : "error" });
    }

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

    // Handle 402 Payment Required responses
    if (error.response?.status === 402) {
      const responseData = error.response.data || {};
      const defaultMessage =
        "Payment required. Please purchase a subscription or credits to continue.";
      const message = responseData.message || defaultMessage;
      toast(message, { type: "warning" });
    } else if (!axios.isCancel(error)) {
      if (isValidationError(error)) {
        const formattedMessage = formatValidationErrors(
          error.response.data.errors
        );
        toast(formattedMessage, {
          type: "error",
          autoClose: 5000,
          style: { whiteSpace: "pre-line" },
        });
      } else {
        const message = error.response?.data?.message || "An error occurred";
        toast(message, { type: "error" });
      }
    }

    // Clean up the pending request even if there's an error
    if (error.config?.url) {
      const requestKey = error.config.url;
      if (pendingRequests.has(requestKey)) {
        pendingRequests.delete(requestKey);
      }
    }

    // Check if the error is due to a cancelled request
    if (axios.isCancel(error)) {
      console.log("Request was automatically aborted:", error.message);
    } else {
      console.error("API error:", error);
    }

    return Promise.reject(error);
  }
);

export default apiClient;
