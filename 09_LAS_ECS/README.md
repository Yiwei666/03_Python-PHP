# 1. 项目功能

- 用于音视频、文档、日志等处理的小脚本


# 2. 项目结构

```php
uname.php                             # 输入网站名和网址，生成 markdown 和 html 格式的链接
11_musicMix.php                       # 通过构造音频链接，随机播放指定文件夹下的mp3音频
11_musicMixTxt.php                    # 从一个包含MP3音频链接的文件中读取链接，并以随机的方式播放这些音频文件
lsfile_js.php                         # 代码将会显示具有.html、.php和.js扩展名的文件列表

13_MixPlayers.php                     # 聚合在线音频播放器
    ├── 13_MixPlayers_codeGenerate.php        # 输入路径生成相应代码，添加到13_MixPlayers.php文件中
    ├── 13_MixPlayers.php                     # 生成多个在线播放器，每个播放器对应一个音频文件夹

serialNumberGenerate.php              # 生成指定位数的序列号，序列号包含大小写英文字符和数字的组合
question_darkTheme.php                # `question.php` 深色主题版本
01_EnglishWordNote.php                # 单词记录本，包括记录和显示
01_EnglishDisplay.php                 # 显示单词记录内容，可根据终端类型自适应文本显示框高度
```


# 3. 环境配置


## 1. [uname.php](uname.php)

输入网站名和网址，生成 markdown 和 html 格式的链接

## 2. 11_musicMix.php

通过构造音频链接，随机播放指定文件夹下的mp3音频

## 3. 11_musicMixTxt.php

它的主要功能是从一个包含MP3音频链接的文件中读取链接，并以随机的方式播放这些音频文件。代码会自动加载页面并播放一首随机选取的歌曲，然后在歌曲播放完毕后选择下一首随机歌曲继续播放。页面上会显示一个标题 "MusicMix" 和一个包含播放器控件的区域，用于播放音频文件。

生成链接文件的脚本参考：`03_smallTools/05_音频格式转换/generate_mp3_paths.sh`

## 4. lsfile_js.php

代码将会显示具有.html、.php和.js扩展名的文件列表。

`lsfile_darkTheme.php`在`lsfile_js.php`基础上新增深色主题功能。

## 5. 13_MixPlayers.php (聚合在线音频播放器) 

```
├── 13_MixPlayers_codeGenerate.php        # 输入路径生成相应代码，添加到13_MixPlayers.php文件中
├── 13_MixPlayers.php                     # 生成多个在线播放器，每个播放器对应一个音频文件夹

```

## 6. serialNumberGenerate.php

生成指定位数的序列号，序列号包含大小写英文字符和数字的组合


## 7. [question_darkTheme.php](question_darkTheme.php)

`question.php` 深色主题版本。若想要将显示框文本左对齐，只需将`text-align: left`值修改为left即可

```css
textarea[readonly] {
  display: block;
  margin: 0 auto;
  text-align: left; /* Left-align text in readonly textarea */
}
```


## 8. [01_EnglishWordNote.php](01_EnglishWordNote.php)

功能：记录英语单词

### 1. `" "`内文本红色高亮

- 新增的显示框`" "`内文本高亮css style

```css
    /* 新增规则，选择.highlight-text样式的文字，设置为红色 */
    .highlight-text {
      color: red;
    }

    #display-textbox {
      background-color: #333; /* 文本区域的深灰色背景 */
      color: #eee; /* 文本区域的浅白色文字颜色 */
      display: block; /* 将显示属性设置为块级元素 */
      margin: 0 auto; /* 使用自动边距水平居中元素 */
      text-align: center; /* 将文本在元素中居中 */
      font-family: 'Microsoft YaHei', Arial, sans-serif; /* 使用Microsoft YaHei、Arial或sans-serif作为首选字体 */
      padding: 10px; /* 在元素内部添加10像素的填充 */
      border: 0.5px solid #eee; /* 添加0.5像素的实线边框以提高可见性 */
      width: 78ch; /* 将元素的宽度设置为字符宽度的80个字符 */
      height: 20em; /* 将元素的高度设置为大约16行的高度 */
      overflow-y: auto; /* 如果内容超过指定高度，则启用垂直滚动条 */
      white-space: pre-wrap; /* 保留文本中的空格和换行符 */
      resize: both; /* 允许水平和垂直同时调整大小 */
      overflow: auto; /* 在调整大小后添加溢出属性以启用滚动条 */
    }
```

