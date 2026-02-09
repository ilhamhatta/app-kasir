<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from "vue";
import api from "@/services/apiAdmin";
import Swal from "sweetalert2";

/* =======================
   COLUMN FILTER
======================= */
const filters = ref({
  order: "",
  table: "",
  status: "",
});

const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const delta = 2; // kiri-kanan

  let start = Math.max(1, current - delta);
  let end = Math.min(total, current + delta);

  // biar tetap 5 tombol kalau bisa
  if (current <= delta + 1) {
    end = Math.min(total, 1 + delta * 2);
  }
  if (current + delta >= total) {
    start = Math.max(1, total - delta * 2);
  }

  const pages = [];
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const sortedOrders = computed(() => {
  return [...orders.value].sort((a, b) => {
    let valA, valB;

    switch (sort.value.key) {
      case "order_number":
        valA = a.order_number;
        valB = b.order_number;
        break;
      case "table":
        valA = a.table?.table_number ?? "";
        valB = b.table?.table_number ?? "";
        break;
      case "status":
        valA = a.status;
        valB = b.status;
        break;
      default:
        valA = new Date(a.created_at);
        valB = new Date(b.created_at);
    }

    if (valA < valB) return sort.value.direction === "asc" ? -1 : 1;
    if (valA > valB) return sort.value.direction === "asc" ? 1 : -1;
    return 0;
  });
});

const filteredOrders = computed(() => {
  return sortedOrders.value.filter((o) => {
    const matchOrder = o.order_number
      .toLowerCase()
      .includes(filters.value.order.toLowerCase());

    const matchTable = String(o.table?.table_number ?? "")
      .toLowerCase()
      .includes(filters.value.table.toLowerCase().replace("table", "").trim());

    const matchStatus = filters.value.status
      ? o.status === filters.value.status
      : true;

    return matchOrder && matchTable && matchStatus;
  });
});

const paginatedOrders = computed(() => {
  if (perPage.value === -1) {
    return filteredOrders.value; // show all
  }

  const start = (currentPage.value - 1) * perPage.value;
  return filteredOrders.value.slice(start, start + perPage.value);
});

const currentPage = ref(1);
const perPageOptions = [
  { label: "7", value: 7 },
  { label: "15", value: 15 },
  { label: "50", value: 50 },
  { label: "100", value: 100 },
  { label: "All", value: -1 },
];
const perPage = ref(7);

/* =======================
   SORTING
======================= */
const sort = ref({
  key: "order_number",
  direction: "desc",
});

const sortIcon = (key) => {
  if (sort.value.key !== key) return "⇅";
  return sort.value.direction === "asc" ? "▲" : "▼";
};

const toggleSort = (key) => {
  if (sort.value.key === key) {
    sort.value.direction = sort.value.direction === "asc" ? "desc" : "asc";
  } else {
    sort.value.key = key;
    sort.value.direction = "asc";
  }
};

const totalPages = computed(() => {
  if (perPage.value === -1) return 1;
  return Math.ceil(filteredOrders.value.length / perPage.value);
});

const showingFrom = computed(() => {
  if (filteredOrders.value.length === 0) return 0;
  if (perPage.value === -1) return 1;
  return (currentPage.value - 1) * perPage.value + 1;
});

const showingTo = computed(() => {
  if (perPage.value === -1) return filteredOrders.value.length;
  return Math.min(
    currentPage.value * perPage.value,
    filteredOrders.value.length,
  );
});

const badgeClass = (status) => {
  return {
    pending: "bg-yellow-100 text-yellow-700",
    confirmed: "bg-blue-100 text-blue-700",
    processing: "bg-purple-100 text-purple-700",
    completed: "bg-green-100 text-green-700",
    cancelled: "bg-red-100 text-red-700",
  }[status];
};

const actionConfig = (status) => {
  return {
    pending: {
      label: "Konfirmasi",
      color: "bg-blue-600 hover:bg-blue-700",
      next: "confirmed",
    },
    confirmed: {
      label: "Proses",
      color: "bg-purple-600 hover:bg-purple-700",
      next: "processing",
    },
    processing: {
      label: "Selesaikan",
      color: "bg-green-600 hover:bg-green-700",
      next: "completed",
    },
  }[status];
};

watch(
  filters,
  () => {
    currentPage.value = 1;
  },
  { deep: true },
);

