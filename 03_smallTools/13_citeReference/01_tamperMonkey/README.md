# 1. 项目功能

使用Tampermonkey脚本，将从 Google Scholar 页面中提取的 GB/T 7714 和 APA 引用格式进行动态合并，并生成新的参考文献格式

# 2. 文件结构

```bash
01_GBT_APA.js              # 仅从页面上获取GBT和APA格式的参考文献，未合成新格式文献
01_GBT_APA_doi.js          # 在01_GBT_APA.js基础上通过CrossRef API新增doi和title查询
02_elsevier.js             # 基于获取的GBT和APA格式参考文献，转换成适用于elsevier期刊的格式，但是期刊缩写采用脚本内硬编码实现
03_api_cros.js             # 脚本通过指定的远程URL获取期刊简称字典，用于将期刊全称转换为简称。使用 GM_xmlhttpRequest 解决跨域请求
03_api_cros_debug.js       # 在03_api_cros.js基础上优化，包括不打印所有期刊名称、新增复制按钮、优化页面字体和布局
```

# 3. 环境配置

## 1. `01_GBT_APA.js`


## 2. `01_GBT_APA_doi.js`

### 1. 功能

1. 在01_GBT_APA.js基础上通过CrossRef API新增doi和title查询。

2. 基于 GB/T 7714 格式参考文献提取论文标题，与CrossRef API返回的标题进行相似度分析，显示匹配结果，从而判断 DOI 的准确性。


- 上述相似度计算算法基于 Levenshtein 距离，通过计算提取标题和查询标题之间的最小编辑操作次数，并将相似度（1 - 编辑距离 / 最大字符串长度）与设定的阈值（0.8）比较来判断是否匹配成功。

- 这种算法的特点是 灵活处理字符串之间的轻微差异（如拼写、格式），但对长字符串的效率较低且无法捕捉语义相似性。



## 3. `02_elsevier.js`





## 4. `03_api_cros.js`

### 1. 环境变量

```js
const journalAbbreviationURL = 'http://39.105.186.182/06_journal_Abbreviation.txt';
```

1. Tampermonkey 设置界面的 XHR 安全 选项与用户脚本中使用的 `XMLHttpRequest（XHR）` 和相关跨域请求的安全性有关。它的功能是控制脚本在发起跨域请求时的权限和行为，确保安全性和兼容性。

2. Tampermonkey 的 XHR 安全选项可以限制哪些跨域请求被允许，哪些会被阻止。如下所示，可以将自己服务器的IP或者域名添加到跨域的白名单中，否则通过 GM_xmlhttpRequest 发起跨域请求会被阻止并提示错误。

<p align="center">
<img src="https://19640810.xyz/05_image/01_imageHost/20241130-163414.png" alt="Image Description" width="1000">
</p>


### 2. 注意事项

1. 浏览器会对网络请求的内容进行缓存，可能导致脚本没有获取最新的数据

2. 在请求 URL 中添加一个随机数或时间戳，确保每次请求的 URL 都唯一，从而绕过缓存



## 5. `03_api_cros_debug.js`

### 1. 新增功能

1. 新增一个按钮，点击后能够 复制 result3 变量内容，以便我可以在其他地方粘贴
2. 在页面中不打印期刊字典，避免可能占据过多的页面空空间
3. 优化页面显示的参考文献等字体格式，以便美观











