from bs4 import BeautifulSoup

# 读取bbc6min.html文件
with open('bbc6min.html', 'r', encoding='utf-8') as file:
    html = file.read()

# 使用Beautiful Soup解析HTML
soup = BeautifulSoup(html, 'html.parser')

# 查找所有class="course-content-item active"的li标签
li_tags = soup.find_all('li', class_='course-content-item active')

# 打开homeUrl.txt文件以便写入结果
with open('homeUrl.txt', 'w', encoding='utf-8') as output_file:
    # 遍历每个li标签
    for li_tag in li_tags:
        # 在li标签中查找class="text"的div标签
        div_tag = li_tag.find('div', class_='text')

        # 在div标签下查找h2标签
        h2_tag = div_tag.find('h2')

        # 获取h2标签中的href值
        href = h2_tag.a['href']

        # 将"https://www.bbc.co.uk"添加到href值前面
        full_url = 'https://www.bbc.co.uk' + href

        # 写入到homeUrl.txt文件中
        output_file.write(full_url + '\n')

print('URLs已写入到homeUrl.txt文件中')
