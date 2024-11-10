import Vue from 'vue';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';
import websocket from './websocket';
import axios from 'axios';
import VueAxios from 'vue-axios';
import linkify from 'vue-linkify';

import VueI18n from 'vue-i18n';
import zhCN from './locales/zh-CN.json';
import en from './locales/en.json';

import {
    prettyFileSize,
    percentage,
    formatTimestamp,
} from './util';

import 'typeface-roboto/index.css';

Vue.config.productionTip = false;

Vue.use(VueI18n);
Vue.use(VueAxios, axios);
Vue.directive('linkified', linkify);
Vue.filter('prettyFileSize', prettyFileSize);
Vue.filter('percentage', percentage);
Vue.filter('formatTimestamp', formatTimestamp);

const messages = {
    'zh-CN': zhCN,
    'en': en
};

const i18n = new VueI18n({
    locale: 'zh-CN', // set default locale
    fallbackLocale: 'en', // set fallback locale
    messages // set locale messages
});


new Vue({
    mixins: [websocket],
    i18n,
    data() {
        return {
            date: new Date,
            dark: null,
            language: null,
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
        language(newval) {
            // this.$root.language = newval;
            localStorage.setItem('language', newval);
            console.log('save_language:', newval)
            if (newval === 'browser') {
                // seems no change event
                const browserLanguage = navigator.language;
                const availableLanguages = Object.keys(messages);
                //1. find by navigator.language
                const matchedLanguage = availableLanguages.find(lang => lang.startsWith(browserLanguage.split('-')[0]));
                if (!matchedLanguage){
                    //2. find by navigator.languages
                    navigator.languages.forEach(lang => {
                        const matchedLanguage = availableLanguages.find(availableLang => availableLang.startsWith(lang.split('-')[0]));
                        if (matchedLanguage) {
                            this.$i18n.locale = matchedLanguage;
                            return;
                        }
                    });
                }
                this.$i18n.locale = matchedLanguage || 'en';
            } else {
                this.$i18n.locale = newval;
            }
            // this.$i18n.locale = newval;
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

        this.language = localStorage.getItem('language') || 'browser';
        this.$root.language = this.language;
        console.log('load_language:', this.language)
        // this.$vuetify.lang.current = this.language;
    },
}).$mount('#app');