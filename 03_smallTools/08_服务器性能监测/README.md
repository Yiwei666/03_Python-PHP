### 项目功能
---
- 监测服务器性能，如内存，CPU，硬盘，数据使用量等等


### 项目结构
---
```
├── 07_VPS_monitor.php
├── 07_server_data.php

```


### 部署环境
---

- 07_VPS_monitor.php

需要通过以下代码指定服务器后端监测性能的脚本

```
fetch('07_server_data.php')
```

- 07_server_data.php

针对不同操作系统，后端获取性能数据的命令会略有不同，注意修改

### 待完成
---

- 实时监测云服务器的网速
- 脚本能够兼容ubuntu和centos系统
