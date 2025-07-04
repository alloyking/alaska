import './assets/main.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

import axios from 'axios';
window.axios = axios;

const app = createApp(App);

app.use(router);

app.mount('#app');