1. 通过 `width: 78ch`和`height: 20em`控制显示框的长和宽，区别于传统通过像素控制

2. 通过`overflow-y`启用垂直滚动条

3. 通过 ` resize` 允许元素在水平和垂直方向上都可以被用户调整大小
      


- CSS样式中的`textarea`，`textarea[readonly]`和`#display-textbox`

在上述代码中，CSS样式中的`textarea`，`textarea[readonly]`和`#display-textbox`分别控制不同的元素样式，不存在直接的重叠或交叉。这三个选择器分别应用于`textarea`元素、带有`readonly`属性的`textarea`元素，以及具有id为`display-textbox`的元素。它们在页面中的不同元素上应用，互不干扰。

具体来说：

◻️ `textarea`选择器应用于所有`<textarea>` 元素，包括用户输入的`textarea`。

◻️ `textarea[readonly]`选择器应用于带有`readonly`属性的`<textarea>` 元素，使其在只读状态下具有不同的样式。

◻️ `#display-textbox`选择器应用于具有id为`display-textbox`的元素，这个元素可能是用于显示内容的`div`。

因此，这些选择器不会导致样式冲突，各自独立地应用于它们所指定的元素。



### 2. `/ /`内文本蓝色高亮

实现`" "`内文本红色高亮的同时`/ /`内文本蓝色高亮，两者之间没有干扰

```php
  // 正确匹配 "[" 和 "]" 之间的内容并进行蓝色高亮显示
  // $content = preg_replace('/\[(.*?)\]/', '<span style="color: blue;">[$1]</span>', $content);

  // 使用正则表达式在内容中查找并标记需要高亮的文字（两个斜杠之间的内容为黄色）
  // $content = preg_replace('/\/(.*?)\//', '<span style="color: yellow;">/$1/</span>', $content);

  // 使用正则表达式在内容中查找并标记需要高亮的文字
  // $content = preg_replace('/"(.*?)"/', '<span class="highlight-text">"$1"</span>', $content);

  // 先将 / / 之间的内容替换为占位符，避免干扰
  $content = preg_replace('/\/(.*?)\//', '[[PLACEHOLDER_YELLOW_$1]]', $content);

  // 对 " " 之间的内容进行红色高亮显示
  $content = preg_replace('/"(.*?)"/', '<span class="highlight-text">"$1"</span>', $content);

  // 将占位符替换为蓝色高亮的 / / 内容
  $content = preg_replace('/\[\[PLACEHOLDER_YELLOW_(.*?)\]\]/', '<span style="color: #258fb8;">/$1/</span>', $content);
```

1. 解释：
    - 占位符的使用：在第一次 `preg_replace` 中，我们将 `/ /` 之间的内容替换为一个自定义的占位符 `[[PLACEHOLDER_YELLOW_$1]]`，以避免后续的替换干扰。
    - 处理引号高亮：紧接着处理引号 `"` 之间的内容高亮，确保它的替换操作不会干扰 `/ /` 的高亮操作。
    - 恢复占位符：最后，再通过 `preg_replace` 将占位符替换为带有蓝色高亮的 `/ /` 标签。

2. 确保`" "`红色高亮的一行关键代码

```php
/* 新增规则，选择.highlight-text样式的文字，设置为红色 */
.highlight-text {
  color: red;
}

// 使用正则表达式在内容中查找并标记需要高亮的文字
$content = preg_replace('/"(.*?)"/', '<span class="highlight-text">"$1"</span>', $content);
```


### 3. [01_EnglishDisplay.php](01_EnglishDisplay.php)

1. `01_EnglishDisplay.php`是`01_EnglishWordNote.php`简化版本，只包含文本显示功能，无提交功能。
2. 能够识别终端类型，可根据终端类型自适应文本框显示高度。





