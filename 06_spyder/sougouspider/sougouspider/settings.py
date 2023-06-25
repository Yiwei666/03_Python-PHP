BOT_NAME = 'sougouspider'

SPIDER_MODULES = ['sougouspider.spiders']
NEWSPIDER_MODULE = 'sougouspider.spiders'

# Obey robots.txt rules
ROBOTSTXT_OBEY = False # 将False改为True

# Disable cookies (enabled by default)
COOKIES_ENABLED = True  # 将False改为True


# Configure item pipelines
# See https://docs.scrapy.org/en/latest/topics/item-pipeline.html
ITEM_PIPELINES = {
    'sougouspider.pipelines.SougouspiderPipeline': 300,
}

REDIRECT_ENABLED = False
HTTPERROR_ALLOWED_CODES = [302]
