import { ref, computed, watch } from "vue";

export function useCart(tableNumber) {
  const storageKey = `cart-${tableNumber}`;

  const cart = ref({
    table: tableNumber,
    items: [],
  });

  // ===== LOAD DARI STORAGE =====
  const saved = localStorage.getItem(storageKey);
  if (saved) {
    cart.value = JSON.parse(saved);
  }

  // ===== AUTO SAVE =====
  watch(
    cart,
    () => {
      localStorage.setItem(storageKey, JSON.stringify(cart.value));
    },
    { deep: true },
  );

  // ===== ACTIONS =====
  const addItem = (menu) => {
    const existing = cart.value.items.find((i) => i.menu_id === menu.id);

    if (existing) {
      existing.qty++;
    } else {
      cart.value.items.push({
        menu_id: menu.id,
        name: menu.name,
        price: menu.price,
        qty: 1,
      });
    }
  };

  const removeItem = (menu_id) => {
    cart.value.items = cart.value.items.filter((i) => i.menu_id !== menu_id);
  };

  const updateQty = (menu_id, qty) => {
    const item = cart.value.items.find((i) => i.menu_id === menu_id);
    if (item) item.qty = Math.max(1, qty);
  };

  const clearCart = () => {
    cart.value.items = [];
    localStorage.removeItem(storageKey);
  };

  const total = computed(() =>
    cart.value.items.reduce((sum, i) => sum + i.price * i.qty, 0),
  );

  return {
    cart,
    addItem,
    removeItem,
    updateQty,
    clearCart,
    total,
  };
}
