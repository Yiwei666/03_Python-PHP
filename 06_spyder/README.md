**Note: 推荐在anaconda prompt和anaconda powershell prompt窗口中执行如下命令，或者在cmd窗口的base环境中运行，cmd窗口中激活base环境命令如下:**

```
conda activate base
```

**1. 创建sougouspider项目**
```
scrapy startproject sougouspider
```

创建项目后，目录树如下
```
C:.
└─sougouspider
    │  scrapy.cfg
    │
    └─sougouspider
        │  items.py
        │  middlewares.py
        │  pipelines.py
        │  settings.py
        │  __init__.py
        │
        └─spiders
                __init__.py
```

**2. 进入子目录，创建sgspider爬虫**
```
cd sougouspider                              # 进入项目文件夹中的sougouspider子文件夹
scrapy genspider sgspider weixin.sogou.com   # sgspider为spider名称，后是网站域名
```

创建项目后，目录树如下
```
C:.
└─sougouspider
    │  scrapy.cfg
    │
    └─sougouspider
        │  items.py
        │  middlewares.py
        │  pipelines.py
        │  settings.py
        │  __init__.py
        │
        ├─spiders
        │  │  sgspider.py
        │  │  __init__.py
        │  │
        │  └─__pycache__
        │          __init__.cpython-38.pyc
        │
        └─__pycache__
                settings.cpython-38.pyc
                __init__.cpython-38.pyc
```

**3. 修改Item**

scrapy默认创建的items.py内容如下：

```
# Define here the models for your scraped items
#
# See documentation in:
# https://docs.scrapy.org/en/latest/topics/items.html

import scrapy


class SougouspiderItem(scrapy.Item):
    # define the fields for your item here like:
    # name = scrapy.Field()
    pass

```

修改后的见项目文件夹

**4. 修改settings.py**

scrapy默认创建的settings.py内容如下：

```
# Scrapy settings for sougouspider project
#
# For simplicity, this file contains only settings considered important or
# commonly used. You can find more settings consulting the documentation:
#
#     https://docs.scrapy.org/en/latest/topics/settings.html
#     https://docs.scrapy.org/en/latest/topics/downloader-middleware.html
#     https://docs.scrapy.org/en/latest/topics/spider-middleware.html

BOT_NAME = 'sougouspider'

SPIDER_MODULES = ['sougouspider.spiders']
NEWSPIDER_MODULE = 'sougouspider.spiders'


# Crawl responsibly by identifying yourself (and your website) on the user-agent
#USER_AGENT = 'sougouspider (+http://www.yourdomain.com)'

# Obey robots.txt rules
ROBOTSTXT_OBEY = True

# Configure maximum concurrent requests performed by Scrapy (default: 16)
#CONCURRENT_REQUESTS = 32

# Configure a delay for requests for the same website (default: 0)
# See https://docs.scrapy.org/en/latest/topics/settings.html#download-delay
# See also autothrottle settings and docs
#DOWNLOAD_DELAY = 3
# The download delay setting will honor only one of:
#CONCURRENT_REQUESTS_PER_DOMAIN = 16
#CONCURRENT_REQUESTS_PER_IP = 16

# Disable cookies (enabled by default)
#COOKIES_ENABLED = False

# Disable Telnet Console (enabled by default)
#TELNETCONSOLE_ENABLED = False

# Override the default request headers:
#DEFAULT_REQUEST_HEADERS = {
#   'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
#   'Accept-Language': 'en',
#}

# Enable or disable spider middlewares
# See https://docs.scrapy.org/en/latest/topics/spider-middleware.html
#SPIDER_MIDDLEWARES = {
#    'sougouspider.middlewares.SougouspiderSpiderMiddleware': 543,
#}

# Enable or disable downloader middlewares
# See https://docs.scrapy.org/en/latest/topics/downloader-middleware.html
#DOWNLOADER_MIDDLEWARES = {
#    'sougouspider.middlewares.SougouspiderDownloaderMiddleware': 543,
#}

# Enable or disable extensions
# See https://docs.scrapy.org/en/latest/topics/extensions.html
#EXTENSIONS = {
#    'scrapy.extensions.telnet.TelnetConsole': None,
#}

# Configure item pipelines
# See https://docs.scrapy.org/en/latest/topics/item-pipeline.html
#ITEM_PIPELINES = {
#    'sougouspider.pipelines.SougouspiderPipeline': 300,
#}

# Enable and configure the AutoThrottle extension (disabled by default)
# See https://docs.scrapy.org/en/latest/topics/autothrottle.html
#AUTOTHROTTLE_ENABLED = True
# The initial download delay
#AUTOTHROTTLE_START_DELAY = 5
# The maximum download delay to be set in case of high latencies
#AUTOTHROTTLE_MAX_DELAY = 60
# The average number of requests Scrapy should be sending in parallel to
# each remote server
#AUTOTHROTTLE_TARGET_CONCURRENCY = 1.0
# Enable showing throttling stats for every response received:
#AUTOTHROTTLE_DEBUG = False

# Enable and configure HTTP caching (disabled by default)
# See https://docs.scrapy.org/en/latest/topics/downloader-middleware.html#httpcache-middleware-settings
#HTTPCACHE_ENABLED = True
#HTTPCACHE_EXPIRATION_SECS = 0
#HTTPCACHE_DIR = 'httpcache'
#HTTPCACHE_IGNORE_HTTP_CODES = []
#HTTPCACHE_STORAGE = 'scrapy.extensions.httpcache.FilesystemCacheStorage'

```
修改后的见项目文件夹

**5. 修改默认生成的sgspider.py文件**

默认生成的sgspider.py内容如下：
```
import scrapy


class SgspiderSpider(scrapy.Spider):
    name = 'sgspider'
    allowed_domains = ['weixin.sogou.com']
    start_urls = ['http://weixin.sogou.com/']

    def parse(self, response):
        pass

```

修改后的见项目文件夹，注意下面两个模块的导入

```
from IP.free_ip import get_random_proxy
from IP.get_cookies import get_new_cookies,get_new_headers
```

**6. 运行scrapy框架**
```
scrapy crawl sgspider -o XXX.json  # XXX.json是爬虫生成的json文件名，sgspider是上面步骤2中创建的爬虫名字，可以在sougouspider项目中的任意一级目录下运行该命令
```

**7. XXX.json后处理**

使用同级目录下的conver_json.py脚本处理后可以复制粘贴到markdown文件中查看


**参考资料和链接**   

[【学习笔记】爬虫框架Scrapy入门](http://t.csdn.cn/TY3ex)

[fake-useragent 1.1.3 ](https://pypi.org/project/fake-useragent/)

[An Efficient ProxyPool with Getter, Tester and Server ](https://github.com/Python3WebSpider/ProxyPool)

