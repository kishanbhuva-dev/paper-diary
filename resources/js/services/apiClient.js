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

// Request interceptor for adding auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("authToken");

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

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

      const {message} = responseData;
      toast(message, { type: "warning" });
    } else if (isValidationError(error)) {
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

    console.error("API error:", error);

    return Promise.reject(error);
  }
);

export default apiClient;
