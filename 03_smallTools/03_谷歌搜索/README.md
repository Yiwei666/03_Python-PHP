### 项目功能
```
基于谷歌对指定网站内容进行搜索，如github，v2ex等
```

### 项目结构
```

```


### 使用案例
---

输入
```
Name:
ACS,RSC,sciencedirect

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


