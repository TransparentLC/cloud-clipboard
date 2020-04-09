<template>
    <div>
        <div class="headline text--primary mb-4">发送文本</div>
        <v-textarea
            no-resize
            outline
            dense
            rows="6"
            placeholder="请输入需要发送的文本"
            v-model="$root.send.text"
        ></v-textarea>
        <div class="text-right">
            <v-btn
                color="primary"
                :disabled="!$root.send.text"
            >发送</v-btn>
        </div>
    </div>
</template>

<script>
export default {
    name: 'send-text',
}
</script>