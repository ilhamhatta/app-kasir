<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/services/apiPublic";
import { useCart } from "@/composables/useCart";
import { confirmAction, successAlert, errorAlert } from "@/utils/swal";

const submitting = ref(false);
const showCheckout = ref(false);
const route = useRoute();
const router = useRouter();
const tableNumber = route.params.tableNumber;

const menus = ref([]);
const loading = ref(true);

const { cart, addItem, updateQty, clearCart, total } = useCart(tableNumber);

const submitOrder = async () => {
  if (!cart.value.items.length) return;

  const confirm = await confirmAction({
    text: `Total Rp ${total.value.toLocaleString()}`,
  });

  if (!confirm.isConfirmed) return;

  submitting.value = true;

  try {
    const payload = {
      table_number: cart.value.table,
      items: cart.value.items.map((i) => ({
        menu_id: i.menu_id,
        qty: i.qty,
      })),
    };

    const res = await api.post("/orders", payload);

    const orderNumber = res.data.order_number;

    // simpan ke localStorage (per meja)
    localStorage.setItem(`order_${tableNumber}`, orderNumber);

    clearCart();
    showCheckout.value = false;

    await successAlert("Pesanan dikirim", "Silakan tunggu pesanan Anda 🙏");
    router.push({
      name: "order-status",
      params: { tableNumber },
    });
  } catch (e) {
    console.error("ORDER ERROR:", e);
    errorAlert("Gagal mengirim pesanan. Coba lagi.");
  } finally {
    submitting.value = false;
  }
};

const fetchMenu = async () => {
  try {
    const res = await api.get(`/menu/${tableNumber}`);
    menus.value = res.data.menus ?? [];
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchMenu);
</script>

<template>
  <div class="min-h-screen bg-gray-100 dark:bg-slate-900 p-4 pb-32">
    <!-- HEADER -->
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold">Menu Café</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Meja {{ tableNumber }}
      </p>
    </div>

    <!-- MENU GRID -->
    <div
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-w-6xl mx-auto"
    >
      <div
        v-for="m in menus"
        :key="m.id"
        class="bg-white dark:bg-slate-800 rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col"
      >
        <!-- IMAGE -->
        <div class="relative aspect-[4/3] bg-gray-200 dark:bg-slate-700">
          <img
            v-if="m.image"
            :src="m.image"
            class="w-full h-full object-cover"
          />
          <div
            v-else
            class="absolute inset-0 flex items-center justify-center text-4xl text-gray-400"
          >
            ☕
          </div>
        </div>

        <!-- CONTENT -->
        <div class="p-4 flex-1 flex flex-col justify-between">
          <div>
            <h3 class="font-semibold text-lg">{{ m.name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ m.description || "Tanpa deskripsi" }}
            </p>
          </div>

          <div class="mt-4 flex items-center justify-between">
            <span class="font-bold text-green-600">
              Rp {{ Number(m.price).toLocaleString() }}
            </span>

            <button
              @click="addItem(m)"
              :disabled="loading"
              class="px-3 py-1 text-sm bg-green-600 text-white rounded disabled:bg-gray-400"
            >
              Pesan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- EMPTY -->
    <div
      v-if="!loading && menus.length === 0"
      class="text-center text-gray-400 mt-10"
    >
      Menu belum tersedia
    </div>

    <!-- FLOATING CART -->
    <div
      v-if="cart.items.length"
      class="fixed bottom-4 left-4 right-4 bg-white dark:bg-slate-800 shadow-xl rounded-xl p-4 flex justify-between items-center max-w-2xl mx-auto"
    >
      <div>
        <p class="text-sm text-gray-500">Total</p>
        <p class="font-bold text-lg text-green-600">
          Rp {{ total.toLocaleString() }}
        </p>
      </div>

      <button
        @click="showCheckout = true"
        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
      >
        Checkout
      </button>
    </div>
  </div>
  <!-- CHECKOUT MODAL -->
  <div
    v-if="showCheckout"
    class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center"
  >
    <div
      class="bg-white dark:bg-slate-800 w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl p-4 max-h-[80vh] overflow-y-auto"
    >
      <!-- HEADER -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold">Checkout</h2>
        <button @click="showCheckout = false">✕</button>
      </div>

      <!-- ITEMS -->
      <div class="space-y-3">
        <div
          v-for="item in cart.items"
          :key="item.menu_id"
          class="flex justify-between items-center"
        >
          <div>
            <p class="font-semibold">{{ item.name }}</p>
            <p class="text-sm text-gray-500">
              Rp {{ Number(item.price).toLocaleString() }}
            </p>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="updateQty(item.menu_id, item.qty - 1)"
              :disabled="item.qty <= 1"
              class="px-2 py-1 border rounded"
            >
              −
            </button>

            <span>{{ item.qty }}</span>

            <button
              @click="updateQty(item.menu_id, item.qty + 1)"
              class="px-2 py-1 border rounded"
            >
              +
            </button>
          </div>
        </div>
      </div>

      <!-- TOTAL -->
      <div class="border-t mt-4 pt-4 flex justify-between font-bold">
        <span>Total</span>
        <span class="text-green-600">
          Rp {{ Number(total).toLocaleString() }}
        </span>
      </div>

      <!-- ACTION -->
      <button
        @click="submitOrder"
        :disabled="submitting"
        class="mt-4 w-full py-2 rounded text-white transition bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
      >
        <span v-if="!submitting">Kirim Pesanan</span>
        <span v-else>Mengirim...</span>
      </button>
    </div>
  </div>
</template>
