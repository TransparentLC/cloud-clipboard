import Vue from 'vue';
import VueRouter from 'vue-router';
import Home from '@/views/Home.vue';
import Device from '@/views/Device.vue';
import About from '@/views/About.vue';

Vue.use(VueRouter);

const router = new VueRouter({
    routes: [
        {
            path: '/',
            component: Home,
            meta: {
                keepAlive: true,
            },
        },
        {
            path: '/device',
            component: Device,
            meta: {
                keepAlive: true,
            },
        },
        {
            path: '/about',
            component: About,
            meta: {
                keepAlive: true,
            },
        },
    ],
});

export default router;
