<template>
  <div style="padding: 40px">
    <h2>Dashboard Kasir</h2>
    <button @click="logout">Logout</button>

    <p v-if="loading">Memuat order...</p>

    <table v-else border="1" cellpadding="8" style="margin-top: 20px">
      <tr>
        <th>Order</th>
        <th>Meja</th>
        <th>Status</th>
        <th>Total</th>
        <th>Payment</th>
        <th>Aksi</th>
      </tr>

      <tr v-for="order in orders" :key="order.id">
        <td>{{ order.order_number }}</td>
        <td>{{ order.table.table_number }}</td>
        <td>{{ order.status }}</td>
        <td>{{ order.total_price }}</td>
        <td>
          <span v-if="order.payment_status === 'paid'">
            Paid ({{ order.payment_method }})
          </span>

          <div v-else>
            <button @click="pay(order.id, 'cash')">Cash</button>
            <button @click="pay(order.id, 'transfer')">Transfer</button>
          </div>
        </td>
        <td>
          <button
            v-if="order.status === 'pending'"
            @click="updateStatus(order.id, 'confirmed')"
          >
            Confirm
          </button>

          <button
            v-if="order.status === 'confirmed'"
            @click="updateStatus(order.id, 'processing')"
          >
            Process
          </button>

          <button
            v-if="order.status === 'processing'"
            @click="updateStatus(order.id, 'completed')"
          >
            Complete
          </button>
        </td>
      </tr>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import api from "../services/api";
import { useRouter } from "vue-router";

const orders = ref([]);
const loading = ref(true);
const router = useRouter();

let intervalId = null;

const loadOrders = async () => {
  try {
    const res = await api.get("/admin/orders");
    orders.value = res.data;
    loading.value = false;
  } catch (e) {
    logout();
  }
};

const pay = async (id, method) => {
  try {
    await api.patch(`/admin/orders/${id}/payment`, {
      payment_method: method,
    });
    loadOrders();
  } catch (e) {
    alert(e.response?.data?.message || "Gagal memproses pembayaran");
  }
};

const updateStatus = async (id, status) => {
  await api.patch(`/admin/orders/${id}/status`, { status });
  loadOrders();
};

const logout = () => {
  localStorage.removeItem("admin_token");
  router.push("/login");
};

onMounted(() => {
  loadOrders();

  // 🔁 polling tiap 5 detik
  intervalId = setInterval(loadOrders, 5000);
});

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>
