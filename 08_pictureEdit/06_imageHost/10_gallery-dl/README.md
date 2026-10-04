# 1. 项目功能

使用本地 socks5 代理，基于 gallery-dl  能否下载x（前身twitter）指定账号在指定日期范围内的高清原图


# 2. 文件结构

```
01_rename_twitter_imgs_gallery-dl.py         # 图片重命名，按照 新名称: YYYYMMDD-HHmmss-<子文件夹名/x账号>-<6位随机数字/小写字母>.jpg
```



# 3.  环境配置


## 1. gallery-dl 安装

### 1. 创建/激活虚拟环境

- 如果之前创建过 [iopaint_env](https://github.com/Yiwei666/12_blog/blob/main/888/8-008.md) 虚拟环境，可以使用该环境。

```cmd
conda env list
conda activate iopaint_env
```

- 也可以重新创建一个虚拟环境 `gallery_env`

```cmd
conda create -n gallery_env python=3.11 -y
conda activate gallery_env
```

### 2. gallery-dl 安装

`gallery-dl` 是一个命令行工具，用于从各种图像托管网站（如 Pixiv、DeviantArt、Danbooru、Twitter、Instagram 等）下载图像及其相关元数据。它支持大量站点，能够自动解析网页内容、按作者、标签、专辑等结构化方式批量下载资源。

```cmd
python -m pip install -U gallery-dl
python -m pip install PySocks
```


1. 核心依赖（通常已包含或自动安装）：

   - `Python 3.4+`: gallery-dl 运行所需的基础编程语言环境。
   - `Requests`: Python HTTP 库，用于发送网络请求并获取网页内容。


2. 可选依赖及其作用：

   - `yt-dlp` 或 `youtube-dl`：用于支持 HLS/DASH 视频下载，并提供 ytdl 集成。这意味着如果 gallery-dl 遇到视频内容，它可以使用这两个库来处理视频流的下载。yt-dlp 是 youtube-dl 的一个活跃维护的分支，通常推荐使用它。

   - `FFmpeg`：用于 Pixiv Ugoira 动画的转换。Ugoira 是 Pixiv 特有的一种动图格式，FFmpeg 可以将其转换为更常见的视频格式（如 WebM）。

   - `mkvmerge`：用于 精确的 Ugoira 帧时间码。与 FFmpeg 结合使用时，可以确保转换后的 Ugoira 视频具有更准确的帧时间信息。

   - `PySocks`：提供 SOCKS 代理支持。如果您需要通过 SOCKS 代理访问网络来下载内容，则需要安装此库。

   - `brotli` 或 `brotlicffi`：支持 Brotli 压缩。一些网站可能使用 Brotli 算法压缩其内容，安装这些库可以更好地处理此类压缩。

   - `zstandard`：支持 `Zstandard` 压缩。与 Brotli 类似，用于处理使用 Zstandard 算法压缩的内容。

   - `PyYAML`：提供 YAML 配置文件支持。虽然 gallery-dl 默认使用 JSON 格式的配置文件，但如果您希望使用 YAML 格式来编写配置文件，则需要此库。

   - `toml` (适用于 Python < 3.11)：提供 TOML 配置文件支持。如果您希望使用 TOML 格式来编写配置文件（在 Python 3.11 之前的版本中），则需要此库。

   - `SecretStorage`：用于 GNOME 密钥环密码，特别是当您使用 --cookies-from-browser 选项从浏览器中提取 cookie 时。在某些 Linux 环境下，浏览器将密码存储在密钥环中，此库可以帮助 gallery-dl 访问这些密码来解密 cookie。

   - `Psycopg`：提供 PostgreSQL 归档支持。如果您希望将下载记录或元数据存储到 PostgreSQL 数据库中进行管理，则需要此库。

总结来说，虽然 gallery-dl 自身可以独立运行完成基本下载任务，但安装这些可选库可以极大地扩展其功能，例如支持视频下载、处理特殊格式、使用代理、处理不同压缩格式以及更方便地管理认证和数据归档。






## 2. 导出 x 账号 cookie

`Get cookies.txt LOCALLY` 0.7.0 是一款开源的浏览器插件，专注于将 Cookie 导出为 Netscape 或 JSON 格式，适用于 Chrome 和 Firefox 等浏览器。

注意：限制该插件在指定网站使用，使用后应关闭，避免 cookies 等敏感信息泄露




## 3. 下载命令（powershell）

powershell 执行如下命令


- 指定账号全部下载

```sh
gallery-dl   --cookies "D:\software\27_nodejs\gallery-dl\x.com_cookies.txt"   --proxy "socks5://127.0.0.1:1080"     https://twitter.com/Japantravelco/media
```



- 指定时间范围（推荐使用该命令）

```sh
gallery-dl --cookies "D:\software\27_nodejs\gallery-dl\x.com_cookies.txt" --proxy "socks5://127.0.0.1:1080" --filter "date >= datetime(2025, 4, 12) and date < datetime(2025, 5, 29)"   https://twitter.com/username/media
```


- 指定时间范围 & 输出日志
 
```sh
gallery-dl --cookies "D:\software\27_nodejs\gallery-dl\x.com_cookies.txt" --proxy "socks5://127.0.0.1:1080" --filter "date >= datetime(2025, 1, 27) and date < datetime(2025, 5, 28)" -v https://twitter.com/Japantravelco/media > download_log.txt 2>&1
```



## 4. 图片重命名

### 1. 编程思路

`D:\software\27_nodejs\gallery-dl\gallery-dl\twitter`  目录下有多个子文件夹，每个子文件中有多张 jpg 图片，请编写一个python脚本，对子文件夹中的图片进行重命名，命名方式为 `YYYYMMDD-HHmmss-对应子文件夹的名字-6位数字字母随机字符串.jpg`   其中 `YYYYMMDD-HHmmss` 指的是图片创建的具体时间`年月日-时分秒`，6位数字字母随机字符串是由阿拉伯数字和26个英文小写字母随机组成。如果文件夹中的图片已经采用该格式命名了，则跳过对图片的重命名。一个命名的参考示例为：`20250518-135453-supperrocky-i908rd.jpg` 


### 2. `01_rename_twitter_imgs_gallery-dl.py`

- 环境变量

```py
# === 1. 修改为你的 twitter 根目录 ===
ROOT = Path(r"D:\software\27_nodejs\gallery-dl\gallery-dl\twitter")
```


# 4. SOCKS代理、DNS解析与`routeOnly`排障小结

## 1. 问题现象与结论

Windows 使用 `gallery-dl` 通过 Xray VLESS 下载 Twitter/X 图片时，普通 SOCKS5 代理曾反复出现如下错误：

```text
SSLError: UNEXPECTED_EOF_WHILE_READING
```

测试结果如下：

- 客户端采用 `socks5://127.0.0.1:1080` 且 `routeOnly: true` 时下载失败；
- 改用 `socks5h://127.0.0.1:1080` 或 `http://127.0.0.1:8080` 后下载正常；
- 普通 SOCKS5 保持不变，但将xray客户端改为 `routeOnly: false` 后下载正常；
- 服务器是否保留 `geosite:twitter` 到 WARP 的分流不影响上述结果。

由此可知，问题的关键不是 VLESS、REALITY 或 WARP 本身，而是普通 SOCKS5 先使用 Windows 本地 DNS 得到 IP，而该 IP 可能受到 DNS 污染，也可能只是 CDN 返回了不适合 VPS 出口的节点。`routeOnly` 也不直接指定使用哪个 DNS，它决定的是：嗅探到 HTTP Host 或 TLS SNI 域名后，是否用该域名替换当前连接目标。

## 2. DNS解析和流量路径

三种代理写法的主要区别如下：

| 代理写法 | 域名解析位置 | Xray最初收到的目标 |
|---|---|---|
| `socks5://` | Windows 本地 | IP 地址 |
| `socks5h://` | Xray代理链路/服务器端 | 原始域名 |
| `http://` | HTTPS 通过 `CONNECT 域名:443` 提交 | 原始域名 |

旧 V2Ray 客户端配置没有列出 `routeOnly`，其默认值相当于 `false`，因此普通 SOCKS5 可以通过 TLS SNI 将 IP 恢复为域名：

```text
旧 V2Ray + socks5
    ↓
Windows 本地 DNS 将域名解析成 IP
    ↓
V2Ray 从 TLS SNI 嗅探出原始域名
    ↓
routeOnly=false：用域名替换 IP
    ↓
VMess 将域名传给服务器重新解析
    ↓
正常下载
```

当前 Xray 客户端显式使用 `routeOnly: true` 时，嗅探域名只用于路由，不能修正本地 DNS 结果：

```text
Xray VLESS + socks5 + routeOnly=true
    ↓
Windows 本地 DNS 将域名解析成 IP
    ↓
Xray 从 TLS SNI 嗅探出原始域名
    ↓
域名只参与路由，实际目标仍为原 IP
    ↓
服务器继续连接该 IP
    ↓
TLS EOF，图片下载失败
```

改用 SOCKS5H 后，域名从一开始就进入代理链路，不再依赖嗅探重写：

```text
Xray VLESS + socks5h
    ↓
SOCKS 请求直接携带原始域名
    ↓
VLESS 将域名传给服务器
    ↓
服务器端 DNS 解析并选择目标 IP
    ↓
正常下载
```

HTTP 代理的原理与 SOCKS5H 类似。HTTPS 使用 `CONNECT 域名:443`，所以 Xray 最初得到的就是域名，`routeOnly: true` 也不会造成这次问题。

假设程序使用普通 SOCKS5，Windows 已经将域名解析为 IP，同时 TLS SNI 中仍包含正确域名，客户端与服务器端的四种组合如下：

| 客户端 | 服务器端 | 实际结果 |
|---|---|---|
| `false` | `true` | 客户端把 IP 重写为域名并通过 VLESS 发送；服务器尊重该域名并重新解析。推荐组合。 |
| `false` | `false` | 客户端已经发送域名，服务器通常不需要再次改写；嗅探结果不一致时可能覆盖客户端目标。 |
| `true` | `true` | 两端都只将嗅探域名用于路由，最终仍连接 Windows 解析的 IP，无法修复本地 DNS 或 CDN 节点问题。 |
| `true` | `false` | 客户端保留 IP，服务器再次嗅探并重写为域名，属于服务端兜底方案。 |

服务器端使用 `routeOnly: true` 仍有独立意义：即使客户端提交的是 IP，服务器也可以根据嗅探域名匹配 `geosite` 规则，选择直连、WARP 或 Tor 出站，同时尊重客户端明确提交的目标地址。因此推荐采用“客户端 `false`、服务器端 `true`”的职责分工。

## 3. 配置调整与下载命令

Windows 客户端为了兼容只能使用普通 SOCKS5、会先在本地解析域名的程序，可以将 SOCKS 入站调整为：

```jsonc
"sniffing": {
  "enabled": true,
  "destOverride": ["http", "tls"],
  "routeOnly": false
}
```

服务器端建议保留：

```jsonc
"sniffing": {
  "enabled": true,
  "destOverride": ["http", "tls", "quic"],
  "routeOnly": true
}
```

使用普通 SOCKS5；适合客户端已经设置 `routeOnly: false`，或者能够保证 Windows 本地 DNS 正确的情况：

```powershell
gallery-dl --cookies "D:\software\27_nodejs\gallery-dl\x.com_cookies.txt" --proxy "socks5://127.0.0.1:1080" --filter "date >= datetime(2026, 6, 1 + 1) and date <= datetime(2026, 10, 2 + 1)" "https://twitter.com/Immortal_047/media"
```

使用 SOCKS5H；直接把域名交给 Xray，最适合绕过本地 DNS 问题：

```powershell
gallery-dl --cookies "D:\software\27_nodejs\gallery-dl\x.com_cookies.txt" --proxy "socks5h://127.0.0.1:1080" --filter "date >= datetime(2026, 6, 1 + 1) and date <= datetime(2026, 10, 2 + 1)" "https://twitter.com/Immortal_047/media"
```

使用 HTTP 代理；HTTPS 的 `CONNECT` 请求会直接携带域名：

```powershell
gallery-dl --cookies "D:\software\27_nodejs\gallery-dl\x.com_cookies.txt" --proxy "http://127.0.0.1:8080" --filter "date >= datetime(2026, 6, 1 + 1) and date <= datetime(2026, 10, 2 + 1)" "https://twitter.com/Immortal_047/media"
```

综合可靠性和兼容性，支持远程域名解析的程序优先使用 SOCKS5H 或 HTTP 代理；普通 SOCKS5 则由客户端 `routeOnly: false` 作为兼容补救。服务器端只有在需要为配置不统一的客户端提供二次 DNS 纠正时，才考虑使用 `routeOnly: false`，因为它可能影响 Hosts、内网 DNS、固定 CDN 节点以及目标 IP 与 SNI 有意不一致的连接。

需要注意，`routeOnly: false` 依赖 HTTP/TLS 等流量嗅探，不能完全替代代理 DNS；ECH、非 TLS 协议或无法识别的流量仍可能保留原始 IP。




# 参考资料

- [gallery-dl官方github项目](https://github.com/mikf/gallery-dl/tree/master)





