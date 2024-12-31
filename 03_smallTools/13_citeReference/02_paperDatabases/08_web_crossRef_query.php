<?php
// 输出页面并指定编码
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8" />
    <title>CrossRef 查询示例</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f7f7f7;
        }
        h1, h2, h3 {
            color: #333;
        }
        .search-container {
            background-color: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .search-container input[type="text"] {
            width: 70%;
            padding: 8px;
            font-size: 14px;
        }
        .search-container button {
            padding: 10px 20px;
            background-color: #4CAF50;
            border: none;
            color: #fff;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
        }
        .search-container button:hover {
            background-color: #45a049;
        }
        /* 新增：单选按钮容器 */
        .radio-group {
            margin-top: 10px;
        }
        .radio-group label {
            margin-right: 20px;
            cursor: pointer;
        }
        .results-container {
            margin-top: 20px;
        }
        .item-card {
            background-color: #fff;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .item-header {
            font-weight: bold;
            margin-bottom: 10px;
            color: #555;
        }
        .item-content p {
            margin: 4px 0;
        }
        .item-buttons {
            margin-top: 10px;
        }
        .item-buttons button {
            padding: 8px 16px;
            margin-right: 8px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 13px;
            color: #fff;
        }
        .copy-base32-btn {
            background-color: #FF9800; /* 橙色 */
        }
        .tag-btn {
            background-color: #2196F3; /* 蓝色 */
        }
        .category-selection {
            position: fixed;
            top: 70px;
            right: 50px;
            width: 320px;
            max-height: 600px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            display: none; /* 默认隐藏 */
        }
        .category-selection h3 {
            margin-top: 0;
        }
        .category-selection .cat-item {
            margin-bottom: 8px;
        }
        .category-selection .cat-item input[type="checkbox"] {
            margin-right: 5px;
        }
        .save-categories-btn {
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .close-cat-btn {
            float: right;
            background: none;
            border: none;
            font-size: 18px;
            color: #666;
            cursor: pointer;
            margin-top: -8px;
        }
        .close-cat-btn:hover {
            color: #333;
        }
        /* -------------------------
           加载指示器 (Spinner) 样式
           ------------------------- */
        #loading-overlay {
            display: none; /* 默认隐藏 */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 99999; /* 盖住所有内容 */
            text-align: center;
        }
        #loading-overlay .spinner {
            display: inline-block;
            margin-top: 300px; /* 让spinner大致居中 */
            width: 60px;
            height: 60px;
            border: 6px solid #f3f3f3;
            border-radius: 50%;
            border-top: 6px solid #4CAF50;
            animation: spin 1s linear infinite;
        }
        /* 旋转动画关键帧 */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<h1>CrossRef 查询示例</h1>

<div class="search-container">
    <h2>输入查询信息</h2>
    <input type="text" id="query-input" placeholder="请输入需要查询的参考文献信息、标题或关键字，或输入DOI..." />
    <button id="search-btn">查询</button>
    
    <!-- 新增：单选按钮组，用于选择搜索模式 -->
    <div class="radio-group">
        <label>
            <input type="radio" name="search-mode" value="title" checked>
            Title
        </label>
        <label>
            <input type="radio" name="search-mode" value="doi">
            DOI
        </label>
    </div>
</div>

<div class="results-container" id="results-container"></div>

<!-- 分类选择的弹窗容器 -->
<div class="category-selection" id="category-selection-container">
    <button class="close-cat-btn" id="close-cat-btn">&times;</button>
    <h3>为论文添加分类标签</h3>
    <div id="category-list"></div>
    <button class="save-categories-btn" id="save-categories-btn">保存分类</button>
</div>

<!-- 加载指示器 (Spinner) -->
<div id="loading-overlay">
    <div class="spinner"></div>
</div>

<script>
/**
 * 本示例与之前的脚本逻辑相似，但新增了搜索模式切换(title / doi)。
 * 若搜索模式是title，则使用原来的查询API（可返回多条items）。
 * 若搜索模式是doi，则使用新的API (/works/{DOI}），只会返回一条数据。
 */

