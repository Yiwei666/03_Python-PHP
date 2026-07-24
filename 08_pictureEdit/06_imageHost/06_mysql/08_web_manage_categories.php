<?php
session_start();

function decrypt($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

$key = 'signin-key-1';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if (isset($_COOKIE['user_auth'])) {
        $decryptedValue = decrypt($_COOKIE['user_auth'], $key);
        if ($decryptedValue == 'mcteaone') {
            $_SESSION['loggedin'] = true;
        } else {
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('user_auth', '', time() - 3600, '/');
    header('Location: login.php');
    exit;
}

include '08_db_config.php';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function cleanText($value) {
    return trim((string)$value);
}

function cleanKindID($value) {
    $value = trim((string)$value);
    return $value === '' ? null : $value;
}

function getAllCategoriesWithKindID() {
    global $mysqli;

    $result = $mysqli->query("SELECT id, category_name, kindID FROM Categories ORDER BY id ASC");
    if (!$result) {
        return [];
    }

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id' => (int)$row['id'],
            'category_name' => $row['category_name'],
            'kindID' => $row['kindID']
        ];
    }

    return $categories;
}

function categoryExists($categoryName) {
    global $mysqli;

    $stmt = $mysqli->prepare("SELECT 1 FROM Categories WHERE category_name = ? LIMIT 1");
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $exists = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $exists;
}

function kindIDExists($kindID, $excludeCategoryName = null) {
    global $mysqli;

    if ($kindID === null) {
        return false;
    }

    if ($excludeCategoryName === null) {
        $stmt = $mysqli->prepare("SELECT 1 FROM Categories WHERE kindID = ? LIMIT 1");
        $stmt->bind_param("s", $kindID);
    } else {
        $stmt = $mysqli->prepare("SELECT 1 FROM Categories WHERE kindID = ? AND category_name <> ? LIMIT 1");
        $stmt->bind_param("ss", $kindID, $excludeCategoryName);
    }

    $stmt->execute();
    $exists = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $exists;
}

