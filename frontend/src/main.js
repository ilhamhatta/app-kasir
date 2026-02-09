import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import "./style.css";
import { initDarkMode } from "@/composables/useDarkMode";

initDarkMode();
createApp(App).use(router).mount("#app");
