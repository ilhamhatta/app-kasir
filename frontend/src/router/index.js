import { createRouter, createWebHistory } from "vue-router";
import Login from "@/views/Login.vue";
import Dashboard from "@/views/Dashboard.vue";
import Menu from "@/views/Menu.vue";
import AdminLayout from "@/layouts/AdminLayout.vue";
import OrderStatus from "@/views/OrderStatus.vue";

const routes = [
  // ===== AUTH =====
  {
    path: "/login",
    component: Login,
  },

  // ===== CUSTOMER (QR CODE) =====
  {
    path: "/menu/:tableNumber",
    name: "menu",
    component: Menu,
  },
  {
    path: "/status/:tableNumber",
    name: "order-status",
    component: OrderStatus,
  },

  // ===== ADMIN =====
  {
    path: "/",
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: "",
        name: "dashboard",
        component: Dashboard,
      },
    ],
  },

  // ===== LEGACY =====
  {
    path: "/dashboard",
    redirect: "/",
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// ===== AUTH GUARD =====
router.beforeEach((to, _, next) => {
  const token = localStorage.getItem("admin_token");

  if (to.meta.requiresAuth && !token) {
    next("/login");
  } else {
    next();
  }
});

export default router;
