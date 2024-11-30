# 1. 项目功能

使用Tampermonkey脚本，将从 Google Scholar 页面中提取的 GB/T 7714 和 APA 引用格式进行动态合并，并生成新的参考文献格式

# 2. 文件结构

```
01_GBT_APA.js        # 仅从页面上获取GBT和APA格式的参考文献，未合成新格式文献
02_elsevier.js       # 基于获取的GBT和APA格式参考文献，转换成适用于elsevier期刊的格式，但是期刊缩写采用脚本内硬编码实现
03_api_cros.js       # 脚本通过指定的远程URL获取期刊简称字典，用于将期刊全称转换为简称。使用 GM_xmlhttpRequest 解决跨域请求
```

# 3. 环境配置


### `03_api_cros.js`

- **环境变量**

```js
const journalAbbreviationURL = 'http://39.105.186.182/06_journal_Abbreviation.txt';
```


1. Tampermonkey 设置界面的 XHR 安全 选项与用户脚本中使用的 `XMLHttpRequest（XHR）` 和相关跨域请求的安全性有关。它的功能是控制脚本在发起跨域请求时的权限和行为，确保安全性和兼容性。

2. Tampermonkey 的 XHR 安全选项可以限制哪些跨域请求被允许，哪些会被阻止。如下所示，可以将自己服务器的IP或者域名添加到跨域的白名单中，否则通过 GM_xmlhttpRequest 发起跨域请求会被阻止并提示错误。

<p align="center">
<img src="https://19640810.xyz/05_image/01_imageHost/20241130-163414.png" alt="Image Description" width="1000">
</p>
