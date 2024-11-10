<template>
    <div>
        <div class="headline text--primary mb-4">{{ $t('sendText') }}</div>
        <v-textarea
            no-resize
            outlined
            dense
            rows="6"
            :counter="$root.config.text.limit"
            :placeholder="$t('enterTextToSend')"
            v-model="$root.send.text"
        ></v-textarea>
        <div class="text-right">
            <v-btn
                color="primary"
                :block="$vuetify.breakpoint.smAndDown"
                :disabled="!$root.send.text || !$root.websocket || $root.send.text.length > $root.config.text.limit"
                @click="send"
            >{{ $t('send') }}</v-btn>
        </div>
    </div>
</template>

<script>
export default {
    name: 'send-text',
    methods: {
        send() {
            this.$http.post(
                'text',
                this.$root.send.text,
                {
                    params: new URLSearchParams([['room', this.$root.room]]),
                    headers: {
                        'Content-Type': 'text/plain',
                    },
                },
            ).then(response => {
                this.$toast(this.$t('sendSuccess'));
                this.$root.send.text = '';
            }).catch(error => {
                if (error.response && error.response.data.msg) {
                    this.$toast(this.$t('sendFailed', { msg: error.response.data.msg }));
                } else {
                    this.$toast(this.$t('sendFailedNoMsg'));
                }
            });
        },
    },
}
</script>