// ======================
//   全局配置
// ======================
const API_BASE_URL = 'https://chaye.one/'; // 与原油猴脚本保持一致

// 用于暂存每条 item 的完整信息
let currentItemsData = [];

// 用于在「标签」操作时保存当前正在处理的那条 item 的信息
let activeItemData = null;

// 分类选择弹窗
const categorySelectionContainer = document.getElementById('category-selection-container');
const categoryListContainer = document.getElementById('category-list');
const closeCatBtn = document.getElementById('close-cat-btn');
const saveCatBtn = document.getElementById('save-categories-btn');

// loading overlay
const loadingOverlay = document.getElementById('loading-overlay');

// 关闭分类弹窗
closeCatBtn.addEventListener('click', () => {
    categorySelectionContainer.style.display = 'none';
});

// ======================
//   页面事件绑定
// ======================
document.getElementById('search-btn').addEventListener('click', () => {
    const query = document.getElementById('query-input').value.trim();
    if (!query) {
        alert('请先输入查询信息或DOI');
        return;
    }
    searchCrossRef(query);
});

// 显示加载指示器
function showLoading() {
    loadingOverlay.style.display = 'block';
}

// 隐藏加载指示器
function hideLoading() {
    loadingOverlay.style.display = 'none';
}

// ======================
//   调用 CrossRef API
// ======================
function searchCrossRef(query) {
    // 显示 loading
    showLoading();

    // 获取当前的搜索模式
    const searchMode = document.querySelector('input[name="search-mode"]:checked').value;
    let apiUrl = '';

    if (searchMode === 'title') {
        // 原先的查询：一次返回多条 items
        apiUrl = `https://api.crossref.org/works?query=${encodeURIComponent(query)}&rows=20`;
    } else {
        // 查询 DOI：只返回单一 data.message
        // 注意：需对传入的 query 做 URL encode 处理
        apiUrl = `https://api.crossref.org/works/${encodeURIComponent(query)}`;
    }

    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            // 隐藏 loading
            hideLoading();

            if (searchMode === 'title') {
                // 处理多条结果
                if (data.message && data.message.items && data.message.items.length > 0) {
                    currentItemsData = data.message.items;
                    displayResults(currentItemsData);
                } else {
                    document.getElementById('results-container').innerHTML = `<p>未查询到结果。</p>`;
                }
            } else {
                // 处理单条结果
                // data.message 就是一个对象，与 items[i] 结构相似
                if (data.message) {
                    // 包装为数组后可重用 displayResults 逻辑
                    currentItemsData = [ data.message ];
                    displayResults(currentItemsData);
                } else {
                    document.getElementById('results-container').innerHTML = `<p>未查询到结果。</p>`;
                }
            }
        })
        .catch(err => {
            hideLoading();
            console.error('CrossRef 查询出错:', err);
            document.getElementById('results-container').innerHTML = `<p>查询失败，请稍后重试。</p>`;
        });
}

