# 项目功能

- 01_pdf_split.py

利用PyPDF2库将pdf文件拆分成单独页，使用页数命名单独页

- 02_pdf_to_png.py

利用pdf2image库将所有pdf文件转换成png格式


# 环境部署

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
