import { createApp } from "vue";
import { Icon } from "@iconify/vue";

import router from "./router";
import App from "./App.vue";
import "../css/app.css";
// Centralize Sonner styles so it is loaded once globally
import "vue-sonner/style.css";

// createApp(App).use(router).mount("#app");
const app = createApp(App);
app.component("Icon", Icon);
app.use(router);

app.mount("#app");
