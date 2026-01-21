const routes = [
  {
    path: '/',
    component: () => import('../../js/layouts/AuthLayout.vue'),
    children: [
      {
        path: '/',
        name: 'home',
        component: () => import('../../js/pages/guest/HomePage.vue'),
      },
      {
        path: '/property/:slug',
        name: 'details',
        component: () => import('../../js/pages/guest/DetailsPage.vue'),
      },

      {
        path: '/login',
        name: 'login',
        component: () => import('../../js/pages/auth/LoginPage.vue'),
      },
      {
        path: '/register',
        name: 'register',
        component: () => import('../../js/pages/auth/RegisterPage.vue'),
      },
      {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('../../js/pages/auth/ForgotPassword.vue'),
      },
      {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('../../js/pages/auth/ResetPassword.vue'),
      },
      {
        path: '/subscription',
        name: 'subscription',
        component: () => import('../../js/pages/owner/SubscriptionPage.vue'),
      },
    ],
  },

  {
    path: '/owner',
    component: () => import('../../js/layouts/OwnerLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '/owner',
        name: 'owner-dashboard',
        component: () => import('../pages/owner/OwnerDashboard.vue'),
        meta: { pageTitle: 'Owner Dashboard' },
      },
      {
        name: 'properties',
        path: '/owner/properties',
        component: () => import('../../js/pages/owner/Properties.vue'),
        meta: { pageTitle: 'Property List' },
      },
      {
        name: 'property-wizard',
        path: '/owner/property-form/:id?',
        component: () => import('../../js/pages/owner/PropertyWizard.vue'),
        meta: { pageTitle: 'Property Form' },
      },
      {
        name: 'bookings',
        path: '/owner/bookings',
        component: () => import('../../js/pages/owner/Bookings.vue'),
        meta: { pageTitle: 'Booking List' },
      },
      {
        path: 'profile',
        name: 'owner-profile',
        component: () => import('../../js/pages/auth/Profile.vue'),
        meta: { pageTitle: 'My Profile' },
      },
      {
        path: 'change-password',
        name: 'owner-change-password',
        component: () => import('../../js/pages/auth/ChangePassword.vue'),
        meta: { pageTitle: 'Change Password' },
      },
      {
        path: 'subcription-view',
        name: 'owner-subcription-view',
        component: () => import('../../js/pages/owner/SubscriptionView.vue'),
        meta: { pageTitle: 'Manage Subcription' },
      },
    ],
  },

  {
    path: '/admin',
    component: () => import('../../js/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: () => import('../../js/pages/admin/Dashboard.vue'),
        meta: { pageTitle: 'Admin Dashboard' },
      },
      {
        path: '/admin/owners',
        name: 'admin-owners',
        component: () => import('../../js/pages/admin/Owners.vue'),
        meta: { pageTitle: 'Owner List' },
      },
      {
        path: '/admin/users',
        name: 'admin-users',
        component: () => import('../../js/pages/admin/Users.vue'),
        meta: { pageTitle: 'User List' },
      },
      {
        path: 'profile',
        name: 'admin-profile',
        component: () => import('../../js/pages/auth/Profile.vue'),
        meta: { pageTitle: 'Admin Profile' },
      },
      {
        path: 'change-password',
        name: 'admin-change-password',
        component: () => import('../../js/pages/auth/ChangePassword.vue'),
        meta: { pageTitle: 'Change Password' },
      },
      {
        path: '/admin/listings',
        name: 'listings',
        component: () => import('../pages/admin/Property/Listing.vue'),
        meta: { pageTitle: 'Poperty Listings' },
      },
      {
        path: '/admin/facilities',
        name: 'facilities',
        component: () => import('../pages/admin/Property/Facilities.vue'),
        meta: { pageTitle: 'Poperty Facilities' },
      },
      {
        path: '/admin/subscriptions',
        name: 'subscriptions',
        component: () => import('../pages/admin/Subscriptions.vue'),
        meta: { pageTitle: 'Subscriptions' },
      },
      {
        path: '/admin/bookings',
        name: 'admin-bookings',
        component: () => import('../pages/admin/Bookings.vue'),
        meta: { pageTitle: 'Admin Bookings' },
      },
      {
        path: '/admin/migrations',
        name: 'admin-migrations',
        component: () => import('../pages/admin/MigrationManager.vue'),
        meta: { pageTitle: 'Migration Manager' },
      },
    ],
  },
  {
    path: '/user',
    component: () => import('../../js/layouts/AuthLayout.vue'),
    meta: { requiresAuth: true, role: 'user' },
    children: [
      // {
      //   path: "dashboard",
      //   name: "user-dashboard",
      //   component: () => import("../../js/pages/user/UserDashboard.vue"),
      //   meta: { pageTitle: "My Dashboard" },
      // },
      {
        path: '/booking-summary',
        name: 'booking-summary',
        component: () => import('../../js/pages/guest/BookingPage.vue'),
      },
      {
        path: '/my-bookings',
        name: 'my-bookings',
        component: () => import('../../js/pages/guest/MyBookings.vue'),
      },
      {
        path: 'profile',
        name: 'user-profile',
        component: () => import('../../js/pages/auth/Profile.vue'),
        meta: { pageTitle: 'Admin Profile' },
      },
      {
        path: 'change-password',
        name: 'user-change-password',
        component: () => import('../../js/pages/auth/ChangePassword.vue'),
        meta: { pageTitle: 'Change Password' },
      },
    ],
  },

  {
    path: '/:pathMatch(.*)*',
    name: 'catch-all',
    component: () => import('../../js/pages/NotFound.vue'),
    meta: { pageTitle: '404 Not Found' },
  },
];

export default routes;
