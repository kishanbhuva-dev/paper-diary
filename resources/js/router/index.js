// index.js (Vue Router Setup)
import { createRouter, createWebHistory } from "vue-router";
import routes from "./routes";
import { useAuthStore } from "../stores/authStore";

// Create router instance
const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard for authentication and roles
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // --- LOCAL STORAGE CHECK (The Direct Solution) ---
  // Get token and user data directly from persistent storage
  const localToken = localStorage.getItem("authToken");
  const localUser = localStorage.getItem("user")
    ? JSON.parse(localStorage.getItem("user"))
    : null;
  const isAuthLocally = !!localToken;
  const localRole = localUser?.role?.toLowerCase();
  // --------------------------------------------------
  // console.log("authtoken is --  ", localToken);

  // 0. Explicitly Handle Root Path Redirect for Authenticated Users
  if (to.path === "/" && isAuthLocally) {
    console.log("[Guard] Authenticated user on root path, redirecting...");
    if (localRole === "owner") {
      next({ name: "owner-dashboard" });
    } else if (localRole === "admin") {
      next({ name: "admin-dashboard" });
    } else {
      next("/");
    }
    return;
  }

  // 1. Check for Token (General Authentication)
  // Use the local check for maximum reliability at this stage
  if (to.meta.requiresAuth && !isAuthLocally) {
    console.log(`[Guard] Blocked unauthenticated access to: ${to.path}`);
    next({ name: "login" });
    return;
  }

  // 2. Prevent authenticated users from seeing the login/register pages
  // Use the local check here to force the redirect immediately if a token exists
  if (isAuthLocally && to.meta.authRoutes) {
    console.log(
      `[Guard] Authenticated user redirected from auth page: ${to.path}`
    );

    // Use the role from Local Storage for the initial, immediate redirect
    if (localRole === "owner") {
      next({ name: "owner-dashboard" });
    } else if (localRole === "admin") {
      next({ name: "admin-dashboard" }); // Ensure this route exists
    } else {
      // Standard user fallback (assuming a default home route "/")
      next("/");
    }
    return;
  }

  // 3. Role-Based Authorization Checks (Owner Routes)
  // For role checks on secure pages, it's safer to use the Pinia state
  // since it ideally validates the token with the server (your authStore does this!)
  if (to.path.startsWith("/owner")) {
    if (!authStore.isOwner) {
      console.log(`[Guard] Non-owner access blocked to: ${to.path}`);
      if (authStore.isAdmin) {
        next({ name: "admin-dashboard" });
      } else {
        // Redirect non-owner/non-admin to the login page
        next({ name: "login" });
      }
      return;
    }
  }

  // ... (Keep your Admin Routes check, Step 4, as is) ...

  // 5. Allow Access
  next();
});

export default router;
