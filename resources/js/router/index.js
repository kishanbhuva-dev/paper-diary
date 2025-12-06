import { createRouter, createWebHistory } from "vue-router";
import routes from "./routes";
import { useAuth } from "../composables/useAuth";

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const { isAuthenticated, isAdmin, isOwner } = useAuth();

  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return next({ name: "login" });
  }

  const path = to.path;

  if (path.startsWith("/admin")) {
    if (!isAuthenticated.value) return next({ name: "login" });

    if (!isAdmin.value) {
      return next(
        isOwner.value ? { name: "owner-dashboard" } : { name: "home" }
      );
    }
  }

  if (path.startsWith("/owner")) {
    if (!isAuthenticated.value) return next({ name: "login" });

    if (!isOwner.value) {
      return next(
        isAdmin.value ? { name: "admin-dashboard" } : { name: "home" }
      );
    }
  }

  next();
});

export default router;
