# 1. 项目功能

收集的v2ex页面自定义CSS

# 2. 选择器类型


当涉及到这些选择器时，最好通过具体的例子来说明。以下是一些示例：

元素选择器：
```css
p {
    color: blue; /* 选择所有 <p> 元素并设置文字颜色为蓝色 */
}
```

通用选择器：
```css
* {
    margin: 0; /* 选择所有元素并将外边距设置为0 */
}
```

子元素选择器：
```css
div > p {
    font-weight: bold; /* 选择所有直接位于 <div> 元素内的 <p> 元素，并将字体加粗 */
}
```

后代选择器：
```css
div p {
    color: green; /* 选择所有位于 <div> 元素内的 <p> 元素，不仅仅是直接子元素 */
}
```

相邻兄弟选择器：
```css
h2 + p {
    font-style: italic; /* 选择紧接在 <h2> 元素后的 <p> 元素，并将文字风格设置为斜体 */
}

通用兄弟选择器：
```css
h2 ~ p {
    text-decoration: underline; /* 选择所有与 <h2> 元素有相同父元素的 <p> 元素，并添加下划线 */
}
```

属性选择器：
```css
input[type="text"] {
    border: 1px solid #ccc; /* 选择所有 type 属性为 "text" 的 <input> 元素，并设置边框样式 */
}
```

伪类选择器：
```css
a:hover {
    color: red; /* 选择鼠标悬停在 <a> 元素上时，将文字颜色设置为红色 */
}

li:nth-child(odd) {
    background-color: #f2f2f2; /* 选择所有奇数位置的 <li> 元素，并设置背景颜色 */
}
```

# 环境配置

- example1: https://www.v2ex.com/p/688RNvc1

```css
#Wrapper{
background-color: #f2eee8;
background-image:none;
}
#Tabs{padding:0 !important;}
#Tabs a.tab_current{
border-color: #80a8cc !important;
background-color: #f5f5f5 !important;
color: #000 !important;
line-height:46px;
height:46px;
}
#Tabs a.tab,#Tabs a.tab_current{
width: 50px;
text-align: center;
line-height:46px !important;
display: inline-block;
margin-right:0 !important;
padding: 0!important;
border-radius:0 !important;
border-bottom: 2px solid transparent;
-webkit-transition: border-color .15s ease-in-out, background-color .15s ease-in-out;
-moz-transition: border-color .15s ease-in-out, background-color .15s ease-in-out;
transition: border-color .15s ease-in-out, background-color .15s ease-in-out;
}
#Tabs a.tab:hover{border-color: #80a8cc;}
#Bottom{display:none}
#Top{
background-image:none;
background-color: rgba(255, 251, 245, 0.39);
border-bottom: 1px solid #e2e2e2;
}

.box{
-webkit-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0.1);
box-shadow: 0px 0px 1px rgba(0, 0, 0, 0.1);
border: 1px solid #e2e2e2;
border-radius: 2px;
}
img.avatar {
-moz-border-radius: 50%;
-webkit-border-radius: 50%;
border-radius: 50%;
}
a.top{
line-height: 13px;
padding: 5px 3px;
border-radius: 3px;
color: #555;

}
a.top:hover{
line-height: 13px;
padding: 5px 3px;
border-radius: 3px;

color: #000;
} 

