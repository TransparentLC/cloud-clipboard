# cloud-clipboard

因为不想为了手机和电脑互传文件这种小事就扫🐴登录某个辣鸡 APP，而自己折腾出来的一个在线剪贴板。

* 支持**传输纯文本**和**一键复制**
* 支持**传输文件**
* 前端使用 [Vue](https://cn.vuejs.org) 和 [Vuetify](https://vuetifyjs.com/zh-Hans/) 构建
* 后端使用 [Swoole](https://www.swoole.com/) 构建

*仅供个人在连接到同一局域网（比如家里的路由器）的设备之间使用，如果放在公开的服务器上大概会出现各种奇怪的问题吧 \_(:зゝ∠)\_*

<details>
<summary><em>以及……这东西本身大概就会有不少奇怪的 BUG \_(:зゝ∠)\_</em></summary>

比如说低概率出现的这种错误，我暂时也不知道是怎么回事（摊手）

```text
WARNING swReactorEpoll_set(:178): reactor#0->set(fd=15|type=0|events=5) failed, Error: No such file or directory
段错误 (核心已转储)
```

</details>

## 截图

<details>
<summary>桌面端</summary>

![](https://ae01.alicdn.com/kf/Hfce3a9b69b3d404c8e3073ab0fffa913v.png)

</details>

<details>
<summary>移动端</summary>

![](https://ae01.alicdn.com/kf/Hbf859dd0e42c4406bf94a6b6f2f4658cf.png)

</details>

## 使用方法

### 准备环境

需要安装了 Swoole 扩展的 PHP 运行环境。

* Windows：使用官方在百毒网盘上提供的 [Cygwin 整合包](https://pan.baidu.com/s/15RodWdoIgwBLmG1I5HXzOg#list/path=%2Fsharelink2059756482-531706993208199%2Fswoole%2Fcygwin)（或者从[这里](https://files.catbox.moe/wz2ktt.zip)下载我自己搬运的 4.5.0 RC1 版本），也可以在 WSL(Windows Subsystem for Linux) 中安装运行环境
* Linux：参见 Swoole 文档的[安装说明](https://wiki.swoole.com/#/environment)

在命令行中输入 `php --ri swoole`，可以输出配置信息就代表准备完成了～

### 安装和运行

~~实际上也不能叫安装，毕竟下载之后就可以直接用了~~

1. 前往 [Release](https://github.com/TransparentLC/cloud-clipboard/releases) 下载最新的 `cloud-clipboard.phar`。
2. 在同一目录新建配置文件 `config.json`（可以参见下面的说明），**一定要根据实际修改 IP 地址！**
3. `php cloud-clipboard.phar`
4. 打开 `http://192.168.1.136:9501`（需要替换为在配置文件中**实际设定的地址和端口**）即可使用～

<details>
<summary>配置文件说明</summary>

`//` 开头的部分是注释，**并不需要写入配置文件中**，否则会导致读取失败。

```json
{
    "server": {
        "host": "192.168.1.136", // 服务端的 IP 地址或域名
        "port": 9501, // 端口号
        "wss": false, // 使用 wss 协议而不是 ws 协议，一般不修改
        "history": 10 // 消息历史记录的数量
    },
    "text": {
        "limit": 4096 // 文本的长度限制
    },
    "file": {
        "expire": 3600, // 上传文件的有效期，超过有效期后自动删除，单位为秒
        "chunk": 1048576, // 上传文件的分片大小，不能超过 5 MB，单位为 byte
        "limit": 104857600 // 上传文件的大小限制，单位为 byte
    }
}
```

> 查看本机 IP 地址的方法：
>
> * Windows：任务管理器 -> 性能 -> 以太网 / Wi-Fi -> IPv4 地址
> * Linux：`ifconfig | grep inet`
>
> IP 地址可能有多个，需要选择**当前正在使用的** IP 地址。

</details>

### 从源代码运行

需要安装 [Vue CLI](https://cli.vuejs.org/zh/guide/installation.html) 和 [Composer](https://getcomposer.org/download/)。

```bash
cd client
npm install
npm run build
cd ../server
composer install --no-dev

# 从源代码直接运行
php main.php

# 生成 Phar
cd ..
php build-phar.php
```
