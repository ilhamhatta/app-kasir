<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import api from "@/services/apiPublic";

const route = useRoute();
const tableNumber = route.params.tableNumber;

const status = ref("pending");
let interval = null;

const steps = [
  { key: "pending", label: "Menunggu" },
  { key: "confirmed", label: "Dikonfirmasi" },
  { key: "processing", label: "Diproses" },
  { key: "completed", label: "Selesai" },
];

const currentStepIndex = computed(() =>
  steps.findIndex((s) => s.key === status.value),
);

const fetchStatus = async () => {
  const orderNumber = localStorage.getItem(`order_${tableNumber}`);
  if (!orderNumber) return;

  try {
    const res = await api.get(`/orders/${orderNumber}`);
    status.value = res.data.status;
  } catch (e) {
    console.error(e);
  }
};

onMounted(() => {
  fetchStatus();
  interval = setInterval(fetchStatus, 5000);
});

onUnmounted(() => clearInterval(interval));
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-slate-900 p-4"
  >
    <div
      class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow max-w-md w-full"
    >
      <h1 class="text-xl font-bold text-center">Status Pesanan</h1>
      <p class="text-center text-sm text-gray-500 mb-4">
        Meja {{ tableNumber }}
      </p>
      <!-- STEPPER -->
      <div class="mt-10 relative">
        <!-- LINE BACKGROUND -->
        <div
          class="absolute top-5 left-0 right-0 h-1 bg-gray-300 dark:bg-gray-600"
          style="margin: 0 20px"
        ></div>

        <!-- LINE ACTIVE -->
        <div
          class="absolute top-5 h-1 bg-green-600 transition-all duration-500"
          :style="{
            left: '20px',
            right:
              currentStepIndex === steps.length - 1
                ? '20px'
                : `calc(100% - ${
                    (currentStepIndex / (steps.length - 1)) * 100
                  }% - 20px)`,
          }"
        ></div>

        <!-- STEPS -->
        <div class="relative flex justify-between">
          <div
            v-for="(step, index) in steps"
            :key="step.key"
            class="flex flex-col items-center"
          >
            <!-- CIRCLE -->
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center font-bold z-10 transition"
              :class="
                index <= currentStepIndex
                  ? 'bg-green-600 text-white'
                  : 'bg-gray-300 dark:bg-gray-600 text-gray-600'
              "
            >
              {{ index + 1 }}
            </div>

            <!-- LABEL -->
            <span class="mt-3 text-xs text-center">
              {{ step.label }}
            </span>
          </div>
        </div>
      </div>

      <div
        v-if="status === 'completed'"
        class="mt-6 text-center text-green-600 font-semibold"
      >
        🎉 Pesanan siap diambil!
      </div>
    </div>
  </div>
</template>
