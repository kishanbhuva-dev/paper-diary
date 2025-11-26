const routes = [
  // 1. Default Home Route (Required for standard users)
  {
    path: "/",
    name: "root",
    redirect: { name: "login" },
    component: () => import("../../js/layouts/AuthLayout.vue"),
    meta: { requiresAuth: false, pageTitle: "Login" },
  },

  // 2. Auth Routes
  {
    name: "auth",
    path: "/login",
    redirect: { name: "login" },
    component: () => import("../../js/layouts/AuthLayout.vue"),
    meta: { authRoutes: true },
    children: [
      {
        name: "login",
        path: "/login",
        component: () => import("../../js/pages/auth/LoginPage.vue"),
        meta: { pageTitle: "Student Login", permission: "allowed" },
      },
      {
        name: "register",
        path: "/register",
        component: () => import("../../js/pages/auth/RegisterPage.vue"),
        meta: { pageTitle: "Student Register", permission: "allowed" },
      },
    ],
  },

  // 3. Owner Routes
  {
    name: "owner",
    path: "/owner",
    redirect: { name: "owner-dashboard" },
    component: () => import("../../js/layouts/OwnerLayout.vue"),
    meta: { requiresAuth: true },
    children: [
      {
        name: "owner-dashboard",
        path: "",
        component: () => import("../../js/pages/owner/Dashboard.vue"),
        meta: { pageTitle: "Owner Dashboard" },
      },
      {
        name: "properties",
        path: "properties",
        component: () => import("../../js/pages/owner/Properties.vue"),
        meta: { pageTitle: "Property List" },
      },
      // === NEW ROUTE FOR ADD/EDIT PROPERTY FORM ===
      {
        name: "property-form",
        // The ':id?' makes the ID parameter optional for 'Add New' functionality
        path: "properties/form/:id?",
        component: () => import("../../js/pages/owner/PropertiesForm.vue"),
        meta: { pageTitle: "Property Form" },
      },
      // ===========================================
    ],
  },
];

export default routes;
