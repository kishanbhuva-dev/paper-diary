const routes = [
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
        component: () => import("../../js/pages/Dashboard.vue"),
        meta: { pageTitle: "Owner Dashboard" },
      },
    ],
  },
];

export default routes;
