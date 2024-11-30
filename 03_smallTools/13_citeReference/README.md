# 1. 项目功能

1. 使用php脚本、Tampermonkey脚本等在线构造分子液体等期刊参考文献引用格式


# 2. 文件结构

```bash
06_referenceJML.php                        # 引文格式转换脚本，需要输入 GB/T 7714 和 APA 两种引文格式
06_journal_Abbreviation.txt                # 存储期刊全称和简写的txt文本，按照首字母排序
06_sort_journal_Abbreviation.py            # 对06_journal_Abbreviation.txt所有行按照英文字母递增进行排序
06_journal_abbreviation_AddView.php        # 在线写入和查看 06_journal_Abbreviation.txt 中的内容
```


# 3. 使用教程

### 1. `GB/T 7714`引文格式结构

1. 提取第一个"."和第二个"."之间的字符串部分是论文标题
2. 提取倒数第二个","和最后一个"."之间的字符串部分 是 卷 出版年 和 页码范围
3. 提取字符串$G中倒数第二个"."和倒数第二个","之间的字符串作为期刊名
4. 部分期刊可能不含有括号指明的期数，需要考虑不含有 "("字符的情况，但是年和卷之间使用逗号分隔，冒号后面是页码范围


### 2. 使用教程

1. 谷歌学术检索文章，导出`GB/T 7714`格式参考文献，后续会被用于提取 文章题目，期刊名，卷，出版年，页码范围
2. [semanticscholar](https://www.semanticscholar.org/) 导出相应文章`APA`格式参考文献，后续会被用于提取人名。注意，谷歌学术也能够导出`APA`格式参考文献

- 通常来说本代码适用于谷歌学术导出的`GB/T 7714`引文格式：`Yang S, Wan X, Wei K, et al. Novel reaction media of Na2CO3–CaO for silicon extraction and aluminum removal from diamond wire saw silicon powder by roasting–smelting process[J]. ACS Sustainable Chemistry & Engineering, 2020, 8(10): 4146-4157.` ，代码考虑了期刊名不含逗号","的情况，如对于`Resources, Conservation and Recycling, 2022, 176: 105913.` 期刊名中含有逗号。

- 除了考虑卷后面有括号包含期`2020, 8(10): 4146-4157`的情况，也考虑了不含括号指明期数的`2022, 176: 105913`情况。



# 4. 环境配置

## 1. `06_referenceJML.php`

### 1. 配置刷新按钮对应的网址

```javascript
<script>
    function refreshPage() {
        /* Redirect to the specified URL */
        window.location.href = "http://120.46.81.41/06_referenceJML.php";
    }
</script>
```


### 2. 常用引文格式


| 引文格式   | 制定机构   | 示例   |
|-------|-------|-------|
| GB/T 7714 | 中国国家标准化管理委员会 | Guo J, Tucker Z D, Wang Y, et al. Ionic liquid enables highly efficient low temperature desalination by directional solvent extraction[J]. Nature communications, 2021, 12(1): 437. |
| MLA | 现代语言协会 | Guo, Jiaji, et al. "Ionic liquid enables highly efficient low temperature desalination by directional solvent extraction." Nature communications 12.1 (2021): 437. |
| APA | 美国心理学协会 | Rubie, D.C., Laurenz, V., Jacobson, S.A., Morbidelli, A., Palme, H., Vogel, A.K., & Frost, D.J. (2016). Highly siderophile elements were stripped from Earth’s mantle by iron sulfide segregation. Science, 353, 1141 - 1144. |
| Chicago   | 芝加哥大学出版社   | Rubie, David C., Vera Laurenz, Seth Andrew Jacobson, Alessandro Morbidelli, H. Palme, Antje K. Vogel and Daniel J. Frost. “Highly siderophile elements were stripped from Earth’s mantle by iron sulfide segregation.” Science 353 (2016): 1141 - 1144.   |
| 引文格式   | 制定机构   | 示例   |


### 3. 代码思路

1. // 提取第一个"."和第二个"."之间的字符串部分，作为 string1，对应文章题目
2. // 从 string1 中删除 "[J]"，作为 string2，对应修改后的文章题目
3. // 提取倒数第二个","和最后一个"."之间的字符串部分，作为 string3，例如 "2021, 12(1): 5994"，用来处理 卷 出版年 和 页码范围
4. // 拼接新的字符串 string4   如 "Result-string4: 123 (2023) 2436-2608."
5. // 提取字符串$G中倒数第二个"."和倒数第二个","之间的字符串作为期刊名。 （注意：不能提取倒数第二个 "." 和倒数第一个 "." 之间的字符串部分，然后 使用","对该字符串进行分割，获取分割后的第一个字符串，并删掉其中的空格作为期刊名 string5，对应期刊的全称，如"Nature Communications"。因为可能存在期刊名中含有逗号的情况，如"Resources, Conservation and Recycling"
6. 能否修改下面这行代码，增加一个判断逻辑，如果$string3 含有 "("字符，$s1, $s2, $s3, $s4则对应如下代码

```php
list($s1, $s2, $s3, $s4) = array_map('trim', preg_split("/[,:\(\)]+/", $string3, -1, PREG_SPLIT_NO_EMPTY));
```

7. 如果不含有 "("字符，则 $s1, $s2, $s4对应 ","  ":" 将$string3分割成的三部分字符串 , 设置$s3 = "NULL" 字符串恒定值


### 4. APA格式人名转换（名在前，姓在后，名采用首字母缩写）

- `"1, 2, 3, 4, 5, &6"`转换为 `"2 1, 4 3, 6 5"`


1. 读取一个字符串变量 $APAname ，其中含有2n-1个","且n大于等于1，请编写php代码，使用","将 $APAname 字符串分割成2n个子字符串，并且去除每个子字符串中可能存在的空格和"&"符号，现在请将第2n和第2n-1个字符串互换位置，连接成一个新的字符串，字符串之间使用", "进行连接得到字符串$string7

2. 再删除掉$string7中的第奇数个","变成$string8。

3. 我举一个简单例子，比如 $APAname = "1, 2, 3, 4, 5, &6"，使用","分割并删除每个子字符串中可能存在的空格和"&"符号后，变成成"1","2","3","4","5","6"。这个时候后 将第2n和第2n-1个字符串互换位置，就变成了"2","1","4","3","6","5"，使用", "进行连接变成了"2, 1, 4, 3, 6, 5"，再删除掉其中中的第奇数个","符号，则变成了"2 1, 4 3, 6 5"

4. 对于字符串$string7= "2, 1, 4, 3, 6, 5" 使用","进行分割并去掉子字符串中的空格得到偶数个子字符串，现在请将第2n-1和第2n个子字符串间使用" "进行连接，第2n和2n+1个子字符串间使用", "进行连接



```php
<?php
$APAname = "1, 2, 3, 4, 5, &6"; // 你的输入字符串

// 使用","分割字符串，并去除空格和"&"
$parts = array_map(function($part) {
    return str_replace([' ', '&'], '', $part);
}, explode(',', $APAname));

// 计算2n
$n = count($parts) / 2;

// 将第2n和第2n-1个字符串互换位置
for ($i = 0; $i < $n; $i++) {
    $temp = $parts[$i * 2];
    $parts[$i * 2] = $parts[$i * 2 + 1];
    $parts[$i * 2 + 1] = $temp;
}

// 使用", "连接成新的字符串$string7
$string7 = implode(', ', $parts);
// echo $string7;


// $string7 = "2, 1, 4, 3, 6, 5";

// 使用逗号分割字符串并去掉子字符串中的空格
$splitArray = array_map('trim', explode(',', $string7));

// 初始化结果字符串
$string7 = '';

// 循环连接子字符串
for ($i = 0; $i < count($splitArray); $i++) {
    // 奇数索引，连接使用" "
    if ($i % 2 == 0) {
        $string7 .= $splitArray[$i] . ' ';
    } else {
        // 偶数索引，连接使用", "
        $string7 .= $splitArray[$i] . ', ';
    }
}

// 去除末尾的", "
$string7 = rtrim($string7, ', ');

// 在末尾再添加一个", "
$string7 .= ', ';

echo $string7;

?>
```


## 2. `06_sort_journal_Abbreviation.py`

- 功能：读取当前脚本目录下的`06_journal_Abbreviation.txt`文件内容，对每一行按照忽略大小写的英文字母顺序进行排序，然后将排序结果写入到新的文件中

### 1. 代码思路

- 编写一个python脚本实现以下需求：

读取脚本所在目录下的 `06_journal_Abbreviation.txt` 中的每一行内容，示例内容如下所示，对这些行进行重新排序，排序时忽略字母大小写，按照英文字母递增的顺序进行排序，例如先比较第一个字母，第一个字母相同时比较第二个字母，以此类推，注意每行内容除了字母外还会有空格和符号，注意处理。


### 2. 环境变量

```py
# 定义文件名
input_file = "06_journal_Abbreviation.txt"
output_file = "sorted_06_journal_Abbreviation.txt"
```


## 3. `06_journal_abbreviation_AddView.php`

- 功能：PHP代码实现了一个基于网页的期刊全称与简写管理系统，支持用户新增键值对、校验重复、按字母排序保存，并通过表格展示所有期刊信息。

### 1. 代码思路

1. 首先读取 `/home/01_html/06_journal_Abbreviation.txt`，其中每一行都是由`“期刊全称/简写”`组成，使用`“/”`分隔，分别对应期刊的键和值，示例内容如下。

2. 在网页上显示两个输入框，分别是 期刊全称 和 简写，然后显示一个提交按钮。用户提交后，如果期刊全称 和 简写都不为空，则与`06_journal_Abbreviation.txt`中期刊的键和值进行比较，如果键重复，无论值是否重复，都需要给出提示，并且新提交的键值对不写入到txt文本中，使用“/”分隔。如果键不重复，无论值是否重复，则新提交的键值对要写入到txt文本中。

3. 将键值对逐行写入到txt文本中时，需要基于键的字母组成按照一定顺序写入，对这些键进行重新排序，排序时忽略字母大小写，按照英文字母递增的顺序进行排序，例如先比较所有键的第一个字母，第一个字母相同时比较第二个字母，以此类推，注意每个键的内容除了字母外还会有空格和符号，注意处理。

4. 提交按钮下方显示一个查看按钮，点击该按钮则打印出txt中所有行的内容，使用表格来呈现，包含3列，依次为序号，键和值。

注意，页面样式要尽量美观


### 2. 环境配置

1. 修改txt文本路径

```php
// 文件路径
$file_path = "/home/01_html/06_journal_Abbreviation.txt";
```

2. 设置组和权限

```bash
chown www-data:www-data /home/01_html/06_journal_Abbreviation.txt
```


# 4. 参考资料

- https://github.com/Yiwei666/08_computional-chemistry-learning-materials-/wiki/10_reference
