# 1. 项目功能

打印 美团/携程 等预定信息

# 2. 文件结构

```
37_hotel_booking.php           # 打印美团/携程/美团民宿预订信息
38_hotel_price.php             # 计算不同房型折扣前价格
```


# 3. 环境配置

### 1. `37_hotel_booking.php`

💎 **功能：**

1. 平台：下拉栏包括 `携程，美团酒店，美团民宿`
2. 姓名：需要输入
2. 入住日期：默认 `“当前月日”~“当前月日+1天”`，下拉栏包括 `当前月日”~“当前月日+1天”，当前月日+1天”~“当前月日+2天”`
4. 到账金额：需要输入
5. 房型：下拉栏包括：`大床房（不带餐），标准大床房（含早），标准双床房（含早），轻奢大床房（含早晚），轻奢双床房（含早晚），豪华双床房（含早中晚）`
6. 间数：下拉栏包括 `1间1晚，1间2晚，2间1晚，2间2晚，3间1晚`
7. 备注：需要输入
8. 佣金率：如果 平台是 `“美团酒店”，佣金率为 12%`，如果是`“携程”或者 “美团民宿”，佣金率为 10%`
9. 客人付款：如果 平台是 “美团酒店”，佣金率为 12%，则客人付款为 `“到账金额 / 0.88”`； 如果是“携程”或者 “美团民宿”，佣金率为 10%，则付款为 `“到账金额 / 0.9”`
10. 平台佣金：如果 平台是 “美团酒店”，佣金率为 12%，则佣金为 `“到账金额 / 0.88 * 0.12 ”`； 如果是“携程”或者 “美团民宿”，佣金率为 10%，则佣金为 `“到账金额 / 0.9 * 0.1”`


### 2. 美团酒店价格统计


```py
import matplotlib.pyplot as plt

# 数据
data = [131, 182, 253, 233, 128, 147, 185, 123, 98, 268, 186, 315, 326, 696, 138, 159, 138, 188, 398, 169, 480, 118, 130]

# 直方图分析，步长为30
bin_size = 30
plt.hist(data, bins=range(min(data), max(data) + bin_size, bin_size), edgecolor='black')
plt.title('Frequency Histogram (Bin Size = 30)')
plt.xlabel('Value Range')
plt.ylabel('Frequency')
plt.grid(True)
plt.show()
```



### 3. `38_hotel_price.php`

- 脚本思路

该php脚本可以在网页上计算房间价格。有几个参数需要用户提前在输入框中设置，例如早餐价格`a`，午餐价格`b`（晚餐价格同午餐价格），每间双床比大床贵的价格`c`，折扣系数`k`，以及打折后价格`x`。基于上述参数计算打折前价格`y`和预计到手金额`z`。默认`a=40，b=120，c=90，k=0.5`。注意，针对所有房间，`a，b，c，k`的取值都是一样的，但是x可能不一样。所有房间的预计到手价格z都为`z=0.9x`，房型及折扣前价格计算公式分别如下。

```
1. 大床房(不含早餐)：y1=x1/k，x1需要输入
2. 标准大床房（含早餐）y2=x2/k，默认x2=x1+a
3. 标准双床房（含早餐）y3=x3/k，默认x3=x1+a+c
4. 轻奢大床房（含早晚餐）y4=x4/k，默认x4=x1+a+b
5. 轻奢双床房（含早晚餐）y5=x5/k，默认x5=x1+a+b+c
6. 豪华大床房（含早中晚餐）y6=x6/k，默认x6=x1+a+2*b
7. 豪华双床房（含早中晚餐）y7=x7/k，默认x7=x1+a+2*b+c
```







