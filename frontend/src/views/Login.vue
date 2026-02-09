<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/apiAdmin";

const router = useRouter();

const email = ref("");
const password = ref("");
const loading = ref(false);
const errorMessage = ref("");

const handleLogin = async () => {
  errorMessage.value = "";
  loading.value = true;

  try {
    const { data } = await api.post("/admin/login", {
      email: email.value,
      password: password.value,
    });

    if (!data.token) {
      throw new Error("Token tidak ditemukan");
    }

    localStorage.setItem("admin_token", data.token);
    localStorage.setItem("admin_user", JSON.stringify(data.user));

    router.push("/dashboard");
  } catch (err) {
    if (err.response) {
      errorMessage.value = err.response.data.message || "Login gagal";
    } else {
      errorMessage.value = "Server tidak dapat dihubungi";
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-[var(--color-gray-50)] px-4"
  >
    <!-- CARD -->
    <form
      @submit.prevent="handleLogin"
      class="w-full max-w-md bg-white rounded-2xl shadow-theme-md border border-slate-200 p-8"
    >
      <!-- BRAND -->
      <div class="mb-8 text-center">
        <div class="text-2xl font-semibold text-slate-900">☕ QR Café</div>
        <p class="text-sm text-slate-500 mt-1">Admin Dashboard Login</p>
      </div>

      <!-- ERROR -->
      <div
        v-if="errorMessage"
        class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700"
      >
        {{ errorMessage }}
      </div>

      <!-- EMAIL -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-700 mb-1">
          Email
        </label>
        <input
          v-model="email"
          type="email"
          required
          placeholder="admin@qrcafe.app"
          class="w-full h-11 px-4 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
        />
      </div>

      <!-- PASSWORD -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-slate-700 mb-1">
          Password
        </label>
        <input
          v-model="password"
          type="password"
          required
          class="w-full h-11 px-4 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
        />
      </div>

      <!-- BUTTON -->
      <button
        type="submit"
        :disabled="loading"
        class="w-full h-11 rounded-lg bg-brand-500 hover:bg-brand-600 text-white font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed"
      >
        {{ loading ? "Memproses..." : "Login" }}
      </button>

      <!-- FOOTER -->
      <p class="mt-6 text-center text-xs text-slate-500">
        © 2026 QR Café System
      </p>
    </form>
  </div>
</template>
