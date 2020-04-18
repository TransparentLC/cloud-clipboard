# cloud-clipboard

因为不想为了手机和电脑互传文件这种小事就扫🐴登录某个辣鸡 APP，而自己折腾出来的一个在线剪贴板。

* 支持**传输纯文本**和**一键复制**
* 支持**传输文件**
* 前端使用 [Vue](https://cn.vuejs.org) 和 [Vuetify](https://vuetifyjs.com/zh-Hans/) 构建
* 后端使用 [Swoole](https://www.swoole.com/) 构建

*仅供个人在连接到同一局域网（比如家里的路由器）的设备之间使用，如果放在公开的服务器上大概会出现各种奇怪的问题吧 \_(:зゝ∠)\_*

*以及……这东西本身大概就会有不少奇怪的 BUG \_(:зゝ∠)\_*

## 截图

<details>
<summary>桌面端</summary>

![](https://ae01.alicdn.com/kf/Hfce3a9b69b3d404c8e3073ab0fffa913v.png)

</details>

<details>
<summary>移动端</summary>

![](https://ae01.alicdn.com/kf/Hbf859dd0e42c4406bf94a6b6f2f4658cf.png)

</details>

## 部署

> TODO:
> 或许打一个 Phar 包会更简单……
> 这样就可以直接用 `php cloud-clipborad.phar` 执行了。

### 准备环境

需要安装了 Swoole 扩展的 PHP 运行环境。
* Linux / MacOS：参见 Swoole 文档的[安装说明](https://wiki.swoole.com/#/environment)
* Windows：使用官方提供的 [Cygwin 整合包](https://pan.baidu.com/s/15RodWdoIgwBLmG1I5HXzOg#list/path=%2Fsharelink2059756482-531706993208199%2Fswoole%2Fcygwin)，或者在 WSL(Windows Subsystem for Linux) 中安装运行环境（和 Linux 方法相同）

### 修改配置文件

读取的配置文件为 `server/config.json`，该文件**默认不存在**，需要从模板 `server/config.template.json` 复制再自行修改。

<details>
<summary>参数说明</summary>

```json
{
    "server": {
        "host": "192.168.1.136", // 服务端的 IP 地址
        "port": 9501, // 端口号
        "wss": false, // 使用 wss 协议而不是 ws 协议，一般不使用
        "storage": ".storage" // 临时文件存储路径
    },
    "text": {
        "limit": 4096 // 文本的长度限制
    },
    "file": {
        "expire": 3600000, // 上传的文件在经过一定时间后自动删除，单位为秒
        "chunk": 1048576, // 上传文件的分片大小，不能超过 5 MB，单位为 byte
        "limit": 104857600 // 上传文件的大小限制，单位为 byte
    }
}
```
</details>

### 打包前端资源

```bash
cd client
npm run build
```

### 运行

```bash
cd server
php main.php
```
