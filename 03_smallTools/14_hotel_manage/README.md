# 1. 项目功能

打印 美团/携程 等预定信息

# 2. 文件结构




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