// ======================
//   显示检索结果
// ======================
function displayResults(items) {
    const container = document.getElementById('results-container');
    container.innerHTML = '';

    items.forEach((item, index) => {
        // 解析并收集信息
        const doi          = item.DOI || '未找到 DOI';
        const title        = item.title ? item.title.join(' ') : '未找到标题';
        const journal      = item['container-title'] ? item['container-title'].join(' ') : '未找到期刊名';
        const publisher    = item.publisher || '未找到出版商';
        const volume       = item.volume || '未找到卷号';
        const issue        = item.issue || '未找到期号';
        const pages        = item.page || '未找到页码';
        const articleNumber= item['article-number'] || '未找到文章号';

        // 出版年
        let publicationYear = '未找到出版年';
        if (item['published-print'] && item['published-print']['date-parts']) {
            publicationYear = item['published-print']['date-parts'][0][0];
        } else if (item['published-online'] && item['published-online']['date-parts']) {
            publicationYear = item['published-online']['date-parts'][0][0];
        }

        // ISSN
        let issnPrint = '未找到印刷版 ISSN';
        let issnOnline = '未找到电子版 ISSN';
        if (item['issn-type']) {
            const issnType = item['issn-type'];
            const foundPrint = issnType.find(i => i.type === 'print');
            const foundOnline = issnType.find(i => i.type === 'electronic');
            if (foundPrint) issnPrint = foundPrint.value;
            if (foundOnline) issnOnline = foundOnline.value;
        }

        // 作者信息
        const authorsArray = item.author || [];
        const fullAuthors = formatFullAuthors(authorsArray) || '未找到作者信息';
        const abbreviatedAuthors = formatAbbreviatedAuthors(authorsArray) || '未找到作者信息';

        // 计算 Base32
        let doiBase32 = '';
        if (doi && doi !== '未找到 DOI') {
            doiBase32 = toBase32(doi);
        }

        // 构造 item-card
        const card = document.createElement('div');
        card.className = 'item-card';

        // 标题区域
        const headerDiv = document.createElement('div');
        headerDiv.className = 'item-header';
        headerDiv.textContent = `检索结果 #${index + 1}`;
        card.appendChild(headerDiv);

        // 内容区域
        const contentDiv = document.createElement('div');
        contentDiv.className = 'item-content';
        contentDiv.innerHTML = `
            <p><strong>DOI:</strong> ${doi}</p>
            ${doiBase32 ? `<p><strong>DOI Base32:</strong> ${doiBase32}</p>` : ''}
            <p><strong>标题:</strong> ${title}</p>
            <p><strong>期刊名:</strong> ${journal}</p>
            <p><strong>出版年:</strong> ${publicationYear}</p>
            <p><strong>卷号:</strong> ${volume}</p>
            <p><strong>期号:</strong> ${issue}</p>
            <p><strong>页码:</strong> ${pages}</p>
            <p><strong>文章号:</strong> ${articleNumber}</p>
            <p><strong>出版商:</strong> ${publisher}</p>
            <p><strong>ISSN (印刷版):</strong> ${issnPrint}</p>
            <p><strong>ISSN (电子版):</strong> ${issnOnline}</p>
            <p><strong>完整作者信息:</strong> ${fullAuthors}</p>
            <p><strong>缩写作者信息:</strong> ${abbreviatedAuthors}</p>
        `;
        card.appendChild(contentDiv);

        // 按钮区域
        const buttonsDiv = document.createElement('div');
        buttonsDiv.className = 'item-buttons';

        // 复制 Base32 按钮
        const copyBtn = document.createElement('button');
        copyBtn.className = 'copy-base32-btn';
        copyBtn.textContent = '复制 Base32';
        if (!doiBase32) {
            copyBtn.disabled = true;
            copyBtn.style.opacity = '0.6';
        }
        copyBtn.addEventListener('click', () => {
            if (!doiBase32) {
                alert('当前 DOI 不可用，无法复制 Base32。');
                return;
            }
            copyToClipboard(doiBase32)
                .then(() => alert('Base32 已复制到剪贴板！'))
                .catch(e => console.error('复制失败:', e));
        });
        buttonsDiv.appendChild(copyBtn);

        // 标签 按钮
        const tagBtn = document.createElement('button');
        tagBtn.className = 'tag-btn';
        tagBtn.textContent = '标签';
        tagBtn.addEventListener('click', () => {
            if (!doi || doi === '未找到 DOI') {
                alert('当前 DOI 不可用，无法进行标签操作。');
                return;
            }
            // 准备该条 item 数据
            activeItemData = {
                doi,
                title,
                journal,
                publicationYear,
                volume,
                issue,
                pages,
                articleNumber,
                publisher,
                issnPrint,
                issnOnline,
                fullAuthors,
                abbreviatedAuthors
            };
            // 调用标签操作
            handleTagButtonClick();
        });
        buttonsDiv.appendChild(tagBtn);

        card.appendChild(buttonsDiv);
        container.appendChild(card);
    });
}

