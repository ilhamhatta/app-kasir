<template>
  <div style="padding: 20px">
    <h2>Menu Meja {{ tableNumber }}</h2>

    <div v-if="loading">Memuat menu...</div>

    <div v-else>
      <div v-for="menu in menus" :key="menu.id" style="margin-bottom: 10px">
        <b>{{ menu.name }}</b> - {{ menu.price }}
        <br />
        <button @click="add(menu)">Tambah</button>
      </div>

      <hr />

      <h3>Pesanan</h3>

      <div v-for="item in cart" :key="item.id">
        {{ item.name }} x {{ item.qty }}
      </div>

      <button v-if="cart.length" style="margin-top: 10px" @click="submit">
        Kirim Pesanan
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "../services/api";

const route = useRoute();
const tableNumber = route.params.tableNumber;

const menus = ref([]);
const cart = ref([]);
const loading = ref(true);

const loadMenu = async () => {
  const res = await api.get(`/menu/${tableNumber}`);
  menus.value = res.data.menus;
  loading.value = false;
};

const add = (menu) => {
  const found = cart.value.find((i) => i.id === menu.id);
  if (found) {
    found.qty++;
  } else {
    cart.value.push({
      id: menu.id,
      name: menu.name,
      qty: 1,
    });
  }
};

const submit = async () => {
  await api.post("/orders", {
    table_number: tableNumber,
    items: cart.value.map((i) => ({
      menu_id: i.id,
      qty: i.qty,
    })),
  });

  cart.value = [];
  alert("Pesanan terkirim");
};

onMounted(loadMenu);
</script>
