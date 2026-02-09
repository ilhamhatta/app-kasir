<script setup>
import { isDark, toggleDark } from "@/composables/useDarkMode";
</script>

<template>
  <button
    @click="toggleDark"
    class="flex items-center gap-2 px-4 py-2 rounded-lg border bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
  >
    <span v-if="isDark">☀️ Light</span>
    <span v-else>🌙 Dark</span>
  </button>
</template>
