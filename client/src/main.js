import Vue from 'vue';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';

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
        };
    },
    router,
    vuetify,
    render: h => h(App),
}).$mount('#app');