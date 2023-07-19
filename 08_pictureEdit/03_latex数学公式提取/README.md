### 项目功能
---

- 接收一张数学公式的图像，并返回相应的LaTeX代码



### 部署方法
---

- 原项目地址：https://github.com/lukas-blecher/LaTeX-OCR


1. To run the model you need Python 3.7+

2. If you don't have PyTorch installed. Follow their instructions [here](https://pytorch.org/get-started/locally/). Recommend using ubuntu system

3. Install the package `pix2tex`: 

```
pip install pix2tex[gui]
```

4. Use from within Python
  ```python
  from PIL import Image
  from pix2tex.cli import LatexOCR
  
  img = Image.open('path/to/image.png')
  model = LatexOCR()
  print(model(img))
  ```








