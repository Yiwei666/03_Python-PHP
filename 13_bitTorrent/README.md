# 1. 项目功能






# 2. 文件结构







# 3. Transmission-daemon安装和配置

### 1. 安装

```bash
sudo apt update
sudo apt install transmission-daemon
```

### 2. 常用命令以及alias

- alias

```bash
alias trl="transmission-remote -n 'transmission:123456' -l"
alias trp="service transmission-daemon stop"
alias trs="service transmission-daemon start"
alias lsd="ls /var/lib/transmission-daemon/downloads"
alias cdd='cd /var/lib/transmission-daemon/downloads'
alias lsj="cat /etc/transmission-daemon/settings.json"
```

- 命令行

```bash
transmission-remote -t all --remove-and-delete
transmission-remote -n 'transmission:123456' -t all --remove-and-delete   # 删除所有的种子
transmission-remote -t all --remove
transmission-remote -t 2 --remove-and-delete
transmission-remote -n 'transmission:123456' -t 1 --remove-and-delete
transmission-remote -n 'username:password' -t 2 --remove-and-delete
```

### 3. 更改配置

需要先stop，修改完成后再start，running过程中修改配置无效。

```bash
sudo service transmission-daemon stop

sudo service transmission-daemon start
```

⭐ 重点修改参数

```json
"rpc-password": "{8916cecbd4d607ecc2e4bd842e3fdb9f54b3918bOZtGPoh0",     // 初始化密码，设置明文，start后自动加密
"rpc-whitelist-enabled": false,                                          // 由true改为false

"rpc-whitelist": "127.0.0.1",                                            // 白名单中只有本地回环地址
"rpc-whitelist-enabled": true,                                           // 开启白名单，只允许本地回环地址以及限定ip通过web界面访问
```


- Transmission-daemon 配位置文件 `/etc/transmission-daemon/settings.json`


```json
{
    "alt-speed-down": 50,
    "alt-speed-enabled": false,
    "alt-speed-time-begin": 540,
    "alt-speed-time-day": 127,
    "alt-speed-time-enabled": false,
    "alt-speed-time-end": 1020,
    "alt-speed-up": 50,
    "bind-address-ipv4": "0.0.0.0",
    "bind-address-ipv6": "::",
    "blocklist-enabled": false,
    "blocklist-url": "http://www.example.com/blocklist",
    "cache-size-mb": 4,
    "dht-enabled": true,
    "download-dir": "/var/lib/transmission-daemon/downloads",
    "download-limit": 100,
    "download-limit-enabled": 0,
    "download-queue-enabled": true,
    "download-queue-size": 5,
    "encryption": 1,
    "idle-seeding-limit": 30,
    "idle-seeding-limit-enabled": false,
    "incomplete-dir": "/var/lib/transmission-daemon/Downloads",
    "incomplete-dir-enabled": false,
    "lpd-enabled": false,
    "max-peers-global": 200,
    "message-level": 1,
    "peer-congestion-algorithm": "",
    "peer-id-ttl-hours": 6,
    "peer-limit-global": 200,
    "peer-limit-per-torrent": 50,
    "peer-port": 51413,
    "peer-port-random-high": 65535,
    "peer-port-random-low": 49152,
    "peer-port-random-on-start": false,
    "peer-socket-tos": "default",
    "pex-enabled": true,
    "port-forwarding-enabled": false,
    "preallocation": 1,
    "prefetch-enabled": true,
    "queue-stalled-enabled": true,
    "queue-stalled-minutes": 30,
    "ratio-limit": 2,
    "ratio-limit-enabled": false,
    "rename-partial-files": true,
    "rpc-authentication-required": true,
    "rpc-bind-address": "0.0.0.0",
    "rpc-enabled": true,
    "rpc-host-whitelist": "",
    "rpc-host-whitelist-enabled": true,
    "rpc-password": "{8916cecbd4d607ecc2e4bd842e3fdb9f54b3918bOZtGPoh0",
    "rpc-port": 9091,
    "rpc-url": "/transmission/",
    "rpc-username": "transmission",
    "rpc-whitelist": "127.0.0.1",
    "rpc-whitelist-enabled": false,
    "scrape-paused-torrents-enabled": true,
    "script-torrent-done-enabled": false,
    "script-torrent-done-filename": "",
    "seed-queue-enabled": false,
    "seed-queue-size": 10,
    "speed-limit-down": 100,
    "speed-limit-down-enabled": false,
    "speed-limit-up": 100,
    "speed-limit-up-enabled": false,
    "start-added-torrents": true,
    "trash-original-torrent-files": false,
    "umask": 18,
    "upload-limit": 100,
    "upload-limit-enabled": 0,
    "upload-slots-per-torrent": 14,
    "utp-enabled": true
}
```