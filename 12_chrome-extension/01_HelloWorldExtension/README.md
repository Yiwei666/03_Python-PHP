# 项目功能

简单的chrome extension构建示例

# 文件结构

🟢 **manifest.json**

```json
{
  "manifest_version": 3,
  "name": "Hello World Extension",
  "version": "1.0",
  "description": "A simple Chrome extension that displays 'Hello World'",
  "permissions": [
    "activeTab"
  ],
  "content_scripts": [
    {
      "matches": ["<all_urls>"],
      "js": ["content.js"]
    }
  ]
}
```


🟢 **content.js**

```js
const button = document.createElement('button');
button.innerText = 'Click me!';
button.style.position = 'fixed';
button.style.top = '10px';
button.style.left = '10px';
button.style.zIndex = '9999';
document.body.appendChild(button);

button.addEventListener('click', function() {
  alert('Hello World!');
});
```
