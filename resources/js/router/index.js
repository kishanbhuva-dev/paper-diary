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

router.beforeEach((to, from, next) => {
  const { isAuthenticated, isAdmin, isOwner } = useAuth();

  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return next({ name: 'login', query: { redirect: to.fullPath } });
  }

  const { path } = to;

  if (path.startsWith('/admin')) {
    if (!isAuthenticated.value) {
      return next({ name: 'login' });
    }

    if (!isAdmin.value) {
      return next(isOwner.value ? { name: 'owner-dashboard' } : { name: 'my-bookings' });
    }
  }

  if (path.startsWith('/owner')) {
    if (!isAuthenticated.value) {
      return next({ name: 'login' });
    }

    if (!isOwner.value) {
      return next(isAdmin.value ? { name: 'admin-dashboard' } : { name: 'my-bookings' });
    }
  }

  // Redirect authenticated admin/owner users from guest pages to their dashboards
  if (isAuthenticated.value && path === '/') {
    if (isAdmin.value) {
      return next({ name: 'admin-dashboard' });
    }
    if (isOwner.value) {
      return next({ name: 'owner-dashboard' });
    }
    if (!isAdmin.value && !isOwner.value) {
      return next({ name: 'my-bookings' });
    }
  }

  next();
});

export default router;
