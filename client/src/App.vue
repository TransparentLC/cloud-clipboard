<template>
    <v-app>
        <v-navigation-drawer
            v-model="drawer"
            app
        >
            <v-list-item>
                <v-list-item-content>
                    <v-list-item-title class="title">
                        Application
                    </v-list-item-title>
                    <v-list-item-subtitle>
                        subtext
                    </v-list-item-subtitle>
                </v-list-item-content>
            </v-list-item>

            <v-divider></v-divider>

            <v-list>
                <v-list-item link href="#/">
                    <v-list-item-action>
                        <v-icon>mdi-content-paste</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>剪贴板</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item link href="#/device">
                    <v-list-item-action>
                        <v-icon>mdi-devices</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>设备列表</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item link href="#/about">
                    <v-list-item-action>
                        <v-icon>mdi-information</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>关于</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar
            app
            color="primary"
            dark
        >
            <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
            <v-toolbar-title>云剪贴板</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-tooltip left>
                <template v-slot:activator="{ on }">
                    <v-btn icon v-on="on" @click="if (!$root.websocket) {$root.retry = 0; $root.connect();}">
                        <v-icon v-if="$root.websocket">mdi-lan-connect</v-icon>
                        <v-icon v-else>mdi-lan-disconnect</v-icon>
                    </v-btn>
                </template>
                <span v-if="$root.websocket">已连接</span>
                <span v-else>未连接，点击重连</span>
            </v-tooltip>
        </v-app-bar>

        <v-content>
            <template v-if="$route.meta.keepAlive">
                <keep-alive><router-view class="mdui-container mdui-typo mdui-m-y-2"/></keep-alive>
            </template>
            <router-view v-else />
        </v-content>
    </v-app>
</template>

<script>
export default {
    data() {
        return {
            drawer: false,
        };
    },
};
</script>
