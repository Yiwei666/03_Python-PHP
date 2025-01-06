// ==UserScript==
// @name         Extract Citation Data with DOI Lookup and Complete Reference Info (Base32 added)
// @namespace    http://tampermonkey.net/
// @version      1.14
// @description  提取 Google Scholar 上 GB/T 7714 和 APA 引用，查询 DOI 并显示详细元数据（含分类标签），并将数据写入云服务器数据库并进行分类。新增Base32显示与复制功能，优化按钮垂直居中。增加当部分关键信息缺失时弹窗提示的功能，并在分类窗口中增加关闭和取消按钮。
// @author
// @match        https://scholar.google.com/*
// @match        https://scholar.google.com.hk/*
// @match        https://scholar.google.co.uk/*
// @match        https://scholar.google.de/*
// @match        https://scholar.google.fr/*
// @match        https://scholar.google.co.jp/*
// @match        https://scholar.google.ca/*
// @match        https://scholar.google.com.au/*
// @match        https://scholar.google.co.in/*
// @match        https://scholar.google.es/*
// @match        https://scholar.google.it/*
// @match        https://scholar.google.com.br/*
// @match        https://scholar.google.ru/*
// @match        https://scholar.google.nl/*
// @match        https://scholar.google.com.sg/*
// @match        https://scholar.google.com.mx/*
// @match        https://scholar.google.com.tr/*
// @match        https://scholar.google.com.ar/*
// @match        https://scholar.google.co.kr/*
// @match        https://scholar.google.se/*
// @match        https://scholar.google.ch/*
// @grant        GM_xmlhttpRequest
// @connect      api.crossref.org
// @connect      chaye.one
// ==/UserScript==

