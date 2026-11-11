(() => {
    const moreToolsBtn = document.getElementById('moreToolsBtn');
    const moreToolsMenu = document.getElementById('moreToolsMenu');

    function setMoreToolsOpen(open) {
        moreToolsMenu.hidden = !open;
        moreToolsBtn.setAttribute('aria-expanded', open);
    }

    moreToolsBtn.addEventListener('click', () => setMoreToolsOpen(moreToolsMenu.hidden));
    moreToolsBtn.parentElement.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !moreToolsMenu.hidden) {
            setMoreToolsOpen(false);
            moreToolsBtn.focus();
        }
    });

    document.addEventListener('click', event => {
        if (!moreToolsBtn.parentElement.contains(event.target)) {
            setMoreToolsOpen(false);
        }
    });

    const copyMultiModal = document.getElementById('copyMultiCategoryModal');
    const copyCategoryCheckboxes = document.getElementById('copyCategoryCheckboxes');
    const copyMultiConfirm = document.getElementById('copyMultiConfirmBtn');
    const copyMultiCancel = document.getElementById('copyMultiCancelBtn');
    const copyMultiStatus = document.getElementById('copyMultiStatus');
    const copyMultiSortButtons = [
        document.getElementById('copyMultiTagSortBtn'),
        document.getElementById('copyMultiRecentSortBtn')
    ];
    let copyCategoryOrders = [];
    let copyCategoryLoadID = 0;

    function showCopyMultiStatus(message = '') {
        copyMultiStatus.textContent = message;
        copyMultiStatus.hidden = !message;
    }

    function checkedCopyCategories() {
        return Array.from(copyCategoryCheckboxes.querySelectorAll('input:checked'), input => input.value);
    }

    function renderCopyCategories(order) {
        const selected = new Set(checkedCopyCategories());
        copyCategoryCheckboxes.replaceChildren();
        const rendered = new Set();
        for (const category of [...copyCategoryOrders[order], ...copyCategoryOrders[1 - order]]) {
            const id = String(category.categoryID);
            if (rendered.has(id)) continue;
            rendered.add(id);
            const label = document.createElement('label');
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.value = id;
            input.checked = selected.has(id);
            const name = document.createElement('span');
            name.textContent = category.category_name;
            label.append(input, name);
            copyCategoryCheckboxes.append(label);
        }
        copyMultiConfirm.disabled = !selected.size;
        copyMultiSortButtons.forEach((button, index) => button.setAttribute('aria-pressed', index === order));
    }

    document.getElementById('copyMultiCategoryBtn').addEventListener('click', async () => {
        setMoreToolsOpen(false);
        moreToolsBtn.focus();
        const loadID = ++copyCategoryLoadID;
        copyCategoryCheckboxes.replaceChildren();
        copyMultiConfirm.disabled = true;
        copyMultiSortButtons.forEach(button => { button.disabled = true; });
        showCopyMultiStatus('正在获取分类…');
        copyMultiModal.showModal();
        try {
            const orders = await Promise.all(['', '?order=paperID_desc'].map(async query => {
                const response = await fetch('08_tm_get_categories.php' + query, {
                    headers: { 'X-Api-Key': API_KEY }
                });
                const result = await response.json();
                if (!response.ok || !result.success || !Array.isArray(result.categories)) {
                    throw new Error('获取分类列表失败。');
                }
                return result.categories;
            }));
            if (loadID !== copyCategoryLoadID || !copyMultiModal.open) return;
            copyCategoryOrders = orders;
            renderCopyCategories(0);
            copyMultiSortButtons.forEach(button => { button.disabled = false; });
            showCopyMultiStatus(orders[0].length ? '' : '当前没有可选分类。');
        } catch (error) {
            if (loadID === copyCategoryLoadID && copyMultiModal.open) {
                showCopyMultiStatus('获取分类列表失败，请关闭后重试。');
            }
        }
    });

    copyMultiSortButtons.forEach((button, order) => {
        button.addEventListener('click', () => renderCopyCategories(order));
    });
    copyCategoryCheckboxes.addEventListener('change', () => {
        copyMultiConfirm.disabled = !checkedCopyCategories().length;
    });
    copyMultiCancel.addEventListener('click', () => copyMultiModal.close());
    copyMultiModal.addEventListener('cancel', event => {
        if (copyMultiCancel.disabled) event.preventDefault();
    });

    copyMultiConfirm.addEventListener('click', async () => {
        const categoryIDs = checkedCopyCategories();
        if (!categoryIDs.length) return;
        if (!navigator.clipboard) {
            showCopyMultiStatus('当前页面无法访问剪贴板。');
            return;
        }

        copyMultiConfirm.disabled = copyMultiCancel.disabled = true;
        copyMultiSortButtons.forEach(button => { button.disabled = true; });
        copyCategoryCheckboxes.inert = true;
        showCopyMultiStatus('正在汇总并复制论文…');

        const papers = (async () => {
            const sort = new URLSearchParams(window.location.search).get('sort') ?? 'paperID_desc';
            const response = await fetch('08_web_copy_categories.php', {
                method: 'POST',
                headers: { 'X-Api-Key': API_KEY },
                body: new URLSearchParams({ categoryIDs: categoryIDs.join(','), sort })
            });
            const result = await response.json();
            if (!response.ok || !Array.isArray(result.data)) {
                throw new Error(result.message || '无法获取所选分类的论文数据。');
            }
            if (!result.data.length) throw new Error('所选分类下没有可复制的论文数据。');
            return result.data;
        })();

        // 在点击事件内发起剪贴板写入，查询完成后再提供文本。
        const write = (async () => {
            if (typeof ClipboardItem !== 'undefined' && navigator.clipboard.write) {
                const blob = papers.then(items => new Blob([JSON.stringify(items, null, 2)], { type: 'text/plain' }));
                // 剪贴板提前拒绝时，查询结果仍由下方的 Promise.allSettled 处理。
                blob.catch(() => {});
                await navigator.clipboard.write([new ClipboardItem({ 'text/plain': blob })]);
            } else {
                await navigator.clipboard.writeText(JSON.stringify(await papers, null, 2));
            }
        })();
        const [dataResult, writeResult] = await Promise.allSettled([papers, write]);
        copyMultiConfirm.disabled = copyMultiCancel.disabled = false;
        copyMultiSortButtons.forEach(button => { button.disabled = false; });
        copyCategoryCheckboxes.inert = false;

        if (dataResult.status === 'rejected') {
            showCopyMultiStatus(dataResult.reason instanceof TypeError || dataResult.reason instanceof SyntaxError
                ? '获取论文数据失败，请重试。' : dataResult.reason.message);
        } else if (writeResult.status === 'rejected') {
            showCopyMultiStatus('写入剪贴板失败，请重试。');
        } else {
            copyMultiModal.close();
            alert('已复制 ' + dataResult.value.length + ' 篇论文。');
        }
    });
})();
