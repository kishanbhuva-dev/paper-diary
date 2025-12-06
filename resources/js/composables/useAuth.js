import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import authService from "../services/authService";

const TOKEN_KEY = "authToken";
const USER_KEY = "user";

function loadStoredUser() {
  try {
    const stored = localStorage.getItem(USER_KEY);
    return stored ? JSON.parse(stored) : null;
  } catch (e) {
    console.error("[useAuth] Invalid user JSON:", e);
    localStorage.removeItem(USER_KEY);
    return null;
  }
}

const token = ref(localStorage.getItem(TOKEN_KEY) || null);
const user = ref(loadStoredUser());
const isAuthenticated = computed(() => !!token.value);

let routerInstance = null;

function persistAuth(newToken, newUser) {
  if (newToken) {
    localStorage.setItem(TOKEN_KEY, newToken);
    token.value = newToken;
  } else {
    localStorage.removeItem(TOKEN_KEY);
    token.value = null;
  }

  if (newUser) {
    try {
      localStorage.setItem(USER_KEY, JSON.stringify(newUser));
      user.value = newUser;
    } catch (e) {
      console.error("[useAuth] Failed to save user:", e);
      user.value = null;
    }
  } else {
    localStorage.removeItem(USER_KEY);
    user.value = null;
  }
}

export function useAuth() {
  if (!routerInstance) routerInstance = useRouter();

  const checkAuth = () => {
    const storedToken = localStorage.getItem(TOKEN_KEY);
    const storedUser = loadStoredUser();

    if (storedToken && storedUser) {
      token.value = storedToken;
      user.value = storedUser;
    } else {
      persistAuth(null, null);
    }
  };

  async function login(credentials) {
    const res = await authService.login(credentials);

    if (!res?.data?.token || !res?.data?.user) {
      throw new Error("Login failed: Invalid response from server.");
    }

    persistAuth(res.data.token, res.data.user);

    const role = res.data.user.role?.toLowerCase();
    const roleRoutes = {
      owner: "owner-dashboard",
      admin: "admin-dashboard",
    };

    routerInstance.push({ name: roleRoutes[role] || "home" });

    return { success: true, data: res.data };
  }

  async function logout() {
    try {
      await authService.logout();
    } catch (e) {
      console.warn("[logout] API failed:", e);
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
    checkAuth,
  };
}

export default useAuth;
