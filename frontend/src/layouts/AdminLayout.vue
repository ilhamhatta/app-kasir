<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import AdminSidebar from "@/components/AdminSidebar.vue";
import AdminNavbar from "@/components/AdminNavbar.vue";

const isMobile = ref(false);
const sidebarOpen = ref(false); // mobile
const sidebarCollapsed = ref(false); // desktop

const checkScreen = () => {
  isMobile.value = window.innerWidth < 1024;

  if (!isMobile.value) {
    sidebarOpen.value = false; // mobile sidebar off
  }
};

const toggleSidebar = () => {
  if (isMobile.value) {
    sidebarOpen.value = !sidebarOpen.value;
  } else {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  }
};

onMounted(() => {
  checkScreen();
  window.addEventListener("resize", checkScreen);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkScreen);
});
</script>

<template>
  <div class="min-h-screen flex bg-[var(--color-gray-50)] text-gray-900">
    <!-- OVERLAY (mobile) -->
    <div
      v-if="isMobile && sidebarOpen"
      class="fixed inset-0 bg-black/40 z-30"
      @click="sidebarOpen = false"
    />

    <!-- SIDEBAR -->
    <AdminSidebar
      :collapsed="!isMobile && sidebarCollapsed"
      :mobile="isMobile"
      :open="sidebarOpen"
      @close="sidebarOpen = false"
      class="z-40"
    />

    <!-- MAIN -->
    <div class="flex-1 flex flex-col min-h-screen">
      <AdminNavbar @toggle-sidebar="toggleSidebar" />

      <main class="flex-1 p-4 sm:p-6 overflow-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>
