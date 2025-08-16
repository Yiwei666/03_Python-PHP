# 1. 项目功能

1. 完成 hathitrust 网站上多页 pdf 的自动下载


# 2. 文件结构

```js  
01_hathitrust_pdf_download.js              # 完成 hathitrust 网站上多页 pdf 的自动下载
```




# 3. 环境配置


## 1. `01_hathitrust_pdf_download.js`


### 1. 编程思路


请编写一个油猴脚本，实现以下功能，具体分析和需求如下：

1. 如下这些页面中存在一个 `Download` 按钮，点击该按钮会下载一个pdf文件。

```
https://babel.hathitrust.org/cgi/pt?id=hvd.hc54vb&seq=1
...
https://babel.hathitrust.org/cgi/pt?id=hvd.hc54vb&seq=5
```

2. 请编写一个油猴脚本，在 `hathitrust.org` 页面显示一个 `自动下载` 按钮，实现一键下载 seq=1-5对应的pdf。首先加载 seq=1 的页面，加载成功后，模拟点击 `Download` 按钮，会在原来的 `Download` 按钮下方再出现一个 `Download` 按钮，模拟点击新出现的第 2 个下方的 `Download` 按钮，可实现pdf下载；然后加载第2页，再模拟点击`Download`按钮，出现第二个`Download`按钮后，在模拟点击进行下载，以此类推。注意：只有点击第二个 `Download` 按钮 才能下载，且第二个 `Download` 按钮需要第一个 `Download` 按钮点击后才会出现。先下载1-5页，每次加载间隔1秒钟，避免对网站过载。

3. 如果我想把上述代码抽象成一个可复用的而不是硬编码的，即针对不同的 seq 范围，起始页为 page_start，终止页为 page_end，网址的 id 字符串可以自定义，第二次点击后多等的时间也可以自定义，这些可以初始化的参量放在代码开始部分。

输出满足上述需求的完整篡改候脚本。



### 2. 环境变量

```js
  // ====== 可配置参数 ======
  const CONFIG = {
    // 指定书籍 id；为 null 表示使用当前 URL 的 id 参数
    bookId: "hvd.hc54vb",                     // 例: "hvd.hc54vb"
    // 下载页码范围
    pageStart: 121,
    pageEnd: 127,
    // 第二次 Download 点击后停留等待（避免请求被取消）
    waitAfterSecondMs: 3500,
    // 翻页之间的基础间隔
    gapBetweenPagesMs: 1500,
    // 优先用 GM_download 后台下载；若为 false 则只模拟点击
    useGMDownload: true,
    // 文件名模板（仅 GM_download 生效）
    filenamePattern: '{id}_seq{seq}.pdf',
    // 显示调试日志
    debug: false,
  };
```

注意修改 `bookId, pageStart, pageEnd` 这3个参数




