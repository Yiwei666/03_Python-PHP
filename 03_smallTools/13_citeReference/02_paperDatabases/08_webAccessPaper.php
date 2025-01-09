<?php
// 08_webAccessPaper.php

// 启用错误报告（开发阶段使用，生产环境请关闭）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入数据库连接模块和分类操作模块
require_once '08_db_config.php';
require_once '08_category_operations.php';

// 引入 Base32 编码类（请确保本地存在 08_web_Base32.php 并包含题目中的实现）
require_once '08_web_Base32.php';

// 获取所有分类
$categories = getCategories($mysqli);

// 处理 POST 请求（创建、删除、修改分类）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'create') {
            $categoryName = trim($_POST['category_name']);
            // 检查是否已存在该分类（不区分大小写）
            $existing = array_filter($categories, function($c) use ($categoryName) {
                return strtolower($c['category_name']) === strtolower($categoryName);
            });
            if ($existing) {
                $message = "分类 '$categoryName' 已存在。";
            } else {
                $message = addCategory($mysqli, $categoryName);
                // 重新获取分类列表
                $categories = getCategories($mysqli);
            }
        } elseif ($action === 'delete') {
            $categoryName = trim($_POST['category_name']);
            // 查找对应的 categoryID
            $categoryID = null;
            foreach ($categories as $category) {
                if (strtolower($category['category_name']) === strtolower($categoryName)) {
                    $categoryID = $category['categoryID'];
                    break;
                }
            }
            if ($categoryID === null) {
                $message = "分类 '$categoryName' 不存在。";
            } else {
                $message = deleteCategory($mysqli, $categoryID);
                // 重新获取分类列表
                $categories = getCategories($mysqli);
            }
        } elseif ($action === 'modify') {
            $original = trim($_POST['original_category']);
            $newName = trim($_POST['new_category']);
            // 查找原分类的 categoryID
            $categoryID = null;
            foreach ($categories as $category) {
                if (strtolower($category['category_name']) === strtolower($original)) {
                    $categoryID = $category['categoryID'];
                    break;
                }
            }
            if ($categoryID === null) {
                $message = "分类 '$original' 不存在。";
            } else {
                // 检查新分类名是否已存在
                $existing = array_filter($categories, function($c) use ($newName) {
                    return strtolower($c['category_name']) === strtolower($newName);
                });
                if ($existing) {
                    $message = "分类 '$newName' 已存在。";
                } else {
                    $message = updateCategoryName($mysqli, $categoryID, $newName);
                    // 重新获取分类列表
                    $categories = getCategories($mysqli);
                }
            }
        }
        // 处理完 POST 请求后刷新页面并显示消息
        header("Location: 08_webAccessPaper.php?message=" . urlencode($message));
        exit();
    }
}

// 处理 GET 请求中的消息
$message = isset($_GET['message']) ? $_GET['message'] : '';

// 获取当前选中的分类
$selectedCategoryID = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : null;

// 获取排序参数，默认 paperID_desc
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'paperID_desc';