(function () {
    'use strict';

    // ======================
    //    配置区
    // ======================

    // 配置您的服务器API基础URL
    const API_BASE_URL = 'https://chaye.one/'; // 确保末尾有斜杠

    // [MODIFIED] 在脚本中添加 API_KEY 用于后端认证
    const API_KEY = 'YOUR_API_KEY_HERE';

    // 是否已弹出缺失信息提示（只弹一次）
    let missingFieldsAlertShown = false;

    // ======================
    //    界面元素创建
    // ======================

    // 创建一个通用的按钮样式函数，以确保所有按钮的样式一致
    function createButton(text, top, right, backgroundColor) {
        const button = document.createElement('button');
        button.textContent = text;
        button.style.position = 'fixed';
        button.style.top = `${top}px`;
        button.style.right = `${right}px`;
        button.style.zIndex = 9999;
        button.style.backgroundColor = backgroundColor;
        button.style.color = 'white';
        button.style.border = 'none';
        button.style.padding = '10px 15px';
        button.style.cursor = 'pointer';
        button.style.display = 'flex';
        button.style.alignItems = 'center';
        button.style.justifyContent = 'center';
        button.style.fontSize = '13px';
        button.style.borderRadius = '4px'; // 增加圆角效果
        return button;
    }

    // 「提取内容并查询 DOI」按钮
    const extractButton = createButton('提取内容并查询 DOI', 10, 10, '#4CAF50');

    // 「标签」按钮（初始隐藏，在提取后显示）
    const tagButton = createButton('标签', 50, 10, '#2196F3');
    tagButton.style.display = 'none';

    // 「复制 Base32」按钮（初始隐藏，只有成功获取 doi 后才显示）
    const copyBase32Button = createButton('复制 Base32', 10, 170, '#FF9800');
    copyBase32Button.style.display = 'none';

    // 存储提取的论文数据
    let extractedData = {};

    // ======================
    //   事件与功能逻辑
    // ======================

    window.addEventListener('load', () => {
        document.body.appendChild(extractButton);
        document.body.appendChild(tagButton);
        document.body.appendChild(copyBase32Button);

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

                // 先在弹窗中展示初步信息
                displayResult(extractedData);

                // 执行 CrossRef DOI 查询
                queryDOI(gbText, extractedTitle)
                    .then(() => {
                        // 如果查询到一个有效 DOI，则再尝试获取它在数据库中已有的分类
                        if (
                            extractedData.doi &&
                            extractedData.doi !== '未找到 DOI' &&
                            extractedData.doi !== '查询失败'
                        ) {
                            return fetchPaperCategories(extractedData.doi)
                                .then(paperCatIDs => {
                                    // 如果存在paperCatIDs，则尝试获取分类的名称并在显示中列出
                                    return fetchCategories().then(allCats => {
                                        // 将 categoryIDs 转换为数字
                                        const numericPaperCatIDs = paperCatIDs.map(id => parseInt(id, 10));

                                        // 将所有分类的 categoryID 也转为数字，以便匹配
                                        const catNameList = [];
                                        allCats.forEach(cat => {
                                            const catID = parseInt(cat.categoryID, 10);
                                            if (numericPaperCatIDs.includes(catID)) {
                                                catNameList.push(cat.category_name);
                                            }
                                        });

                                        // 将已有分类名称保存到 extractedData 以显示
                                        extractedData.existingCategoryNames = catNameList;

                                        // 再次更新弹窗信息，显示分类标签
                                        displayResult(extractedData);
                                    });
                                })
                                .catch(() => {
                                    // 如果获取分类出错，也不影响后续
                                    return;
                                });
                        }
                    })
                    .then(() => {
                        // 最后才显示标签按钮 & 如果有合法doi则显示Base32复制按钮
                        tagButton.style.display = 'flex';

                        if (
                            extractedData.doi &&
                            extractedData.doi !== '未找到 DOI' &&
                            extractedData.doi !== '查询失败'
                        ) {
                            // 显示 复制base32 按钮
                            copyBase32Button.style.display = 'flex';
                        }

                        // 检查并提示缺失信息
                        checkMissingData(extractedData);
                    })
                    .catch(err => {
                        console.error('DOI查询或分类获取时出错:', err);
                        // 最后还是让标签按钮显示
                        tagButton.style.display = 'flex';

                        // 检查并提示缺失信息
                        checkMissingData(extractedData);
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

            // 发送论文数据到服务器，如果已存在则直接返回 paperID
            sendPaperData(extractedData)
                .then(response => {
                    if (response.success) {
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
                            // parseInt
                            const numericPaperCategories = paperCategories.map(id => parseInt(id, 10));
                            return { categories, paperCategories: numericPaperCategories };
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

        // 点击“复制 Base32”按钮事件
        copyBase32Button.addEventListener('click', function () {
            if (!extractedData.doiBase32) {
                alert('未找到可复制的Base32编码，请先进行查询。');
                return;
            }
            copyToClipboard(extractedData.doiBase32);
            alert('Base32 已复制到剪贴板！');
        });
    });

    // ======================
    //   主要逻辑函数
    // ======================

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
        matchResult = '',
        existingCategoryNames = [],
        doiBase32 = ''
    }) {
        let container = document.getElementById('result-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'result-container';
            container.style.position = 'fixed';
            container.style.top = '80px';
            container.style.right = '10px';
            container.style.width = '450px';
            container.style.maxHeight = '900px';
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

        // 如果 existingCategoryNames 不为空，则拼接成字符串
        const categoryLabel = (existingCategoryNames && existingCategoryNames.length > 0)
            ? existingCategoryNames.join(', ')
            : '暂无';

        // 将 doiBase32 也显示在“DOI”下方一行
        const doiBase32Display = doiBase32
            ? `<p><strong>DOI Base32:</strong> ${doiBase32}</p>`
            : ''; // 如果还没有计算出来，则不显示

        container.innerHTML = `
            <h3>提取结果</h3>
            <p><strong>GB/T 7714:</strong> ${gbText}</p>
            <p><strong>APA:</strong> ${apaText}</p>
            <p><strong>提取的文章标题:</strong> ${extractedTitle}</p>

            <h3>DOI 查询结果</h3>
            <p><strong>DOI:</strong> ${doi}</p>
            ${doiBase32Display}
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
            <p><strong>分类标签:</strong> ${categoryLabel}</p>
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

                            // 计算 DOI 的 Base32 编码
                            let doiBase32 = '';
                            if (doi && doi !== '未找到 DOI') {
                                doiBase32 = toBase32(doi);
                            }

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
                                matchResult,
                                doiBase32
                            });

                            // 更新 extractedData
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
                                matchResult,
                                doiBase32
                            };

                            resolve();
                        } else {
                            // 未查询到结果
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
                                extractedTitle: extractTitleFromReference(reference),
                                existingCategoryNames: [],
                                doiBase32: ''
                            });

                            // 更新 extractedData
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
                                matchResult: '',
                                existingCategoryNames: [],
                                doiBase32: ''
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
                            extractedTitle: extractTitleFromReference(reference),
                            existingCategoryNames: [],
                            doiBase32: ''
                        });
                        extractedData = {
                            ...extractedData,
                            doi: '查询失败',
                            doiBase32: ''
                        };
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
                        extractedTitle: extractTitleFromReference(reference),
                        existingCategoryNames: [],
                        doiBase32: ''
                    });
                    extractedData = {
                        ...extractedData,
                        doi: '查询失败',
                        doiBase32: ''
                    };
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
                    'Content-Type': 'application/json',
                    // [MODIFIED] 添加 X-Api-Key 头
                    'X-Api-Key': API_KEY
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
                headers: {
                    // [MODIFIED] 添加 X-Api-Key 头
                    'X-Api-Key': API_KEY
                },
                onload: (response) => {
                    try {
                        const res = JSON.parse(response.responseText);
                        if (res.success) {
                            // categories 里每个对象有 categoryID, category_name
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

    // 获取论文当前的分类(返回 categoryID 数组)
    function fetchPaperCategories(doi) {
        return new Promise((resolve, reject) => {
            GM_xmlhttpRequest({
                method: 'GET',
                url: API_BASE_URL + `08_tm_get_paper_categories.php?doi=${encodeURIComponent(doi)}`,
                headers: {
                    // [MODIFIED] 添加 X-Api-Key 头
                    'X-Api-Key': API_KEY
                },
                onload: (response) => {
                    try {
                        const res = JSON.parse(response.responseText);
                        if (res.success) {
                            // res.categoryIDs 可能是字符串数组，需要后续 parseInt
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

        // 在右上角添加一个关闭按钮 (叉符号)
        const closeButton = document.createElement('button');
        closeButton.textContent = '×';
        closeButton.style.position = 'absolute';
        closeButton.style.top = '5px';
        closeButton.style.right = '5px';
        closeButton.style.border = 'none';
        closeButton.style.background = 'none';
        closeButton.style.fontSize = '20px';
        closeButton.style.cursor = 'pointer';
        closeButton.addEventListener('click', function () {
            container.style.display = 'none';
        });

        container.appendChild(closeButton);

        // 标题
        const titleElem = document.createElement('h3');
        titleElem.textContent = '为论文添加分类标签';
        titleElem.style.marginTop = '0'; // 距离顶部零间距，避免和关闭按钮重叠
        container.appendChild(titleElem);

        categories.forEach(category => {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = `category-${category.categoryID}`;
            checkbox.value = category.categoryID;

            // 将 categoryID 也转成数字以便匹配
            const catIDNum = parseInt(category.categoryID, 10);

            // '0 All papers' 实际是 categoryID = 1；必须始终选中、不可取消
            if (catIDNum === 1) {
                checkbox.checked = true;
                checkbox.disabled = true;
            } else {
                // 如果该论文已有此分类，则勾选
                if (paperCategories.includes(catIDNum)) {
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

        // 底部按钮容器
        const btnContainer = document.createElement('div');
        btnContainer.style.display = 'flex';
        btnContainer.style.justifyContent = 'space-between';
        btnContainer.style.marginTop = '10px';

        // "保存分类" 按钮
        const saveButton = document.createElement('button');
        saveButton.textContent = '保存分类';
        saveButton.style.padding = '5px 10px';
        saveButton.style.backgroundColor = '#4CAF50';
        saveButton.style.color = 'white';
        saveButton.style.border = 'none';
        saveButton.style.cursor = 'pointer';
        saveButton.style.borderRadius = '4px'; // 增加圆角效果
        saveButton.addEventListener('click', function () {
            const selectedCategories = [];
            categories.forEach(category => {
                const catIDNum = parseInt(category.categoryID, 10);
                const checkbox = document.getElementById(`category-${category.categoryID}`);
                if (checkbox.checked) {
                    selectedCategories.push(catIDNum);
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

        // "取消" 按钮
        const cancelButton = document.createElement('button');
        cancelButton.textContent = '取消';
        cancelButton.style.padding = '5px 10px';
        cancelButton.style.backgroundColor = '#f44336';
        cancelButton.style.color = 'white';
        cancelButton.style.border = 'none';
        cancelButton.style.cursor = 'pointer';
        cancelButton.style.borderRadius = '4px';
        cancelButton.addEventListener('click', function () {
            // 放弃保存分类，直接关闭窗口
            container.style.display = 'none';
        });

        // 将按钮添加到容器
        btnContainer.appendChild(saveButton);
        btnContainer.appendChild(cancelButton);

        // 将按钮容器添加到分类选择框
        container.appendChild(btnContainer);

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
                    'Content-Type': 'application/json',
                    // [MODIFIED] 添加 X-Api-Key 头
                    'X-Api-Key': API_KEY
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

    // ======================
    //   Base32 编码函数
    // ======================

    /**
     * 按照 RFC 4648 标准进行 Base32 编码
     * 包含必要的“=”号填充，使结果长度为 8 的倍数
     * @param {string} input
     * @returns {string}
     */
    function toBase32(input) {
        // 1. 转换为 UTF-8 字节数组（如果 DOI 都是 ASCII，其实可以直接取 charCode）
        //   以防万一，还是做一个简单的 utf8 转换
        const bytes = stringToUtf8Bytes(input);

        // 2. 将字节数组转换为二进制字符串
        let bitString = '';
        for (const b of bytes) {
            bitString += b.toString(2).padStart(8, '0');
        }

        // 3. 将二进制按 5 位分割
        //   若末尾不够 5 位则补 0
        const remainder = bitString.length % 5;
        if (remainder !== 0) {
            bitString = bitString.padEnd(bitString.length + (5 - remainder), '0');
        }

        // 4. 映射 base32 字母表（RFC4648）
        const base32Alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        let base32String = '';
        for (let i = 0; i < bitString.length; i += 5) {
            const chunk = bitString.substr(i, 5);
            const index = parseInt(chunk, 2);
            base32String += base32Alphabet[index];
        }

        // 5. 在末尾添加 '=' 使长度为 8 的倍数
        const mod8 = base32String.length % 8;
        if (mod8 !== 0) {
            base32String += '='.repeat(8 - mod8);
        }

        return base32String;
    }

    // 将字符串转换为 UTF-8 字节
    function stringToUtf8Bytes(str) {
        const encoder = new TextEncoder();
        return encoder.encode(str);
    }

    // ======================
    //  工具函数：复制到剪贴板
    // ======================
    function copyToClipboard(text) {
        // 使用现代 API
        if (navigator.clipboard && window.isSecureContext) {
            // navigator.clipboard.writeText returns a promise
            return navigator.clipboard.writeText(text);
        } else {
            // 创建一个隐藏的textarea
            const textarea = document.createElement('textarea');
            textarea.value = text;
            // 使文本区域在页面上不可见
            textarea.style.position = 'fixed';
            textarea.style.top = '-99999px';
            document.body.appendChild(textarea);
            // 选中并复制
            textarea.select();
            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('复制失败:', err);
            }
            // 移除
            document.body.removeChild(textarea);
            return Promise.resolve();
        }
    }

    // ======================
    //  检查并提示缺失信息
    // ======================
    function checkMissingData(data) {
        // 若已弹窗过，则不再重复弹窗
        if (missingFieldsAlertShown) return;

        // 条件1：期刊名、出版年、作者、出版商，任意一项缺失或为“查询失败”
        const cond1 =
            isFieldMissing(data.journal, '未找到期刊名') ||
            isFieldMissing(data.publicationYear, '未找到出版年') ||
            isFieldMissing(data.fullAuthors, '未找到作者信息') ||
            isFieldMissing(data.publisher, '未找到出版商');

        // 条件2：卷号和期号都缺失或为“查询失败”
        const cond2 =
            isFieldMissing(data.volume, '未找到卷号') &&
            isFieldMissing(data.issue, '未找到期号');

        // 条件3：页码和文章号都缺失或为“查询失败”
        const cond3 =
            isFieldMissing(data.pages, '未找到页码') &&
            isFieldMissing(data.articleNumber, '未找到文章号');

        if (cond1 || cond2 || cond3) {
            missingFieldsAlertShown = true;
            alert('提示：该论文的部分关键信息缺失，请注意核对或补充！');
        }
    }

    /**
     * 判断字段是否“缺失”或“查询失败”
     * @param {string} fieldValue 要判断的值
     * @param {string} notFoundText 对应的“未找到xxx”标志
     * @returns {boolean} 如果是“查询失败”或者“未找到xxx”，返回 true
     */
    function isFieldMissing(fieldValue, notFoundText) {
        if (!fieldValue) return true;
        return (
            fieldValue === '查询失败' ||
            fieldValue === notFoundText
        );
    }

})();
