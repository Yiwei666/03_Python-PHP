from sougouspider.items import SougouspiderItem
from IP.free_ip import get_random_proxy
from IP.get_cookies import get_new_cookies,get_new_headers
import scrapy
import time
import random
from tqdm import tqdm 


class SgspiderSpider(scrapy.Spider):
    name = 'sgspider'
    allowed_domains = ['weixin.sogou.com']
    start_urls = ['https://weixin.sogou.com/weixin?&query=java2022&type=2&ie=utf8']  # 注意替换不同关键字进行查询
    def start_requests(self):
        headers = get_new_headers()
        print('请求的headers',headers)        
        for url in self.start_urls:
            # 获取代理IP
            proxy = 'http://' + str(get_random_proxy())
            print('请求的proxy',proxy)
            yield scrapy.Request(url=url,
                                 callback=self.parse,
                                 headers=headers,
                                 meta={'http_proxy': proxy})

    def parse(self, response):
        headers_new = get_new_headers()
        print('第二次请求的headers',headers_new)
        cookies_new = get_new_cookies()
        print('请求的cookies_new',cookies_new)
        
        # 获取当前页码
        current_page = int(response.xpath('//div[@id="pagebar_container"]/span/text()').extract_first())
        print('response的内容',response)
        print('reponse成功被解析，此时的页码',current_page)
        # 解析当前页面
        #for i, a in enumerate(response.xpath('//div[contains(@class,"news-box")]/div[@class="txt-box"]/h3/a')):
        for i, a in enumerate(response.xpath('//div[contains(@class,"txt-box")]/h3/a')):
            # 获取标题，去除空格和换行符
            # title = ''.join(a.xpath('./em/text() | ./text()').extract()).replace(' ', '').replace('\n', '')
            print('i和a分别为',i,a)
            title = a.xpath('.//text()').extract()
# string(.)函数会得到所指元素的所有节点文本内容，这些文本讲会被拼接成一个字符串
            # title = a.xpath('string(.)')
            print('页面标题是',title)
            if title:
                item = SougouspiderItem()
                # 获取访问链接（①非跳转链接②跳转链接）、页码、行数、标题
                if a.xpath('@href').extract_first().startswith('/link'):
                    item['visit_url'] = 'https://weixin.sogou.com' + a.xpath('@href').extract_first()  # 提取链接
                else:
                    item['visit_url'] = a.xpath('@href').extract_first()
                item['page'] = current_page
                item['rank'] = i + 1
                item['title'] = title
                yield item
        # 控制爬取频率
        #time_wait=random.randint(8, 10)
        #print('等待时间',time_wait)        
        #time.sleep(time_wait)        
        time_wait=random.randint(40, 50)
        for i in tqdm(range(time_wait)):           
            time.sleep(0.2)
        # 获取“下一页”的链接
        p = response.xpath('//div[@id="pagebar_container"]/a[@id="sogou_next"]')
        print('准备解析下一页的网址',p)
        if p:
            p_url = 'https://weixin.sogou.com/weixin' + str(p.xpath('@href').extract_first())
            print('下一页的网址',p_url)
            proxy = 'http://' + str(get_random_proxy())
            yield scrapy.Request(url=p_url,
                                 callback=self.parse,
                                 headers=headers_new,
                                 cookies=cookies_new,
                                 meta={'http_proxy': proxy})
