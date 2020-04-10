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
            this.$http.get('/ws-url.txt', {responseType: 'text'}).then(response => {
                return new Promise((resolve, reject) => {
                    let ws = new WebSocket(response.data);
                    ws.onopen = () => {resolve(ws)};
                    ws.onerror = reject;
                });
            }).then(ws => {
                this.retry = 0;
                this.$toast('已连接到服务器');
                setInterval(() => {ws.send('')}, 60000);
                ws.send('');
                ws.onclose = () => {this.failure()};
                ws.onmessage = e => {
                    try {
                        let parsed = JSON.parse(e.data);
                        (this.event[parsed.event] || (() => {}))(parsed.data);
                    } catch {}
                };
            }).catch(error => {
                console.log(error);
                this.failure();
            });
        },
        failure() {
            this.websocket = null;
            if (this.retry++ < 3) {
                this.$toast(`未能连接到服务器，正在尝试第 ${this.retry} 次重连……`, {
                    showClose: false,
                    dismissable: false,
                    timeout: 0,
                });
                this.connect();
            } else {
                this.$toast.error('连接失败，请刷新页面重试', {
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