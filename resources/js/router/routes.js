const routes = [
  {
    path: "/",
    component: () => import("../../js/layouts/AuthLayout.vue"),
    children: [
      {
        path: "/",
        name: "home",
        component: () => import("../../js/pages/guest/HomePage.vue"),
      },
      {
        path: "/login",
        name: "login",
        component: () => import("../../js/pages/auth/LoginPage.vue"),
      },
      {
        path: "/register",
        name: "register",
        component: () => import("../../js/pages/auth/RegisterPage.vue"),
      },
    ],
  },

  {
    path: "/owner",
    component: () => import("../../js/layouts/OwnerLayout.vue"),
    meta: { requiresAuth: true },
    children: [
      {
        path: "/owner/dashboard",
        name: "owner-dashboard",
        component: () => import("../../js/pages/owner/Dashboard.vue"),
        meta: { pageTitle: "Owner Dashboard" },
      },
      {
        name: "properties",
        path: "/owner/properties",
        component: () => import("../../js/pages/owner/Properties.vue"),
        meta: { pageTitle: "Property List" },
      },
      {
        name: "property-form",
        path: "/owner/property-form/:id?",
        component: () => import("../../js/pages/owner/PropertiesForm.vue"),
        meta: { pageTitle: "Property Form" },
      },
    ],
  },

  {
    path: "/admin",
    component: () => import("../../js/layouts/AdminLayout.vue"),
    meta: { requiresAuth: true },
    children: [],
  },
];

export default routes;
