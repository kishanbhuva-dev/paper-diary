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
  const localToken = localStorage.getItem("authToken");
  const localUser = localStorage.getItem("user")
    ? JSON.parse(localStorage.getItem("user"))
    : null;
  const isAuthLocally = !!localToken;

  const userRole = localUser?.role.toLowerCase();
  const dashboardRoutes = {
    owner: "owner-dashboard",
    admin: "admin-dashboard",
  };

  if (to.path === "/" && isAuthLocally) {
    return next({ name: dashboardRoutes[userRole] || "/" });
  }

  if (to.meta.requiresAuth && !isAuthLocally) {
    return next({ name: "login" });
  }

  if (isAuthLocally && to.meta.authRoutes) {
    return next({ name: dashboardRoutes[userRole] || "/" });
  }

  if (to.path.startsWith("/owner") && userRole !== "owner") {
    return next({ name: dashboardRoutes[userRole] || "login" });
  }

  if (to.path.startsWith("/admin") && userRole !== "admin") {
    return next({ name: dashboardRoutes[userRole] || "login" });
  }

  next();
});

export default router;
