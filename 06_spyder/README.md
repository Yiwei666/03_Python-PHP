**Note: 推荐在anaconda prompt和anaconda powershell prompt窗口中执行如下命令，或者在cmd窗口的base环境中运行，cmd窗口中激活base环境命令如下:**

```
conda activate base
```

1. 创建sougouspider项目
```
scrapy startproject sougouspider
```

2. 创建spyder
```
cd sougouspider                              # 进入项目文件夹中的sougouspider子文件夹
scrapy genspider sgspider weixin.sogou.com   # sgspider为spider名称，后是网站域名
```

3. 修改Item

5. 修改settings.py


6. 修改默认生成的spider文件
注意下面两个模块的导入
```
from IP.free_ip import get_random_proxy
from IP.get_cookies import get_new_cookies,get_new_headers
```

7. 运行scrapy框架
```
scrapy crawl sgspider -o XXX.json  # XXX.json是爬虫生成的json文件名，sgspider是上面步骤2中创建的爬虫名字，可以在sougouspider项目中的任意一级目录下运行该命令
```

**参考资料**   

[【学习笔记】爬虫框架Scrapy入门](http://t.csdn.cn/TY3ex)
