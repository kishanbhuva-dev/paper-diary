import { createRouter, createWebHistory } from "vue-router";
import routes from "./routes";

// Create router instance
const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard for authentication and roles
// router.beforeEach((to, from, next) => {
//   const { isAuthenticated, isAdmin, isOwner } = useAuth();

//   // Check if route requires authentication
//   if (to.meta.requiresAuth && !isAuthenticated.value) {
//     // Redirect to login if not authenticated
//     next({ name: "login" });
//     return;
//   }

//   // Check admin routes - only admins can access
//   if (to.path.startsWith("/admin")) {
//     if (!isAuthenticated.value) {
//       next({ name: "login" });
//       return;
//     }

//     if (!isAdmin.value) {
//       // Redirect owners and guests away from admin pages
//       if (isOwner.value) {
//         // Redirect owners to their dashboard
//         next({ name: "dashboard" });
//       } else {
//         // Redirect guests to home
//         next({ name: "home" });
//       }
//       return;
//     }
//   }

//   // Check owner routes - only owners can access (not admins)
//   if (to.path.startsWith("/owner")) {
//     if (!isAuthenticated.value) {
//       next({ name: "login" });
//       return;
//     }

//     if (!isOwner.value) {
//       // Redirect non-owners (including admins) to appropriate pages
//       if (isAdmin.value) {
//         // Redirect admins to admin dashboard
//         next({ name: "admin-dashboard" });
//       } else {
//         // Redirect guests to home
//         next({ name: "home" });
//       }
//       return;
//     }
//   }

//   // Allow access to all other routes
//   next();
// });

export default router;
