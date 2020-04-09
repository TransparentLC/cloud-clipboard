import Vue from 'vue';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';

import '@mdi/font/css/materialdesignicons.css';
import 'typeface-roboto/index.css';

Vue.config.productionTip = false;

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
    data() {
        return {
            send: {
                text: '',
                file: null,
            },
            // TODO 测试用数据
            received: [
                {
                    id: 1,
                    type: 'text',
                    content: ''.padStart(256, 'The quickly brown fox jumps over a lazy dog. '),
                },
                {
                    id: 2,
                    type: 'file',
                    name: 'php-7.4.4.tar.gz',
                    size: 16477200,
                    cache: '691d44a42de68d9da10f99d85fbd1d21',
                },
                {
                    id: 3,
                    type: 'text',
                    content: ''.padStart(128, '我能吞下玻璃而不伤身体'),
                },
            ],
        };
    },
    router,
    vuetify,
    render: h => h(App),
}).$mount('#app');