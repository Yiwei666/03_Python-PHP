# 1. 项目功能

1. 使用Tampermonkey脚本，将从 Google Scholar 页面中提取的 `GB/T 7714` 和 APA 引用格式进行动态合并，并生成新的参考文献格式
2. 在油猴脚本中跨域调用crossRef API获取指定论文的元数据，包括标题、期刊名、出版年、卷号、期号、页码、文章号、作者等信息
3. 在油猴脚本中跨域调用自有服务器中的外部资源，提供期刊全称-缩写的映射字典



# 2. 文件结构

### 1. 文件结构

```bash
01_GBT_APA.js              # 仅从页面上获取GBT和APA格式的参考文献，未合成新格式文献
01_GBT_APA_doi.js          # 在01_GBT_APA.js基础上通过CrossRef API新增doi和title查询
01_GBT_api_items.js        # 通过api返回的信息获取显示：标题、期刊名、出版年、卷号、期号、页码、文章号、作者等信息
01_crossRef_url.js         # 在01_GBT_api_items.js基础上新增显示期刊简写，简写是通过外部url资源加载获取的
01_crossRef_url_CEJ.js     # 基于01_crossRef_url.js合成 CEJ 参考文献格式，考虑期、文章号或者页码不存在的情况

# Elsevier
02_elsevier.js             # 基于获取的GBT和APA格式参考文献，转换成适用于elsevier期刊的格式，但是期刊缩写采用脚本内硬编码实现
03_api_cros.js             # 脚本通过指定的远程URL获取期刊简称字典，用于将期刊全称转换为简称。使用 GM_xmlhttpRequest 解决跨域请求
03_api_cros_debug.js       # 在03_api_cros.js基础上优化，包括不打印所有期刊名称、新增复制按钮、优化页面字体和布局
04_api_cros_doi.js         # 在 03_api_cros_debug.js 基础上新增doi查询、核验，生成带有doi格式的参考文献

# Springer
05_MMTB_doi.js             # 适用于MMTB期刊格式的参考文献生成，在04_api_cros_doi.js基础上修改，修改部分包括：使用and替代&，删除论文title，调整卷、年、页码/文章号顺序等
05_MMTB_api_author.js      # 使用 CrossRef API 返回的作者信息，来构建新格式参考文献的作者部分，解决 apa格式中的 and替代&、省略号"..."、given名中可能存在空格、作者不全等问题。
```

### 2. 顶刊ISSN

```
science: 0036-8075
nature: 0028-0836
Nature Materials: 1476-1122
PHYSICAL REVIEW LETTERS: 0031-9007
pnas: 0027-8424
CHEMICAL REVIEWS: 0009-2665
CHEMICAL SOCIETY REVIEWS: 	0306-0012
Journal of the American Chemical Society: 0002-7863
ANGEWANDTE CHEMIE-INTERNATIONAL EDITION: 1433-7851
JOURNAL OF CHEMICAL PHYSICS: 0021-9606
```



# 3. 参考文献查询API

## 1. 参考文献格式

### 1. Journal of molecular liquids

### 2. Metallurgical and Materials Transactions B



## 2. `crossRef API`文档

### 1. 常用网址

