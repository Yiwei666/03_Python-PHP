# 1. 项目功能

使用 tampermonkey 对携程、美团等酒店后台进行管理

# 2. 文件结构

```
01_ctrip.js            # 携程后台订单信息提取
02_meituan.js          # 美团后台订单信息提取
```


# 3. 环境配置

### 1. `01_ctrip.js`

1. 功能

该脚本用于携程后台订单管理页面，自动提取订单信息（如客人姓名、下单网站、房间信息、总金额、佣金率、房间定价等），在页面右侧实时显示，并提供“复制信息”按钮，方便用户一键复制整理好的数据，提高工作效率。

2. 页面显示示例

```
客人姓名: 王锤
下单网站: 去哪儿
预订客房: 标准双床房<双早>
住宿日期: 2024/11/23 - 2024/11/24
入住星期: 周六 - 周日
间数: 1
天数: 1
总间夜数: 1
客人付款: 292.00
预到账: 262.80
平台佣金: 29.20
佣金率: 10.0%
房间定价: 292.0
```

3. 使用方法：
    - 信息提取按钮只会在包含 `https://ebooking.ctrip.com/` 网址的页面中显示。
    - 打开携程的订单处理页面，在包含上述示例信息的页面操作即可。
    - 点击复制按钮可以一键复制。



