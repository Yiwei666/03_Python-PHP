// ==UserScript==
// @name         Extract Citation Data with DOI Lookup and Complete Reference Info
// @namespace    http://tampermonkey.net/
// @version      1.9
// @description  提取 Google Scholar 上 GB/T 7714 和 APA 引用，查询 DOI 并显示详细元数据，包括期号、文章编号、出版商和 ISSN（标注类型），并将数据写入云服务器数据库并进行分类。
// @author
// @match        https://scholar.google.com/*
// @match        https://scholar.google.com.hk/*
// @grant        GM_xmlhttpRequest
// @connect      api.crossref.org
// @connect      chaye.one
// ==/UserScript==

(function () {
    'use strict';

    // 配置您的服务器API基础URL
    const API_BASE_URL = 'https://chaye.one/'; // 确保末尾有斜杠

    // 定义按钮
    const extractButton = document.createElement('button');
    extractButton.textContent = '提取内容并查询 DOI';
    extractButton.style.position = 'fixed';
    extractButton.style.top = '10px';
    extractButton.style.right = '10px';
    extractButton.style.zIndex = 9999;
    extractButton.style.backgroundColor = '#4CAF50';
    extractButton.style.color = 'white';
    extractButton.style.border = 'none';
    extractButton.style.padding = '10px';
    extractButton.style.cursor = 'pointer';

    const tagButton = document.createElement('button');
    tagButton.textContent = '标签';
    tagButton.style.position = 'fixed';
    tagButton.style.top = '50px';
    tagButton.style.right = '10px';
    tagButton.style.zIndex = 9999;
    tagButton.style.backgroundColor = '#2196F3';
    tagButton.style.color = 'white';
    tagButton.style.border = 'none';
    tagButton.style.padding = '10px';
    tagButton.style.cursor = 'pointer';
    tagButton.style.display = 'none'; // 初始隐藏

    // 存储提取的论文数据
    let extractedData = {};

    window.addEventListener('load', () => {
        document.body.appendChild(extractButton);
        document.body.appendChild(tagButton);

        // 点击“提取内容并查询 DOI”按钮事件
        extractButton.addEventListener('click', function () {
            try {
                const gbElement = document.evaluate(
                    "//th[text()='GB/T 7714']/following-sibling::td/div[@class='gs_citr']",
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null
                ).singleNodeValue;

                const apaElement = document.evaluate(
                    "//th[text()='APA']/following-sibling::td/div[@class='gs_citr']",
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null
                ).singleNodeValue;

                if (!gbElement || !apaElement) {
                    alert('未找到目标内容，请检查页面是否正确加载');
                    return;
                }

                const gbText = gbElement.textContent.trim();
                const apaText = apaElement.textContent.trim();

                // 提取 GB/T 7714 中的标题
                const extractedTitle = extractTitleFromReference(gbText);

                // 存储提取的数据
                extractedData = { gbText, apaText, extractedTitle };

                displayResult(extractedData);

                queryDOI(gbText, extractedTitle).then(() => {
                    // 显示“标签”按钮
                    tagButton.style.display = 'block';
                });
            } catch (e) {
                console.error("提取时出错:", e);
                alert('提取过程中发生错误，请检查脚本或页面内容');
            }
        });

        // 点击“标签”按钮事件
        tagButton.addEventListener('click', function () {
            if (!extractedData.doi || extractedData.doi === '查询中...' || extractedData.doi === '未找到 DOI') {
                alert('请先提取并查询 DOI 后再进行标签操作。');
                return;
            }

            // 发送论文数据到服务器
            sendPaperData(extractedData)
                .then(response => {
                    if (response.success) {
                        const paperID = response.paperID;
                        alert('论文已成功添加到数据库（或已存在数据库中）。');

                        // 获取所有分类
                        return fetchCategories();
                    } else {
                        throw new Error(response.message || '添加论文失败。');
                    }
                })
                .then(categories => {
                    // 获取论文当前的分类
                    return fetchPaperCategories(extractedData.doi)
                        .then(paperCategories => {
                            return { categories, paperCategories };
                        });
                })
                .then(({ categories, paperCategories }) => {
                    // 显示分类选择界面
                    displayCategorySelection(categories, paperCategories, extractedData.doi);
                })
                .catch(error => {
                    console.error(error);
                    alert(error.message);
                });
        });
    });

    // 动态创建弹窗显示结果
    function displayResult({
        gbText,
        apaText,
        extractedTitle = '提取中...',
        doi = '查询中...',
        title = '查询中...',
        journal = '查询中...',
        publicationYear = '查询中...',
        volume = '查询中...',
        issue = '查询中...',
        pages = '查询中...',
        articleNumber = '查询中...',
        publisher = '查询中...',
        issnPrint = '查询中...',
        issnOnline = '查询中...',
        fullAuthors = '查询中...',
        abbreviatedAuthors = '查询中...',
        matchResult = ''
    }) {
        let container = document.getElementById('result-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'result-container';
            container.style.position = 'fixed';
            container.style.top = '80px';
            container.style.right = '10px';
            container.style.width = '350px';
            container.style.maxHeight = '600px';
            container.style.padding = '10px';
            container.style.border = '1px solid #ccc';
            container.style.backgroundColor = '#fff';
            container.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
            container.style.zIndex = 9999;
            container.style.overflowY = 'auto';
            container.style.fontFamily = 'Arial, sans-serif';
            container.style.fontSize = '14px';
            document.body.appendChild(container);
        }

        container.innerHTML = `
            <h3>提取结果</h3>
            <p><strong>GB/T 7714:</strong> ${gbText}</p>
            <p><strong>APA:</strong> ${apaText}</p>
            <p><strong>提取的文章标题:</strong> ${extractedTitle}</p>
            <h3>DOI 查询结果</h3>
            <p><strong>DOI:</strong> ${doi}</p>
            <p><strong>标题:</strong> ${title}</p>
            <p><strong>期刊名:</strong> ${journal}</p>
            <p><strong>出版年:</strong> ${publicationYear}</p>
            <p><strong>卷:</strong> ${volume}</p>
            <p><strong>期:</strong> ${issue}</p>
            <p><strong>页码:</strong> ${pages}</p>
            <p><strong>文章号:</strong> ${articleNumber}</p>
            <p><strong>出版商:</strong> ${publisher}</p>
            <p><strong>ISSN (印刷版):</strong> ${issnPrint}</p>
            <p><strong>ISSN (电子版):</strong> ${issnOnline}</p>
            <p><strong>完整作者信息:</strong> ${fullAuthors}</p>
            <p><strong>缩写作者信息:</strong> ${abbreviatedAuthors}</p>
            <p><strong>匹配结果:</strong> ${matchResult}</p>
        `;
    }

    // 查询 CrossRef API 并更新弹窗
    function queryDOI(reference, extractedTitle) {
        return new Promise((resolve, reject) => {
            const apiUrl = `https://api.crossref.org/works?query=${encodeURIComponent(reference)}`;

            GM_xmlhttpRequest({
                method: 'GET',
                url: apiUrl,
                onload: (response) => {
                    try {
                        const data = JSON.parse(response.responseText);
                        if (data.message && data.message.items && data.message.items.length > 0) {
                            const firstResult = data.message.items[0];
                            const doi = firstResult.DOI || '未找到 DOI';
                            const title = firstResult.title ? firstResult.title.join(' ') : '未找到标题';

                            // 获取期刊名、出版年、卷、期、页码、文章号
                            const journal = firstResult['container-title']
                                ? firstResult['container-title'].join(' ')
                                : '未找到期刊名';
                            const publicationYear = firstResult['published-print']
                                ? firstResult['published-print']['date-parts'][0][0]
                                : (firstResult['published-online']
                                    ? firstResult['published-online']['date-parts'][0][0]
                                    : '未找到出版年');
                            const volume = firstResult.volume || '未找到卷号';
                            const issue = firstResult.issue || '未找到期号';
                            const pages = firstResult.page || '未找到页码';
                            const articleNumber = firstResult['article-number'] || '未找到文章号';

                            // 获取出版商
                            const publisher = firstResult.publisher || '未找到出版商';

                            // 获取 ISSN 并标注类型
                            const issnType = firstResult['issn-type'] || [];
                            const issnPrint = issnType.find(item => item.type === 'print')?.value || '未找到印刷版 ISSN';
                            const issnOnline = issnType.find(item => item.type === 'electronic')?.value || '未找到电子版 ISSN';

                            // 获取完整作者信息
                            const authorsArray = firstResult.author || [];
                            const fullAuthors = formatFullAuthors(authorsArray) || '未找到作者信息';

                            // 获取并格式化缩写的作者信息
                            const abbreviatedAuthors = formatAbbreviatedAuthors(authorsArray) || '未找到作者信息';

                            // 校验标题是否匹配
                            const matchResult = compareTitles(title, extractedTitle)
                                ? '匹配成功'
                                : '标题不匹配，请检查引用或查询结果';

                            // 更新弹窗内容
                            displayResult({
                                gbText: reference,
                                apaText: reference,
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
                                abbreviatedAuthors,
                                matchResult
                            });

                            // 更新 'extractedData' with fetched data
                            extractedData = {
                                ...extractedData,
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
                                abbreviatedAuthors,
                                matchResult
                            };

                            resolve();
                        } else {
                            displayResult({
                                gbText: reference,
                                apaText: reference,
                                doi: '未找到 DOI',
                                title: '未找到标题',
                                journal: '未找到期刊名',
                                publicationYear: '未找到出版年',
                                volume: '未找到卷号',
                                issue: '未找到期号',
                                pages: '未找到页码',
                                articleNumber: '未找到文章号',
                                publisher: '未找到出版商',
                                issnPrint: '未找到印刷版 ISSN',
                                issnOnline: '未找到电子版 ISSN',
                                fullAuthors: '未找到作者信息',
                                abbreviatedAuthors: '未找到作者信息',
                                extractedTitle: extractTitleFromReference(reference)
                            });

                            // 更新 'extractedData' with incomplete data
                            extractedData = {
                                ...extractedData,
                                doi: '未找到 DOI',
                                title: '未找到标题',
                                journal: '未找到期刊名',
                                publicationYear: '未找到出版年',
                                volume: '未找到卷号',
                                issue: '未找到期号',
                                pages: '未找到页码',
                                articleNumber: '未找到文章号',
                                publisher: '未找到出版商',
                                issnPrint: '未找到印刷版 ISSN',
                                issnOnline: '未找到电子版 ISSN',
                                fullAuthors: '未找到作者信息',
                                abbreviatedAuthors: '未找到作者信息',
                                matchResult: ''
                            };

                            resolve();
                        }
                    } catch (e) {
                        console.error("解析 API 响应时出错:", e);
                        displayResult({
                            gbText: reference,
                            apaText: reference,
                            doi: '查询失败',
                            title: '查询失败',
                            journal: '查询失败',
                            publicationYear: '查询失败',
                            volume: '查询失败',
                            issue: '查询失败',
                            pages: '查询失败',
                            articleNumber: '查询失败',
                            publisher: '查询失败',
                            issnPrint: '查询失败',
                            issnOnline: '查询失败',
                            fullAuthors: '查询失败',
                            abbreviatedAuthors: '查询失败',
                            extractedTitle: extractTitleFromReference(reference)
                        });

                        resolve(); // 即使出错，也继续执行
                    }
                },
                onerror: () => {
                    displayResult({
                        gbText: reference,
                        apaText: reference,
                        doi: '查询失败',
                        title: '查询失败',
                        journal: '查询失败',
                        publicationYear: '查询失败',
                        volume: '查询失败',
                        issue: '查询失败',
                        pages: '查询失败',
                        articleNumber: '查询失败',
                        publisher: '查询失败',
                        issnPrint: '查询失败',
                        issnOnline: '查询失败',
                        fullAuthors: '查询失败',
                        abbreviatedAuthors: '查询失败',
                        extractedTitle: extractTitleFromReference(reference)
                    });
                    resolve(); // 即使出错，也继续执行
                }
            });
        });
    }

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

    // 格式化缩写后的作者信息
    function formatAbbreviatedAuthors(authorsArray) {
        if (!authorsArray || authorsArray.length === 0) {
            return '';
        }

        const formattedAuthors = authorsArray.map(author => {
            const given = author.given || '';
            const family = author.family || '';

            // 处理 given 名
            let abbreviatedGiven = '';
            if (given.includes(' ')) {
                const parts = given.split(' ');
                abbreviatedGiven = parts.map(part => part.charAt(0).toUpperCase() + '.').join('');
            } else {
                abbreviatedGiven = given.charAt(0).toUpperCase() + '.';
            }

            return `${abbreviatedGiven} ${family}`.trim();
        });

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

    // 从 GB/T 7714 格式中提取标题
    function extractTitleFromReference(reference) {
        // 定位第一个和第二个 "."，并去掉 [J]
        const firstDot = reference.indexOf('.');
        const secondDot = reference.indexOf('.', firstDot + 1);
        if (firstDot === -1 || secondDot === -1) {
            return '未找到标题';
        }
        const string1 = reference.substring(firstDot + 1, secondDot).trim();
        const string2 = string1.replace('[J]', '').trim(); // 去掉 [J]
        return string2;
    }

    // 比较两个标题是否匹配
    function compareTitles(title1, title2) {
        if (!title1 || !title2) return false;

        // 使用字符串相似度算法（Levenshtein 距离）
        const similarity = calculateSimilarity(title1.toLowerCase(), title2.toLowerCase());
        return similarity > 0.5; // 阈值设置为 50%
    }

    // 计算字符串相似度（Levenshtein 距离）
    function calculateSimilarity(s1, s2) {
        const len1 = s1.length, len2 = s2.length;
        const dp = Array.from({ length: len1 + 1 }, () => Array(len2 + 1).fill(0));
        for (let i = 0; i <= len1; i++) dp[i][0] = i;
        for (let j = 0; j <= len2; j++) dp[0][j] = j;
        for (let i = 1; i <= len1; i++) {
            for (let j = 1; j <= len2; j++) {
                const cost = s1[i - 1] === s2[j - 1] ? 0 : 1;
                dp[i][j] = Math.min(
                    dp[i - 1][j] + 1,       // deletion
                    dp[i][j - 1] + 1,       // insertion
                    dp[i - 1][j - 1] + cost // substitution
                );
            }
        }
        const distance = dp[len1][len2];
        return 1 - distance / Math.max(len1, len2); // 相似度计算
    }

    // 发送论文数据到服务器并获取 paperID
    function sendPaperData(data) {
        return new Promise((resolve, reject) => {
            GM_xmlhttpRequest({
                method: 'POST',
                url: API_BASE_URL + '08_tm_add_paper.php',
                headers: {
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    title: data.title,
                    authors: data.fullAuthors,
                    journal_name: data.journal,
                    publication_year: data.publicationYear,
                    volume: data.volume,
                    issue: data.issue,
                    pages: data.pages,
                    article_number: data.articleNumber,
                    doi: data.doi,
                    issn: data.issnPrint, // 仅写入印刷版 ISSN
                    publisher: data.publisher
                }),
                onload: (response) => {
                    try {
                        const res = JSON.parse(response.responseText);
                        if (res.success) {
                            resolve({ success: true, paperID: res.paperID });
                        } else {
                            resolve({ success: false, message: res.message });
                        }
                    } catch (e) {
                        reject(new Error('解析服务器响应失败。'));
                    }
                },
                onerror: () => {
                    reject(new Error('发送请求失败。'));
                }
            });
        });
    }

    // 获取所有分类
    function fetchCategories() {
        return new Promise((resolve, reject) => {
            GM_xmlhttpRequest({
                method: 'GET',
                url: API_BASE_URL + '08_tm_get_categories.php',
                onload: (response) => {
                    try {
                        const res = JSON.parse(response.responseText);
                        if (res.success) {
                            resolve(res.categories);
                        } else {
                            reject(new Error(res.message || '获取分类失败。'));
                        }
                    } catch (e) {
                        reject(new Error('解析服务器响应失败。'));
                    }
                },
                onerror: () => {
                    reject(new Error('发送请求失败。'));
                }
            });
        });
    }

    // 获取论文当前的分类
    function fetchPaperCategories(doi) {
        return new Promise((resolve, reject) => {
            GM_xmlhttpRequest({
                method: 'GET',
                url: API_BASE_URL + `08_tm_get_paper_categories.php?doi=${encodeURIComponent(doi)}`,
                onload: (response) => {
                    try {
                        const res = JSON.parse(response.responseText);
                        if (res.success) {
                            resolve(res.categoryIDs);
                        } else {
                            reject(new Error(res.message || '获取论文分类失败。'));
                        }
                    } catch (e) {
                        reject(new Error('解析服务器响应失败。'));
                    }
                },
                onerror: () => {
                    reject(new Error('发送请求失败。'));
                }
            });
        });
    }

    // 显示分类选择界面
    function displayCategorySelection(categories, paperCategories, doi) {
        let container = document.getElementById('category-selection-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'category-selection-container';
            container.style.position = 'fixed';
            container.style.top = '50px';
            container.style.right = '400px';
            container.style.width = '300px';
            container.style.maxHeight = '600px';
            container.style.padding = '10px';
            container.style.border = '1px solid #ccc';
            container.style.backgroundColor = '#fff';
            container.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
            container.style.zIndex = 9999;
            container.style.overflowY = 'auto';
            container.style.fontFamily = 'Arial, sans-serif';
            container.style.fontSize = '14px';
            document.body.appendChild(container);
        } else {
            container.innerHTML = ''; // 清空之前的内容
        }

        container.innerHTML = `<h3>为论文添加分类标签</h3>`;

        categories.forEach(category => {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = `category-${category.categoryID}`;
            checkbox.value = category.categoryID;

            // '0 All papers' 实际上是 categoryID = 1，始终选中且禁用
            if (category.categoryID === 1) {
                checkbox.checked = true;
                checkbox.disabled = true;
            } else {
                if (paperCategories.includes(category.categoryID)) {
                    checkbox.checked = true;
                }
            }

            const label = document.createElement('label');
            label.htmlFor = `category-${category.categoryID}`;
            label.textContent = category.category_name;
            label.style.marginLeft = '5px';

            const div = document.createElement('div');
            div.style.marginBottom = '5px';
            div.appendChild(checkbox);
            div.appendChild(label);

            container.appendChild(div);
        });

        // 添加保存按钮
        const saveButton = document.createElement('button');
        saveButton.textContent = '保存分类';
        saveButton.style.marginTop = '10px';
        saveButton.style.padding = '5px 10px';
        saveButton.style.backgroundColor = '#4CAF50';
        saveButton.style.color = 'white';
        saveButton.style.border = 'none';
        saveButton.style.cursor = 'pointer';
        container.appendChild(saveButton);

        // 保存按钮点击事件
        saveButton.addEventListener('click', function () {
            const selectedCategories = [];
            categories.forEach(category => {
                const checkbox = document.getElementById(`category-${category.categoryID}`);
                if (checkbox.checked) {
                    selectedCategories.push(category.categoryID);
                }
            });

            // 确保 "0 All papers"（categoryID=1） 始终包含
            if (!selectedCategories.includes(1)) {
                selectedCategories.push(1);
            }

            // 发送更新到服务器
            updatePaperCategories(doi, selectedCategories)
                .then(response => {
                    if (response.success) {
                        alert('分类已成功更新。');
                        // 关闭分类选择界面
                        container.style.display = 'none';
                    } else {
                        throw new Error(response.message || '更新分类失败。');
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert(error.message);
                });
        });

        // 显示分类选择界面
        container.style.display = 'block';
    }

    // 更新论文分类
    function updatePaperCategories(doi, categoryIDs) {
        return new Promise((resolve, reject) => {
            GM_xmlhttpRequest({
                method: 'POST',
                url: API_BASE_URL + '08_tm_update_paper_categories.php',
                headers: {
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    doi: doi,
                    categoryIDs: categoryIDs
                }),
                onload: (response) => {
                    try {
                        const res = JSON.parse(response.responseText);
                        if (res.success) {
                            resolve({ success: true });
                        } else {
                            resolve({ success: false, message: res.message });
                        }
                    } catch (e) {
                        reject(new Error('解析服务器响应失败。'));
                    }
                },
                onerror: () => {
                    reject(new Error('发送请求失败。'));
                }
            });
        });
    }

})();
