# 1. 项目功能

pdf文件拆分与合并


# 2. 文件结构

```py
01_pdf_split.py            # 利用PyPDF2库将pdf文件拆分成单独页，使用页数命名单独页
02_pdf_to_png.py           # 利用pdf2image库将所有pdf文件转换成png格式
03_pdf_split_per2.py       # 该代码用于将输入的PDF文件拆分为包含两个页面的多个PDF文件。如果总页数是奇数，最后一个文件只包含最后一页。如果总页数是偶数，每个文件包含两页。
04_pdf_split_perM.py       # 用于将输入的PDF文件按照指定的每组页数进行拆分的程序
05_pdf_extract_merge.py    # 对于1个包含n页（n大于等于2）的pdf文件，提取部分页合并成一个新的pdf文件
06_pikepdf_extract_merge.py     # 功能同05_pdf_extract_merge.py，但是使用 pikepdf 库来实现
```

# 3. 环境配置

### 1. 安装`PyPDF2`库和`pdf2image`库

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



### 2. `04_pdf_split_perM.py`

1. 功能：用于将输入的PDF文件按照指定的每组页数进行拆分的程序。

2. 用户需要输入要处理的PDF文件的绝对路径和每组包含的页数（m），然后程序会按照指定的每组页数将输入的PDF文件拆分成多个小的PDF文件。每个小的PDF文件包含连续的页数，文件名格式为`glencoe-Pages_start_page_end_page.pdf`，其中`start_page`表示当前拆分组的起始页码，`end_page`表示结束页码。


### 3. `05_pdf_extract_merge.py`

对于1个包含n页（n大于等于2）的pdf文件，提取部分页合并成一个新的pdf文件。

1. 打印当前目录下的所有pdf文件名，提示用户输入要操作的pdf文件名
2. 提示用户输入需要提取的pdf文件的页面，例如 `“1,5,7-10,21-24”` 对于单独页可以直接输入页码，如1和5，对于连续页，可以使用“-”进行连接，例如7-10和21-24，
3. 判断输入的页码是否合法，例如是否超过了总页数，删除重复的页码，注意，对于连续页，“-”后的页码数字要大于其前面的页码数字，例如24大于21。
4. 按照原始pdf文件中页码的先后顺序，而不是输入的页码顺序合并pdf页，合并后的pdf页命名需要包含所含页码


### 4. `06_pikepdf_extract_merge.py`

1. 功能同`05_pdf_extract_merge.py`，但是使用`pikepdf 库`来实现。

2. 由于`PyPDF2`库在提取文件过程可能会出现如下报错，而`pikepdf 库`支持更多加密算法

```
NotImplementedError: only algorithm code 1 and 2 are supported. This PDF uses code 4
```

3. 安装`pikepdf`库

```
pip install pikepdf
```


  
