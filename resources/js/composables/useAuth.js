import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import authService from "../services/authService";

const token = ref(localStorage.getItem("authToken") || null);
const user = ref(
  localStorage.getItem("user") ? JSON.parse(localStorage.getItem("user")) : null
);

const isAuthenticated = computed(() => !!token.value);
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
  if (!routerInstance) {
    routerInstance = useRouter();
  }
  async function login(credentials) {
    const res = await authService.login(credentials);
    if (!res || !res.data || !res.data.token || !res.data.user) {
      throw new Error(
        res?.message || "Login failed: Invalid response from server"
      );
    }

    persistAuth(res.data.token, res.data.user);

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

  async function logout() {
    try {
      await authService.logout();
    } catch (e) {
      console.debug("Logout API failed:", e);
    }

    persistAuth(null, null);

    routerInstance.push({ name: "login" });
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    logout,
  };
}

export default useAuth;
