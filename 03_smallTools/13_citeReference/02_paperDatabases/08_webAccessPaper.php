<?php
// 08_webAccessPaper.php

// 启用错误报告（开发阶段使用，生产环境请关闭）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入数据库连接模块和分类操作模块
require_once '08_db_config.php';
require_once '08_category_operations.php';

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

// 获取选中分类的论文
$papers = null;
if ($selectedCategoryID) {
    $papers = getPapersByCategory($mysqli, $selectedCategoryID);
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
        }
        
        #papers-container { 
            width: 75%; 
            padding: 20px; 
            box-sizing: border-box; 
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
        /* 为“标签”按钮设置颜色和字号，去掉下划线，并让其左对齐 */
        .paper-categories {
            text-align: left;
        }
        .paper-categories button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            color: #1a0dab;  /* 蓝色 */
            font-size: 13px; /* 13 px */
            text-decoration: none; /* 去掉下划线 */
            padding: 0; /* 去除默认内边距，使其与左侧对齐 */
        }
        /* 弹窗 (modal) 样式 */
        #categoryModal {
            display: none; 
            position: fixed; 
            top: 10%; 
            left: 10%; 
            width: 80%; 
            background-color: #fff; 
            border: 1px solid #ccc; 
            padding: 20px; 
            z-index: 9999;
        }
        #categoryModal h2 {
            margin-top: 0;
        }
        #categoryModal button {
            margin: 5px;
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
    </style>
</head>
<body>
    <div id="categories-container">
        <h2>分类管理</h2>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
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
        
        <!-- 显示分类表格 -->
        <h3>现有分类</h3>
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
                        <a href="?categoryID=<?= htmlspecialchars($category['categoryID']) ?>" style="color: <?= $catColor ?>;">
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
        <h2>论文列表<?php if ($selectedCategoryID && $papers !== null) { echo " No. " . $papers->num_rows; } ?></h2>
        
        <?php if ($selectedCategoryID): ?>
            <?php if ($papers !== null): ?>
                <?php if ($papers->num_rows > 0): ?>
                    <?php while ($paper = $papers->fetch_assoc()): ?>
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
                                <button type="button" onclick="openCategoryModal('<?= htmlspecialchars($paper['doi']) ?>')">
                                    标签
                                </button>
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
        <h2>更改标签</h2>
        <div id="categoryCheckboxes"></div>
        <button id="saveCategoriesBtn">保存</button>
        <button id="cancelCategoriesBtn">取消</button>
    </div>

    <script>
        let currentDOI = null;      // 当前正在修改标签的论文的 DOI
        let allCategories = [];     // 所有分类的缓存

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
            fetch('08_tm_get_categories.php')
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
            fetch('08_tm_get_paper_categories.php?doi=' + encodeURIComponent(doi))
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

        // 动态渲染分类复选框
        function renderCategoryCheckboxes(allCats, paperCatIDs) {
            const container = document.getElementById('categoryCheckboxes');
            container.innerHTML = '';

            // 先把 paperCatIDs 全部转成字符串，防止数字 vs. 字符串不一致
            const paperCatIDsStr = paperCatIDs.map(id => String(id));

            allCats.forEach(cat => {
                const catID = String(cat.categoryID); // 转为字符串比较
                const catName = cat.category_name;
                let checked = false;
                let disabledAttr = '';

                // 如果 paperCatIDs 中包含 catID，则默认勾选
                if (paperCatIDsStr.includes(catID)) {
                    checked = true;
                }

                // 如果是 "0 All papers" (categoryID=1)，不允许用户取消勾选
                if (catID === '1') {
                    checked = true;    // 保证始终勾选
                    disabledAttr = 'disabled'; // 禁止取消
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
            fetch('08_tm_update_paper_categories.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
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
    </script>
</body>
</html>
