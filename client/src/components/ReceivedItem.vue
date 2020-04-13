<template>
    <v-hover
        v-slot:default="{ hover }"
    >
        <v-card :elevation="hover ? 6 : 2" class="mb-2">
            <v-card-text class="d-flex">
                <div class="flex-grow-1 mr-2" style="min-width: 0">
                    <template v-if="meta.type === 'text'">
                        <div class="title text-truncate text--primary">文本消息</div>
                        <div :class="{'text-truncate': $vuetify.breakpoint.smAndDown}" @click="expand = !expand">
                            {{meta.content}}
                        </div>
                        <v-expand-transition>
                            <div v-if="$vuetify.breakpoint.smAndDown" v-show="expand" @click="expand = !expand">
                                <v-divider class="my-2"></v-divider>
                                {{meta.content}}
                            </div>
                        </v-expand-transition>
                    </template>

                    <template v-if="meta.type === 'file'">
                        <div class="title text-truncate text--primary" :title="meta.name">{{meta.name}}</div>
                        <div class="caption">{{meta.size | prettyFileSize}}</div>
                    </template>
                </div>

                <div class="align-self-center text-no-wrap">
                    <v-tooltip v-if="meta.type === 'text'" bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" icon color="grey" @click="copyText">
                                <v-icon>mdi-content-copy</v-icon>
                            </v-btn>
                        </template>
                        <span>复制</span>
                    </v-tooltip>

                    <v-tooltip v-if="meta.type === 'file'" bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" icon color="grey" @click="getFile">
                                <v-icon>mdi-download</v-icon>
                            </v-btn>
                        </template>
                        <span>下载</span>
                    </v-tooltip>

                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" icon color="grey" @click="deleteItem">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </template>
                        <span>删除</span>
                    </v-tooltip>
                </div>
            </v-card-text>
        </v-card>
    </v-hover>
</template>

<script>
export default {
    name: 'received-item',
    props: {
        meta: {
            type: Object,
            default() {
                return {};
            },
        },
    },
    data() {
        return {
            expand: false,
        };
    },
    methods: {
        copyText() {
            let el = document.createElement('textarea');
            el.value = this.meta.content;
            el.style.cssText = 'top:0;left:0;position:fixed';
            document.body.appendChild(el);
            el.focus();
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            this.$toast('复制成功');
        },
        getFile() {
            this.$http.get(`/file/${this.meta.cache}`, {responseType: 'arraybuffer'}).then(response => {
                let blobURL = URL.createObjectURL(new Blob([response.data]));
                let cd = response.headers['content-disposition'];
                let el = document.createElement('a');
                el.href = blobURL;
                el.setAttribute('download', decodeURIComponent(cd.substring(cd.indexOf('"') + 1, cd.lastIndexOf('"'))));
                el.click();
                URL.revokeObjectURL(blobURL);
            }).catch(error => {
                if (error.response && error.response.data.msg) {
                    this.$toast(`文件获取失败：${error.response.data.msg}`);
                } else {
                    this.$toast('文件获取失败');
                }
            });

        },
        deleteItem() {
            this.$root.received.splice(this.$root.received.indexOf(this.meta), 1);
            switch (this.meta.type) {
                case 'text':
                    this.$toast('已删除文本消息');
                    break;
                case 'file':
                    this.$http.delete(`/file/${this.meta.cache}`).then(() => {
                        this.$toast(`已删除文件 ${this.meta.name}`);
                    }).catch(error => {
                        if (error.response && error.response.data.msg) {
                            this.$toast(`文件删除失败：${error.response.data.msg}`);
                        } else {
                            this.$toast('文件删除失败');
                        }
                    });
                    break;
                default:
                    break;
            }
        },
    },
}
</script>