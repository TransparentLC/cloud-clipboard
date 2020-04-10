import Vue from 'vue';
import Vuetify, { VSnackbar, VBtn, VIcon } from 'vuetify/lib';
import VuetifyToast from 'vuetify-toast-snackbar';

Vue.use(Vuetify, {
    components: {
        VSnackbar,
        VBtn,
        VIcon,
    }
});
Vue.use(VuetifyToast, {
    x: '',
    color: '',
    showClose: true,
    closeText: '关闭',
    closeColor: 'accent',
});

export default new Vuetify({
});