```


- example2: https://github.com/jinzhe/v2ex.theme/blob/master/v2ex.css

```css
/*超链接*/
a:link, a:visited, a:active {
    color: #333;
}
#Top{
    border:none;
    margin: 10px;
}
#Wrapper {
    background-color: #f6f8f9;
    background-image: none;
}
/*广告*/
.sidebar_compliance {
    background-color: #f2f2f2;
    padding: 5px;
    margin: 5px;
    border-radius: 8px;
    font-size: 10px;
}
.sidebar_compliance a{
    color:#999;
}
.box{
    border-radius:8px;
    box-shadow: 0 0 6px 0 rgba(122,146,159,.1);
}
.box .inner{
    border-top-left-radius: 8px !important;
    border-top-right-radius: 8px !important;
}
/*设置最大宽度适合mbp访问*/
.content{
    max-width: 1260px !important;
}
.header {
    padding: 10px 20px;
}
.topic_content {
    font-size: 14px;
    line-height: 1.6;
    color: #000;
    word-wrap: break-word;
    padding: 0px 10px;
}
/*标签*/
a.tab_current:link,
a.tab_current:visited,
a.tab_current:active {
    padding: 5px 10px;
    border-radius: 20px;
    background-color: #1f1f20;
}
a.tab:link,
a.tab:visited,
a.tab:active {
    padding: 5px 10px;
    border-radius: 20px;
}
a.tab_current{
    position: relative;
}
a.tab_current:after{
    content: "";
    position: absolute;
    bottom: -6px;
    left: 45%;
    border: 4px solid transparent;
    border-top-color: #1f1f20;
    border-right-width: 2px;
}
/*头像*/
img.avatar {
    width: 40px;
    height: 40px;
    max-width: 40px;
    max-height: 40px;
    border-radius: 50%;
    box-shadow: 0 0 0 4px #fafafa;
}
img.avatar:hover {
   box-shadow: 0 0 0 4px #efefef; 
}

/*列表*/
.cell {
    border-bottom: 1px solid #efefef;
    padding: 10px 15px;
}
/*最后一行分页下面处理*/
.box > .cell:last-child {
    border-radius: 8px;
}
/*列表回复气泡*/
a.count_livid:link,
a.count_livid:active {
    position: relative;
    font-weight: normal;
    font-size: 10px;
    background-color: #1f1f20;
    padding: 5px 8px;
}
a.count_livid:link:after, 
a.count_livid:active:after{
    content:"";
    position: absolute;
    top:6px;
    left:-6px;
    border:4px solid transparent;
    border-right-color:#1f1f20;
}
a.count_livid:hover {
    font-weight: normal; 
    background-color: #1f1f20;
    padding: 5px 8px;
}


a.count_blue:visited,
a.count_green:visited,
a.count_orange:visited,
a.count_livid:visited {
    font-weight: normal;
    color: #c5c5c5;
    background-color: #efefef;
}

a.count_blue:visited:after,
a.count_green:visited:after,
a.count_orange:visited:after,
a.count_livid:visited:after{
    content:"";
    position: absolute;
    top:6px;
    left:-6px;
    border:4px solid transparent;
    border-right-color:#efefef;
}
```

- example3: https://github.com/CrazyMelody/v2ex_style/blob/main/wechat.css

```css
/* 顶栏 */
div#Top {
    position: fixed;
    width: 100%;
    border-bottom: 0px;
    backdrop-filter: blur(20px);
    background-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0px 10px 10px 0px rgb(0 0 0 / 10%);
    z-index: 9999;
}

div#Wrapper {
    padding-top: 50px;
}

/* 对话框 */
.reply_content:before {
    content: "";
    width: 0px;
    height: 0px;
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
    border-right: 5px solid #ffffff;
    position: absolute;
    top: 5px;
    left: -4px;
    display: block;
}

.reply_content {
    font-size: 14px;
    line-height: 1.6;
    color: var(--box-foreground-color);
    word-break: break-word;
    background-color: white;
    border-radius: 4px;
    padding: 7px;
    position: relative;
    width: fit-content;
}

.cell {
    padding: 10px;
    font-size: 14px;
    line-height: 150%;
    text-align: left;
    border-bottom: 0px solid var(--box-border-color);
    background-color: #F3F3F3;
}

/* .cell:hover {
    background-color: #DEDEDE;
} */

/* 隐藏回复时间,回复按钮 */
.ago,
.fr {
    /* opacity: 0; */
    display: none;
}

/* 显示发布按钮 */
form+div>.fr {
    display: inline;
}