// ======================
//   标签按钮的逻辑
// ======================
function handleTagButtonClick() {
    // 先将论文信息写入数据库
    sendPaperData(activeItemData)
        .then(response => {
            if (response.success) {
                // 获取所有分类
                return fetchCategories();
            } else {
                throw new Error(response.message || '添加论文失败。');
            }
        })
        .then(categories => {
            // 获取该论文已有的分类
            return fetchPaperCategories(activeItemData.doi)
                .then(paperCategories => {
                    return { categories, paperCategories };
                });
        })
        .then(({ categories, paperCategories }) => {
            // 显示分类选择界面
            displayCategorySelection(categories, paperCategories);
        })
        .catch(error => {
            console.error(error);
            alert(error.message);
        });
}

// 显示分类选择界面
function displayCategorySelection(categories, paperCategories) {
    categoryListContainer.innerHTML = '';
    categorySelectionContainer.style.display = 'block';

    // paperCategories 可能是字符串，需要转成数字再比较
    const numericPaperCategories = paperCategories.map(x => parseInt(x, 10));

    categories.forEach(cat => {
        const catIDNum = parseInt(cat.categoryID, 10);

        // 创建 checkbox
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = catIDNum;
        checkbox.id = `cat-checkbox-${catIDNum}`;

        // 0 All papers (实际上 categoryID=1) 要强制选中
        if (catIDNum === 1) {
            checkbox.checked = true;
            checkbox.disabled = true;
        } else {
            if (numericPaperCategories.includes(catIDNum)) {
                checkbox.checked = true;
            }
        }

        // label
        const label = document.createElement('label');
        label.htmlFor = `cat-checkbox-${catIDNum}`;
        label.textContent = cat.category_name;

        const div = document.createElement('div');
        div.className = 'cat-item';
        div.appendChild(checkbox);
        div.appendChild(label);

        categoryListContainer.appendChild(div);
    });
}

// 点击「保存分类」按钮
saveCatBtn.addEventListener('click', () => {
    // 收集所有选中的分类
    const checkboxes = categoryListContainer.querySelectorAll('input[type="checkbox"]');
    const selectedIDs = [];
    checkboxes.forEach(cb => {
        if (cb.checked) {
            selectedIDs.push(parseInt(cb.value, 10));
        }
    });
    // 确保 0 All papers (categoryID=1) 一定在里面
    if (!selectedIDs.includes(1)) {
        selectedIDs.push(1);
    }

    // 调用 updatePaperCategories
    updatePaperCategories(activeItemData.doi, selectedIDs)
        .then(response => {
            if (response.success) {
                alert('分类已成功更新。');
                categorySelectionContainer.style.display = 'none';
            } else {
                throw new Error(response.message || '更新分类失败。');
            }
        })
        .catch(err => {
            console.error(err);
            alert(err.message);
        });
});

// ======================
//   与后端接口交互
// ======================

// 向服务器发送论文数据
function sendPaperData(data) {
    return new Promise((resolve, reject) => {
        fetch(API_BASE_URL + '08_tm_add_paper.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: data.title,
                authors: data.fullAuthors,
                journal_name: data.journal,
                publication_year: data.publicationYear,
                volume: data.volume,
                issue: data.issue,
                pages: data.pages,
                article_number: data.articleNumber,
                doi: data.doi,
                issn: data.issnPrint,  // 仅写入印刷版 ISSN
                publisher: data.publisher
            })
        })
        .then(res => res.json())
        .then(json => {
            if (json.success) {
                resolve({ success: true, paperID: json.paperID });
            } else {
                resolve({ success: false, message: json.message });
            }
        })
        .catch(err => reject(err));
    });
}

// 获取所有分类
function fetchCategories() {
    return new Promise((resolve, reject) => {
        fetch(API_BASE_URL + '08_tm_get_categories.php')
            .then(res => res.json())
            .then(json => {
                if (json.success) {
                    resolve(json.categories);
                } else {
                    reject(new Error(json.message || '获取分类失败。'));
                }
            })
            .catch(err => reject(err));
    });
}

