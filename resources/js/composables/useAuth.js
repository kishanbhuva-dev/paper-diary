/*
New composable implementation for authentication.
- Provides shared reactive token & user state without Pinia.
- Uses `authService` for API calls and persists to localStorage.
*/

import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import authService from "../services/authService";

// Shared singleton reactive state for the app
const token = ref(localStorage.getItem("authToken") || null);
const user = ref(
  localStorage.getItem("user") ? JSON.parse(localStorage.getItem("user")) : null
);
// console.log("testing ", user);

const isAuthenticated = computed(() => !!token.value);
const isAdmin = computed(
  () =>
    !!(
      user.value &&
      user.value.role &&
      user.value.role.toLowerCase() === "admin"
    )
);

// Router instance - accessed from function context
let routerInstance = null;

function persistAuth(newToken, newUser) {
  try {
    if (newToken) {
      localStorage.setItem("authToken", newToken);
      token.value = newToken;
    } else {
      localStorage.removeItem("authToken");
      token.value = null;
    }
  } catch (e) {
    console.error("[persistAuth] Error saving token:", e);
  }

  try {
    if (newUser) {
      localStorage.setItem("user", JSON.stringify(newUser));
      user.value = newUser;
    } else {
      localStorage.removeItem("user");
      user.value = null;
    }
  } catch (e) {
    console.error("[persistAuth] Error saving user:", e);
  }
}

export function useAuth() {
  // Initialize router on first useAuth call
  if (!routerInstance) {
    routerInstance = useRouter();
  }

  // Login via authService and persist token/user. Also redirect to dashboard on success.
  async function login(credentials) {
    const res = await authService.login(credentials);

    // res is already response.data from authService
    // Backend response structure: { status, message, data: { token, user: { id, email, role, ... } } }
    if (!res || !res.data || !res.data.token || !res.data.user) {
      throw new Error(
        res?.message || "Login failed: Invalid response from server"
      );
    }

    // Persist token and user from res.data
    persistAuth(res.data.token, res.data.user);

    // Redirect based on user role
    setTimeout(() => {
      const role = res.data.user?.role?.toLowerCase();

      if (role === "owner") {
        routerInstance.push({ name: "owner-dashboard" });
      } else if (role === "admin") {
        routerInstance.push({ name: "admin-dashboard" });
      } else {
        routerInstance.push("/");
      }
    }, 300);

    return { success: true, data: res };
  }

  // Logout and clear persisted state, then redirect to login
  async function logout() {
    try {
      await authService.logout();
    } catch (e) {
      console.debug("Logout API failed:", e);
    }

    // Clear authentication state
    persistAuth(null, null);

    // Redirect to login page
    routerInstance.push({ name: "login" });
  }

  // Refresh token endpoint
  async function refreshToken() {
    const res = await authService.refreshToken();
    if (res && res.status && res.data) {
      const tkn = res.data.token || res.data.access_token || null;
      if (tkn) persistAuth(tkn, user.value);
    }
    return res;
  }

  function setUser(u) {
    persistAuth(token.value, u);
  }

  return {
    token,
    user,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    refreshToken,
    setUser,
    // Expose a setter to persist both token and user (useful for compatibility shims)
    setAuth: (tkn, usr) => persistAuth(tkn, usr),
  };
}

export default useAuth;
