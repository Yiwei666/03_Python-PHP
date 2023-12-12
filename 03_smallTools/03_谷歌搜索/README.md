# 1. 项目功能

```
基于谷歌对指定网站内容进行搜索，如github，v2ex，Nature，Science等
```

# 2. 项目结构

```
google.php                   # 谷歌搜索指定网站内容
google_mgugesrch.php         # 生成单个或多个网站的谷歌搜索代码，可直接复制粘贴至 google.php
```


# 3. 使用案例

输入示例如下
```
Name:
ACS,RSC,sciencedirect                # 如果单个网站name由多个word组成，不要使用空格进行分隔，比如使用HackerNews代替Hacker News，否则不能正常运行

Domain:
acs.org,rsc.org,sciencedirect.com
```

输出
```
if(isset($_GET['query_ACSRSCsciencedirect'])) {
    $query_ACSRSCsciencedirect = $_GET['query_ACSRSCsciencedirect'];
    $ACSRSCsciencedirect_Url = 'https://www.google.com/search?q=site%3Aacs.org+OR+site%3Arsc.org+OR+site%3Asciencedirect.com+' . $query_ACSRSCsciencedirect;
    echo "<script>window.location.replace('$ACSRSCsciencedirect_Url');</script>";
    exit;
}

<form method="GET">
    <label for="query_ACSRSCsciencedirect">在 ACS、RSC、sciencedirect 中搜索:</label>
    <input type="text" name="query_ACSRSCsciencedirect" id="query_ACSRSCsciencedirect" placeholder=" ACS、RSC、sciencedirect 搜索">
    <input type="submit" value="搜索">
</form>
```