1. `crossRef API` 状态查询：[https://status.crossref.org/](https://status.crossref.org/)

2. `crossRef API` 说明文档：[https://api.crossref.org/swagger-ui/index.html](https://api.crossref.org/swagger-ui/index.html)


### 2. 注意事项

1. 查询链接构造：使用论文标题或参考文献

```
https://api.crossref.org/works?query=${encodeURIComponent(gbText)}
```

2. 使用doi号进行查询

```
https://api.crossref.org/works/{DOI}

# 使用礼貌池
https://api.crossref.org/works/{DOI}?mailto=your-email@example.com
https://api.crossref.org/works/10.1038/nature12373?mailto=xiaoming@example.com
```


### 3. 礼貌池

礼貌池是Crossref为那些遵循API使用礼仪的用户分配的一组专用API服务器。这些用户通过在API请求中提供联系信息（如电子邮件）并使用HTTPS协议进行访问，表明其使用API的方式是“礼貌”的，从而获得更好的服务质量和更高的优先级。

1. 如何使用礼貌池？

- 在查询参数中包含`mailto`参数：在您的API请求URL中添加一个`mailto`参数，提供一个有效的联系邮箱。例如：

```php
https://api.crossref.org/works?filter=has-full-text:true&mailto=your-email@example.com
https://api.crossref.org/works?query=${encodeURIComponent(gbText)}&mailto=your-email@example.com
```

- 在User-Agent头中包含mailto:联系信息：设置您的HTTP请求的User-Agent头，包含描述性的应用名称及联系邮箱。例如：

```php
User-Agent: YourAppName/1.0 (https://yourwebsite.com/; mailto:your-email@example.com)
```


2. 注意事项：

   - 使用HTTPS协议： 礼貌池仅适用于通过HTTPS协议进行的API请求。确保您的所有API请求都使用HTTPS，以符合礼貌池的使用条件。

   - 提供有效的联系信息： 提供的联系邮箱应为有效且可接收邮件的地址，以便Crossref在需要时能够联系到您。


3. 礼貌池的优势:
   
   - 更好的性能： 通过礼貌池，您的请求将被分配到专用的服务器池中，减少因其他用户的不当使用导致的性能波动。

   - 优先级管理： 礼貌池的用户在资源分配和请求处理上享有更高的优先级，确保在高负载情况下仍能获得稳定的服务。

   - 便于问题解决： 当出现API使用问题时，Crossref能够快速联系礼貌池用户，协助解决问题，减少服务中断时间。





# 4. 环境配置

# 1. `01_GBT_APA.js`

- 仅显示 GB/T 和 APA格式参考文献，且不支持复制粘贴





# 2.1 `01_GBT_APA_doi.js`

### 1. 功能特性

1. 在`01_GBT_APA.js`基础上通过`CrossRef API`新增doi和title查询。并显示doi号，基于CrossRef查询返回的`完整作者信息`和构造`缩写作者信息`。

2. 基于 `GB/T 7714` 格式参考文献提取论文标题，与`CrossRef API`返回的标题进行相似度分析，显示匹配结果，从而判断 DOI 的准确性。
    - 上述相似度计算算法基于 Levenshtein 距离，通过计算提取标题和查询标题之间的最小编辑操作次数，并将相似度（`1 - 编辑距离 / 最大字符串长度`）与设定的阈值（`0.8`）比较来判断是否匹配成功。
    - 这种算法的特点是 灵活处理字符串之间的轻微差异（如拼写、格式），但对长字符串的效率较低且无法捕捉语义相似性。

3. 新增一个校验，即判断通过CrossRef API查询到的title是否是 `GB/T 7714` 格式参考文献的一部分（标题部分），即判断查询到的title是否是准确的，从而确保获取的doi是正确的。有什么好的实现思路呢？是否需要使用模糊查询呢？严格匹配字符串似乎很容易出问题，因为可能会有一些格式问题。


- 注意：
    - 代码在进行 DOI 查询时，使用的是从网页上提取的 `GB/T 7714` 格式的完整引用字符串 `gbText` 作为查询参数，发送给 `CrossRef API` 进行搜索。
    - 仅用标题可能会导致检索到多个相同标题的文献，使用完整引用可减少这种情况。
    - 使用 `encodeURIComponent` 对引用字符串进行 URL 编码，构建查询 URL。
    - 期刊缩写查询网站：[https://cassi.cas.org/search.jsp](https://cassi.cas.org/search.jsp)
    - 可使用API的礼貌池（添加 `&mailto=email-username@domain.com`参数）来提升查询性能

```js
const apiUrl = `https://api.crossref.org/works?query=${encodeURIComponent(gbText)}`;
```

- 其他：
    - 注意：`https://scholar.google.com.hk/` 域名可能会有提示 "我们的系统检测到您的计算机网络中存在异常流量"，限制使用。
    - 但是 `https://scholar.google.com/`  不会限制，使用插件时要注意

- 谷歌学术全球所有子域名：

```js
// @match        https://scholar.google.com/*
// @match        https://scholar.google.com.hk/*
// @match        https://scholar.google.co.uk/*
// @match        https://scholar.google.de/*
// @match        https://scholar.google.fr/*
// @match        https://scholar.google.co.jp/*
// @match        https://scholar.google.ca/*
// @match        https://scholar.google.com.au/*
// @match        https://scholar.google.co.in/*
// @match        https://scholar.google.es/*
// @match        https://scholar.google.it/*
// @match        https://scholar.google.com.br/*
// @match        https://scholar.google.ru/*
// @match        https://scholar.google.nl/*
// @match        https://scholar.google.com.sg/*
// @match        https://scholar.google.com.mx/*
// @match        https://scholar.google.com.tr/*
// @match        https://scholar.google.com.ar/*
// @match        https://scholar.google.co.kr/*
// @match        https://scholar.google.se/*
// @match        https://scholar.google.ch/*
```




### 2. 编程思路

**关键思路一**：

新增显示基于 crossRef 查询返回的作者信息，按照如下格式显示

1. 对于每个作者，按照 `given+" "+family` 进行拼接，不同作者之间使用`", "`进行拼接 （完整作者信息）


能不能在上述代码基础上继续修改？（缩写作者信息）

1. 对于每一个作者的 `given名` 字符串，如果字符串中含有`空格`，则使用空格将given名分割成若干部分，对于每一部分`保留首字母并大写`并添加"."，然后再将这几部分拼接到一块作为缩写的given名

2. 如果有两个作者，则使用 `" and "` 进行拼接；如果有三个及以上的作者，最后两个作者之间使用 `", and "` 进行拼接，其余作者之间使用`", "`进行拼接



### 3. 提取结果示例

- 测试结果1：

```txt
提取结果
GB/T 7714: Katayama Y, Mizutani T, Utsumi W, et al. A first-order liquid–liquid phase transition in phosphorus[J]. Nature, 2000, 403(6766): 170-173.

APA: Katayama Y, Mizutani T, Utsumi W, et al. A first-order liquid–liquid phase transition in phosphorus[J]. Nature, 2000, 403(6766): 170-173.

提取的文章标题: A first-order liquid–liquid phase transition in phosphorus

DOI 查询结果
DOI: 10.1038/35003143

标题: A first-order liquid–liquid phase transition in phosphorus

完整作者信息: Yoshinori Katayama, Takeshi Mizutani, Wataru Utsumi, Osamu Shimomura, Masaaki Yamakata, Ken-ichi Funakoshi

缩写作者信息: Y. Katayama, T. Mizutani, W. Utsumi, O. Shimomura, M. Yamakata, and K. Funakoshi

匹配结果: 匹配成功
```





# 2.2 `01_GBT_api_items.js`

### 1. 功能特性

1. 通过api返回的信息获取显示：  标题、期刊名、出版年、卷号、期号、页码、文章号、作者等信息，后续脚本可以基于这些信息生成任意格式参考文献。
2. 新增显示出版商和ISSN（包括印刷版和电子版）。

3. 注意：CrossRef API 返回的元数据涵盖了学术文章的全面信息，
   - 包括标题、作者（含完整姓名和 ORCID 标识符）、期刊名、卷号、期号、页码、文章编号、DOI（数字对象标识符）及其标准链接（URL）。
   - 此外，还提供出版商名称、出版日期（在线或纸质）、ISSN/ISBN 编号、文章摘要、语言、学科分类、参考文献列表、文章类型（如期刊文章或会议论文）、基金资助信息（资助方名称和编号）、版权许可信息（含开放获取状态和许可证链接）以及文章关键字等。
   - 这些数据可进一步扩展到引用次数、文章标识符、全文链接及多语言标题等，确保对文献的详细描述与学术用途的支持。



### 2. 提取结果示例


```txt

提取结果

GB/T 7714: Wang T, Liu X, Sun Y, et al. Coordination of Zr4+/Hf4+/Nb5+/Ta5+ in silicate melts: insight from first principles molecular dynamics simulations[J]. Chemical Geology, 2020, 555: 119814.

APA: Wang T, Liu X, Sun Y, et al. Coordination of Zr4+/Hf4+/Nb5+/Ta5+ in silicate melts: insight from first principles molecular dynamics simulations[J]. Chemical Geology, 2020, 555: 119814.

提取的文章标题: Coordination of Zr4+/Hf4+/Nb5+/Ta5+ in silicate melts: insight from first principles molecular dynamics simulations
DOI 查询结果

DOI: 10.1016/j.chemgeo.2020.119814

标题: Coordination of Zr4+/Hf4+/Nb5+/Ta5+ in silicate melts: insight from first principles molecular dynamics simulations

期刊名: Chemical Geology

出版年: 2020

卷: 555

期: 未找到期号

页码: 119814

文章号: 119814

出版商: Elsevier BV

ISSN (印刷版): 0009-2541

ISSN (电子版): 未找到电子版 ISSN

完整作者信息: Tianhua Wang, Xiandong Liu, Yicheng Sun, Xiancai Lu, Rucheng Wang

缩写作者信息: T. Wang, X. Liu, Y. Sun, X. Lu, and R. Wang

匹配结果: 匹配成功
```




# 2.3 `01_crossRef_url.js`


### 1. 功能特性

保留`01_GBT_api_items.js`功能基础上，新增从外部url资源加载期刊全称-简称映射字典的功能，页面上新增显示期刊简写



### 2. 提取结果示例

```txt
提取结果
GB/T 7714: Nakajima K, Ohno H, Kondo Y, et al. Simultaneous material flow analysis of nickel, chromium, and molybdenum used in alloy steel by means of input–output analysis[J]. Environmental science & technology, 2013, 47(9): 4653-4660.

APA: Nakajima, K., Ohno, H., Kondo, Y., Matsubae, K., Takeda, O., Miki, T., ... & Nagasaka, T. (2013). Simultaneous material flow analysis of nickel, chromium, and molybdenum used in alloy steel by means of input–output analysis. Environmental science & technology, 47(9), 4653-4660.

提取的文章标题: Simultaneous material flow analysis of nickel, chromium, and molybdenum used in alloy steel by means of input–output analysis

DOI 查询结果
DOI: 10.1021/es3043559

标题: Simultaneous Material Flow Analysis of Nickel, Chromium, and Molybdenum Used in Alloy Steel by Means of Input–Output Analysis

期刊名: Environmental Science & Technology

期刊简称: Environ. Sci. Technol.

出版年: 2013

卷: 47

期: 9

页码: 4653-4660

文章号: 未找到文章号

完整作者信息: Kenichi Nakajima, Hajime Ohno, Yasushi Kondo, Kazuyo Matsubae, Osamu Takeda, Takahiro Miki, Shinichiro Nakamura, Tetsuya Nagasaka

缩写作者信息: K. Nakajima, H. Ohno, Y. Kondo, K. Matsubae, O. Takeda, T. Miki, S. Nakamura, and T. Nagasaka

匹配结果: 匹配成功
```



# 2.4 `01_crossRef_url_CEJ.js`

### 1. 功能特性

合成 Chemical Engineering Journal 参考文献格式，考虑期、文章号/页码不存在的情况 


### 2. 编程思路

请修改`01_crossRef_url.js`代码，新增一个显示（其余所有显示及相关代码均不变），该显示基于crossRef API 返回的信息进行合成，格式如下

```
缩写作者信息+", "+标题+", "+期刊简称+" "+卷+" ("+期+")"+" ("+出版年+") "+页码/文章号+", "+"https://doi.org/"+doi
```

注意：
1. 如果 缩写作者信息 字符串中有and，则删除掉其中的 `"and "` 字符部分。
2. 如果页码不存在，则使用文章号
3. 如果期号不存在，则不需要包含  `" ("+期+")"`   这一部分

非常好，请再新增一个按钮，点击该按钮能够复制 新合成的参考文献，以便我可以在其他地方粘贴


### 3. 提取结果示例

```txt
M. Zhang Y. Li, Breaking of Henry’s law for sulfide liquid–basaltic melt partitioning of Pt and Pd, Nat. Commun. 12 (1) (2021) 5994, https://doi.org/10.1038/s41467-021-26311-x

Z. Sun, Y. Ma, D. Ponge, S. Zaefferer, E.A. Jägle, B. Gault, A.D. Rollett, D. Raabe, Thermodynamics-guided alloy and process design for additive manufacturing, Nat. Commun. 13 (1) (2022) 4361, https://doi.org/10.1038/s41467-022-31969-y
```



# 3.1 `02_elsevier.js`

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

```txt
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




# 3.2 `03_api_cros.js`


### 1. 功能特性

1. 使用api或者url获取期刊全称/简写，避免脚本内`期刊全称和简写`硬编码的问题。

2. 浏览器会对网络请求的内容进行缓存，可能导致脚本没有获取最新的数据。在请求 URL 中添加一个随机数或时间戳，确保每次请求的 URL 都唯一，从而绕过缓存。

3. 由于同源策略的限制，使用 `GM_xmlhttpRequest` 解决api调用时的跨域问题。


### 2. 提取结果示例

```txt
GB/T 7714 引用: Bykova E, Bykov M, Černok A, et al. Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts[J]. Nature communications, 2018, 9(1): 4789.
APA 引用: Bykova, E., Bykov, M., Černok, A., Tidholm, J., Simak, S. I., Hellman, O., ... & Dubrovinsky, L. (2018). Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts. Nature communications, 9(1), 4789.
文章标题 (string2): Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts
卷、出版年和页码范围 (string3): 2018, 9(1): 4789
格式化的出版信息 (string4): 9 (2018) 4789.
期刊全称 (string5): Nature communications
期刊简称或全称 (string6): Nat. Commun.
APA 作者部分 (authorParts): Bykova,E.,Bykov,M.,Černok,A.,Tidholm,J.,Simak,S. I.,Hellman,O.,...  Dubrovinsky,L.
重排后的作者名 (string7): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S. I. Simak, O. Hellman, L. ...  Dubrovinsky, 
最终合并的新格式参考文献 (result3): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S. I. Simak, O. Hellman, L. ...  Dubrovinsky, Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts, Nat. Commun. 9 (2018) 4789.
```

注意：测试结果与 `02_elsevier.js` 测试结果格式一样，没有改变。




### 3. 环境变量

```js
const journalAbbreviationURL = 'http://39.105.186.182/06_journal_Abbreviation.txt';
```

1. Tampermonkey 设置界面的 XHR 安全 选项与用户脚本中使用的 `XMLHttpRequest（XHR）` 和相关跨域请求的安全性有关。它的功能是控制脚本在发起跨域请求时的权限和行为，确保安全性和兼容性。

2. Tampermonkey 的 XHR 安全选项可以限制哪些跨域请求被允许，哪些会被阻止。如下所示，可以将自己服务器的IP或者域名添加到跨域的白名单中，否则通过 GM_xmlhttpRequest 发起跨域请求会被阻止并提示错误。

<p align="center">
<img src="https://19640810.xyz/05_image/01_imageHost/20241130-163414.png" alt="Image Description" width="1000">
</p>





# 3.3 `03_api_cros_debug.js`

### 1. 新增功能

1. 新增一个按钮，点击后能够 复制 result3 变量内容，以便我可以在其他地方粘贴
2. 在页面中不打印期刊字典，避免可能占据过多的页面空空间
3. 优化页面显示的参考文献等字体格式，以便美观



### 2. 提取结果示例

```txt
GB/T 7714 引用: Bykova E, Bykov M, Černok A, et al. Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts[J]. Nature communications, 2018, 9(1): 4789.
APA 引用: Bykova, E., Bykov, M., Černok, A., Tidholm, J., Simak, S. I., Hellman, O., ... & Dubrovinsky, L. (2018). Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts. Nature communications, 9(1), 4789.
文章标题 (string2): Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts
卷、出版年和页码范围 (string3): 2018, 9(1): 4789
格式化的出版信息 (string4): 9 (2018) 4789.
期刊全称 (string5): Nature communications
期刊简称或全称 (string6): Nat. Commun.
APA 作者部分 (authorParts): Bykova,E.,Bykov,M.,Černok,A.,Tidholm,J.,Simak,S. I.,Hellman,O.,... Dubrovinsky,L.
重排后的作者名 (string7): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S. I. Simak, O. Hellman, L. ... Dubrovinsky,
最终合并的新格式参考文献 (result3): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S. I. Simak, O. Hellman, L. ... Dubrovinsky, Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts, Nat. Commun. 9 (2018) 4789.
```



# 4.1 `04_api_cros_doi.js`

### 1. 功能特性

1. 整合`01_GBT_APA_doi.js`代码功能，新增基于CrossRef API查询doi和title，并计算相似度。
2. 生成带有doi号格式的参考文献
3. 删除APA参考文献作者部分名字中的`空格`，使用`et al.`替代含有`"..."`的最后一项作者部分（注意，部分期刊可能要求列出所有作者信息）。

### 2. 编程思路

**思路一：**

在`04_api_cros_doi.js`代码v1版本基础上进行扩展，保持原有代码的逻辑、功能、样式等都不要变，新增如下功能：

1. 通过`CrossRef API`基于GB/T 7714 格式参考文献查询doi和title，参考`01_GBT_APA_doi.js`代码。

2. 计算参考文献中的论文标题（string2）与api返回的title的相似度，从而确保返回的doi是准确的。上面两个需求可以参考`01_GBT_APA_doi.js`代码的函数来实现（已验证有效性）。

3. 将获取的准确的doi进行字符串拼接，得到的字符串变量为 doiLink, 拼接格式为 `"https://doi.org/"+doi+"."`，然后 result3 字符串末尾的 "."删掉，与doiLink字符产进行拼接，二者之间使用 `", "` 进行衔接。获取的字符串为 result4，这也是合成的新格式的参考文献，在页面进行显示。另外，复制参考文献按钮对应的内容同步更新为result4。


**思路二：**

非常好，我想在有两个新的请求，在上述代码基础上进行修改扩展：

1. 新增显示api查询返回的title以及计算的相似度（以便人工审阅）

2. 对于string7进行格式检查和修正。对于 string7 变量，其中含有多个","，例如"M. Zhu, G. Wu, K. Tang, M. Müller, J. Safarian, "，或者 "J. Chen, C. Chen, M. Qin, B. Li, B. Lin, Q. Mao, Y. ... Wang, " ， 首先使用"," 进行分割得到多个子字符串，例如"J. Safarian"或者"Y. ... Wang"，如果分割后的子字符串不含"..."，则string7保持不变。如果含有"..."，则将该子字符串替换成"et al."，例如上述string7替换后为 "J. Chen, C. Chen, M. Qin, B. Li, B. Lin, Q. Mao, et al.," 将替换后的值重新赋值为 string7，后续使用的都是修改后的值。



### 3. 提取结果示例

- 测试结果1：已新增doi查询，生成含有doi的参考文献，但作者部分仍存在名字缩写间有空格、"..."未处理的问题。

```
信息: 正在加载期刊简称...
GB/T 7714 引用: Bykova E, Bykov M, Černok A, et al. Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts[J]. Nature communications, 2018, 9(1): 4789.
APA 引用: Bykova, E., Bykov, M., Černok, A., Tidholm, J., Simak, S. I., Hellman, O., ... & Dubrovinsky, L. (2018). Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts. Nature communications, 9(1), 4789.
文章标题 (string2): Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts
卷、出版年和页码范围 (string3): 2018, 9(1): 4789
格式化的出版信息 (string4): 9 (2018) 4789.
期刊全称 (string5): Nature communications
期刊简称或全称 (string6): Nat. Commun.
APA 作者部分 (authorParts): Bykova,E.,Bykov,M.,Černok,A.,Tidholm,J.,Simak,S. I.,Hellman,O.,... Dubrovinsky,L.
重排后的作者名 (string7): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S. I. Simak, O. Hellman, L. ... Dubrovinsky,
最终合并的新格式参考文献 (result3): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S. I. Simak, O. Hellman, L. ... Dubrovinsky, Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts, Nat. Commun. 9 (2018) 4789.
信息: 正在查询 DOI...
DOI 查询结果: 找到匹配的 DOI: 10.1038/s41467-018-07265-z
最终合成的新格式参考文献 (result4): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S. I. Simak, O. Hellman, L. ... Dubrovinsky, Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts, Nat. Commun. 9 (2018) 4789, https://doi.org/10.1038/s41467-018-07265-z.
```

- 测试结果2：空格和"..."问题已经解决

```
信息: 正在加载期刊简称...
GB/T 7714 引用: Bykova E, Bykov M, Černok A, et al. Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts[J]. Nature communications, 2018, 9(1): 4789.
APA 引用: Bykova, E., Bykov, M., Černok, A., Tidholm, J., Simak, S. I., Hellman, O., ... & Dubrovinsky, L. (2018). Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts. Nature communications, 9(1), 4789.
文章标题 (string2): Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts
卷、出版年和页码范围 (string3): 2018, 9(1): 4789
格式化的出版信息 (string4): 9 (2018) 4789.
期刊全称 (string5): Nature communications
期刊简称或全称 (string6): Nat. Commun.
APA 作者部分 (authorParts): Bykova,E.,Bykov,M.,Černok,A.,Tidholm,J.,Simak,S. I.,Hellman,O.,... & Dubrovinsky,L.
重排后的作者名 (string7): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S.I. Simak, O. Hellman, L. ...Dubrovinsky,
修正后的作者名 (string7): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S.I. Simak, O. Hellman, et al.,
最终合并的新格式参考文献 (result3): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S.I. Simak, O. Hellman, et al., Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts, Nat. Commun. 9 (2018) 4789.
信息: 正在查询 DOI...
API 返回的标题: Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts
计算的相似度: 100.00%
DOI 查询结果: 找到匹配的 DOI: 10.1038/s41467-018-07265-z
最终合成的新格式参考文献 (result4): E. Bykova, M. Bykov, A. Černok, J. Tidholm, S.I. Simak, O. Hellman, et al., Metastable silica high pressure polymorphs as structural proxies of deep Earth silicate melts, Nat. Commun. 9 (2018) 4789, https://doi.org/10.1038/s41467-018-07265-z.
```


# 4.2 `05_MMTB_doi.js`


### 1. 功能特性

相比于`04_api_cros_doi.js`脚本，主要做了以下调整：

1. 将 & 替换成 and
2. 删除掉文章标题名
3. 作者和期刊名之间使用 `": "`连接
4. 将 `"卷 （出版年） 页码"` 顺序调整为 `"出版年, vol. 卷, pp. 页码"`
5. 末尾添加doi号
6. 注意：当apa格式中省略部分作者信息时（"..."），最后一位作者会被`"et al."`替代。后续会使用 CrossRef API 返回的所有作者信息来解决这个问题。


### 2. 编程思路

在 `04_api_cros_doi.js` 基础上进行修改和扩展，下面是关键修改地方

1. 关键修改一：

```js
let string7 = reorderedAuthors.join(', ') + ', ';
```
能否修改上述代码，假如 reorderedAuthors 中作者数量等于2，两个作者使用`" and "`进行连接；如果作者数量大于2，对于倒数第一个和倒数第二个作者，使用`", and "`进行连接，其余作者之间连接仍保持为`', '`  例如  `"S.C. Duan, X.L. Guo, H.J. Guo and J. Guo,"` 
上述代码怎么修改最简单？


2. 关键修改二：

`E. Bykova, M. Bykov, A. Černok, J. Tidholm, S.I. Simak, O. Hellman, et al. Nat. Commun., 2018, vol. 9, pp. 4789, https://doi.org/10.1038/s41467-018-07265-z.`

对于上面这个参考文献，`"pp. 4789"` 部分使用正确吗，还是应该用`"p. 4789"`？这部分不应该是一个页码范围吗？

```js
const string4 = `${s1}, vol. ${s2}, pp. ${s4}.`;
```
能否修改上述代码，新增一个判断，如果s4中含有`"-"`，则 （适用于有页码范围的情况）

```js
const string4 = `${s1}, vol. ${s2}, pp. ${s4}.`; 
```

否则（适用于仅有文章号，没有页码的情况）

```js
const string4 = `${s1}, vol. ${s2}, ${s4}.`;
```



### 3. 提取结果示例

- 测试结果1：

```txt
信息: 正在加载期刊简称...
GB/T 7714 引用: Katayama Y, Mizutani T, Utsumi W, et al. A first-order liquid–liquid phase transition in phosphorus[J]. Nature, 2000, 403(6766): 170-173.
APA 引用: Katayama, Y., Mizutani, T., Utsumi, W., Shimomura, O., Yamakata, M., & Funakoshi, K. I. (2000). A first-order liquid–liquid phase transition in phosphorus. Nature, 403(6766), 170-173.
文章标题 (string2): A first-order liquid–liquid phase transition in phosphorus
卷、出版年和页码范围 (string3): 2000, 403(6766): 170-173
格式化的出版信息 (string4): 2000, vol. 403, pp. 170-173.
期刊全称 (string5): Nature
期刊简称或全称 (string6): Nature
APA 作者部分 (authorParts): Katayama,Y.,Mizutani,T.,Utsumi,W.,Shimomura,O.,Yamakata,M.,& Funakoshi,K. I.
重排后的作者名 (string7): Y. Katayama, T. Mizutani, W. Utsumi, O. Shimomura, M. Yamakata, and K.I. Funakoshi:
最终合并的新格式参考文献 (result3): Y. Katayama, T. Mizutani, W. Utsumi, O. Shimomura, M. Yamakata, and K.I. Funakoshi: Nature, 2000, vol. 403, pp. 170-173.
信息: 正在查询 DOI...
API 返回的标题: A first-order liquid–liquid phase transition in phosphorus
计算的相似度: 100.00%
DOI 查询结果: 找到匹配的 DOI: 10.1038/35003143
最终合成的新格式参考文献 (result4): Y. Katayama, T. Mizutani, W. Utsumi, O. Shimomura, M. Yamakata, and K.I. Funakoshi: Nature, 2000, vol. 403, pp. 170-173, https://doi.org/10.1038/35003143.
```


# 4.3 `05_MMTB_api_author.js`

### 1. 功能特性

- 使用 CrossRef API 返回的作者信息构建新格式的作者部分，解决 apa格式中的 `and替代&`、省略号`"..."`、given名中可能存在空格、作者不全（et al.）等问题。


### 2. 编程思路

**关键思路一：**

我认为`05_MMTB_doi.js`代码中，从apa格式中提取作者信息，构建 string7 的过程不够优雅，我想到了另外一种更好的方式：使用CrossRef返回的作者信息来构建 string7。

1. 对于CrossRef返回的每个作者，按照 given+" "+family 进行拼接，不同作者之间使用", "进行拼接。

2. 对于每一个作者的 given名 字符串，如果字符串中含有空格，则使用空格将given名分割成若干部分，对于每一部分保留首字母并大写并添加"."，然后再将这几部分拼接到一块作为缩写的given名。

3. 如果有两个作者，则使用 " and " 进行拼接；如果有三个及以上的作者，最后两个作者之间使用 ", and " 进行拼接，其余作者之间使用", "进行拼接。

基于上述方式来获取 string7，其余代码部分不要变，只修改string7 的构建方式


### 3. 提取结果示例

```txt
信息: 正在加载期刊简称...
GB/T 7714 引用: Suer T A, Siebert J, Remusat L, et al. Reconciling metal–silicate partitioning and late accretion in the Earth[J]. Nature Communications, 2021, 12(1): 2913.
APA 引用: Suer, T. A., Siebert, J., Remusat, L., Day, J. M., Borensztajn, S., Doisneau, B., & Fiquet, G. (2021). Reconciling metal–silicate partitioning and late accretion in the Earth. Nature Communications, 12(1), 2913.
文章标题 (string2): Reconciling metal–silicate partitioning and late accretion in the Earth
卷、出版年和页码范围 (string3): 2021, 12(1): 2913
格式化的出版信息 (string4): 2021, vol. 12, 2913.
期刊全称 (string5): Nature Communications
期刊简称或全称 (string6): Nat. Commun.
初步合并的新格式参考文献 (result3): Nat. Commun., 2021, vol. 12, 2913.
信息: 正在查询 DOI...
API 返回的标题: Reconciling metal–silicate partitioning and late accretion in the Earth
计算的相似度: 100.00%
DOI 查询结果: 找到匹配的 DOI: 10.1038/s41467-021-23137-5
构建的作者字符串 (string7): T. Suer, J. Siebert, L. Remusat, J.M.D. Day, S. Borensztajn, B. Doisneau, and G. Fiquet:
最终合成的新格式参考文献 (result): T. Suer, J. Siebert, L. Remusat, J.M.D. Day, S. Borensztajn, B. Doisneau, and G. Fiquet: Nat. Commun., 2021, vol. 12, 2913, https://doi.org/10.1038/s41467-021-23137-5.
```


















