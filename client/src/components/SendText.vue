<template>
    <div>
        <div class="headline text--primary mb-4">发送文本</div>
        <v-textarea
            no-resize
            outlined
            dense
            rows="6"
            placeholder="请输入需要发送的文本"
            v-model="$root.send.text"
        ></v-textarea>
        <div class="text-right">
            <v-btn
                color="primary"
                :disabled="!$root.send.text || !$root.websocket"
                @click="send"
            >发送</v-btn>
        </div>
    </div>
</template>

<script>
export default {
    name: 'send-text',
    methods: {
        send() {
            this.$http.post('/text', this.$root.send.text, {headers: {'Content-Type': 'text/plain'}}).then(response => {
                this.$toast('发送成功');
                this.$root.send.text = '';
            }).catch(error => {
                if (error.response && error.response.data.msg) {
                    this.$toast(`发送失败：${error.response.data.msg}`);
                } else {
                    this.$toast('发送失败');
                }
            });
        },
    },
}
</script>