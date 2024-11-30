# 1. 项目功能

使用Tampermonkey脚本，将从 Google Scholar 页面中提取的 GB/T 7714 和 APA 引用格式进行动态合并，并生成新的参考文献格式

# 2. 文件结构

```
01_GBT_APA.js        # 仅从页面上获取GBT和APA格式的参考文献，未合成新格式文献
02_elsevier.js       # 基于获取的GBT和APA格式参考文献，转换成适用于elsevier期刊的格式，但是期刊缩写采用脚本内硬编码实现
03_api_cros.js       # 脚本通过指定的远程URL获取期刊简称字典，用于将期刊全称转换为简称。使用 GM_xmlhttpRequest 解决跨域请求
```

# 3. 环境配置
