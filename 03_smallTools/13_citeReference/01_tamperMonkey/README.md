# 1. 项目功能

使用Tampermonkey脚本，将从 Google Scholar 页面中提取的 GB/T 7714 和 APA 引用格式进行动态合并，并生成新的参考文献格式

# 2. 文件结构

```bash
01_GBT_APA.js              # 仅从页面上获取GBT和APA格式的参考文献，未合成新格式文献
01_GBT_APA_doi.js          # 在01_GBT_APA.js基础上通过CrossRef API新增doi和title查询
02_elsevier.js             # 基于获取的GBT和APA格式参考文献，转换成适用于elsevier期刊的格式，但是期刊缩写采用脚本内硬编码实现
03_api_cros.js             # 脚本通过指定的远程URL获取期刊简称字典，用于将期刊全称转换为简称。使用 GM_xmlhttpRequest 解决跨域请求
03_api_cros_debug.js       # 在03_api_cros.js基础上优化，包括不打印所有期刊名称、新增复制按钮、优化页面字体和布局
04_api_cros_doi.js         # 在 03_api_cros_debug.js 基础上新增doi查询、核验，生成带有doi格式的参考文献
```

# 3. 环境配置

## 1. `01_GBT_APA.js`

- 仅显示 GB/T 和 APA格式参考文献，且不支持复制粘贴


## 2. `01_GBT_APA_doi.js`

### 1. 功能特性

1. 在01_GBT_APA.js基础上通过CrossRef API新增doi和title查询。

2. 基于 GB/T 7714 格式参考文献提取论文标题，与CrossRef API返回的标题进行相似度分析，显示匹配结果，从而判断 DOI 的准确性。
    - 上述相似度计算算法基于 Levenshtein 距离，通过计算提取标题和查询标题之间的最小编辑操作次数，并将相似度（`1 - 编辑距离 / 最大字符串长度`）与设定的阈值（`0.8`）比较来判断是否匹配成功。
    - 这种算法的特点是 灵活处理字符串之间的轻微差异（如拼写、格式），但对长字符串的效率较低且无法捕捉语义相似性。



### 2. 提取结果示例

- 测试结果1：

```txt
GB/T 7714: Katayama Y, Mizutani T, Utsumi W, et al. A first-order liquid–liquid phase transition in phosphorus[J]. Nature, 2000, 403(6766): 170-173.

APA: Katayama Y, Mizutani T, Utsumi W, et al. A first-order liquid–liquid phase transition in phosphorus[J]. Nature, 2000, 403(6766): 170-173.

提取的文章标题: A first-order liquid–liquid phase transition in phosphorus

DOI 查询结果
DOI: 10.1038/35003143

标题: A first-order liquid–liquid phase transition in phosphorus

匹配结果: 匹配成功
```





## 3. `02_elsevier.js`

### 1. 功能特性

- 使用脚本内硬编码获取`期刊全称和简写`
- 合成elsevier出版社期刊参考文献格式
- APA格式作者名字部分中的空格和"..."处理未考虑，例如`K. I. Funakoshi`和`M. ...  Fiorentini`



### 2. 提取结果示例

- 测试结果1：

```txt
GB/T 7714 引用: Katayama Y, Mizutani T, Utsumi W, et al. A first-order liquid–liquid phase transition in phosphorus[J]. Nature, 2000, 403(6766): 170-173.
APA 引用: Katayama, Y., Mizutani, T., Utsumi, W., Shimomura, O., Yamakata, M., & Funakoshi, K. I. (2000). A first-order liquid–liquid phase transition in phosphorus. Nature, 403(6766), 170-173.
文章标题 (string2): A first-order liquid–liquid phase transition in phosphorus
卷、出版年和页码范围 (string3): 2000, 403(6766): 170-173
格式化的出版信息 (string4): 403 (2000) 170-173.
期刊全称 (string5): Nature
期刊简称或全称 (string6): Nature
APA 作者部分 (authorParts): Katayama,Y.,Mizutani,T.,Utsumi,W.,Shimomura,O.,Yamakata,M.,Funakoshi,K. I.
重排后的作者名 (string7): Y. Katayama, T. Mizutani, W. Utsumi, O. Shimomura, M. Yamakata, K. I. Funakoshi, 
最终合并的新格式参考文献 (result3): Y. Katayama, T. Mizutani, W. Utsumi, O. Shimomura, M. Yamakata, K. I. Funakoshi, A first-order liquid–liquid phase transition in phosphorus, Nature 403 (2000) 170-173.
```

