import Vue from 'vue';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';
import websocket from './websocket';
import axios from 'axios';
import VueAxios from 'vue-axios';

import '@mdi/font/css/materialdesignicons.css';
import 'typeface-roboto/index.css';

Vue.config.productionTip = false;

Vue.use(VueAxios, axios);

Vue.filter('prettyFileSize', size => {
    let units = ['TB', 'GB', 'MB', 'KB'];
    let unit = 'Bytes';
    while (size >= 1024 && units.length) {
        size /= 1024;
        unit = units.pop();
    };
    return `${Math.floor(100 * size) / 100} ${unit}`;
});

new Vue({
    mixins: [websocket],
    data() {
        return {
            send: {
                text: '',
                file: null,
            },
            received: [],
        };
    },
    router,
    vuetify,
    render: h => h(App),
}).$mount('#app');