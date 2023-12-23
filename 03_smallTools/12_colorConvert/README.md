# 1. 项目功能

将RGB颜色值转为十六进制颜色值，用于HTML页面设置，RGB值可通过微信截图获取

# 2. 文件结构

```
04_RGBtoHEX.php       # RGB和HEX互转
04_HEXtoRGB.php       # HEX转RGB
```

# 3. 环境配置

无需环境配置，开箱即用，通过下面的php代码可设置输出的字符串格式

```php
echo "<div style='width: 120px; height: 75px; background-color: $hexValue;'></div>";
// echo "<p>| $rgbValue | $hexValue | $colorName | 用户输入 | ![Color Box](https://via.placeholder.com/50/$hexValue/000000?text=+) | 通用 | 无 |</p>";

// 假设 $hexValue 是一个包含十六进制颜色值的变量
// 使用 str_replace 将 "#" 替换为空字符串
$cleanedHexValue = str_replace('#', '', $hexValue);

// 输出带有清理后的十六进制颜色值的字符串
echo "<p>| $rgbValue | $cleanedHexValue | $colorName | 用户输入 | ![Color Box](https://via.placeholder.com/32/$cleanedHexValue/000000?text=+) | 通用 | 无 |</p>";
echo "<p>$hexValue, $rgbValue</p>";
```


# 4. 笔记

### 1. 常用颜色

   
| RGB       | HEX       | 颜色      | 来源      | 示例      | 场景     | 备注      |
|:---------:|:---------:|:---------:|:---------:|:---------:|:---------:|:---------:|
| (29,31,33)       | #1d1f21       | 黑色    | dark reader    | ![Color Box](https://via.placeholder.com/50/1d1f21/000000?text=+)         | 深色主题背景   |  备注     |
| (51,51,51)       | #333333       | 灰黑色      | 自定义      |  ![Color Box](https://via.placeholder.com/50/333333/000000?text=+)      | 深色主题背景   | 备注      |
| (13,17,23)       | #0d1117        | 黑色      | github深色主题      | ![Color Box](https://via.placeholder.com/50/0d1117/000000?text=+)     | 深色主题背景     | 备注      |
| (48,48,48)       | #303030       | 灰黑色      | 自定义      | ![Color Box](https://via.placeholder.com/50/303030/000000?text=+)      | 场景     | 曾用于mainpage.html灰黑色背景      |
| (47,129,247)     | #2f81f7       |蓝色      | github链接      | ![Color Box](https://via.placeholder.com/50/2f81f7/000000?text=+)      | 场景     | 备注      |
| (37,143,184)     | #258fb8       | 蓝色      | 自定义      | ![Color Box](https://via.placeholder.com/50/258fb8/000000?text=+)      | 场景     | 曾用于mainpage.html超链接      |
| (52,53,65)       | #343541       | 灰黑色      | chatgpt深色背景      | ![Color Box](https://via.placeholder.com/50/343541/000000?text=+)      | 场景     | 备注      |
| (48,56,65) | #303841 | 颜色 | sublime text | ![Color Box](https://via.placeholder.com/50/303841/000000?text=+) | 通用 | 无 |
| RGB       | HEX       | 颜色      | 来源      | 示例      | 场景     | 备注      |
| RGB       | HEX       | 颜色      | 来源      | 示例      | 场景     | 备注      |
| RGB       | HEX       | 颜色      | 来源      | 示例      | 场景     | 备注      |
| RGB       | HEX       | 颜色      | 来源      | 示例      | 场景     | 备注      |




### 2. 生成占位图在线服务

- 这是一个生成占位图的在线服务的基础URL：https://via.placeholder.com/，调用格式如下所示“

```markdown
![Color Box](https://via.placeholder.com/50/FF0000/000000?text=+)
```

1. `![Color Box]`：这是一个Markdown的图像插入语法，Color Box是图像的替代文本，用于在图像无法显示时提供文字描述。

2. `https://via.placeholder.com/`：这是一个生成占位图的在线服务的基础URL。

3. `50/FF0000/000000`：这一部分是URL的参数，以斜杠分隔。具体含义如下：
    - `50`：图像的宽度，这里设置为50像素。
    - `FF0000`：背景颜色的十六进制代码，这里是红色。
    - `000000`：文本颜色的十六进制代码，这里是黑色。
    - `?text=+`：这是一个额外的参数，用于在图像上显示文本。在这里，文本是一个空格，通过`+`表示。



### 3. RGB和HEX十六进制颜色互转网站


- RGB和HEX十六进制颜色互转网站：https://uutool.cn/color/






