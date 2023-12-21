# 1. 项目功能

获取剪贴板中的截图，上传至云服务器，作为图床使用

# 2. 文件结构




# 环境配置


```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clipboard Image Viewer with Size Control</title>
    <style>
        #imageContainer {
            border: 1px solid #ccc;
            padding: 10px;
            max-width: 300px; /* 设置最大宽度 */
            max-height: 300px; /* 设置最大高度 */
            overflow: hidden; /* 超出尺寸时隐藏 */
        }
    </style>
</head>
<body>
    <div>
        <p>Paste your image here:</p>
        <div contenteditable="true" id="imageContainer"></div>
    </div>

    <script>
        document.getElementById('imageContainer').addEventListener('paste', function (event) {
            event.preventDefault();

            var items = (event.clipboardData || event.originalEvent.clipboardData).items;

            for (var index in items) {
                var item = items[index];

                if (item.kind === 'file' && item.type.indexOf('image') !== -1) {
                    var blob = item.getAsFile();
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        var img = new Image();
                        img.src = e.target.result;
                        document.getElementById('imageContainer').innerHTML = ''; // 清空容器
                        document.getElementById('imageContainer').appendChild(img);
                    };

                    reader.readAsDataURL(blob);
                    break;
                }
            }
        });
    </script>
</body>
</html>
```

这个简单的例子中，当用户在<div>元素中粘贴图片时，会触发paste事件。通过检查剪贴板中的项，找到包含图片的项，然后使用FileReader读取该项并将其显示在网页上。

请注意，这里将图片以base64格式显示，但在实际应用中，你可能想将它上传到服务器或以其他方式处理。



