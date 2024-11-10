<template>
    <div>
        <div class="headline text--primary mb-4">{{ $t('sendFile') }}</div>
        <v-card
            outlined
            class="pa-3 mb-6 d-flex flex-row align-center"
            @dragenter="$event.preventDefault()"
            @dragover="$event.preventDefault()"
            @dragleave="$event.preventDefault()"
            @drop="$event.preventDefault(); handleSelectFiles(Array.from($event.dataTransfer.files))"
        >
            <template v-if="$root.send.files.length">
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
                        <div
                            class="text-truncate"
                            :title="$root.send.files[0].name + ' ' + ($root.send.files.length > 1 ? `等 ${$root.send.files.length} 个文件` : '')"
                        >{{$root.send.files[0].name}} {{$root.send.files.length > 1 ? `等 ${$root.send.files.length} 个文件` : ''}}
                        </div>
                        <div class="caption">{{fileSize | prettyFileSize}}</div>
                    </div>
                    <div class="align-self-center">
                        <v-btn icon color="grey" @click="$root.send.files.splice(0)">
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
                    <div :title="$t('supportDragAndPaste')">
                        {{ $t('selectFilesToSend') }}<span class="d-none d-xl-inline">（{{ $t('supportDragAndPaste') }}）</span>
                        <br>
                        <small class="text--secondary">{{ $t('fileSizeLimit') }}: {{ $root.config.file.limit | prettyFileSize }}</small>
                    </div>
                </v-btn>
                <input
                    ref="selectFile"
                    type="file"
                    class="d-none"
                    multiple
                    @change="handleSelectFiles(Array.from($event.target.files))"
                >
            </template>
        </v-card>
        <div class="text-right">
            <v-btn
                color="primary"
                :block="$vuetify.breakpoint.smAndDown"
                :disabled="!$root.send.files.length || !$root.websocket || progress"
                @click="send"
            >{{ $t('send') }}</v-btn>
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
            uploadedSizes: [],
            imagePreview: '',
            uploading: false,
            mdiClose,
        };
    },
    computed: {
        fileSize() {
            return this.$root.send.files.length ? this.$root.send.files.reduce((acc, cur) => acc += cur.size, 0) : 0;
        },
        uploadedSize() {
            return this.uploadedSizes.length ? this.uploadedSizes.reduce((acc, cur) => acc += cur, 0) : 0;
        },
        uploadProgress() {
            return Math.min(this.fileSize !== 0 ? (this.uploadedSize / this.fileSize) : 0, 1);
        },
        isUploadingImage() {
            return this.$root.send.files.length && this.$root.send.files[0].type.startsWith('image/');
        },
    },
    methods: {
        /**
         * @param {File[]} files
         */
        handleSelectFiles(files) {
            if (files.some(e => !e.size)) {
                this.$toast(this.$t('cannotSendEmptyFile'));
            } else if (files.some(e => e.size > this.$root.config.file.limit)) {
                this.$toast(`${this.$t('fileSizeExceedsLimit')}（${prettyFileSize(this.$root.config.file.limit)}）`);
            } else {
                this.$root.send.files.splice(0);
                this.$root.send.files.push(...files);
                if (this.isUploadingImage) {
                    URL.revokeObjectURL(this.imagePreview);
                    this.imagePreview = URL.createObjectURL(files[0]);
                }
            }
        },
        async send() {
            try {
                const chunkSize = this.$root.config.file.chunk;
                this.uploadedSizes.splice(0);
                this.uploadedSizes.push(...Array(this.$root.send.files.length).fill(0));
                await Promise.all(this.$root.send.files.map(async (file, i) => {
                    let response = await this.$http.post('upload', file.name, {headers: {'Content-Type': 'text/plain'}});
                    let uuid = response.data.result.uuid;

                    let uploadedSize = 0;
                    this.progress = true;
                    while (uploadedSize < file.size) {
                        let chunk = file.slice(uploadedSize, uploadedSize + chunkSize);
                        await this.$http.post(`upload/chunk/${uuid}`, chunk, {
                            headers: {'Content-Type': 'application/octet-stream'},
                            onUploadProgress: e => this.$set(this.uploadedSizes, i, uploadedSize + e.loaded),
                        });
                        uploadedSize += chunkSize;
                    }
                    await this.$http.post(`upload/finish/${uuid}`, null, {
                        params: new URLSearchParams([['room', this.$root.room]]),
                    });
                }));
                this.$toast(this.$t('sendSuccess'));
                this.$root.send.files.splice(0);
            } catch (error) {
                if (error.response && error.response.data.msg) {
                    this.$toast(`${this.$t('sendFailed')}: ${error.response.data.msg}`);
                } else {
                    this.$toast(this.$t('sendFailedNoMsg'));
                }
            } finally {
                this.progress = false;
            }
        }
    },
    mounted() {
        document.onpaste = e => {
            if (!(e && e.clipboardData)) return;
            console.log(e.clipboardData);
            const items = Array.from(e.clipboardData.items);
            if (!(items.length && items.every(e => e.kind === 'file'))) return;
            this.handleSelectFiles(items.map(e => e.getAsFile()));
        };
    },
}
</script>