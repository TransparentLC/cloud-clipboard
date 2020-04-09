<template>
    <v-container>
        <v-row>
            <v-col cols="12" md="4" class="hidden-sm-and-down">
                <send-text></send-text>
                <v-divider class="my-4"></v-divider>
                <send-file></send-file>
            </v-col>
            <v-col cols="12" md="8">
                <v-fade-transition group>
                    <received-item v-for="item in $root.received" :key="item.id" :meta="item"></received-item>
                </v-fade-transition>
                <div class="text-center caption text--secondary py-2">{{$root.received.length ? '已经到底了哦～' : '这里空空的'}}</div>
            </v-col>
        </v-row>

        <v-dialog
            v-model="dialog"
            fullscreen
            hide-overlay
            transition="dialog-bottom-transition"
            scrollable
        >
            <template v-slot:activator="{ on }">
                <v-btn
                    fab
                    fixed
                    right
                    bottom
                    color="primary"
                    class="hidden-md-and-up"
                    v-on="on"
                >
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </template>
            <v-card>
                <v-toolbar dark color="primary" class="flex-grow-0">
                    <v-btn icon @click="dialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                    <v-toolbar-title>发送文本 / 文件</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
                <v-card-text class="px-4">
                    <div class="my-4">
                        <send-text></send-text>
                        <v-divider class="my-4"></v-divider>
                        <send-file></send-file>
                    </div>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
import SendText from '@/components/SendText.vue';
import SendFile from '@/components/SendFile.vue';
import ReceivedItem from '@/components/ReceivedItem.vue';

export default {
    components: {
        SendText,
        SendFile,
        ReceivedItem,
    },
    data() {
        return {
            dialog: false,
        };
    },
}
</script>
