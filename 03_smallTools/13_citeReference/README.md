# 1. 项目功能

1. 构造分子液体参考文献引用格式

# 2. 文件结构

```
06_referenceJML.php                  # 引文格式转换脚本，需要输入 GB/T 7714 和 APA 两种引文格式
06_journal_Abbreviation.txt          # 存储期刊全称和简写的txt文本，按照首字母排序
```

# 3. 使用教程

1. 谷歌学术检索文章，导出`GB/T 7714`格式参考文献，后续会被用于提取 文章题目，期刊名，卷，出版年，页码范围
2. [semanticscholar](https://www.semanticscholar.org/) 导出相应文章`APA`格式参考文献，后续会被用于提取人名

- 通常来说本代码适用于 `Yang S, Wan X, Wei K, et al. Novel reaction media of Na2CO3–CaO for silicon extraction and aluminum removal from diamond wire saw silicon powder by roasting–smelting process[J]. ACS Sustainable Chemistry & Engineering, 2020, 8(10): 4146-4157.` 引文格式，即期刊名不含逗号","，卷后面需要有括号包含期`2020, 8(10): 4146-4157`。

- 对于`Resources, Conservation and Recycling, 2022, 176: 105913.` 期刊名中含有逗号，不含括号指明期数的需要注意。



# 4. 环境配置

### 1. 常用引文格式


| 引文格式   | 制定机构   | 示例   |
|-------|-------|-------|
| GB/T 7714 | 中国国家标准化管理委员会 | Guo J, Tucker Z D, Wang Y, et al. Ionic liquid enables highly efficient low temperature desalination by directional solvent extraction[J]. Nature communications, 2021, 12(1): 437. |
| MLA | 现代语言协会 | Guo, Jiaji, et al. "Ionic liquid enables highly efficient low temperature desalination by directional solvent extraction." Nature communications 12.1 (2021): 437. |
| APA | 美国心理学协会 | Rubie, D.C., Laurenz, V., Jacobson, S.A., Morbidelli, A., Palme, H., Vogel, A.K., & Frost, D.J. (2016). Highly siderophile elements were stripped from Earth’s mantle by iron sulfide segregation. Science, 353, 1141 - 1144. |
| Chicago   | 芝加哥大学出版社   | Rubie, David C., Vera Laurenz, Seth Andrew Jacobson, Alessandro Morbidelli, H. Palme, Antje K. Vogel and Daniel J. Frost. “Highly siderophile elements were stripped from Earth’s mantle by iron sulfide segregation.” Science 353 (2016): 1141 - 1144.   |
| 引文格式   | 制定机构   | 示例   |


### 2. 代码思路

1. // 提取第一个"."和第二个"."之间的字符串部分，作为 string1，对应文章题目
2. // 从 string1 中删除 "[J]"，作为 string2，对应修改后的文章题目
3. // 提取倒数第二个","和最后一个"."之间的字符串部分，作为 string3，例如 "2021, 12(1): 5994"，用来处理 卷 出版年 和 页码范围
4. // 拼接新的字符串 string4   如 "Result-string4: 123 (2023) 2436-2608."
5. // 提取倒数第二个 "." 和倒数第一个 "." 之间的字符串部分
6. // 使用","对该字符串进行分割，获取分割后的第一个字符串，并删掉其中的空格作为 string5，对应 期刊的全称 "Nature Communications"


### 3. APA格式人名转换

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

# 4. 参考资料

- https://github.com/Yiwei666/08_computional-chemistry-learning-materials-/wiki/10_reference
