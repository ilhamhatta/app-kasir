<script setup>
import { useRoute, useRouter } from "vue-router";
import api from "@/services/api";
import { useCart } from "@/composables/useCart";

const route = useRoute();
const router = useRouter();
const tableNumber = route.params.tableNumber;

const { cart, total, updateQty, removeItem, clearCart } = useCart(tableNumber);

const submitOrder = async () => {
  if (!cart.items.length) return;

  await api.post("/orders", {
    table_number: tableNumber,
    items: cart.items.map((i) => ({
      menu_id: i.menu_id,
      qty: i.qty,
    })),
  });

  clearCart();
  router.push(`/menu/${tableNumber}`);
};
</script>

<template>
  <div class="min-h-screen bg-gray-100 p-4">
    <h1 class="text-xl font-bold mb-4 text-center">
      Checkout – Meja {{ tableNumber }}
    </h1>

    <div class="max-w-xl mx-auto bg-white rounded-xl shadow p-4 space-y-4">
      <div
        v-for="i in cart.items"
        :key="i.menu_id"
        class="flex justify-between items-center border-b pb-2"
      >
        <div>
          <p class="font-semibold">{{ i.name }}</p>
          <p class="text-sm text-gray-500">Rp {{ i.price.toLocaleString() }}</p>
        </div>

        <div class="flex items-center gap-2">
          <input
            type="number"
            min="1"
            class="w-14 border rounded px-2 py-1"
            v-model.number="i.qty"
            @change="updateQty(i.menu_id, i.qty)"
          />

          <button @click="removeItem(i.menu_id)" class="text-red-600 text-sm">
            ✕
          </button>
        </div>
      </div>

      <div class="flex justify-between font-bold text-lg">
        <span>Total</span>
        <span>Rp {{ total.toLocaleString() }}</span>
      </div>

      <button
        @click="submitOrder"
        class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700"
      >
        Kirim Pesanan
      </button>
    </div>
  </div>
</template>
