<template>
  <div style="padding: 40px; max-width: 400px">
    <h2>Login Admin</h2>

    <form @submit.prevent="login">
      <input v-model="email" placeholder="Email" /><br /><br />
      <input
        type="password"
        v-model="password"
        placeholder="Password"
      /><br /><br />

      <button type="submit">Login</button>
    </form>

    <p v-if="error" style="color: red">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from "vue";
import api from "../services/api";
import { useRouter } from "vue-router";

const email = ref("");
const password = ref("");
const error = ref("");
const router = useRouter();

const login = async () => {
  error.value = "";

  try {
    const res = await api.post("/admin/login", {
      email: email.value,
      password: password.value,
    });

    localStorage.setItem("admin_token", res.data.token);
    router.push("/");
  } catch (e) {
    error.value = e.response?.data?.message || "Login gagal";
  }
};
</script>
