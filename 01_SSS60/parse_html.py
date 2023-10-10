from bs4 import BeautifulSoup

html_file = 'sss.html'

with open(html_file, 'r', encoding='utf-8') as file:
    content = file.read()

soup = BeautifulSoup(content, 'html.parser')

# 查找所有class为"podcasts-listing__download"的div标签
download_divs = soup.find_all('div', class_='podcasts-listing__download')

# 以追加方式打开文件
with open('audio_url.txt', 'a', encoding='utf-8') as output_file:
    # 遍历每个div标签，提取data-tooltip-id和href的值，并写入文件
    for div in download_divs:
        a_tag = div.find('a')  # 找到div标签下的a标签
        if a_tag:
            data_tooltip_id = a_tag.get('data-tooltip-id', '')
            href_value = a_tag.get('href', '')
            
            # 将结果写入文件
            output_file.write(f"{data_tooltip_id},{href_value}\n")

            # 打印结果
            print("data-tooltip-id:", data_tooltip_id)
            print("href value:", href_value)
            print("-----")