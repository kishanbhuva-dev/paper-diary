import { createApp } from "vue";
import { Icon } from "@iconify/vue";
import { createPinia } from "pinia";
import { useAuthStore } from "./stores/authStore";

import router from "./router";
import App from "./App.vue";
import "../css/app.css";
// Centralize Sonner styles so it is loaded once globally
import "vue-sonner/style.css";

const pinia = createPinia();
// createApp(App).use(router).mount("#app");
const app = createApp(App);
app.component("Icon", Icon);
app.use(pinia);
app.use(router);

app.mount("#app");
