<template>
    <div>
        <div class="headline text--primary mb-4">发送文件</div>
        <v-card
            outlined
            class="pa-3 mb-6 d-flex flex-row align-center"
            @dragenter="$event.preventDefault()"
            @dragover="$event.preventDefault()"
            @dragleave="$event.preventDefault()"
            @drop="$event.preventDefault(); handleSelectFile($event.dataTransfer.files[0])"
        >
            <template v-if="$root.send.file">
                <template v-if="progress">
                    <div class="flex-grow-1">
                        <small class="d-block text-right text--secondary">
                            {{Math.min(uploadedSize, fileSize) | prettyFileSize}} / {{fileSize | prettyFileSize}} ({{uploadProgress | percentage}})
                        </small>
                        <v-progress-linear :value="uploadProgress * 100"></v-progress-linear>
                    </div>
                </template>
                <template v-else>
                    <v-img
                        v-if="isUploadingImage"
                        :src="imagePreview"
                        class="mr-3 flex-grow-0"
                        width="2.5rem"
                        height="2.5rem"
                        style="border-radius: 3px"
                    ></v-img>
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
                    <div title="支持拖拽和 Ctrl+V 粘贴截图">
                        选择要发送的文件<span class="d-none d-xl-inline">（支持拖拽和 Ctrl+V 粘贴截图）</span>
                        <br>
                        <small class="text--secondary">文件大小限制：{{$root.config.file.limit | prettyFileSize}}</small>
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
    prettyFileSize,
}from '@/util.js';
import {
    mdiClose,
} from '@mdi/js';

export default {
    name: 'send-file',
    data() {
        return {
            progress: false,
            uploadedSize: 0,
            imagePreview: '',
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
        isUploadingImage() {
            return this.$root.send.file && this.$root.send.file.type.startsWith('image/');
        },
    },
    methods: {
        handleSelectFile(file) {
            if (!file.size) {
                this.$toast('不能发送空文件');
            } else if (file.size > this.$root.config.file.limit) {
                this.$toast(`文件大小超过限制（${prettyFileSize(this.$root.config.file.limit)}）`);
            } else {
                this.$root.send.file = file;
                if (this.isUploadingImage) {
                    URL.revokeObjectURL(this.imagePreview);
                    this.imagePreview = URL.createObjectURL(file);
                }
            }
        },
        async send() {
            try {
                const chunkSize = this.$root.config.file.chunk;
                let file = this.$root.send.file;
                let response = await this.$http.post('/upload', file.name, {headers: {'Content-Type': 'text/plain'}});
                let uuid = response.data.result.uuid;

                let uploadedSize = 0;
                this.uploadedSize = 0;
                this.progress = true;
                while (this.uploadedSize < file.size) {
                    let chunk = file.slice(this.uploadedSize, this.uploadedSize + chunkSize);
                    await this.$http.post(`/upload/chunk/${uuid}`, chunk, {
                        headers: {'Content-Type': 'application/octet-stream'},
                        onUploadProgress: e => {this.uploadedSize = uploadedSize + e.loaded},
                    });
                    uploadedSize += chunkSize;
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
    mounted() {
        document.onpaste = e => {
            if (!(e && e.clipboardData && e.clipboardData.items[0] && e.clipboardData.items[0].kind === 'file')) return;
            this.handleSelectFile(e.clipboardData.items[0].getAsFile());
        };
    },
}
</script>