# Define here the models for your scraped items
#
# See documentation in:
# https://docs.scrapy.org/en/latest/topics/items.html


import scrapy


class SougouspiderItem(scrapy.Item):
    # define the fields for your item here like:
    # name = scrapy.Field()
    visit_url = scrapy.Field()
    page = scrapy.Field()
    rank = scrapy.Field()
    title = scrapy.Field()
    # pass
