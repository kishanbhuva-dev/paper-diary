import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import authService from '../services/authService';

const TOKEN_KEY = 'authToken';
const USER_KEY = 'user';
const ADMIN_TOKEN_KEY = 'adminToken';
const ADMIN_USER_KEY = 'adminUser';

function loadStoredUser() {
  try {
    const stored = localStorage.getItem(USER_KEY);
    return stored ? JSON.parse(stored) : null;
  } catch {
    localStorage.removeItem(USER_KEY);
    return null;
  }
}

const token = ref(localStorage.getItem(TOKEN_KEY) || null);
const user = ref(loadStoredUser());
const isAuthenticated = computed(() => !!token.value && !!user.value);

const isAdmin = computed(() => user.value?.role === 'admin');
const isOwner = computed(() => user.value?.role === 'owner');
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
      localStorage.setItem(USER_KEY, JSON.stringify(newUser));
      user.value = newUser;
    } catch (e) {
      console.error('Failed to persist user:', e);
      user.value = null;
      token.value = null;
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
      throw new Error('Failed to save user session');
    }
  } else {
    localStorage.removeItem(USER_KEY);
    user.value = null;
  }
}

export function useAuth() {
  if (!routerInstance) {
    routerInstance = useRouter();
  }

  /**
   * Check authentication from localStorage on app load
   * This is called once when router initializes
   */
  const checkAuth = () => {
    const storedToken = localStorage.getItem(TOKEN_KEY);
    const storedUser = loadStoredUser();

    if (storedToken && storedUser) {
      token.value = storedToken;
      user.value = storedUser;
      return true;
    }
    persistAuth(null, null);
    return false;
  };

  /**
   * Login user and redirect to appropriate dashboard
   */
  async function login(credentials) {
    try {
      const { data } = await authService.login(credentials);

      if (!data?.token || !data?.user) {
        throw new Error('Login failed: Invalid response from server.');
      }

      // Attach subscription data to user object if available
      if (data.subscription) {
        data.user.subscription = data.subscription;
      }

      // Persist the new session
      persistAuth(data.token, data.user);

      // Check for redirect query parameter
      const redirectPath = new URLSearchParams(window.location.search).get('redirect');
      if (redirectPath) {
        routerInstance.push(redirectPath);
        return { success: true, data };
      }

      // Redirect based on role - let router guard handle subscription checks
      const role = data.user.role?.toLowerCase();
      let routeName = 'my-bookings';

      if (role === 'owner') {
        // Always redirect to owner dashboard; router guard will handle subscription check
        routeName = 'owner-dashboard';
      } else if (role === 'admin') {
        routeName = 'admin-dashboard';
      }

      routerInstance.push({ name: routeName });

      return { success: true, data };
    } catch (error) {
      console.error('Login error:', error);
      persistAuth(null, null);
      throw error;
    }
  }

  /**
   * Switch from owner to admin (or vice versa)
   * Saves current session and restores previous admin session
   */
  function switchToAdmin() {
    try {
      const adminToken = localStorage.getItem(ADMIN_TOKEN_KEY);
      const adminUserStr = localStorage.getItem(ADMIN_USER_KEY);

      if (!adminToken || !adminUserStr) {
        throw new Error('Admin session not found. Please login as admin.');
      }

      const adminUser = JSON.parse(adminUserStr);

      // Validate it's actually an admin
      if (adminUser.role !== 'admin') {
        throw new Error('Invalid admin session.');
      }

      // Save current owner session (optional - for potential future switch back)
      // You could store this for later use

      // Restore admin session
      persistAuth(adminToken, adminUser);

      // Clean up admin backup
      localStorage.removeItem(ADMIN_TOKEN_KEY);
      localStorage.removeItem(ADMIN_USER_KEY);

      routerInstance.push({ name: 'admin-dashboard' });
      return { success: true };
    } catch (error) {
      console.error('Switch to admin error:', error);
      throw error;
    }
  }

  /**
   * Switch from admin to owner
   * Saves admin session before switching
   */
  function switchToOwner(ownerToken, ownerUser) {
    try {
      // Save current admin session as backup
      localStorage.setItem(ADMIN_TOKEN_KEY, token.value);
      localStorage.setItem(ADMIN_USER_KEY, JSON.stringify(user.value));

      // Switch to owner
      persistAuth(ownerToken, ownerUser);

      routerInstance.push({ name: 'owner-dashboard' });
      return { success: true };
    } catch (error) {
      console.error('Switch to owner error:', error);
      throw error;
    }
  }

  /**
   * Logout user
   * Clears current session but preserves admin session if exists
   */
  async function logout() {
    try {
      await authService.logout();
    } catch (e) {
      console.error('Logout API error:', e);
      // Continue logout even if API fails
    }

    // Check if there's an admin session to restore
    const adminToken = localStorage.getItem(ADMIN_TOKEN_KEY);
    const adminUserStr = localStorage.getItem(ADMIN_USER_KEY);

    // Clear user session
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);

    token.value = null;
    user.value = null;

    // If admin session exists, restore it
    if (adminToken && adminUserStr) {
      try {
        const adminUser = JSON.parse(adminUserStr);
        localStorage.setItem(TOKEN_KEY, adminToken);
        localStorage.setItem(USER_KEY, adminUserStr);
        token.value = adminToken;
        user.value = adminUser;
        localStorage.removeItem(ADMIN_TOKEN_KEY);
        localStorage.removeItem(ADMIN_USER_KEY);

        routerInstance.push({ name: 'admin-dashboard' });
        return { success: true, isAdmin: true };
      } catch (e) {
        console.error('Failed to restore admin session:', e);
      }
    }

    routerInstance.push({ name: 'login' });
    return { success: true, isAdmin: false };
  }

  /**
   * Force logout and clear all sessions
   */
  async function logoutCompletely() {
    try {
      await authService.logout();
    } catch (e) {
      console.error('Logout API error:', e);
    }

    // Clear all sessions
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    localStorage.removeItem(ADMIN_TOKEN_KEY);
    localStorage.removeItem(ADMIN_USER_KEY);

    token.value = null;
    user.value = null;

    routerInstance.push({ name: 'login' });
    return { success: true };
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
    logoutCompletely,
    switchToAdmin,
    switchToOwner,
    checkAuth,
  };
}

export default useAuth;