watch(perPage, () => {
  currentPage.value = 1;
});

/* =======================
   STATE
======================= */
const loading = ref(true);
const loadingDetailId = ref(null);
const orders = ref([]);
const stats = ref({
  total: 0,
  pending: 0,
  confirmed: 0,
  processing: 0,
  completed: 0,
  cancelled: 0,
});

let poller = null;
const lastPending = ref(0);

/* =======================
   TOAST
======================= */
const showToast = (message) => {
  const toast = document.createElement("div");
  toast.textContent = message;
  toast.className =
    "fixed top-5 right-5 z-50 bg-green-600 text-white px-4 py-2 rounded-lg shadow";
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
};

/* =======================
   FETCH DASHBOARD
======================= */
const fetchDashboard = async () => {
  try {
    const { data } = await api.get("/admin/orders");

    orders.value = data.orders;
    stats.value = data.stats;

    if (lastPending.value && stats.value.pending > lastPending.value) {
      showToast("Pesanan baru masuk 🛎️");
    }
    lastPending.value = stats.value.pending;
  } finally {
    loading.value = false;
  }
};

/* =======================
   UPDATE STATUS
======================= */
const updateStatus = async (id, status) => {
  const label = {
    confirmed: "Konfirmasi",
    processing: "Proses",
    completed: "Selesaikan",
  };

  const confirm = await Swal.fire({
    title: "Konfirmasi Aksi",
    text: `Yakin ingin ${label[status]} pesanan ini?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, lanjutkan",
    cancelButtonText: "Batal",
    confirmButtonColor: "#16a34a",
    cancelButtonColor: "#dc2626",
  });

  if (!confirm.isConfirmed) return;

  try {
    await api.patch(`/admin/orders/${id}/status`, { status });
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      timer: 1200,
      showConfirmButton: false,
    });
    fetchDashboard();
  } catch {
    Swal.fire("Gagal", "Terjadi kesalahan", "error");
  }
};

/* =======================
   DETAIL PESANAN
======================= */
const formatRupiah = (v) =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
  }).format(v);

const statusBadge = (s) =>
  ({
    pending: "background:#FEF3C7;color:#92400E;",
    confirmed: "background:#DBEAFE;color:#1E40AF;",
    processing: "background:#EDE9FE;color:#5B21B6;",
    completed: "background:#DCFCE7;color:#166534;",
    cancelled: "background:#FEE2E2;color:#991B1B;",
  })[s];

const showOrderDetail = async (id) => {
  if (loadingDetailId.value === id) return;
  loadingDetailId.value = id;

  try {
    const { data } = await api.get(`/admin/orders/${id}`);

    console.log("ORDER DETAIL:", data);
    console.log("ITEMS:", data.items);

    const rows = data.items
      .map(
        (i, idx) => `
        <tr>
          <td style="padding:12px 8px">${idx + 1}</td>
          <td style="padding:12px 8px;font-weight:500">${i.menu.name}</td>
          <td style="padding:12px 8px;text-align:center">${i.qty}</td>
          <td style="padding:12px 8px;text-align:right">
            ${formatRupiah(i.price)}
          </td>
          <td style="padding:12px 8px;text-align:right;font-weight:500">
            ${formatRupiah(i.price * i.qty)}
          </td>
        </tr>
      `,
      )
      .join("");

    await Swal.fire({
      width: 860,
      showCloseButton: true,
      showConfirmButton: false,
      customClass: {
        popup: "rounded-2xl",
      },
      html: `
      <div style="text-align:left;font-family:Inter,system-ui">
        
        <!-- HEADER -->
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px">
          <div>
            <h2 style="font-size:20px;font-weight:600;margin-bottom:4px">
              Invoice
            </h2>
            <div style="color:#64748b;font-size:13px">
              ${data.order_number}
            </div>
          </div>

          <span style="
            padding:6px 14px;
            border-radius:999px;
            font-size:12px;
            font-weight:500;
            ${statusBadge(data.status)}
          ">
            ${data.status}
          </span>
        </div>

        <!-- INFO -->
        <div style="display:flex;justify-content:space-between;margin-bottom:24px">
          <div style="font-size:13px;color:#334155">
            <div><b>Meja:</b> ${data.table?.table_number ?? "-"}</div>
            <div><b>Tanggal:</b> ${new Date(data.created_at).toLocaleDateString("id-ID")}</div>
            <div><b>Metode Bayar:</b> ${data.payment_method ?? "-"}</div>
          </div>

          <div style="font-size:13px;color:#334155;text-align:right">
            <div><b>Status Bayar:</b></div>
            <div style="margin-top:4px">
              ${data.payment_status}
            </div>
          </div>
        </div>

        <!-- TABLE -->
        <div style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
          <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead style="background:#f8fafc;color:#64748b">
              <tr>
                <th style="padding:12px 8px;text-align:left">#</th>
                <th style="padding:12px 8px;text-align:left">Menu</th>
                <th style="padding:12px 8px;text-align:center">Qty</th>
                <th style="padding:12px 8px;text-align:right">Harga</th>
                <th style="padding:12px 8px;text-align:right">Total</th>
              </tr>
            </thead>
            <tbody>
              ${rows}
            </tbody>
          </table>
        </div>

        <!-- SUMMARY -->
        <div style="display:flex;justify-content:flex-end;margin-top:24px">
          <div style="width:280px;font-size:14px">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
              <span style="color:#64748b">Subtotal</span>
              <span>${formatRupiah(data.total_price)}</span>
            </div>

            <div style="
              display:flex;
              justify-content:space-between;
              font-weight:600;
              font-size:16px;
              border-top:1px solid #e2e8f0;
              padding-top:12px;
            ">
              <span>Total</span>
              <span>${formatRupiah(data.total_price)}</span>
            </div>
          </div>
        </div>

      </div>
      `,
    });
  } finally {
    loadingDetailId.value = null;
  }
};

/* =======================
   LIFECYCLE
======================= */
onMounted(() => {
  fetchDashboard();
  poller = setInterval(fetchDashboard, 5000);
});

onUnmounted(() => clearInterval(poller));
</script>

<template>
  <div class="space-y-6">
    <!-- HEADER -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-semibold text-slate-900">Orders List</h1>
        <p class="text-sm text-slate-500">Track & manage incoming orders.</p>
      </div>
    </div>

    <!-- TABLE CARD -->
    <div
      class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
    >
      <!-- TABLE TOOLBAR -->
      <div
        class="flex items-center justify-between px-6 py-3 border-b bg-slate-50"
      >
        <!-- LEFT: PAGE LENGTH -->
        <div class="flex items-center gap-2 text-sm text-slate-600">
          <span>Rows:</span>
          <select
            v-model="perPage"
            class="px-2 py-1 rounded-lg border border-slate-300 bg-white"
          >
            <option
              v-for="opt in perPageOptions"
              :key="opt.value"
              :value="opt.value"
            >
              {{ opt.label }}
            </option>
          </select>
        </div>

        <!-- RIGHT (optional, future use) -->
        <!-- bisa diisi global search / export button -->
      </div>

      <div class="max-h-[70vh] overflow-y-auto">
        <table class="w-full text-sm table-fixed">
          <!-- TABLE HEAD -->
          <thead
            class="bg-slate-50 text-xs uppercase text-slate-500 relative z-30"
          >
            <tr>
              <!-- ORDER (kolom pertama, TANPA border-l) -->
              <th
                class="px-6 py-4 text-left w-[260px] sticky top-0 z-30 bg-slate-50 border-b border-slate-200 cursor-pointer select-none"
                @click="toggleSort('order_number')"
              >
                Order
                <span class="ml-1 text-xs text-slate-400">
                  {{ sortIcon("order_number") }}
                </span>
              </th>

              <!-- TABLE -->
              <th
                class="px-6 py-4 text-left w-[140px] sticky top-0 z-30 bg-slate-50 border-b border-l border-slate-200"
                @click="toggleSort('table')"
              >
                Table
                <span class="ml-1 text-xs text-slate-400">
                  {{ sortIcon("table") }}
                </span>
              </th>

              <!-- STATUS -->
              <th
                class="px-6 py-4 text-left w-[140px] sticky top-0 z-30 bg-slate-50 border-b border-l border-slate-200"
                @click="toggleSort('status')"
              >
                Status
                <span class="ml-1 text-xs text-slate-400">
                  {{ sortIcon("status") }}
                </span>
              </th>

              <!-- ACTION -->
              <th
                class="px-6 py-4 text-right w-[180px] sticky top-0 z-30 bg-slate-50 border-b border-l border-slate-200"
              >
                Action
              </th>
            </tr>

            <tr
              class="sticky top-[56px] z-20 bg-slate-50 border-b border-slate-200"
            >
              <!-- Order -->
              <th class="px-6 py-3">
                <input
                  v-model="filters.order"
                  placeholder="Search order"
                  class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-1 focus:ring-brand-500"
                />
              </th>

              <!-- Table -->
              <th class="px-6 py-3 border-l border-slate-200">
                <input
                  v-model="filters.table"
                  placeholder="Search table (A1, A2...)"
                  class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-1 focus:ring-brand-500"
                />
              </th>

              <!-- Status -->
              <th class="px-6 py-3 border-l border-slate-200">
                <select
                  v-model="filters.status"
                  class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-1 focus:ring-brand-500"
                >
                  <option value="">All</option>
                  <option value="pending">Pending</option>
                  <option value="confirmed">Confirmed</option>
                  <option value="processing">Processing</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </th>

              <th class="px-6 py-3 w-[180px] border-l border-slate-200"></th>
            </tr>
          </thead>

          <!-- TABLE BODY -->
          <tbody class="relative z-0">
            <tr
              v-for="o in paginatedOrders"
              :key="o.id"
              class="border-t hover:bg-slate-50 transition"
            >
              <td class="px-6 py-4">
                <div class="font-mono text-xs text-slate-700">
                  {{ o.order_number }}
                </div>
              </td>

              <td class="px-6 py-4 text-slate-700 border-l border-slate-200">
                Table {{ o.table?.table_number ?? "-" }}
              </td>

              <td class="px-6 py-4 border-l border-slate-200">
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                  :class="badgeClass(o.status)"
                >
                  {{ o.status }}
                </span>
              </td>

              <td class="px-6 py-4 w-[180px] border-l border-slate-200">
                <div class="flex justify-end items-center gap-2">
                  <!-- PROSES BUTTON -->
                  <button
                    v-if="actionConfig(o.status)"
                    @click="updateStatus(o.id, actionConfig(o.status).next)"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium text-white transition"
                    :class="actionConfig(o.status).color"
                  >
                    {{ actionConfig(o.status).label }}
                  </button>

                  <!-- DETAIL BUTTON -->
                  <button
                    @click="showOrderDetail(o.id)"
                    :disabled="loadingDetailId === o.id"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 hover:bg-slate-100 transition disabled:opacity-50"
                    title="Detail Pesanan"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-4 h-4 text-slate-600"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                      />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!loading && orders.length === 0">
              <td colspan="4" class="py-10 text-center text-slate-400">
                No orders found
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- FOOTER / PAGINATION -->
      <div
        class="flex items-center justify-between px-6 py-4 border-t bg-white"
      >
        <!-- INFO + PAGE LENGTH -->
        <div class="flex items-center gap-4 text-sm text-slate-500">
          <!-- SHOWING INFO -->
          <div>
            Showing {{ showingFrom }} to {{ showingTo }} of
            {{ filteredOrders.length }}
          </div>
        </div>

        <!-- PAGINATION -->
        <div class="flex items-center gap-2 overflow-x-auto max-w-[420px]">
          <button
            class="pager-nav"
            :disabled="currentPage === 1 || perPage === -1"
            @click="currentPage--"
          >
            ←
          </button>

          <button
            v-for="page in visiblePages"
            :key="page"
            :disabled="perPage === -1"
            @click="currentPage = page"
            class="pager-number"
            :class="page === currentPage ? 'pager-active' : ''"
          >
            {{ page }}
          </button>

          <button
            class="pager-nav"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pager-nav {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: white;
  font-size: 14px;
  color: #0f172a; /* slate-900 */
}

.pager-number {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: white;
  font-size: 14px;
  color: #0f172a; /* hitam untuk non-active */
  transition:
    background-color 0.15s ease,
    color 0.15s ease;
}

/* 🔥 FIX UTAMA */
.pager-number:hover {
  background: #f1f5f9; /* slate-100 */
  color: #0f172a; /* TETAP HITAM */
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
}

/* ACTIVE PAGE */
.pager-active {
  background: #4f46e5;
  color: #ffffff;
  border-color: #4f46e5;
}

/* HOVER ACTIVE (biar nggak berubah) */
.pager-active:hover {
  background: #4f46e5;
  color: #ffffff;
}
</style>
