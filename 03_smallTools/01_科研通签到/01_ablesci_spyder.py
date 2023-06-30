# -*- coding: utf-8 -*-
"""
Created on Tue Jun 27 14:40:18 2023

@author: sun78
"""

import requests

# 登录后的cookie信息，需要进行修改，浏览器登录账号后获取，https://www.ablesci.com/
cookies = {
    'Hm_lvt_21ea3daf4a17e94a98a483d3d810f41a': '1687245271',
    'advanced-frontend': '3fmk52h030e4g1tv1cqu8chbau'
    ...
}

# 请求头中的User-Agent，需要进行修改，浏览器登录账号后获取，https://www.ablesci.com/
headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Referer': 'https://www.ablesci.com/'
}

# 签到接口URL
url = 'https://www.ablesci.com/user/sign'

# 发送GET请求模拟签到
response = requests.get(url, headers=headers, cookies=cookies)

# 解析返回的JSON数据
data = response.json()

# 判断签到结果
if data['code'] == 0:
    print('签到成功！')
    # 在这里可以根据需要进行相关操作，如更新签到天数、积分数等
    # 可以使用data['data']中的数据进行相应处理
else:
    print('签到失败！')
    print('错误信息：', data['msg'])
