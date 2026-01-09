import { createRouter, createWebHistory } from "vue-router";
import routes from "./routes";
import { useAuth } from "../composables/useAuth";

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // if (savedPosition) {
    //   return savedPosition;
    // } else {
    return { top: 0, behavior: "smooth" };
    // }
  },
});

router.beforeEach((to, from, next) => {
  const { isAuthenticated, isAdmin, isOwner } = useAuth();

  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return next({ name: "login",
      query:{redirect:to.fullPath}
     });
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
