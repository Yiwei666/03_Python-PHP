<?php
// 启用错误报告（开发阶段使用，生产环境请关闭）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入数据库连接模块和分类操作模块
require_once '08_db_config.php';
require_once '08_category_operations.php';

// 获取所有分类
$categories = getCategories($mysqli);

// 处理 POST 请求
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
    <title>分类管理</title>
    <style>
        body { display: flex; font-family: Arial, sans-serif; margin: 0; padding: 0; }
        #categories-container { width: 25%; padding: 20px; border-right: 1px solid #ccc; box-sizing: border-box; }
        #categories-container table { width: 100%; border-collapse: collapse; }
        #categories-container table td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        #papers-container { width: 75%; padding: 20px; box-sizing: border-box; }
        .form-section { margin-bottom: 20px; }
        .form-section input[type="text"] { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        .form-section button { padding: 8px 12px; }
        .paper { margin-bottom: 15px; }
        .paper-title { font-size: 18px; margin: 0; }
        .paper-meta { font-size: 14px; color: gray; margin: 0; }
        .message { padding: 10px; margin-bottom: 20px; background-color: #f0f0f0; border: 1px solid #ccc; }
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
                ?>
                    <td>
                        <a href="?categoryID=<?= htmlspecialchars($category['categoryID']) ?>"><?= htmlspecialchars($category['category_name']) ?></a>
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
        <h2>论文列表</h2>
        <?php if ($selectedCategoryID): ?>
            <?php if ($papers !== null): ?>
                <?php if ($papers->num_rows > 0): ?>
                    <?php while ($paper = $papers->fetch_assoc()): ?>
                        <div class="paper">
                            <a class="paper-title" href="https://doi.org/<?= htmlspecialchars($paper['doi']) ?>" target="_blank">
                                <?= htmlspecialchars($paper['title']) ?>
                            </a>
                            <p class="paper-meta">
                                <?= htmlspecialchars($paper['authors']) ?>, <?= htmlspecialchars($paper['publication_year']) ?>, <?= htmlspecialchars($paper['journal_name']) ?>
                            </p>
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
</body>
</html>
