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

        <v-speed-dial
            v-model="fab"
            bottom
            right
            fixed
            direction="top"
            open-on-hover
            transition="scale-transition"
            class="hidden-md-and-up"
        >
            <template v-slot:activator>
                <v-btn
                    v-model="fab"
                    fab
                    dark
                    color="primary"
                >
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </template>
            <v-btn fab dark small color="primary" @click="dialog = true; mode = 'file'">
                <v-icon>mdi-file-document-outline</v-icon>
            </v-btn>
            <v-btn fab dark small color="primary" @click="dialog = true; mode = 'text'">
                <v-icon>mdi-text</v-icon>
            </v-btn>
        </v-speed-dial>
        <v-dialog
            v-model="dialog"
            fullscreen
            hide-overlay
            transition="dialog-bottom-transition"
            scrollable
        >
            <v-card>
                <v-toolbar dark color="primary" class="flex-grow-0">
                    <v-btn icon @click="dialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                    <v-toolbar-title v-if="mode === 'text'">发送文本</v-toolbar-title>
                    <v-toolbar-title v-if="mode === 'file'">发送文件</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
                <v-card-text class="px-4">
                    <div class="my-4">
                        <send-text v-if="mode === 'text'"></send-text>
                        <send-file v-if="mode === 'file'"></send-file>
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
            fab: false,
            dialog: false,
            mode: null,
        };
    },
}
</script>
