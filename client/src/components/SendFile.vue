<template>
    <div>
        <div class="headline text--primary mb-4">发送文件</div>
        <v-card outlined class="pa-4 mb-6 d-flex" @drop="handleSelectFile($event.dataTransfer.files[0])">
            <template v-if="$root.send.file">
                <template v-if="progress">
                    <div class="flex-grow-1">
                        <div class="text-right text--secondary">
                            {{Math.min(uploadedSize, fileSize) | prettyFileSize}} / {{fileSize | prettyFileSize}} ({{uploadProgress | percentage}})
                        </div>
                        <v-progress-linear :value="uploadProgress * 100"></v-progress-linear>
                    </div>
                </template>
                <template v-else>
                    <div class="flex-grow-1 mr-2" style="min-width: 0">
                        <div class="text-truncate" :title="$root.send.file.name">{{$root.send.file.name}}</div>
                        <div class="caption">{{$root.send.file.size | prettyFileSize}}</div>
                    </div>
                    <div class="align-self-center">
                        <v-btn icon color="grey" @click="$root.send.file = null">
                            <v-icon>{{mdiClose}}</v-icon>
                        </v-btn>
                    </div>
                </template>
            </template>
            <template v-else>
                <v-btn
                    text
                    color="primary"
                    large
                    class="d-block mx-auto"
                    @click="$refs.selectFile.click()"
                >
                    <div>
                        选择要发送的文件<br><span class="hidden-sm-and-down text--secondary">（支持拖拽）</span>
                    </div>
                </v-btn>
                <input
                    ref="selectFile"
                    type="file"
                    class="d-none"
                    @change="handleSelectFile($event.target.files[0])"
                >
            </template>
        </v-card>
        <div class="text-right">
            <v-btn
                color="primary"
                :block="$vuetify.breakpoint.smAndDown"
                :disabled="!$root.send.file || !$root.websocket"
                @click="send"
            >发送</v-btn>
        </div>
    </div>
</template>

<script>
import {
    mdiClose,
} from '@mdi/js';

export default {
    name: 'send-file',
    data() {
        return {
            progress: false,
            uploadedSize: 0,
            mdiClose,
        };
    },
    computed: {
        fileSize() {
            return this.$root.send.file ? this.$root.send.file.size : 0;
        },
        uploadProgress() {
            return Math.min(this.fileSize !== 0 ? (this.uploadedSize / this.fileSize) : 0, 1);
        },
    },
    methods: {
        handleSelectFile(file) {
            if (file.size) {
                this.$root.send.file = file;
            } else {
                this.$toast('不能发送空文件');
            }
        },
        async send() {
            const chunkSize = 49152; // 48KB
            let file = this.$root.send.file;
            try {
                let response = await this.$http.post('/upload', file.name, {headers: {'Content-Type': 'text/plain'}});
                let uuid = response.data.result.uuid;

                this.uploadedSize = 0;
                this.progress = true;
                while (this.uploadedSize < file.size) {
                    let chunk = file.slice(this.uploadedSize, this.uploadedSize + chunkSize);
                    this.uploadedSize += chunkSize;
                    await this.$http.post(`/upload/chunk/${uuid}`, chunk, {headers: {'Content-Type': 'application/octet-stream'}});
                }
                await this.$http.post(`/upload/finish/${uuid}`);
                this.$toast('发送成功');
                this.$root.send.file = null;
            } catch (error) {
                if (error.response && error.response.data.msg) {
                    this.$toast(`发送失败：${error.response.data.msg}`);
                } else {
                    this.$toast('发送失败');
                }
            } finally {
                this.progress = false;
            }
        }
    },
}
</script>