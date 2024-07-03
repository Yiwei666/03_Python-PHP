# 1. 项目功能

### 1. `01_pdf_split.py`

利用PyPDF2库将pdf文件拆分成单独页，使用页数命名单独页

### 2. `02_pdf_to_png.py`

利用pdf2image库将所有pdf文件转换成png格式

### 3. `03_pdf_split_per2.py`

该代码用于将输入的PDF文件拆分为包含两个页面的多个PDF文件。如果总页数是奇数，最后一个文件只包含最后一页。如果总页数是偶数，每个文件包含两页。

### 4. `04_pdf_split_perM.py`

1. 功能：用于将输入的PDF文件按照指定的每组页数进行拆分的程序。

2. 用户需要输入要处理的PDF文件的绝对路径和每组包含的页数（m），然后程序会按照指定的每组页数将输入的PDF文件拆分成多个小的PDF文件。每个小的PDF文件包含连续的页数，文件名格式为`glencoe-Pages_start_page_end_page.pdf`，其中`start_page`表示当前拆分组的起始页码，`end_page`表示结束页码。


# 2. 环境部署

1. 安装PyPDF2库
```
pip install PyPDF2
```

2. 安装pdf2image库

- 首先需要安装poppler-utils

  - ubuntu安装命令

    ```
    apt-get install poppler-utils
    ```
    
  - centos安装命令
    ```
    yum install poppler-utils
    ```

- 安装pdf2image库
  
  ```
  pip install pdf2image
  ```
