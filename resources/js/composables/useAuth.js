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

const isAdmin = computed(() => user.value?.role === "admin");
const isOwner = computed(() => user.value?.role === "owner");
const isGuest = computed(() => !isAuthenticated.value);

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
      const { stripePublicKey, stripeSecretKey, ...safeUser } = newUser;
      localStorage.setItem(USER_KEY, JSON.stringify(safeUser));
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
    let routeName;
    
    if (role === "owner") {
      routeName = res.data.subscription.is_active ? "owner-dashboard" : "subscription";
    } else if (role === "admin") {
      routeName = "admin-dashboard";
    } else {
      routeName = "home";
    }
    const urlParams= new URLSearchParams(window.location.search);
    const redirectPath = urlParams.get('redirect');

    if (redirectPath) {
      routerInstance.push(redirectPath);
      
    } else{

      const role = res.data.user.role?.toLowerCase();
      let routeName;
      
      if (role === "owner") {
        routeName = res.data.subscription ? "owner-dashboard" : "subscription";
      } else if (role === "admin") {
        routeName = "admin-dashboard";
      } else {
        routeName = "home";
      }
      
      routerInstance.push({ name: routeName });
    }

    return { success: true, data: res.data };
  }

  async function logout() {
    try {
      await authService.logout();
    } catch (e) {
      console.warn("[logout] API failed:", e);
    } finally {
      localStorage.clear();
    }

    persistAuth(null, null);
    routerInstance.push({ name: "login" });
  }

  return {
    token,
    user,
    isAuthenticated,
    isAdmin,
    isOwner,
    isGuest,
    login,
    logout,
    checkAuth,
  };
}

export default useAuth;