/* 悬浮显示回复时间 */
.cell:hover .ago,.cell:hover .fr {
    display: inline;
}


.cell .avatar {
    width: 40px;
}


#Wrapper {
    background-image: url(//res.wx.qq.com/t/wx_fed/webwx/res/static/img/2zrdI1g.jpg) !important;
    background-size: cover;
}

.box {
    border: 0px solid #e2e2ff !important;
    background: #F3F3F3;
}

.topic_buttons {
    padding: 5px;
    font-size: 14px;
    line-height: 120%;
    border-top: 1px solid #d4d4d4;
    text-align: left;
    background: #e2e2e2;
}

.super.button {
    background-image: none;
    padding: 4px 15px 3px;
    border: 1px solid rgba(80, 80, 90, .2);
    border-bottom-color: rgba(80, 80, 90, .35);
    border-radius: 3px;
    font-size: 14px;
    font-family: Arial, sans-serif;
    display: inline-block;
    line-height: 1.4;
    outline: 0;
}

/* 头像栏黑色背景 */
#Rightbar > div:nth-child(2) > .cell {
    background-color: #2e3238;
}

/* 发布页右边栏背景 */
.cell.topic_content.markdown_body {
    background-color: #F3F3F3 !important;
}
/* #Rightbar>.box {
    border: 0px;
    background: #F3F3F3;
} */

#Rightbar>.cell span.bigger>a {
    color: #ffffff;
    font-size: 20px;
}

/* #member-activity + .cell {
    background: #3a3f45;
} */

/* 账户余额链接 */
#member-activity+.cell a {
    color: #bcb4b4;
    text-shadow: 0 0 black;
}

.member-activity-start,.member-activity-half,.member-activity-almost,.member-activity-done {
    background-color: #46c11b
}

#Main tr>td:nth-child(3)>strong>a {
    font-weight: 200;
}


/* 回复数量 */
a.count_livid:active,
a.count_livid:link {
    background-color: #E75E58;
}

a:hover {
    text-decoration: none;
    /* text-shadow: 0px 0px 1px rgb(102 112 106 / 74%);
    transition: all 0.3s; */
}
a.topic-link:hover {
    text-decoration: none;
}

.balance_area,
a.balance_area:link,
a.balance_area:visited {
    border-radius: 4px;
    background: none;
}

textarea#reply_content {
    border: 0px;
    background: transparent;
}

textarea#reply_content:active {
    border: 0px;
}

.reply-box-sticky[stuck] {
    border-top: 1px solid var(--box-border-color);
    box-shadow: 0 3px 10px rgb(0 0 0 / 15%);
}

div#reply-box .cell:hover {
    background: none;
}

#reply-box span.gray {
    display: none;
}

#reply-box .cell.flex-row-end {
    display: none;
}

#reply-box>div:nth-child(2) {
    border-radius: 4px;
}

.self {display: flex;flex-direction: row-reverse;}

.self > td> strong {float: right;}

.self .reply_content{
    background-color:rgb(169,233,122)
}

.self .reply_content:before {display: none;}

.self .reply_content:after {
    content: "";
    width: 0px;
    height: 0px;
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
    border-left: 5px solid rgb(169,233,122);
    position: absolute;
    top: 5px;
    right: -4px;
    display: block;
}

/* 详情页标题绿标 */
.header h1:before {border-left: 4px solid #00c250;padding-left: 10px;;content: "";}

.header h1 {font-size: 20px;font-weight: 500;}


input.normal.button,button.normal.button {
    background: #00c250;
    color: #FFF !important;
    font-size: 14px;
    font-weight:400;
    text-shadow: none;
    border:0px;
    padding: 5px 20px;
}


input.normal.button:hover:enabled,button.normal.button:hover:enabled {
    background: #58D78C;
        color: #FFF !important;
    font-size: 14px;
    font-weight:400;
    text-shadow: none;
    border:0px;
    

}
```





