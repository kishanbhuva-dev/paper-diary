import { createRouter, createWebHistory } from 'vue-router';
import routes from './routes';
import { useAuth } from '../composables/useAuth';

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0, behavior: 'smooth' };
  },
});

// Track if auth has been initialized
let authInitialized = false;

router.beforeEach((to, from, next) => {
  const { isAuthenticated, isAdmin, isOwner, user, checkAuth } = useAuth();

  // Initialize auth on first route (check localStorage)
  if (!authInitialized) {
    checkAuth();
    authInitialized = true;
  }

  // Public routes: accessible to everyone (including authenticated users)
  const publicRoutes = [
    'home',
    'login',
    'register',
    'forgot-password',
    'reset-password',
    'details',
    'subscription',
  ];
  if (publicRoutes.includes(to.name)) {
    return next();
  }

  // Routes that must only be accessed by normal users (not admin/owner)
  const userOnlyRoutes = ['my-bookings', 'booking-summary'];
  if (userOnlyRoutes.includes(to.name)) {
    if (!isAuthenticated.value) {
      return next({ name: 'login', query: { redirect: to.fullPath } });
    }
    // If admin or owner, redirect them away
    if (isAdmin.value) {
      return next({ name: 'admin-dashboard' });
    }
    if (isOwner.value) {
      return next({ name: 'owner-dashboard' });
    }
    return next();
  }

  // Admin routes
  if (to.path.startsWith('/admin')) {
    if (!isAuthenticated.value) {
      return next({ name: 'login', query: { redirect: to.fullPath } });
    }
    if (!isAdmin.value) {
      if (isOwner.value) {
        return next({ name: 'owner-dashboard' });
      }
      return next({ name: 'my-bookings' });
    }
    return next();
  }

  // Owner routes
  if (to.path.startsWith('/owner')) {
    if (!isAuthenticated.value) {
      return next({ name: 'login', query: { redirect: to.fullPath } });
    }
    if (!isOwner.value) {
      if (isAdmin.value) {
        return next({ name: 'admin-dashboard' });
      }
      return next({ name: 'my-bookings' });
    }
    // Owner subscription check: only redirect if subscription is NOT active and trying to access non-subscription routes
    if (user.value?.subscription?.is_active === false && to.name !== 'owner-subscription-view') {
      return next({ name: 'owner-subscription-view' });
    }
    return next();
  }

  // User/booking grouped paths that require authentication
  if (to.path.startsWith('/user') || to.path.startsWith('/booking')) {
    if (!isAuthenticated.value) {
      return next({ name: 'login', query: { redirect: to.fullPath } });
    }
    if (isAdmin.value) {
      return next({ name: 'admin-dashboard' });
    }
    if (isOwner.value) {
      return next({ name: 'owner-dashboard' });
    }
    return next();
  }

  // Fallback: respect route meta.requiresAuth
  const requiresAuth = to.matched.some((r) => r.meta && r.meta.requiresAuth);
  if (requiresAuth && !isAuthenticated.value) {
    return next({ name: 'login', query: { redirect: to.fullPath } });
  }

  return next();
});
export default router;