// 获取某论文当前的分类
function fetchPaperCategories(doi) {
    return new Promise((resolve, reject) => {
        fetch(API_BASE_URL + `08_tm_get_paper_categories.php?doi=${encodeURIComponent(doi)}`)
            .then(res => res.json())
            .then(json => {
                if (json.success) {
                    resolve(json.categoryIDs || []);
                } else {
                    reject(new Error(json.message || '获取论文分类失败。'));
                }
            })
            .catch(err => reject(err));
    });
}

// 更新某论文的分类
function updatePaperCategories(doi, categoryIDs) {
    return new Promise((resolve, reject) => {
        fetch(API_BASE_URL + '08_tm_update_paper_categories.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ doi, categoryIDs })
        })
        .then(res => res.json())
        .then(json => {
            if (json.success) {
                resolve({ success: true });
            } else {
                resolve({ success: false, message: json.message });
            }
        })
        .catch(err => reject(err));
    });
}

// ======================
//  工具函数
// ======================

// 格式化完整作者信息
function formatFullAuthors(authorsArray) {
    if (!authorsArray || authorsArray.length === 0) {
        return '';
    }
    const formattedAuthors = authorsArray.map(author => {
        const given = author.given || '';
        const family = author.family || '';
        return `${given} ${family}`.trim();
    });
    return formattedAuthors.join(', ');
}

// 格式化缩写作者信息
function formatAbbreviatedAuthors(authorsArray) {
    if (!authorsArray || authorsArray.length === 0) {
        return '';
    }
    const formattedAuthors = authorsArray.map(author => {
        const given = author.given || '';
        const family = author.family || '';
        // 处理 given 名字，取首字母
        let abbreviatedGiven = '';
        if (given.includes(' ')) {
            const parts = given.split(' ');
            abbreviatedGiven = parts.map(part => part.charAt(0).toUpperCase() + '.').join('');
        } else {
            abbreviatedGiven = given.charAt(0).toUpperCase() + '.';
        }
        return `${abbreviatedGiven} ${family}`.trim();
    });
    // 与之前油猴脚本逻辑相同
    const numAuthors = formattedAuthors.length;
    if (numAuthors === 1) {
        return formattedAuthors[0];
    } else if (numAuthors === 2) {
        return `${formattedAuthors[0]} and ${formattedAuthors[1]}`;
    } else {
        const allButLastTwo = formattedAuthors.slice(0, -2).join(', ');
        const lastTwo = formattedAuthors.slice(-2).join(', and ');
        return allButLastTwo ? `${allButLastTwo}, ${lastTwo}` : lastTwo;
    }
}

/**
 * 按照 RFC 4648 标准进行 Base32 编码
 * 包含必要的“=”号填充，使结果长度为 8 的倍数
 */
function toBase32(input) {
    // 将字符串转换为 UTF-8 字节
    const bytes = stringToUtf8Bytes(input);
    // 转为二进制
    let bitString = '';
    for (const b of bytes) {
        bitString += b.toString(2).padStart(8, '0');
    }
    // 每 5 位分组，不足则补 0
    const remainder = bitString.length % 5;
    if (remainder !== 0) {
        bitString = bitString.padEnd(bitString.length + (5 - remainder), '0');
    }
    // base32 字母表
    const base32Alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    let base32String = '';
    for (let i = 0; i < bitString.length; i += 5) {
        const chunk = bitString.substr(i, 5);
        const index = parseInt(chunk, 2);
        base32String += base32Alphabet[index];
    }
    // 添加 '=' 使长度为 8 的倍数
    const mod8 = base32String.length % 8;
    if (mod8 !== 0) {
        base32String += '='.repeat(8 - mod8);
    }
    return base32String;
}

function stringToUtf8Bytes(str) {
    // 现代浏览器可用 TextEncoder
    const encoder = new TextEncoder();
    return encoder.encode(str);
}

// 复制到剪贴板
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    } else {
        // fallback
        return new Promise((resolve, reject) => {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.top = '-99999px';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                document.body.removeChild(textarea);
                resolve();
            } catch (err) {
                document.body.removeChild(textarea);
                reject(err);
            }
        });
    }
}
</script>

</body>
</html>
