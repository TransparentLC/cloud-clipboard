<template>
    <div>
        <div class="headline text--primary mb-4">发送文件</div>
        <v-card outlined class="pa-4 mb-6 d-flex">
            <template v-if="$root.send.file">
                <div class="flex-grow-1">
                    <div>{{$root.send.file.name}}</div>
                    <div class="caption">{{$root.send.file.size | prettyFileSize}}</div>
                </div>
                <div class="align-self-center">
                    <v-btn icon color="grey" @click="$root.send.file = null">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </div>
            </template>
            <template v-else>
                <v-btn
                    text
                    color="primary"
                    class="d-block mx-auto"
                    @click="$refs.selectFile.click()"
                >选择要发送的文件</v-btn>
                <input ref="selectFile" type="file" class="d-none" @change="$root.send.file = $event.target.files[0]">
            </template>
        </v-card>
        <div class="text-right">
            <v-btn color="primary" :disabled="!$root.send.file">发送</v-btn>
        </div>
    </div>
</template>
<script>
export default {
    name: 'send-file',
}
</script>