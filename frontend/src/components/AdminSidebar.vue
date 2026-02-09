<script setup>
import { useRoute } from "vue-router";

defineProps({
  collapsed: Boolean,
  mobile: Boolean,
  open: Boolean,
});

defineEmits(["close"]);

const route = useRoute();
const isActive = (path) => route.path === path;
</script>

<template>
  <aside
    class="h-screen bg-white border-r border-slate-200 flex flex-col transition-all duration-300 ease-in-out"
    :class="[
      mobile
        ? open
          ? 'fixed inset-y-0 left-0 w-64'
          : 'hidden'
        : collapsed
          ? 'w-18'
          : 'w-64',
    ]"
  >
    <!-- BRAND -->
    <div class="h-16 flex items-center px-4 border-b border-slate-200">
      <span
        class="text-lg font-semibold transition-all duration-200"
        :class="collapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'"
      >
        ☕ QR Café
      </span>
    </div>

    <!-- MENU -->
    <nav class="flex-1 px-2 py-4 space-y-1">
      <!-- Dashboard -->
      <router-link
        to="/"
        @click="mobile && $emit('close')"
        class="menu-item"
        :class="isActive('/') && 'menu-item-active'"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
          <path
            d="M3 3h7v7H3V3zm11 0h7v7h-7V3zM3 14h7v7H3v-7zm11 0h7v7h-7v-7z"
            stroke="currentColor"
            stroke-width="2"
          />
        </svg>

        <span
          class="transition-opacity"
          :class="collapsed ? 'hidden' : 'block'"
        >
          Dashboard
        </span>
      </router-link>

      <!-- Menu -->
      <router-link
        to="/menu"
        @click="mobile && $emit('close')"
        class="menu-item"
        :class="isActive('/menu') && 'menu-item-active'"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
          <path
            d="M4 6h16M4 12h16M4 18h16"
            stroke="currentColor"
            stroke-width="2"
          />
        </svg>

        <span :class="collapsed ? 'hidden' : 'block'">Menu</span>
      </router-link>
    </nav>
  </aside>
</template>
