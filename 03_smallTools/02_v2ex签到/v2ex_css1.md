# 项目功能

收集的v2ex页面自定义CSS

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