// 获取选中分类的论文（带排序）
$papers = null;
if ($selectedCategoryID) {
    $papers = getPapersByCategory($mysqli, $selectedCategoryID, $sort);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Paper Databases</title>
    <link rel="icon" href="https://mctea.one/00_logo/endnote.png" type="image/png">
    <style>
        body { 
            display: flex; 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0;
        }
        #categories-container { 
            width: 25%; 
            padding: 20px; 
            border-right: 1px solid #ccc; 
            box-sizing: border-box; 
        }
        #categories-container table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        #categories-container table td { 
            padding: 10px; 
            border: 1px solid #ddd; 
            text-align: center; 
        }
        /* 去除左侧“现有分类”下所有分类标签的下划线，默认字体为 #222，选中后变为 #d14836 */
        #categories-container table td a {
            text-decoration: none;
            font-size: 14px; /* 设置为您需要的字体大小 */
        }
        
        #papers-container { 
            width: 75%; 
            padding: 20px; 
            box-sizing: border-box; 
            position: relative; /* 允许绝对定位的元素放置在这里 */
        }
        .form-section { 
            margin-bottom: 20px; 
        }
        .form-section input[type="text"] { 
            width: 100%; 
            padding: 8px; 
            margin-bottom: 10px; 
            box-sizing: border-box; 
        }
        .form-section button { 
            padding: 8px 12px; 
        }
        .paper { 
            margin-bottom: 30px; 
        }
        /* 右侧论文标题：去除下划线，设为 17px, 颜色 #1a0dab */
        .paper-title a { 
            text-decoration: none; 
            font-size: 17px; 
            color: #1a0dab; 
        }
        /* 右侧论文出版年、期刊和作者：字体 13px, 颜色 #006621 */
        .paper-meta { 
            font-size: 13px; 
            margin: 5px 0 5px 0; 
            color: #006621; 
        }
        /* 给期刊名称单独添加下划线，下划线颜色为 #006621 */
        .journal-name {
            text-decoration: underline;
            text-decoration-color: #006621;
        }
        .message { 
            padding: 10px; 
            margin-bottom: 20px; 
            background-color: #f0f0f0; 
            border: 1px solid #ccc; 
        }
        /* 为“标签”按钮、工具按钮等 设置颜色和字号，去掉下划线 */
        .paper-categories {
            text-align: left;
        }
        .paper-categories button, .paper-categories span {
            background-color: transparent;
            border: none;
            cursor: pointer;
            color: #1a0dab;  /* 蓝色 */
            font-size: 13px; /* 13 px */
            text-decoration: none; /* 去掉下划线 */
            padding: 0; /* 去除默认内边距，使其与左侧对齐 */
            margin-right: 10px; /* 多个按钮之间留一点间隙 */
        }
        .paper-categories span {
            cursor: default;
        }
        /* “工具”按钮、以及新增的“全部下载”和“全部删除”按钮样式一致 */
        #toolsBtn, #batchDownloadBtn, #batchDeleteBtn {
            background-color: transparent;
            border: none;
            cursor: pointer;
            color: #1a0dab;
            font-size: 13px;
            text-decoration: none;
            padding: 0;
            margin-left: 10px; /* 让它和前面的文字或按钮稍微隔开 */
            margin-right: 10px;
        }
        /* 弹窗 (modal) 样式 */
        #categoryModal {
            display: none; 
            position: fixed; 
            top: 5%; 
            left: 10%; 
            width: 80%; 
            background-color: #fff; 
            border: 1px solid #ccc; 
            padding: 20px; 
            z-index: 9999;
            box-sizing: border-box;
            max-height: 80%; /* 当内容超出时出现滚动条 */
            overflow-y: auto;
        }
        #categoryModal h2 {
            margin-top: 0;
        }
        #categoryModal button {
            margin: 5px;
        }
        /* 关闭按钮 (右上角) */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            font-size: 18px;
            cursor: pointer;
        }
        .overlay {
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0,0,0,0.5); 
            z-index: 9998;
        }
        /* 以5列形式显示分类复选框 */
        #categoryCheckboxes {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 5px;
            margin-bottom: 20px;
        }

        /* 工具菜单的样式 - 简易下拉 */
        #toolsMenu {
            display: none;
            position: absolute;
            top: 0; 
            right: 10px; /* 距离右侧10px */
            background: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            padding: 10px;
            z-index: 10000; /* 使其置顶 */
        }
        #toolsMenu ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
        #toolsMenu li {
            margin: 5px 0;
        }
        #toolsMenu a {
            color: #1a0dab;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
        }
        #toolsMenu a:hover {
            text-decoration: underline;
        }

        /* ========== [NEW CODE] 分类管理弹窗样式 ========== */
        #manageCategoryModal {
            display: none;
            position: fixed;
            top: 10%; 
            left: 35%; 
            width: 30%;
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 20px;
            z-index: 9999; 
            box-sizing: border-box;
            max-height: 80%;
            overflow-y: auto;
        }
        #manageCategoryModal h2 {
            margin-top: 0;
        }
        /* 可以复用同样的遮罩层 overlay，也可单独再写一个。如果复用同一个，记得调用时注意逻辑 */
        /* 这里使用与 categoryModal 相同的 overlay */
        #manageCategoryModal .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            font-size: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="categories-container">

        <!-- [MODIFIED] 将分类管理表单区域移除，只保留“现有分类”部分 -->

        <!-- 显示分类表格 -->
        <h2 style="display: inline-block;">现有分类</h2>

        <!-- [NEW CODE] 在“现有分类”旁边增加一个“分类管理”按钮，样式与右侧"工具"等按钮统一 -->
        <button id="manageCategoryBtn" type="button" style="background-color: transparent; border: none; cursor: pointer; color: #1a0dab; font-size: 13px; text-decoration: none; margin-left: 10px;">
            分类管理
        </button>

        <!-- [MODIFIED] 显示后端的消息提示，如果有的话 -->
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <table>
            <tr>
                <?php 
                $colCount = 0;
                foreach ($categories as $category): 
                    $colCount++;
                    // 判断当前分类是否被选中
                    if ($selectedCategoryID && $selectedCategoryID == $category['categoryID']) {
                        $catColor = "#d14836"; // 选中时字体颜色为红色
                    } else {
                        $catColor = "#222";    // 默认字体颜色为黑色
                    }
                ?>
                    <td>
                        <a href="?categoryID=<?= htmlspecialchars($category['categoryID']) ?>&sort=<?= urlencode($sort) ?>" style="color: <?= $catColor ?>;">
                            <?= htmlspecialchars($category['category_name']) ?>
                        </a>
                    </td>
                    <?php if ($colCount % 3 == 0): ?>
                        </tr><tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <!-- 填充空白单元格以确保表格整齐 -->
                <?php while ($colCount % 3 != 0): $colCount++; ?>
                    <td></td>
                <?php endwhile; ?>
            </tr>
        </table>
    </div>
    
    <div id="papers-container">
        <!-- 在此处加上对应论文的数量 -->
        <h2>
            论文列表
            <?php 
            if ($selectedCategoryID && $papers !== null) { 
                echo " No. " . $papers->num_rows; 
            } 
            ?>
            <!-- 如果用户已选择分类，则显示“工具”、“全部下载”、“全部删除”按钮 -->
            <?php if ($selectedCategoryID): ?>
                <button id="toolsBtn" type="button">工具</button>
                <button id="batchDownloadBtn" type="button">全部下载</button>
                <button id="batchDeleteBtn" type="button">全部删除</button>
            <?php endif; ?>
        </h2>

        <!-- 工具下拉菜单 -->
        <div id="toolsMenu">
            <ul>
                <!-- 这部分通过 sort=xxx 的方式控制排序 -->
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=paperID_asc">论文ID升序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=paperID_desc">论文ID降序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=year_desc">发表年降序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=year_asc">发表年升序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=status_desc">状态码降序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=status_asc">状态码升序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=journal_asc">期刊名升序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=journal_desc">期刊名降序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=authors_asc">作者名升序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=authors_desc">作者名降序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=title_asc">标题升序</a></li>
                <li><a href="?categoryID=<?= $selectedCategoryID ?>&sort=title_desc">标题降序</a></li>
            </ul>
        </div>
        
        <?php 
        // 用于在前端批量操作时，收集论文数据（paperID、doi、status 等）
        $papersData = [];
        ?>

        <?php if ($selectedCategoryID): ?>
            <?php if ($papers !== null): ?>
                <?php if ($papers->num_rows > 0): ?>
                    <?php while ($paper = $papers->fetch_assoc()): ?>
                        <?php 
                            // 收集信息到 $papersData 数组中
                            $papersData[] = [
                                'paperID' => $paper['paperID'],
                                'doi' => $paper['doi'],
                                'status' => $paper['status']
                            ];
                        ?>
                        <div class="paper">
                            <!-- 论文标题（允许包含 <sub> 和 <sup> 等HTML标签） -->
                            <div class="paper-title">
                                <a href="https://doi.org/<?= htmlspecialchars($paper['doi']) ?>" target="_blank">
                                    <?php 
                                    // 去掉 htmlspecialchars 以便正确显示 <sub>/<sup> 等标签
                                    echo $paper['title']; 
                                    ?>
                                </a>
                            </div>
                            <!-- 元信息，顺序为：年份, 期刊名, 作者 -->
                            <p class="paper-meta">
                                <?= htmlspecialchars($paper['publication_year']) ?>, 
                                <span class="journal-name"><?= htmlspecialchars($paper['journal_name'], ENT_QUOTES, 'UTF-8', false) ?></span>,
                                <?= htmlspecialchars($paper['authors']) ?>
                            </p>
                            <!-- “标签”按钮行 -->
                            <div class="paper-categories">
                                <!-- 原有 "标签" 功能 -->
                                <button type="button" onclick="openCategoryModal('<?= htmlspecialchars($paper['doi']) ?>')">
                                    标签
                                </button>
                                
                                <?php 
                                    // 为了生成“查看”按钮，需要对 doi 进行 Base32 编码
                                    $encodedDOI = Base32::encode($paper['doi']);

                                    // 根据 status 显示不同提示或按钮
                                    switch($paper['status']) {
                                        case 'CL':
                                            // 显示 “删除” 与 “查看”
                                            echo '<button type="button" onclick="updatePaperStatus(\'' . htmlspecialchars($paper['doi']) . '\', \'DL\')">删除</button>';
                                            echo '<button type="button" onclick="window.open(\'https://chaye.one/08_paperLocalStorage/' . urlencode($encodedDOI) . '.pdf\', \'_blank\')">查看</button>';
                                            break;
                                        case 'DL':
                                            // 显示 “删除中”
                                            echo '<span>删除中</span>';
                                            break;
                                        case 'C':
                                            // 显示 “下载”
                                            echo '<button type="button" onclick="updatePaperStatus(\'' . htmlspecialchars($paper['doi']) . '\', \'DW\')">下载</button>';
                                            break;
                                        case 'DW':
                                            // 显示 “下载中”
                                            echo '<span>下载中</span>';
                                            break;
                                        case 'L':
                                            // 显示 “查看”
                                            echo '<button type="button" onclick="window.open(\'https://chaye.one/08_paperLocalStorage/' . urlencode($encodedDOI) . '.pdf\', \'_blank\')">查看</button>';
                                            break;
                                        case 'N':
                                        default:
                                            // N 或其它未匹配情况，不显示额外按钮/提示
                                            break;
                                    }

                                    // ====== 复制按钮 =======
                                    // “复制DOI” 按钮
                                    echo '<button type="button" onclick="copyDOI(\'' . htmlspecialchars($paper['doi']) . '\')">复制DOI</button>';
                                    // “复制编码DOI” 按钮
                                    echo '<button type="button" onclick="copyEncodedDOI(\'' . $encodedDOI . '\')">复制编码DOI</button>';
                                ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>所选分类下没有论文。</p>
                <?php endif; ?>
            <?php else: ?>
                <p>无法获取该分类下的论文。</p>
            <?php endif; ?>
        <?php else: ?>
            <p>请选择左侧的分类以查看相关论文。</p>
        <?php endif; ?>
    </div>

    <!-- 遮罩层 -->
    <div class="overlay" id="overlay"></div>

    <!-- 弹窗 (modal) 用于更改标签 -->
    <div id="categoryModal">
        <!-- 右上角关闭按钮 -->
        <button class="close-btn" onclick="closeModal()">X</button>
        
        <h2>更改标签</h2>
        <div id="categoryCheckboxes"></div>
        <button id="saveCategoriesBtn">保存</button>
        <button id="cancelCategoriesBtn">取消</button>
    </div>

    <!-- ========== [NEW CODE] 分类管理弹窗 ========== -->
    <div id="manageCategoryModal">
        <button class="close-btn" onclick="closeManageModal()">X</button>
        <h2>分类管理</h2>
        
        <!-- 创建分类 -->
        <div class="form-section">
            <h4>创建分类</h4>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <input type="text" name="category_name" placeholder="新分类名称" required>
                <button type="submit">创建</button>
            </form>
        </div>
        
        <!-- 删除分类 -->
        <div class="form-section">
            <h4>删除分类</h4>
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="text" name="category_name" placeholder="要删除的分类名称" required>
                <button type="submit">删除</button>
            </form>
        </div>
        
        <!-- 修改分类 -->
        <div class="form-section">
            <h4>修改分类</h4>
            <form method="POST">
                <input type="hidden" name="action" value="modify">
                <input type="text" name="original_category" placeholder="原分类名称" required>
                <input type="text" name="new_category" placeholder="新分类名称" required>
                <button type="submit">修改</button>
            </form>
        </div>
    </div>
    <!-- ========== [NEW CODE] 分类管理弹窗结束 ========== -->

    <script>
        // [MODIFIED] 定义 API_KEY 常量
        const API_KEY = 'YOUR_API_KEY_HERE'; // 与后端 08_api_auth.php 中保持一致

        let currentDOI = null;      // 当前正在修改标签的论文的 DOI
        let allCategories = [];     // 所有分类的缓存

        // 将后端获取到的 $papersData 数组转成 JS 对象
        const papersData = <?php echo json_encode($papersData, JSON_UNESCAPED_UNICODE); ?>;

        // 打开分类选择弹窗
        function openCategoryModal(doi) {
            currentDOI = doi;
            // 显示遮罩层
            document.getElementById('overlay').style.display = 'block';
            // 显示弹窗
            document.getElementById('categoryModal').style.display = 'block';
            
            fetchCategories();
        }

        // 获取所有分类
        function fetchCategories() {
            // [MODIFIED] 在请求头中添加 X-Api-Key
            fetch('08_tm_get_categories.php', {
                headers: {
                    'X-Api-Key': API_KEY
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allCategories = data.categories;
                    fetchPaperCategories(currentDOI);
                } else {
                    alert('获取分类列表失败。');
                }
            })
            .catch(err => {
                console.error(err);
                alert('获取分类列表时出现错误。');
            });
        }

        // 获取当前论文已勾选的分类
        function fetchPaperCategories(doi) {
            // [MODIFIED] 在请求头中添加 X-Api-Key
            fetch('08_tm_get_paper_categories.php?doi=' + encodeURIComponent(doi), {
                headers: {
                    'X-Api-Key': API_KEY
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // 渲染分类复选框，并根据当前论文的分类勾选
                    renderCategoryCheckboxes(allCategories, data.categoryIDs);
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('获取论文分类时出现错误。');
            });
        }

        // 动态渲染分类复选框（5列布局）
        function renderCategoryCheckboxes(allCats, paperCatIDs) {
            const container = document.getElementById('categoryCheckboxes');
            container.innerHTML = '';

            // 先把 paperCatIDs 全部转成字符串，防止数字 vs. 字符串不一致
            const paperCatIDsStr = paperCatIDs.map(id => String(id));

            allCats.forEach(cat => {
                const catID = String(cat.categoryID);
                const catName = cat.category_name;
                let checked = false;
                let disabledAttr = '';

                // 如果 paperCatIDs 中包含 catID，则默认勾选
                if (paperCatIDsStr.includes(catID)) {
                    checked = true;
                }

                // 如果是 "0 All papers" (categoryID=1)，不允许用户取消勾选
                if (catID === '1') {
                    checked = true;    
                    disabledAttr = 'disabled'; 
                }

                container.innerHTML += `
                    <div>
                        <label>
                            <input 
                                type="checkbox" 
                                name="category" 
                                value="${catID}"
                                ${checked ? 'checked' : ''} 
                                ${disabledAttr}
                            >
                            ${catName}
                        </label>
                    </div>
                `;
            });
        }

        // 点击“保存”按钮，更新分类
        document.getElementById('saveCategoriesBtn').addEventListener('click', () => {
            const checkboxes = document.querySelectorAll('#categoryCheckboxes input[type="checkbox"]');
            const selected = [];
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    // 因为后端需要 number, 故 parseInt
                    selected.push(parseInt(checkbox.value));
                }
            });
            updatePaperCategories(currentDOI, selected);
        });

        // 调用后端接口更新论文分类
        function updatePaperCategories(doi, categoryIDs) {
            // [MODIFIED] 在请求头中添加 X-Api-Key
            fetch('08_tm_update_paper_categories.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Api-Key': API_KEY
                },
                body: JSON.stringify({ 
                    doi: doi, 
                    categoryIDs: categoryIDs 
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('分类更新成功。');
                    closeModal();
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('更新分类时出现错误。');
            });
        }

        // 点击“取消”或更新完成后关闭弹窗
        document.getElementById('cancelCategoriesBtn').addEventListener('click', closeModal);

        function closeModal() {
            document.getElementById('categoryModal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        // 更新论文状态（单个）
        function updatePaperStatus(doi, newStatus) {
            // [MODIFIED] 在请求头中添加 X-Api-Key
            fetch('08_web_update_paper_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Api-Key': API_KEY
                },
                body: JSON.stringify({ 
                    doi: doi, 
                    status: newStatus 
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload(); // 刷新页面
                } else {
                    alert(data.message || '更新状态失败。');
                }
            })
            .catch(err => {
                console.error(err);
                alert('更新状态时出现错误。');
            });
        }

        // ====== 复制功能 =======
        // 复制原始DOI
        function copyDOI(doi) {
            navigator.clipboard.writeText(doi)
                .then(() => {
                    alert('已复制DOI: ' + doi);
                })
                .catch((err) => {
                    console.error('复制DOI失败:', err);
                });
        }

        // 复制Base32编码后的DOI
        function copyEncodedDOI(encodedDOI) {
            navigator.clipboard.writeText(encodedDOI)
                .then(() => {
                    alert('已复制编码DOI: ' + encodedDOI);
                })
                .catch((err) => {
                    console.error('复制编码DOI失败:', err);
                });
        }

        // ====== 工具按钮、菜单 ======
        const toolsBtn = document.getElementById('toolsBtn');
        const toolsMenu = document.getElementById('toolsMenu');
        
        if (toolsBtn) {
            toolsBtn.addEventListener('click', () => {
                // 切换菜单的显示/隐藏
                if (toolsMenu.style.display === 'none' || toolsMenu.style.display === '') {
                    toolsMenu.style.display = 'block';
                } else {
                    toolsMenu.style.display = 'none';
                }
            });
        }

        // 如果用户点击页面其他位置，需要隐藏菜单
        document.addEventListener('click', (e) => {
            if (toolsBtn && toolsMenu) {
                if (!toolsBtn.contains(e.target) && !toolsMenu.contains(e.target)) {
                    toolsMenu.style.display = 'none';
                }
            }
        });

        // ====== 全部下载、全部删除功能 ======
        const batchDownloadBtn = document.getElementById('batchDownloadBtn');
        const batchDeleteBtn = document.getElementById('batchDeleteBtn');

        if (batchDownloadBtn) {
            batchDownloadBtn.addEventListener('click', () => {
                batchUpdateStatus('C', 'DW'); // 把 status=C 的改为 DW
            });
        }
        if (batchDeleteBtn) {
            batchDeleteBtn.addEventListener('click', () => {
                batchUpdateStatus('CL', 'DL'); // 把 status=CL 的改为 DL
            });
        }

        /**
         * 批量更新当前分类下所有论文的状态
         * @param {string} oldStatus 需要被更新的旧状态
         * @param {string} newStatus 更新成的新状态
         */
        function batchUpdateStatus(oldStatus, newStatus) {
            // 收集所有需要更新的DOI
            const papersToUpdate = papersData.filter(p => p.status === oldStatus);

            if (papersToUpdate.length === 0) {
                alert('没有符合条件的论文需要更新。');
                return;
            }

            // 依次调用 updatePaperStatus 接口
            let promises = papersToUpdate.map(p => {
                return fetch('08_web_update_paper_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Api-Key': API_KEY  // [MODIFIED]
                    },
                    body: JSON.stringify({ 
                        doi: p.doi, 
                        status: newStatus
                    })
                }).then(res => res.json());
            });

            Promise.all(promises)
                .then(results => {
                    // 如果都成功，则刷新
                    let allSuccess = results.every(r => r.success);
                    if (allSuccess) {
                        window.location.reload();
                    } else {
                        alert('部分论文状态更新失败，请检查。');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('批量更新时出现错误。');
                });
        }

        // ====== [NEW CODE] 分类管理弹窗的打开/关闭 ======
        const manageCategoryBtn = document.getElementById('manageCategoryBtn');
        const manageCategoryModal = document.getElementById('manageCategoryModal');

        manageCategoryBtn.addEventListener('click', () => {
            // 使用与 #categoryModal 同一个遮罩层
            document.getElementById('overlay').style.display = 'block';
            manageCategoryModal.style.display = 'block';
        });

        function closeManageModal() {
            manageCategoryModal.style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>
</body>
</html>
