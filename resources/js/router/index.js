// index.js (Vue Router Setup)
import { createRouter, createWebHistory } from "vue-router";
import routes from "./routes";

// Create router instance
const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard for authentication and roles
router.beforeEach(async (to, from, next) => {
  // --- LOCAL STORAGE CHECK ---
  // Get token and user data directly from persistent storage
  const localToken = localStorage.getItem("authToken");
  const localUser = localStorage.getItem("user")
    ? JSON.parse(localStorage.getItem("user"))
    : null;
  const isAuthLocally = !!localToken;
  const localRole = localUser?.role?.toLowerCase();
  // --------------------------------------------------

  // console.log(
  //   `[Router Guard] Route: ${to.path}, Authenticated: ${isAuthLocally}, Role: ${localRole}`
  // );

  // 0. Explicitly Handle Root Path Redirect for Authenticated Users
  if (to.path === "/" && isAuthLocally) {
    // console.log(
    //   "[Guard] Authenticated user on root path, redirecting to dashboard..."
    // );
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
  if (to.meta.requiresAuth && !isAuthLocally) {
    // console.log(
    //   `[Guard] Blocked unauthenticated access to: ${to.path}, redirecting to login`
    // );
    next({ name: "login" });
    return;
  }

  // 2. Prevent authenticated users from seeing the login/register pages
  if (isAuthLocally && to.meta.authRoutes) {
    // console.log(
    //   `[Guard] Authenticated user blocked from auth page: ${to.path}, redirecting to dashboard`
    // );

    if (localRole === "owner") {
      next({ name: "owner-dashboard" });
    } else if (localRole === "admin") {
      next({ name: "admin-dashboard" });
    } else {
      next("/");
    }
    return;
  }

  // 3. Role-Based Authorization Checks (Owner Routes)
  if (to.path.startsWith("/owner")) {
    if (localRole !== "owner") {
      // console.log(`[Guard] Non-owner access blocked to: ${to.path}`);
      if (localRole === "admin") {
        next({ name: "admin-dashboard" });
      } else {
        next({ name: "login" });
      }
      return;
    }
  }

  // 4. Role-Based Authorization Checks (Admin Routes)
  if (to.path.startsWith("/admin")) {
    if (localRole !== "admin") {
      // console.log(`[Guard] Non-admin access blocked to: ${to.path}`);
      if (localRole === "owner") {
        next({ name: "owner-dashboard" });
      } else {
        next({ name: "login" });
      }
      return;
    }
  }

  // 5. Allow Access
  // console.log(`[Guard] Access granted to: ${to.path}`);
  next();
});

export default router;