$message = '';
$messageType = 'success';
$activeTab = $_POST['active_tab'] ?? $_GET['active_tab'] ?? 'rename';
$sort = $_POST['sort'] ?? $_GET['sort'] ?? 'asc';
$sort = $sort === 'desc' ? 'desc' : 'asc';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $messageType = 'error';

    switch ($action) {
        case 'rename':
            $oldName = cleanText($_POST['old_name'] ?? '');
            $newName = cleanText($_POST['new_name'] ?? '');
            $activeTab = 'rename';

            if ($oldName === '' || $newName === '') {
                $message = '原分类名和新分类名不能为空。';
                break;
            }

            if (!categoryExists($oldName)) {
                $message = "分类 '{$oldName}' 不存在，无法修改。";
                break;
            }

            if (categoryExists($newName)) {
                $message = "新的分类名 '{$newName}' 已存在，无法使用。";
                break;
            }

            $stmt = $mysqli->prepare("UPDATE Categories SET category_name = ? WHERE category_name = ?");
            $stmt->bind_param("ss", $newName, $oldName);
            if ($stmt->execute()) {
                $messageType = 'success';
                $message = "分类 '{$oldName}' 已修改为 '{$newName}'。";
            } else {
                $message = '修改分类名失败：' . $mysqli->error;
            }
            $stmt->close();
            break;

        case 'delete':
            $deleteName = cleanText($_POST['delete_name'] ?? '');
            $activeTab = 'delete';

            if ($deleteName === '') {
                $message = '要删除的分类名不能为空。';
                break;
            }

            if (!categoryExists($deleteName)) {
                $message = "分类 '{$deleteName}' 不存在，无法删除。";
                break;
            }

            $stmt = $mysqli->prepare("DELETE FROM Categories WHERE category_name = ?");
            $stmt->bind_param("s", $deleteName);
            if ($stmt->execute()) {
                $messageType = 'success';
                $message = "分类 '{$deleteName}' 已删除。";
            } else {
                $message = '删除分类失败：' . $mysqli->error;
            }
            $stmt->close();
            break;

        case 'set_kindid':
            $categoryName = cleanText($_POST['category_name'] ?? '');
            $kindID = cleanKindID($_POST['kindid'] ?? '');
            $activeTab = 'set_kindid';

            if ($categoryName === '') {
                $message = '分类名不能为空。';
                break;
            }

            if (!categoryExists($categoryName)) {
                $message = "分类 '{$categoryName}' 不存在，无法设置 kindID。";
                break;
            }

            if (kindIDExists($kindID, $categoryName)) {
                $message = "kindID '{$kindID}' 已被其他分类占用。";
                break;
            }

            if ($kindID === null) {
                $stmt = $mysqli->prepare("UPDATE Categories SET kindID = NULL WHERE category_name = ?");
                $stmt->bind_param("s", $categoryName);
            } else {
                $stmt = $mysqli->prepare("UPDATE Categories SET kindID = ? WHERE category_name = ?");
                $stmt->bind_param("ss", $kindID, $categoryName);
            }

            if ($stmt->execute()) {
                $messageType = 'success';
                $message = "分类 '{$categoryName}' 的 kindID 已更新。";
            } else {
                $message = '更新 kindID 失败：' . $mysqli->error;
            }
            $stmt->close();
            break;

        case 'add':
            $newCategoryName = cleanText($_POST['new_category_name'] ?? '');
            $newKindID = cleanKindID($_POST['new_kindid'] ?? '');
            $activeTab = 'add';

            if ($newCategoryName === '') {
                $message = '新分类名不能为空。';
                break;
            }

            if (categoryExists($newCategoryName)) {
                $message = "分类 '{$newCategoryName}' 已存在，无法添加。";
                break;
            }

            if (kindIDExists($newKindID)) {
                $message = "kindID '{$newKindID}' 已被其他分类占用。";
                break;
            }

            if ($newKindID === null) {
                $stmt = $mysqli->prepare("INSERT INTO Categories (category_name) VALUES (?)");
                $stmt->bind_param("s", $newCategoryName);
            } else {
                $stmt = $mysqli->prepare("INSERT INTO Categories (category_name, kindID) VALUES (?, ?)");
                $stmt->bind_param("ss", $newCategoryName, $newKindID);
            }

            if ($stmt->execute()) {
                $messageType = 'success';
                $message = "分类 '{$newCategoryName}' 已添加。";
            } else {
                $message = '添加分类失败：' . $mysqli->error;
            }
            $stmt->close();
            break;

        case 'list':
            $activeTab = 'list';
            $messageType = 'success';
            $message = '已读取所有分类。';
            break;
    }
}

$categories = getAllCategoriesWithKindID();
$tabs = [
    'rename' => '修改已有分类名',
    'delete' => '删除已有分类名',
    'set_kindid' => '添加或修改 kindID',
    'add' => '添加分类和 kindID',
    'list' => '打印所有分类'
];

if (!array_key_exists($activeTab, $tabs)) {
    $activeTab = 'rename';
}

