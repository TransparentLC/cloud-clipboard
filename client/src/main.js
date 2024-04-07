import Vue from 'vue';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';
import websocket from './websocket';
import axios from 'axios';
import VueAxios from 'vue-axios';
import linkify from 'vue-linkify';

import {
    prettyFileSize,
    percentage,
    formatTimestamp,
} from './util';

import 'typeface-roboto/index.css';

Vue.config.productionTip = false;

Vue.use(VueAxios, axios);
Vue.directive('linkified', linkify);
Vue.filter('prettyFileSize', prettyFileSize);
Vue.filter('percentage', percentage);
Vue.filter('formatTimestamp', formatTimestamp);

new Vue({
    mixins: [websocket],
    data() {
        return {
            date: new Date,
            dark: null,
            config: {
                version: '',
                text: {
                    limit: 0,
                },
                file: {
                    expire: 0,
                    chunk: 0,
                    limit: 0,
                },
            },
            send: {
                text: '',
                files: [],
            },
            received: [],
            device: [],
        };
    },
    router,
    vuetify,
    render: h => h(App),
    watch: {
        dark(newval) {
            this.$vuetify.theme.dark = this.useDark;
            localStorage.setItem('darkmode', newval);
        },
    },
    computed: {
        useDark: {
            cache: false,
            get() {
                switch (this.dark) {
                    case 'enable':
                        return true;
                    case 'disable':
                        return false;
                    case 'time':
                        let hour = this.date.getHours();
                        return hour >= 19 || hour < 7;
                    case 'prefer':
                        return matchMedia('(prefers-color-scheme:dark)').matches;
                    default:
                        return false;
                }
            },
        },
    },
    mounted() {
        this.dark = localStorage.getItem('darkmode') || 'prefer';
        this.$vuetify.theme.dark = this.useDark;
        setInterval(() => {
            this.date = new Date;
            this.$vuetify.theme.dark = this.useDark;
        }, 1000);
    },
}).$mount('#app');