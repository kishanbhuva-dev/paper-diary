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
];

export default routes;