$categoryColumns = [];
$displayCategories = $sort === 'desc' ? array_reverse($categories) : $categories;
if (!empty($displayCategories)) {
    $categoryColumns = array_chunk($displayCategories, (int)ceil(count($displayCategories) / 3));
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <style>
        :root {
            --bg: #f6f7fb;
            --panel: #ffffff;
            --text: #172033;
            --muted: #6b7280;
            --line: #e3e7ef;
            --accent: #2563eb;
            --accent-soft: #e8f0ff;
            --danger: #dc2626;
            --success: #15803d;
            --shadow: 0 16px 42px rgba(27, 39, 68, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: #ffffff;
            border-right: 1px solid var(--line);
            padding: 24px 18px;
        }

        .brand {
            margin: 0 0 22px;
            font-size: 18px;
            font-weight: 700;
        }

        .tab-button {
            display: block;
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #4b5563;
            cursor: pointer;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 8px;
            padding: 12px 14px;
            text-align: left;
        }

        .tab-button:hover,
        .tab-button.active {
            background: var(--accent-soft);
            color: var(--accent);
        }

        .main {
            padding: 30px;
        }

        .panel {
            display: none;
            max-width: 760px;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .panel.active {
            display: block;
        }

        h1,
        h2 {
            margin: 0;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 18px;
        }

        .form-row {
            margin-bottom: 16px;
        }

        label {
            display: block;
            color: #374151;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        input {
            width: 100%;
            border: 1px solid #d6dbe6;
            border-radius: 8px;
            color: var(--text);
            font-size: 15px;
            padding: 11px 12px;
        }

        input:focus {
            border-color: var(--accent);
            outline: 3px solid rgba(37, 99, 235, 0.14);
        }

        .submit-button {
            border: 0;
            border-radius: 8px;
            background: var(--accent);
            color: #ffffff;
            cursor: pointer;
            font-size: 15px;
            font-weight: 700;
            padding: 11px 18px;
        }

        .submit-button.danger {
            background: var(--danger);
        }

        .message {
            max-width: 760px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 18px;
            padding: 13px 15px;
        }

        .message.success {
            background: #eaf7ee;
            color: var(--success);
        }

        .message.error {
            background: #fff1f1;
            color: var(--danger);
        }

        .table-tools {
            display: flex;
            gap: 10px;
            margin-top: 24px;
        }

        .sort-link {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            color: #4b5563;
            font-size: 14px;
            font-weight: 700;
            padding: 9px 12px;
            text-decoration: none;
        }

        .sort-link.active {
            background: var(--accent-soft);
            border-color: rgba(37, 99, 235, 0.28);
            color: var(--accent);
        }

        .category-columns {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 12px;
        }

        .table-wrap {
            overflow-x: auto;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th,
        td {
            border-bottom: 1px solid var(--line);
            padding: 8px 9px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f9fafc;
            color: #374151;
            font-weight: 700;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        th:first-child,
        td:first-child {
            width: 44px;
        }

        .empty {
            color: var(--muted);
        }

        @media (max-width: 1200px) {
            .category-columns {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 800px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .main {
                padding: 18px;
            }

            .category-columns {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <p class="brand">Categories</p>
            <?php foreach ($tabs as $tabKey => $tabLabel): ?>
                <button
                    type="button"
                    class="tab-button<?php echo $tabKey === $activeTab ? ' active' : ''; ?>"
                    data-tab="<?php echo h($tabKey); ?>"
                >
                    <?php echo h($tabLabel); ?>
                </button>
            <?php endforeach; ?>
        </aside>

        <main class="main">
            <h1>分类管理</h1>

            <?php if ($message !== ''): ?>
                <div class="message <?php echo h($messageType); ?>"><?php echo h($message); ?></div>
            <?php endif; ?>

            <section id="panel-rename" class="panel<?php echo $activeTab === 'rename' ? ' active' : ''; ?>">
                <h2>修改已有分类名</h2>
                <form method="post" data-confirm="确认修改这个分类名吗？">
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" name="active_tab" value="rename">
                    <input type="hidden" name="sort" value="<?php echo h($sort); ?>">
                    <div class="form-row">
                        <label for="old_name">原分类名</label>
                        <input id="old_name" name="old_name" list="category_names" required>
                    </div>
                    <div class="form-row">
                        <label for="new_name">新分类名</label>
                        <input id="new_name" name="new_name" required>
                    </div>
                    <button class="submit-button" type="submit">确认修改</button>
                </form>
            </section>

            <section id="panel-delete" class="panel<?php echo $activeTab === 'delete' ? ' active' : ''; ?>">
                <h2>删除已有分类名</h2>
                <form method="post" data-confirm="确认删除这个分类吗？">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="active_tab" value="delete">
                    <input type="hidden" name="sort" value="<?php echo h($sort); ?>">
                    <div class="form-row">
                        <label for="delete_name">分类名</label>
                        <input id="delete_name" name="delete_name" list="category_names" required>
                    </div>
                    <button class="submit-button danger" type="submit">确认删除</button>
                </form>
            </section>

            <section id="panel-set_kindid" class="panel<?php echo $activeTab === 'set_kindid' ? ' active' : ''; ?>">
                <h2>给指定已有分类名添加或修改 kindID</h2>
                <form method="post" data-confirm="确认更新这个 kindID 吗？">
                    <input type="hidden" name="action" value="set_kindid">
                    <input type="hidden" name="active_tab" value="set_kindid">
                    <input type="hidden" name="sort" value="<?php echo h($sort); ?>">
                    <div class="form-row">
                        <label for="category_name">分类名</label>
                        <input id="category_name" name="category_name" list="category_names" required>
                    </div>
                    <div class="form-row">
                        <label for="kindid">kindID</label>
                        <input id="kindid" name="kindid">
                    </div>
                    <button class="submit-button" type="submit">确认保存</button>
                </form>
            </section>

            <section id="panel-add" class="panel<?php echo $activeTab === 'add' ? ' active' : ''; ?>">
                <h2>添加新的分类名和对应 kindID</h2>
                <form method="post" data-confirm="确认添加这个分类吗？">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="active_tab" value="add">
                    <input type="hidden" name="sort" value="<?php echo h($sort); ?>">
                    <div class="form-row">
                        <label for="new_category_name">新分类名</label>
                        <input id="new_category_name" name="new_category_name" required>
                    </div>
                    <div class="form-row">
                        <label for="new_kindid">kindID</label>
                        <input id="new_kindid" name="new_kindid">
                    </div>
                    <button class="submit-button" type="submit">确认添加</button>
                </form>
            </section>

            <section id="panel-list" class="panel<?php echo $activeTab === 'list' ? ' active' : ''; ?>">
                <h2>打印所有分类名以及对应 kindID</h2>
                <form method="post">
                    <input type="hidden" name="action" value="list">
                    <input type="hidden" name="active_tab" value="list">
                    <input type="hidden" name="sort" value="<?php echo h($sort); ?>">
                    <button class="submit-button" type="submit">刷新列表</button>
                </form>
            </section>

            <datalist id="category_names">
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo h($category['category_name']); ?>"></option>
                <?php endforeach; ?>
            </datalist>

            <div class="table-tools">
                <a class="sort-link<?php echo $sort === 'asc' ? ' active' : ''; ?>" href="?sort=asc&active_tab=<?php echo h($activeTab); ?>">ID 正序</a>
                <a class="sort-link<?php echo $sort === 'desc' ? ' active' : ''; ?>" href="?sort=desc&active_tab=<?php echo h($activeTab); ?>">ID 倒序</a>
            </div>

            <div class="category-columns">
                <?php if (empty($categories)): ?>
                    <div class="table-wrap">
                        <table>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="empty">暂无分类。</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <?php foreach ($categoryColumns as $categoryColumn): ?>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>category_name</th>
                                        <th>kindID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categoryColumn as $category): ?>
                                        <tr>
                                            <td><?php echo h($category['id']); ?></td>
                                            <td><?php echo h($category['category_name']); ?></td>
                                            <td><?php echo h($category['kindID'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        const tabButtons = document.querySelectorAll('.tab-button');
        const panels = document.querySelectorAll('.panel');

        tabButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const activeTab = button.dataset.tab;

                tabButtons.forEach((item) => {
                    item.classList.toggle('active', item === button);
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('active', panel.id === `panel-${activeTab}`);
                });
            });
        });

        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