- 测试结果2：

```
GB/T 7714 引用: Schettino E, González-Jiménez J M, Marchesi C, et al. Mantle-to-crust metal transfer by nanomelts[J]. Communications Earth & Environment, 2023, 4(1): 256.
APA 引用: Schettino, E., González-Jiménez, J. M., Marchesi, C., Palozza, F., Blanco-Quintero, I. F., Gervilla, F., ... & Fiorentini, M. (2023). Mantle-to-crust metal transfer by nanomelts. Communications Earth & Environment, 4(1), 256.
文章标题 (string2): Mantle-to-crust metal transfer by nanomelts
卷、出版年和页码范围 (string3): 2023, 4(1): 256
格式化的出版信息 (string4): 4 (2023) 256.
期刊全称 (string5): Communications Earth & Environment
期刊简称或全称 (string6): Communications Earth & Environment
APA 作者部分 (authorParts): Schettino,E.,González-Jiménez,J. M.,Marchesi,C.,Palozza,F.,Blanco-Quintero,I. F.,Gervilla,F.,...  Fiorentini,M.
重排后的作者名 (string7): E. Schettino, J. M. González-Jiménez, C. Marchesi, F. Palozza, I. F. Blanco-Quintero, F. Gervilla, M. ...  Fiorentini, 
最终合并的新格式参考文献 (result3): E. Schettino, J. M. González-Jiménez, C. Marchesi, F. Palozza, I. F. Blanco-Quintero, F. Gervilla, M. ...  Fiorentini, Mantle-to-crust metal transfer by nanomelts, Communications Earth & Environment 4 (2023) 256.
```




## 4. `03_api_cros.js`


### 1. 脚本特性

1. 浏览器会对网络请求的内容进行缓存，可能导致脚本没有获取最新的数据

2. 在请求 URL 中添加一个随机数或时间戳，确保每次请求的 URL 都唯一，从而绕过缓存



### 2. 提取结果示例



### 3. 环境变量

```js
const journalAbbreviationURL = 'http://39.105.186.182/06_journal_Abbreviation.txt';
```

1. Tampermonkey 设置界面的 XHR 安全 选项与用户脚本中使用的 `XMLHttpRequest（XHR）` 和相关跨域请求的安全性有关。它的功能是控制脚本在发起跨域请求时的权限和行为，确保安全性和兼容性。

2. Tampermonkey 的 XHR 安全选项可以限制哪些跨域请求被允许，哪些会被阻止。如下所示，可以将自己服务器的IP或者域名添加到跨域的白名单中，否则通过 GM_xmlhttpRequest 发起跨域请求会被阻止并提示错误。

<p align="center">
<img src="https://19640810.xyz/05_image/01_imageHost/20241130-163414.png" alt="Image Description" width="1000">
</p>




## 5. `03_api_cros_debug.js`

### 1. 新增功能

1. 新增一个按钮，点击后能够 复制 result3 变量内容，以便我可以在其他地方粘贴
2. 在页面中不打印期刊字典，避免可能占据过多的页面空空间
3. 优化页面显示的参考文献等字体格式，以便美观



### 2. 提取结果示例

```

```



## 6. `04_api_cros_doi.js`

### 1. 功能特性

### 2. 提取结果示例

```

```






