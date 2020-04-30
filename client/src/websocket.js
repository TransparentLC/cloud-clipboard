export default {
    data() {
        return {
            websocket: null,
            websocketConnecting: false,
            retry: 0,
            event: {
                receive: data => {
                    this.$root.received.unshift(data);
                },
                revoke: data => {
                    let index = this.$root.received.findIndex(e => e.id === data.id);
                    if (index === -1) return;
                    this.$root.received.splice(index, 1);
                },
                config: data => {
                    this.$root.config = data;
                    console.log(
                        `%c Cloud Clipboard ${data.version} by TransparentLC %c https://github.com/TransparentLC/cloud-clipboard `,
                        'color:#fff;background-color:#1e88e5',
                        'color:#fff;background-color:#64b5f6'
                    );
                },
                connect: data => {
                    this.$root.device.push(data);
                },
                disconnect: data => {
                    let index = this.$root.device.findIndex(e => e.id === data.id);
                    if (index === -1) return;
                    this.$root.device.splice(index, 1);
                },
            },
        };
    },
    methods: {
        connect() {
            this.websocketConnecting = true;
            this.$toast(this.retry ? `未能连接到服务器，正在尝试第 ${this.retry} 次重连……` : '正在连接服务器……', {
                showClose: false,
                dismissable: false,
                timeout: 0,
            });
            this.$http.get('/server', {responseType: 'text'}).then(response => {
                return new Promise((resolve, reject) => {
                    let ws = new WebSocket(response.data);
                    ws.onopen = () => {resolve(ws)};
                    ws.onerror = reject;
                });
            }).then(ws => {
                this.websocketConnecting = false;
                this.retry = 0;
                this.received = [];
                this.$toast('连接服务器成功');
                setInterval(() => {ws.send('')}, 60000);
                ws.onclose = () => {this.failure()};
                ws.onmessage = e => {
                    try {
                        let parsed = JSON.parse(e.data);
                        (this.event[parsed.event] || (() => {}))(parsed.data);
                    } catch {}
                };
                this.websocket = ws;
            }).catch(error => {
                // console.log(error);
                this.websocketConnecting = false;
                this.failure();
            });
        },
        failure() {
            this.websocket = null;
            this.$root.device = [];
            if (this.retry++ < 3) {
                this.connect();
            } else {
                this.$toast.error('连接服务器失败，请点击工具栏上的“连接”图标重试', {
                    showClose: false,
                    dismissable: false,
                    timeout: 0,
                });
            }
        },
    },
    mounted() {
        this.connect();
    },
}