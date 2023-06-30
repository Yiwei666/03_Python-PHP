# -*- coding: utf-8 -*-
"""
Created on Fri Jun 30 20:54:58 2023

@author: sun78
"""

# -*- coding: utf-8 -*-
"""
Created on Tue Jun 27 14:40:18 2023

@author: sun78
"""

'''
import socks
import socket

# 设置SOCKS5代理
socks.set_default_proxy(socks.SOCKS5, "127.0.0.1", 1080)
socket.socket = socks.socksocket
'''

import requests
from bs4 import BeautifulSoup

# 登录后的cookie信息，需要进行修改，浏览器登录账号后获取，https://www.ablesci.com/
cookies = {
    'V2EX_LANG': 'zhcn',
    '_ga': 'GA1.2.212',
    'A2': '2|1:0|10:1687225035|2:A2|48:ODdjM2QhYjU4ZmM1|5be07652ea0a981e8db12de552788f494632eb696484e2899e056ba2fedd1160',
    'PB3_SESSION': '2|1:0|10:1687914002|11:PB3_SESSION|40:Nzc1MjYxNg==|4eb0467ca8261515b91d6e0371aa5ba4673a5a8669c79c066d7c67deb2ea796b',
    'V2EX_TAB': '2|1:0|10:1688129627|8:V2EX_TAB|4:YWxs|d5cf0e3c0ddafa9552dcdb30fde7c',
    '_gid': 'GA1.2.185428129633',
    '__gads': 'ID=b20964de5d9427:RT=1688129635:S=ALNI_MZSqb7w_v7hn6p6xp21jvg8QzT_2A',
    '__gpi': 'UID=00000c37a712deec:TS=ALNI_MZzxvfgpDcc_cmLdWkkue-Npw5asg',
    'Hm_lvt_21ea3daf4a17e9487245271',
    'advanced-frontend': '3fmk5tv1cqu8chbau'
}

# 请求头中的User-Agent，需要进行修改，浏览器登录账号后获取，https://www.ablesci.com/


headers = {
    'User-Agent': 'Mozilla/ (Windows NT 10) AppleWebKit/56',
    'Referer': 'https://www.v2ex.com/'
}


# 签到接口URL
url = 'https://www.v2ex.com/mission/daily'

# 发送GET请求模拟签到
response = requests.get(url, headers=headers, cookies=cookies)  

# Check if the request was successful (status code 200)
if response.status_code == 200:
    # Parse the HTML content
    soup = BeautifulSoup(response.text, 'html.parser')

    # Print the parsed web page data
    # print(soup.prettify())  # or print(soup) for unformatted output
    
    # Find the specific <input> element with the desired class
    input_element = soup.find('input', class_='super normal button')

    if input_element:
        # Extract the value of the 'onclick' attribute
        onclick_value = input_element.get('onclick')

        # Extract the URL from the 'onclick' attribute value
        url_fragment = onclick_value.split('=')[-1].strip("';")
        
        print(url_fragment)
        
        # Join the URL fragment with the base URL
        complete_url = 'https://www.v2ex.com/mission/daily/redeem?once='+ url_fragment
        
        print(complete_url)
        
        # Make a GET request to the complete URL
        response = requests.get(complete_url, headers=headers, cookies=cookies)

        # Check if the request was successful (status code 200)
        if response.status_code == 200:
            print("Sign-in successful!")
        else:
            print("Failed to visit the sign-in URL.")
    else:
        print("No <input> element with class 'super normal button' found.")
else:
    print('Request failed with status code:', response.status_code)
