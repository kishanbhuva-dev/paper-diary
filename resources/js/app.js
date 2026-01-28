import { createApp } from 'vue';
import { Icon } from '@iconify/vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

import { fas } from '@fortawesome/free-solid-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';
import { fab } from '@fortawesome/free-brands-svg-icons';

import router from './router';
import App from './App.vue';
import { useAuth } from './composables/useAuth';
import '../css/app.css';
import 'vue-sonner/style.css';

library.add(fas, far, fab);

const app = createApp(App);
app.component('Icon', Icon);
app.component('FontAwesomeIcon', FontAwesomeIcon);

const { checkAuth } = useAuth();
checkAuth();

app.use(router);

app.mount('#app');
