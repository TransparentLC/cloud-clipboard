export default {
    data() {
        return {
            websocket: null,
            retry: 0,
            event: {
                receive: data => {
                    this.$root.received.unshift(data);
                },
            },
        };
    },
    methods: {
        connect() {
            this.$toast(this.retry ? `未能连接到服务器，正在尝试第 ${this.retry} 次重连……` : '正在连接服务器……', {
                showClose: false,
                dismissable: false,
                timeout: 0,
            });
            this.$http.get('/ws-url.txt', {responseType: 'text'}).then(response => {
                return new Promise((resolve, reject) => {
                    let ws = new WebSocket(response.data);
                    ws.onopen = () => {resolve(ws)};
                    ws.onerror = reject;
                });
            }).then(ws => {
                this.retry = 0;
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
                this.failure();
            });
        },
        failure() {
            this.websocket = null;
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