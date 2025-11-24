import { useRouter } from "vue-router";
import axios from "axios";
import { useAuthStore } from "../stores/authStore";

// Define the composable function
export function useAuth() {
  const router = useRouter();
  const authStore = useAuthStore();

  // --- Login Logic ---
  const login = async (credentials) => {
    try {
      const response = await axios.post("/api/login", credentials);
      const responseData = response.data;

      if (responseData.status === true && responseData.data.token) {
        const { token, user } = responseData.data;

        // 1. Use Pinia Action to save token and user data
        authStore.handleSuccessfulLogin(token, user);

        // 2. Determine the correct redirect path
        let redirectTo = null;

        // FIX: Access Pinia state directly. Do NOT use .value here.
        if (authStore.isOwner) {
          redirectTo = "/owner";
        } else if (authStore.isAdmin) {
          redirectTo = "/admin";
        } else if (authStore.userRole === "user") {
          redirectTo = "/"; // Standard user dashboard/home
        }

        // 3. Handle Redirection
        if (redirectTo) {
          router.push(redirectTo);
        } else {
          // For unauthorized roles, log out and reject
          authStore.logout();
          throw new Error(
            "Access denied. Your user role is not authorized for this application."
          );
        }

        return { success: true, message: "Login successful" };
      } else {
        const message =
          responseData.message ||
          "Login failed. Please check your credentials.";
        return { success: false, message };
      }
    } catch (error) {
      let message = "An unexpected error occurred. Please try again.";

      if (error.message.includes("Access denied")) {
        message = error.message;
      } else if (error.response) {
        if (error.response.status === 401) {
          message = "Invalid email or password.";
        } else if (error.response.data && error.response.data.message) {
          message = error.response.data.message;
        }
      } else {
        message = "Network error. Could not connect to the server.";
      }

      return { success: false, message };
    }
  };

  // --- Logout Logic ---
  const logout = async () => {
    await authStore.logout();
    router.push("/login");
  };

  return {
    login,
    logout,
    // Use computeds if you want reactivity in the component,
    // but access them via the store instance usually works fine too.
    isAuthenticated: authStore.isAuthenticated,
    userRole: authStore.userRole,
    isOwner: authStore.isOwner,
    isAdmin: authStore.isAdmin,
    userName: authStore.userName,
  };
}
