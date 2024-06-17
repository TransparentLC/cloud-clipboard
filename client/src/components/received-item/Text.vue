<template>
    <v-hover
        v-slot:default="{ hover }"
    >
        <v-card :elevation="hover ? 6 : 2" class="mb-2 transition-swing">
            <v-card-text>
                <div class="d-flex flex-row align-center">
                    <div class="flex-grow-1 mr-2" style="min-width: 0">
                        <div class="title text-truncate text--primary" @click="expand = !expand">
                            文本消息<v-icon>{{expand ? mdiChevronUp : mdiChevronDown}}</v-icon>
                        </div>
                        <div class="text-truncate" @click="expand = !expand" v-html="meta.content.trim()" v-linkified></div>
                    </div>

                    <div class="align-self-center text-no-wrap">
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn v-on="on" icon color="grey" @click="copyText">
                                    <v-icon>{{mdiContentCopy}}</v-icon>
                                </v-btn>
                            </template>
                            <span>复制</span>
                        </v-tooltip>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn v-on="on" icon color="grey" @click="deleteItem">
                                    <v-icon>{{mdiClose}}</v-icon>
                                </v-btn>
                            </template>
                            <span>删除</span>
                        </v-tooltip>
                    </div>
                </div>
                <v-expand-transition>
                    <div v-show="expand">
                        <v-divider class="my-2"></v-divider>
                        <div ref="content" v-html="meta.content.replace(/\n/g, '<br>')" v-linkified></div>
                    </div>
                </v-expand-transition>
            </v-card-text>
        </v-card>
    </v-hover>
</template>

<script>
import {
    mdiChevronUp,
    mdiChevronDown,
    mdiContentCopy,
    mdiClose,
} from '@mdi/js';

export default {
    name: 'received-text',
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
            mdiChevronUp,
            mdiChevronDown,
            mdiContentCopy,
            mdiClose,
        };
    },
    methods: {
        copyText() {
            let el = document.createElement('textarea');
            el.value = new DOMParser().parseFromString(this.meta.content, 'text/html').documentElement.textContent;
            el.style.cssText = 'top:0;left:0;position:fixed';
            document.body.appendChild(el);
            el.focus();
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            this.$toast('复制成功');
        },
        deleteItem() {
            this.$http.delete(`revoke/${this.meta.id}`, {
                params: new URLSearchParams([['room', this.$root.room]]),
            }).then(() => {
                this.$toast('已删除文本消息');
            }).catch(error => {
                if (error.response && error.response.data.msg) {
                    this.$toast(`消息删除失败：${error.response.data.msg}`);
                } else {
                    this.$toast('消息删除失败');
                }
            });
        },
    },
}
</script>