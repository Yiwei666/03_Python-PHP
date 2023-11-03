from bs4 import BeautifulSoup

# 读取subPage.html文件
with open('subPage.html', 'r', encoding='utf-8') as file:
    html = file.read()

# 使用Beautiful Soup解析HTML
soup = BeautifulSoup(html, 'html.parser')

# 查找所有class="download bbcle-download-extension-mp3"的a标签
a_tags = soup.find_all('a', class_='download bbcle-download-extension-mp3')

# 打开audioUrl.txt文件以便追加结果
with open('audioUrl.txt', 'a', encoding='utf-8') as output_file:
    # 遍历每个a标签
    for a_tag in a_tags:
        # 获取a标签的href值
        href = a_tag['href']

        # 检查是否已经存在于audioUrl.txt中
        with open('audioUrl.txt', 'r', encoding='utf-8') as existing_file:
            existing_urls = existing_file.read().splitlines()
            if href not in existing_urls:
                # 写入到audioUrl.txt文件中
                output_file.write(href + '\n')
                print(f'已写入URL: {href}')
            else:
                print(f'URL已存在: {href}')

# print('URLs已追加到audioUrl.txt文件中')
