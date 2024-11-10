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
                    <div :is="`received-${item.type}`" v-for="item in $root.received" :key="item.id" :meta="item"></div>
                </v-fade-transition>
                <div class="text-center caption text--secondary py-2">{{ $root.received.length ? $t('endOfList') : $t('emptyList') }}</div>
            </v-col>
        </v-row>

        <v-speed-dial
            v-model="fab"
            bottom
            right
            fixed
            direction="top"
            transition="scale-transition"
            class="hidden-md-and-up"
            style="transform:translateY(-64px)"
        >
            <template v-slot:activator>
                <v-btn
                    v-model="fab"
                    fab
                    dark
                    color="primary"
                >
                    <v-icon>{{mdiPlus}}</v-icon>
                </v-btn>
            </template>
            <v-btn fab dark small color="primary" @click="dialog = true; mode = 'file'">
                <v-icon>{{mdiFileDocumentOutline}}</v-icon>
            </v-btn>
            <v-btn fab dark small color="primary" @click="dialog = true; mode = 'text'">
                <v-icon>{{mdiText}}</v-icon>
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
                        <v-icon>{{mdiClose}}</v-icon>
                    </v-btn>
                    <v-toolbar-title v-if="mode === 'text'">{{ $t('sendText') }}</v-toolbar-title>
                    <v-toolbar-title v-if="mode === 'file'">{{ $t('sendFile') }}</v-toolbar-title>
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
import ReceivedText from '@/components/received-item/Text.vue';
import ReceivedFile from '@/components/received-item/File.vue';
import {
    mdiPlus,
    mdiFileDocumentOutline,
    mdiText,
    mdiClose,
} from '@mdi/js';

export default {
    components: {
        SendText,
        SendFile,
        ReceivedText,
        ReceivedFile,
    },
    data() {
        return {
            fab: false,
            dialog: false,
            mode: null,
            mdiPlus,
            mdiFileDocumentOutline,
            mdiText,
            mdiClose,
        };
    },
}
</script>
