import { defineStore } from "pinia";
import { ref, computed } from "vue";
import axios from "axios";

// Utility to set the global Authorization header for authenticated requests
function setAuthHeader(token) {
  if (token) {
    // Ensure the header is correctly set for all subsequent requests
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
  } else {
    delete axios.defaults.headers.common["Authorization"];
  }
}

// ----------------------------------------------------------------------
// 1. Initial State Setup
// ----------------------------------------------------------------------
// Load state from localStorage on application start
const initialToken = localStorage.getItem("authToken");
// Use try-catch for robustness in case localStorage item is malformed
const initialUser = localStorage.getItem("user")
  ? JSON.parse(localStorage.getItem("user"))
  : null;

if (initialToken) {
  setAuthHeader(initialToken);
}
// ----------------------------------------------------------------------

export const useAuthStore = defineStore("auth", () => {
  // --- STATE ---
  const token = ref(initialToken);
  const user = ref(initialUser);

  // --- GETTERS ---
  const isAuthenticated = computed(() => !!token.value);

  // CRITICAL FIX: Normalize the role to lowercase for case-insensitive checking
  const userRole = computed(() => {
    if (!user.value || !user.value.role) {
      return null;
    }
    return user.value.role.toLowerCase();
  });

  // These now reliably check the normalized (lowercase) role string
  const isAdmin = computed(() => userRole.value === "admin");
  const isOwner = computed(() => userRole.value === "owner");

  const userName = computed(() =>
    user.value ? user.value.firstName : "Guest"
  );

  // --- ACTIONS ---

  function handleSuccessfulLogin(newToken, newUserDetails) {
    token.value = newToken;
    user.value = newUserDetails;

    localStorage.setItem("authToken", newToken);
    // localStorage.setItem("userRole", newUserDetails.role);
    localStorage.setItem("user", JSON.stringify(newUserDetails)); // Store user object

    setAuthHeader(newToken);
  }

  function clearAuthData() {
    token.value = null;
    user.value = null;
    localStorage.removeItem("authToken");
    localStorage.removeItem("user");
    setAuthHeader(null);
  }

  async function logout() {
    if (token.value) {
      try {
        // Ensure the API logout route exists and works
        await axios.post("/api/logout");
      } catch (error) {
        console.error(
          "Logout API call failed. Proceeding with local token removal:",
          error
        );
      }
    }

    clearAuthData();
  }

  return {
    token,
    user,
    isAuthenticated,
    userRole,
    isAdmin,
    isOwner,
    userName,
    handleSuccessfulLogin,
    logout,
  };
});
