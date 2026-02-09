<script setup>
import { ref } from "vue";

defineEmits(["toggle-sidebar"]);

const showUserMenu = ref(false);

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
};

const logout = () => {
  localStorage.removeItem("admin_token");
  window.location.href = "/login";
};
</script>

<template>
  <header
    class="h-16 flex items-center justify-between px-6 bg-white border-b border-slate-200"
  >
    <!-- LEFT -->
    <div class="flex items-center gap-4">
      <!-- Sidebar Toggle -->
      <button
        @click="$emit('toggle-sidebar')"
        class="p-2 rounded-lg border border-slate-200 hover:bg-slate-100 transition"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5 text-slate-700"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16"
          />
        </svg>
      </button>
    </div>

    <!-- RIGHT -->
    <div class="flex items-center gap-4">
      <!-- Notification -->
      <button
        class="relative p-2 rounded-lg border border-slate-200 hover:bg-slate-100 transition"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5 text-slate-700"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 17h5l-1.4-1.4A2 2 0 0118 14V11a6 6 0 00-12 0v3a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0"
          />
        </svg>

        <!-- badge -->
        <span
          class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] px-1.5 py-0.5 rounded-full"
        >
          3
        </span>
      </button>

      <!-- User Dropdown -->
      <div class="relative">
        <button
          @click="toggleUserMenu"
          class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-100 transition"
        >
          <div
            class="w-7 h-7 rounded-full bg-brand-500 text-white flex items-center justify-center text-xs font-semibold"
          >
            A
          </div>
          <span class="text-sm text-slate-700">Admin</span>
        </button>

        <!-- Dropdown -->
        <div
          v-if="showUserMenu"
          class="absolute right-0 mt-2 w-40 bg-white border border-slate-200 rounded-lg shadow-md overflow-hidden z-50"
        >
          <button
            @click="logout"
            class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
          >
            Logout
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
