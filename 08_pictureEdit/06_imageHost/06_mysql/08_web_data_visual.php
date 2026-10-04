<?php
session_start();

function decrypt($data, $key) {
    $decoded = base64_decode((string)$data, true);
    if ($decoded === false || strpos($decoded, '::') === false) {
        return false;
    }

    list($encryptedData, $iv) = explode('::', $decoded, 2);
    return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
}

$key = 'signin-key-1';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if (isset($_COOKIE['user_auth']) && decrypt($_COOKIE['user_auth'], $key) === 'mcteaone') {
        $_SESSION['loggedin'] = true;
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

require_once __DIR__ . '/08_db_config.php';
$mysqli->set_charset('utf8mb4');

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function jsonResponse($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, max-age=0');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getCategoryRows($mysqli) {
    $sql = "
        SELECT
            c.id,
            c.category_name,
            c.kindID,
            COUNT(pc.image_id) AS image_count
        FROM Categories AS c
        LEFT JOIN PicCategories AS pc ON pc.category_id = c.id
        GROUP BY c.id, c.category_name, c.kindID
        ORDER BY c.id ASC
    ";
    $result = $mysqli->query($sql);
    if (!$result) {
        throw new RuntimeException('无法读取分类：' . $mysqli->error);
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            'id' => (int)$row['id'],
            'category_name' => (string)$row['category_name'],
            'kindID' => $row['kindID'] === null ? '' : (string)$row['kindID'],
            'image_count' => (int)$row['image_count'],
            'rank' => 0
        ];
    }

    $sorted = $rows;
    usort($sorted, function ($a, $b) {
        if ($a['image_count'] === $b['image_count']) {
            return $a['id'] <=> $b['id'];
        }
        return $b['image_count'] <=> $a['image_count'];
    });

    $rankById = [];
    $previousCount = null;
    $previousRank = 0;
    foreach ($sorted as $index => $row) {
        if ($previousCount === null || $row['image_count'] !== $previousCount) {
            $previousRank = $index + 1;
            $previousCount = $row['image_count'];
        }
        $rankById[$row['id']] = $previousRank;
    }

    foreach ($rows as &$row) {
        $row['rank'] = $rankById[$row['id']];
    }
    unset($row);

    return $rows;
}

function getSelectedCategory($categories, $categoryId) {
    foreach ($categories as $category) {
        if ($category['id'] === $categoryId) {
            return $category;
        }
    }
    return null;
}

function getStateAndLikesDistribution($mysqli, $categoryId) {
    $sql = "
        SELECT
            COALESCE(i.likes, 0) AS likes_value,
            CASE WHEN i.image_exists = 1 THEN 1 ELSE 0 END AS exists_value,
            COUNT(*) AS image_count
        FROM PicCategories AS pc
        INNER JOIN images AS i ON i.id = pc.image_id
        WHERE pc.category_id = ?
        GROUP BY COALESCE(i.likes, 0), CASE WHEN i.image_exists = 1 THEN 1 ELSE 0 END
        ORDER BY likes_value ASC, exists_value ASC
    ";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('无法准备点赞统计查询：' . $mysqli->error);
    }
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    $all = [];
    $local = [];
    $cloud = [];
    $localTotal = 0;
    $cloudTotal = 0;

    while ($row = $result->fetch_assoc()) {
        $likes = (int)$row['likes_value'];
        $count = (int)$row['image_count'];
        $exists = (int)$row['exists_value'];

        if (!isset($all[$likes])) {
            $all[$likes] = 0;
        }
        $all[$likes] += $count;

        if ($exists === 1) {
            $local[$likes] = $count;
            $localTotal += $count;
        } else {
            $cloud[$likes] = $count;
            $cloudTotal += $count;
        }
    }
    $stmt->close();

    ksort($all, SORT_NUMERIC);
    ksort($local, SORT_NUMERIC);
    ksort($cloud, SORT_NUMERIC);

    $toPoints = function ($items) {
        $points = [];
        foreach ($items as $likes => $count) {
            $points[] = ['likes' => (int)$likes, 'count' => (int)$count];
        }
        return $points;
    };

    return [
        'totals' => [
            'all' => $localTotal + $cloudTotal,
            'local' => $localTotal,
            'cloud' => $cloudTotal
        ],
        'all' => $toPoints($all),
        'local' => $toPoints($local),
        'cloud' => $toPoints($cloud)
    ];
}

function getCategoryOverlap($mysqli, $categoryId) {
    $sql = "
        SELECT
            other_category.id,
            other_category.category_name,
            other_category.kindID,
            COUNT(DISTINCT other_link.image_id) AS total_count,
            COUNT(DISTINCT CASE WHEN i.image_exists = 1 THEN other_link.image_id END) AS local_count,
            COUNT(DISTINCT CASE WHEN i.image_exists <> 1 OR i.image_exists IS NULL THEN other_link.image_id END) AS cloud_count
        FROM PicCategories AS selected_link
        INNER JOIN images AS i ON i.id = selected_link.image_id
        INNER JOIN PicCategories AS other_link
            ON other_link.image_id = selected_link.image_id
            AND other_link.category_id <> selected_link.category_id
        INNER JOIN Categories AS other_category ON other_category.id = other_link.category_id
        WHERE selected_link.category_id = ?
        GROUP BY other_category.id, other_category.category_name, other_category.kindID
        ORDER BY total_count DESC, other_category.id ASC
    ";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('无法准备分类关联查询：' . $mysqli->error);
    }
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            'id' => (int)$row['id'],
            'category_name' => (string)$row['category_name'],
            'kindID' => $row['kindID'] === null ? '' : (string)$row['kindID'],
            'total_count' => (int)$row['total_count'],
            'local_count' => (int)$row['local_count'],
            'cloud_count' => (int)$row['cloud_count']
        ];
    }
    $stmt->close();

    return $rows;
}

try {
    $categories = getCategoryRows($mysqli);

    if (isset($_GET['ajax']) && $_GET['ajax'] === 'category_stats') {
        $categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
        if (!$categoryId || $categoryId < 1) {
            jsonResponse(['ok' => false, 'message' => '分类 ID 无效。'], 400);
        }

        $category = getSelectedCategory($categories, (int)$categoryId);
        if ($category === null) {
            jsonResponse(['ok' => false, 'message' => '未找到该分类。'], 404);
        }

        $distribution = getStateAndLikesDistribution($mysqli, (int)$categoryId);
        $overlap = getCategoryOverlap($mysqli, (int)$categoryId);

        jsonResponse([
            'ok' => true,
            'category' => $category,
            'category_count' => count($categories),
            'totals' => $distribution['totals'],
            'likes' => [
                'all' => $distribution['all'],
                'local' => $distribution['local'],
                'cloud' => $distribution['cloud']
            ],
            'overlap' => $overlap
        ]);
    }
} catch (Throwable $error) {
    if (isset($_GET['ajax'])) {
        jsonResponse(['ok' => false, 'message' => '读取统计数据失败：' . $error->getMessage()], 500);
    }
    $pageError = $error->getMessage();
    $categories = [];
}

$initialCategoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$allowedViews = ['overview', 'frequency_all', 'frequency_local', 'frequency_cloud', 'overlap'];
$initialView = isset($_GET['view']) && in_array($_GET['view'], $allowedViews, true)
    ? $_GET['view']
    : 'overview';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>图片数据分析</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --bg: #f4f7fb;
            --panel: rgba(255, 255, 255, 0.96);
            --text: #172033;
            --muted: #6f7b91;
            --line: #e2e8f1;
            --primary: #4f6ef7;
            --primary-soft: #edf1ff;
            --cyan: #20b7c9;
            --orange: #f59e5b;
            --green: #37b979;
            --purple: #8b6fe8;
            --danger: #dc5b69;
            --shadow: 0 18px 55px rgba(25, 42, 75, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            background:
                radial-gradient(circle at 8% -10%, rgba(79, 110, 247, 0.12), transparent 28rem),
                radial-gradient(circle at 100% 8%, rgba(32, 183, 201, 0.09), transparent 26rem),
                var(--bg);
            font-family: Inter, "PingFang SC", "Microsoft YaHei", Arial, sans-serif;
        }

        button, input, select { font: inherit; }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 26px;
            min-height: 74px;
            padding: 0 34px;
            background: rgba(244, 247, 251, 0.87);
            border-bottom: 1px solid rgba(226, 232, 241, 0.86);
            backdrop-filter: blur(18px);
        }

        .brand { min-width: max-content; }
        .brand-title { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.02em; }
        .brand-subtitle { margin: 4px 0 0; color: var(--muted); font-size: 12px; }

        .top-nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .nav-button {
            border: 0;
            border-radius: 10px;
            padding: 10px 13px;
            color: #59667d;
            background: transparent;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: 0.18s ease;
        }

        .nav-button:hover { color: var(--primary); background: rgba(255, 255, 255, 0.76); }
        .nav-button.active { color: var(--primary); background: #fff; box-shadow: 0 7px 22px rgba(36, 55, 96, 0.09); }

        .page { width: min(1440px, calc(100% - 48px)); margin: 30px auto 70px; }

        .category-section,
        .content-card {
            background: var(--panel);
            border: 1px solid rgba(226, 232, 241, 0.92);
            border-radius: 18px;
            box-shadow: var(--shadow);
        }

        .category-section { padding: 22px; }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .section-heading h2 { margin: 0; font-size: 17px; }
        .section-heading p { margin: 5px 0 0; color: var(--muted); font-size: 13px; }

        .search-wrap { position: relative; width: min(300px, 100%); }
        .category-search {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 11px;
            outline: none;
            padding: 10px 13px;
            color: var(--text);
            background: #f9fbfe;
            transition: 0.18s ease;
        }
        .category-search:focus { border-color: rgba(79, 110, 247, 0.55); box-shadow: 0 0 0 4px rgba(79, 110, 247, 0.1); }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
        }

        .category-button {
            min-width: 0;
            min-height: 58px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 12px;
            text-align: left;
            color: #283650;
            background: #fff;
            cursor: pointer;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .category-button:hover { transform: translateY(-1px); border-color: rgba(79, 110, 247, 0.42); box-shadow: 0 8px 20px rgba(48, 70, 120, 0.08); }
        .category-button.active { color: var(--primary); border-color: rgba(79, 110, 247, 0.42); background: var(--primary-soft); box-shadow: inset 0 0 0 1px rgba(79, 110, 247, 0.08); }
        .category-name { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 14px; font-weight: 750; }
        .category-meta { display: block; margin-top: 5px; color: var(--muted); font-size: 11px; }
        .category-button[hidden] { display: none; }

        .result-area { margin-top: 22px; }
        .content-card { padding: 24px; }
        .empty-state { display: grid; place-items: center; min-height: 280px; text-align: center; }
        .empty-icon { display: grid; place-items: center; width: 54px; height: 54px; margin: 0 auto 14px; border-radius: 16px; color: var(--primary); background: var(--primary-soft); font-size: 24px; }
        .empty-state h2 { margin: 0 0 8px; font-size: 19px; }
        .empty-state p { max-width: 520px; margin: 0; color: var(--muted); line-height: 1.7; }

        .loading { display: grid; place-items: center; min-height: 300px; color: var(--muted); }
        .spinner { width: 34px; height: 34px; margin-bottom: 12px; border: 3px solid #e7ebf5; border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .result-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 20px; }
        .result-header h2 { margin: 0; font-size: 23px; letter-spacing: -0.02em; }
        .result-header p { margin: 7px 0 0; color: var(--muted); font-size: 13px; }
        .view-badge { flex: none; border-radius: 999px; padding: 8px 12px; color: var(--primary); background: var(--primary-soft); font-size: 12px; font-weight: 800; }

        .metric-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
        .metric-card { border: 1px solid var(--line); border-radius: 14px; padding: 17px; background: linear-gradient(145deg, #fff, #fbfcff); }
        .metric-label { color: var(--muted); font-size: 12px; font-weight: 650; }
        .metric-value { display: block; margin-top: 9px; font-size: 26px; font-weight: 850; letter-spacing: -0.03em; }
        .metric-note { display: block; margin-top: 5px; color: var(--muted); font-size: 11px; }

        .chart-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .chart-card { min-width: 0; border: 1px solid var(--line); border-radius: 15px; padding: 18px; background: #fff; }
        .chart-card.wide { grid-column: 1 / -1; }
        .chart-title { margin: 0; font-size: 15px; }
        .chart-subtitle { margin: 6px 0 14px; color: var(--muted); font-size: 12px; line-height: 1.55; }
        .chart-box { position: relative; height: 330px; }
        .chart-box.compact { height: 290px; }

        .toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: 0 0 18px; padding: 12px 14px; border: 1px solid var(--line); border-radius: 13px; background: #f9fbfe; }
        .toolbar-label { color: #3f4d66; font-size: 13px; font-weight: 750; }
        .step-buttons { display: flex; gap: 6px; flex-wrap: wrap; }
        .step-button { border: 1px solid var(--line); border-radius: 8px; padding: 7px 10px; color: #5e6a80; background: #fff; cursor: pointer; font-size: 12px; font-weight: 750; }
        .step-button.active { color: #fff; border-color: var(--primary); background: var(--primary); }

        .overlap-stack { display: grid; gap: 16px; }
        .overlap-scroll { max-height: 660px; overflow-y: auto; padding-right: 5px; }
        .overlap-chart-shell { position: relative; min-height: 300px; }
        .notice { border: 1px solid #f2d5d8; border-radius: 13px; padding: 15px; color: #9f3541; background: #fff6f7; line-height: 1.6; }
        .no-data { display: grid; place-items: center; min-height: 220px; color: var(--muted); text-align: center; }

        @media (max-width: 1180px) {
            .category-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        @media (max-width: 860px) {
            .topbar { position: static; align-items: flex-start; flex-direction: column; padding: 20px; }
            .top-nav { justify-content: flex-start; }
            .page { width: calc(100% - 24px); margin-top: 16px; }
            .category-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .chart-grid { grid-template-columns: 1fr; }
            .chart-card.wide { grid-column: auto; }
        }
        @media (max-width: 560px) {
            .top-nav { gap: 3px; }
            .nav-button { padding: 9px 8px; font-size: 12px; }
            .category-section, .content-card { padding: 15px; border-radius: 14px; }
            .section-heading { align-items: stretch; flex-direction: column; }
            .search-wrap { width: 100%; }
            .category-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .metric-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .metric-card { padding: 13px; }
            .metric-value { font-size: 22px; }
            .toolbar { align-items: flex-start; flex-direction: column; }
            .result-header { flex-direction: column; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <h1 class="brand-title">图片数据分析</h1>
            <p class="brand-subtitle">Category intelligence dashboard</p>
        </div>
        <nav class="top-nav" aria-label="统计视图">
            <button class="nav-button<?php echo $initialView === 'overview' ? ' active' : ''; ?>" type="button" data-view="overview">数据概览</button>
            <button class="nav-button<?php echo $initialView === 'frequency_all' ? ' active' : ''; ?>" type="button" data-view="frequency_all">全部频率</button>
            <button class="nav-button<?php echo $initialView === 'frequency_local' ? ' active' : ''; ?>" type="button" data-view="frequency_local">本地频率</button>
            <button class="nav-button<?php echo $initialView === 'frequency_cloud' ? ' active' : ''; ?>" type="button" data-view="frequency_cloud">云端频率</button>
            <button class="nav-button<?php echo $initialView === 'overlap' ? ' active' : ''; ?>" type="button" data-view="overlap">分类关联</button>
        </nav>
    </header>

    <main class="page">
        <section class="category-section">
            <div class="section-heading">
                <div>
                    <h2>选择分类</h2>
                    <p>共 <?php echo count($categories); ?> 个分类，点击后加载当前统计视图</p>
                </div>
                <div class="search-wrap">
                    <input id="categorySearch" class="category-search" type="search" placeholder="搜索分类名或 kindID" autocomplete="off">
                </div>
            </div>

            <?php if (!empty($pageError)): ?>
                <div class="notice"><?php echo h($pageError); ?></div>
            <?php elseif (empty($categories)): ?>
                <div class="no-data">暂无分类数据。</div>
            <?php else: ?>
                <div id="categoryGrid" class="category-grid">
                    <?php foreach ($categories as $category): ?>
                        <button
                            class="category-button<?php echo (int)$initialCategoryId === $category['id'] ? ' active' : ''; ?>"
                            type="button"
                            data-category-id="<?php echo $category['id']; ?>"
                            data-search="<?php echo h($category['category_name'] . ' ' . $category['kindID']); ?>"
                        >
                            <span class="category-name" title="<?php echo h($category['category_name']); ?>"><?php echo h($category['category_name']); ?></span>
                            <span class="category-meta"><?php echo number_format($category['image_count']); ?> 张 · #<?php echo $category['id']; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section id="resultArea" class="result-area" aria-live="polite">
            <div class="content-card empty-state">
                <div>
                    <div class="empty-icon">⌁</div>
                    <h2>请选择一个分类</h2>
                    <p>选择上方任意分类后，这里会显示对应的数据概览、点赞频率与分类关联图表。</p>
                </div>
            </div>
        </section>
    </main>

    <script>
        const initialCategoryId = <?php echo $initialCategoryId ? (int)$initialCategoryId : 'null'; ?>;
        let activeView = <?php echo json_encode($initialView, JSON_UNESCAPED_UNICODE); ?>;
        let selectedCategoryId = null;
        let frequencyStep = 5;
        let currentStats = null;
        let requestController = null;
        const cache = new Map();
        const charts = [];

        const viewNames = {
            overview: '数据概览',
            frequency_all: '全部频率',
            frequency_local: '本地频率',
            frequency_cloud: '云端频率',
            overlap: '分类关联'
        };

        const colors = {
            primary: '#4f6ef7',
            primarySoft: 'rgba(79, 110, 247, 0.18)',
            cyan: '#20b7c9',
            cyanSoft: 'rgba(32, 183, 201, 0.20)',
            orange: '#f59e5b',
            orangeSoft: 'rgba(245, 158, 91, 0.20)',
            green: '#37b979',
            purple: '#8b6fe8',
            grid: 'rgba(124, 139, 166, 0.15)',
            text: '#66738a'
        };

        const chartLibraryAvailable = typeof Chart !== 'undefined';
        if (chartLibraryAvailable) {
            Chart.defaults.font.family = 'Inter, "PingFang SC", "Microsoft YaHei", Arial, sans-serif';
            Chart.defaults.color = colors.text;
            Chart.defaults.animation.duration = 450;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('zh-CN').format(Number(value || 0));
        }

        function percent(part, total) {
            return total > 0 ? Number((part * 100 / total).toFixed(1)) : 0;
        }

        function destroyCharts() {
            while (charts.length) {
                charts.pop().destroy();
            }
        }

        function updateUrl() {
            const url = new URL(window.location.href);
            url.searchParams.delete('ajax');
            if (selectedCategoryId) url.searchParams.set('category', selectedCategoryId);
            else url.searchParams.delete('category');
            url.searchParams.set('view', activeView);
            history.replaceState(null, '', url);
        }

        function setActiveCategory(categoryId) {
            document.querySelectorAll('.category-button').forEach((button) => {
                button.classList.toggle('active', Number(button.dataset.categoryId) === Number(categoryId));
            });
        }

        function showLoading() {
            destroyCharts();
            document.getElementById('resultArea').innerHTML = `
                <div class="content-card loading">
                    <div><div class="spinner"></div><div>正在读取统计数据…</div></div>
                </div>`;
        }

        function showError(message) {
            destroyCharts();
            document.getElementById('resultArea').innerHTML = `
                <div class="content-card"><div class="notice">${escapeHtml(message)}</div></div>`;
        }

        async function selectCategory(categoryId) {
            selectedCategoryId = Number(categoryId);
            setActiveCategory(selectedCategoryId);
            updateUrl();

            if (requestController) {
                requestController.abort();
                requestController = null;
            }

            if (cache.has(selectedCategoryId)) {
                currentStats = cache.get(selectedCategoryId);
                renderActiveView();
                return;
            }

            requestController = new AbortController();
            showLoading();

            try {
                const url = new URL(window.location.href);
                url.search = '';
                url.searchParams.set('ajax', 'category_stats');
                url.searchParams.set('category_id', selectedCategoryId);
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    signal: requestController.signal
                });
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('登录状态可能已失效，请刷新页面后重新登录。');
                }
                const data = await response.json();
                if (!response.ok || !data.ok) {
                    throw new Error(data.message || '统计数据加载失败。');
                }
                cache.set(selectedCategoryId, data);
                currentStats = data;
                renderActiveView();
            } catch (error) {
                if (error.name !== 'AbortError') {
                    showError(error.message || '统计数据加载失败。');
                }
            }
        }

        function headerHtml(stats, subtitle) {
            const category = stats.category;
            const kindId = category.kindID ? `kindID：${escapeHtml(category.kindID)}` : 'kindID：未设置';
            return `
                <div class="result-header">
                    <div>
                        <h2>${escapeHtml(category.category_name)}</h2>
                        <p>${kindId} · 分类 ID：${category.id} · ${escapeHtml(subtitle)}</p>
                    </div>
                    <span class="view-badge">${escapeHtml(viewNames[activeView])}</span>
                </div>`;
        }

        function metricHtml(label, value, note) {
            return `<div class="metric-card"><span class="metric-label">${escapeHtml(label)}</span><strong class="metric-value">${escapeHtml(value)}</strong><span class="metric-note">${escapeHtml(note)}</span></div>`;
        }

        function commonChartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } },
                    tooltip: { backgroundColor: 'rgba(23, 32, 51, 0.94)', padding: 11, cornerRadius: 9 }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 18 } },
                    y: { beginAtZero: true, grid: { color: colors.grid }, title: { display: true, text: '图片数量' }, ticks: { precision: 0 } },
                    y1: { beginAtZero: true, position: 'right', suggestedMax: 100, grid: { drawOnChartArea: false }, title: { display: true, text: '占比' }, ticks: { callback: (value) => `${value}%` } }
                }
            };
        }

        function createDistributionChart(canvasId, points, total, barColor, barSoftColor) {
            const labels = points.map((item) => String(item.likes));
            const counts = points.map((item) => item.count);
            const percentages = points.map((item) => percent(item.count, total));
            const chart = new Chart(document.getElementById(canvasId), {
                data: {
                    labels,
                    datasets: [
                        { type: 'bar', label: '图片数量', data: counts, yAxisID: 'y', backgroundColor: barSoftColor, borderColor: barColor, borderWidth: 1, borderRadius: 5, maxBarThickness: 28 },
                        { type: 'line', label: '占比', data: percentages, yAxisID: 'y1', borderColor: barColor, backgroundColor: barColor, pointRadius: labels.length > 45 ? 0 : 2.5, pointHoverRadius: 5, borderWidth: 2, tension: 0.25 }
                    ]
                },
                options: commonChartOptions()
            });
            charts.push(chart);
        }

        function createDoughnutChart(canvasId, totals) {
            const total = totals.all;
            const chart = new Chart(document.getElementById(canvasId), {
                type: 'doughnut',
                data: {
                    labels: ['本地图片', '云端图片'],
                    datasets: [{ data: [totals.local, totals.cloud], backgroundColor: [colors.green, colors.orange], borderColor: '#fff', borderWidth: 4, hoverOffset: 7 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '67%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 18 } },
                        tooltip: { callbacks: { label: (context) => ` ${context.label}：${formatNumber(context.raw)} 张（${percent(context.raw, total)}%）` } }
                    }
                }
            });
            charts.push(chart);
        }

        function renderOverview() {
            const stats = currentStats;
            const totals = stats.totals;
            document.getElementById('resultArea').innerHTML = `
                <div class="content-card">
                    ${headerHtml(stats, '分类画像与精确点赞分布')}
                    <div class="metric-grid">
                        ${metricHtml('图片总数', formatNumber(totals.all), '当前分类全部图片')}
                        ${metricHtml('数量排名', `第 ${stats.category.rank} 名`, `共 ${stats.category_count} 个分类`)}
                        ${metricHtml('本地图片', formatNumber(totals.local), `占 ${percent(totals.local, totals.all)}%`)}
                        ${metricHtml('云端图片', formatNumber(totals.cloud), `占 ${percent(totals.cloud, totals.all)}%`)}
                    </div>
                    <div class="chart-grid">
                        <article class="chart-card">
                            <h3 class="chart-title">存储位置构成</h3>
                            <p class="chart-subtitle">image_exists = 1 为本地，image_exists = 0 为云端</p>
                            <div class="chart-box compact"><canvas id="stateChart"></canvas></div>
                        </article>
                        <article class="chart-card">
                            <h3 class="chart-title">全部图片的 likes 分布</h3>
                            <p class="chart-subtitle">横轴按实际 likes 最大值动态扩展；柱形为数量，折线为占比</p>
                            <div class="chart-box compact"><canvas id="allLikesChart"></canvas></div>
                        </article>
                        <article class="chart-card">
                            <h3 class="chart-title">本地图片 likes 分布</h3>
                            <p class="chart-subtitle">仅统计 image_exists = 1</p>
                            <div class="chart-box"><canvas id="localLikesChart"></canvas></div>
                        </article>
                        <article class="chart-card">
                            <h3 class="chart-title">云端图片 likes 分布</h3>
                            <p class="chart-subtitle">统计 image_exists = 0 的图片</p>
                            <div class="chart-box"><canvas id="cloudLikesChart"></canvas></div>
                        </article>
                    </div>
                </div>`;

            createDoughnutChart('stateChart', totals);
            createDistributionChart('allLikesChart', stats.likes.all, totals.all, colors.primary, colors.primarySoft);
            createDistributionChart('localLikesChart', stats.likes.local, totals.local, colors.green, 'rgba(55, 185, 121, 0.20)');
            createDistributionChart('cloudLikesChart', stats.likes.cloud, totals.cloud, colors.orange, colors.orangeSoft);
        }

        function buildHistogram(points, step) {
            if (!points.length) return [];
            const minValue = Math.min(0, ...points.map((item) => item.likes));
            const maxValue = Math.max(...points.map((item) => item.likes));
            const first = Math.floor(minValue / step) * step;
            const bins = [];

            for (let start = first; start <= maxValue; start += step) {
                bins.push({ start, end: start + step - 1, count: 0 });
            }
            points.forEach((item) => {
                const index = Math.floor((item.likes - first) / step);
                if (bins[index]) bins[index].count += item.count;
            });
            return bins;
        }

        function stepToolbarHtml() {
            return `
                <div class="toolbar">
                    <span class="toolbar-label">likes 区间步长</span>
                    <div class="step-buttons">
                        ${[2, 3, 4, 5, 6, 10].map((step) => `<button type="button" class="step-button${frequencyStep === step ? ' active' : ''}" data-step="${step}">${step}</button>`).join('')}
                    </div>
                </div>`;
        }

        function createHistogramChart(canvasId, points, total, color, softColor) {
            const bins = buildHistogram(points, frequencyStep);
            const labels = bins.map((bin) => bin.start === bin.end ? String(bin.start) : `${bin.start}–${bin.end}`);
            const counts = bins.map((bin) => bin.count);
            const percentages = counts.map((count) => percent(count, total));
            const options = commonChartOptions();
            options.scales.x.title = { display: true, text: `likes 区间（步长 ${frequencyStep}）` };
            const chart = new Chart(document.getElementById(canvasId), {
                data: {
                    labels,
                    datasets: [
                        { type: 'bar', label: '图片数量', data: counts, yAxisID: 'y', backgroundColor: softColor, borderColor: color, borderWidth: 1, borderRadius: 6, maxBarThickness: 42 },
                        { type: 'line', label: '占比', data: percentages, yAxisID: 'y1', borderColor: color, backgroundColor: color, pointRadius: labels.length > 35 ? 0 : 3, pointHoverRadius: 5, borderWidth: 2.2, tension: 0.28 }
                    ]
                },
                options
            });
            charts.push(chart);
        }

        function renderFrequency(type) {
            const stats = currentStats;
            const config = {
                all: { title: '全部图片频率分布', subtitle: '不区分图片存储状态', points: stats.likes.all, total: stats.totals.all, color: colors.primary, soft: colors.primarySoft },
                local: { title: '本地图片频率分布', subtitle: '仅统计 image_exists = 1', points: stats.likes.local, total: stats.totals.local, color: colors.green, soft: 'rgba(55, 185, 121, 0.20)' },
                cloud: { title: '云端图片频率分布', subtitle: '仅统计 image_exists = 0', points: stats.likes.cloud, total: stats.totals.cloud, color: colors.orange, soft: colors.orangeSoft }
            }[type];

            document.getElementById('resultArea').innerHTML = `
                <div class="content-card">
                    ${headerHtml(stats, config.subtitle)}
                    <div class="metric-grid">
                        ${metricHtml('统计图片数', formatNumber(config.total), config.subtitle)}
                        ${metricHtml('区间步长', frequencyStep, '可随时切换')}
                        ${metricHtml('最小 likes', config.points.length ? Math.min(...config.points.map((item) => item.likes)) : 0, '当前统计范围')}
                        ${metricHtml('最大 likes', config.points.length ? Math.max(...config.points.map((item) => item.likes)) : 0, '横轴动态上限')}
                    </div>
                    ${stepToolbarHtml()}
                    <article class="chart-card">
                        <h3 class="chart-title">${config.title}</h3>
                        <p class="chart-subtitle">每个柱表示一个 likes 区间内的图片数量，折线表示该区间占当前统计图片的比例</p>
                        <div class="chart-box"><canvas id="histogramChart"></canvas></div>
                    </article>
                </div>`;
            createHistogramChart('histogramChart', config.points, config.total, config.color, config.soft);
        }

        function overlapChartHtml(id, title, subtitle, rows) {
            if (!rows.length) {
                return `<article class="chart-card"><h3 class="chart-title">${escapeHtml(title)}</h3><p class="chart-subtitle">${escapeHtml(subtitle)}</p><div class="no-data">没有发现与其他分类重叠的图片。</div></article>`;
            }
            const height = Math.max(310, rows.length * 34 + 70);
            return `
                <article class="chart-card">
                    <h3 class="chart-title">${escapeHtml(title)}</h3>
                    <p class="chart-subtitle">${escapeHtml(subtitle)}；分类越靠上，共享图片越多</p>
                    <div class="overlap-scroll"><div class="overlap-chart-shell" style="height:${height}px"><canvas id="${id}"></canvas></div></div>
                </article>`;
        }

        function createOverlapChart(canvasId, rows, countKey, denominator, color, softColor) {
            if (!rows.length || !document.getElementById(canvasId)) return;
            const sorted = [...rows].sort((a, b) => b[countKey] - a[countKey] || a.id - b.id);
            const chart = new Chart(document.getElementById(canvasId), {
                type: 'bar',
                data: {
                    labels: sorted.map((row) => row.category_name),
                    datasets: [{ label: '共享图片数', data: sorted.map((row) => row[countKey]), backgroundColor: softColor, borderColor: color, borderWidth: 1, borderRadius: 5, maxBarThickness: 22 }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (context) => ` 共享 ${formatNumber(context.raw)} 张，占当前分类 ${percent(context.raw, denominator)}%` } }
                    },
                    scales: {
                        x: { beginAtZero: true, grid: { color: colors.grid }, title: { display: true, text: '共享图片数量' }, ticks: { precision: 0 } },
                        y: { grid: { display: false } }
                    }
                }
            });
            charts.push(chart);
        }

        function renderOverlap() {
            const stats = currentStats;
            const allRows = stats.overlap.filter((row) => row.total_count > 0);
            const localRows = stats.overlap.filter((row) => row.local_count > 0);
            const cloudRows = stats.overlap.filter((row) => row.cloud_count > 0);

            document.getElementById('resultArea').innerHTML = `
                <div class="content-card">
                    ${headerHtml(stats, '与其他分类的共享图片分析（不包含当前分类自身）')}
                    <div class="metric-grid">
                        ${metricHtml('关联分类数', formatNumber(allRows.length), '至少共享 1 张图片')}
                        ${metricHtml('全部图片', formatNumber(stats.totals.all), '重叠率以此为分母')}
                        ${metricHtml('本地图片', formatNumber(stats.totals.local), '本地重叠率分母')}
                        ${metricHtml('云端图片', formatNumber(stats.totals.cloud), '云端重叠率分母')}
                    </div>
                    <div class="overlap-stack">
                        ${overlapChartHtml('overlapAllChart', '全部图片的分类关联', '不区分 image_exists', allRows)}
                        ${overlapChartHtml('overlapLocalChart', '本地图片的分类关联', '仅统计 image_exists = 1', localRows)}
                        ${overlapChartHtml('overlapCloudChart', '云端图片的分类关联', '仅统计 image_exists = 0', cloudRows)}
                    </div>
                </div>`;

            createOverlapChart('overlapAllChart', allRows, 'total_count', stats.totals.all, colors.primary, colors.primarySoft);
            createOverlapChart('overlapLocalChart', localRows, 'local_count', stats.totals.local, colors.green, 'rgba(55, 185, 121, 0.20)');
            createOverlapChart('overlapCloudChart', cloudRows, 'cloud_count', stats.totals.cloud, colors.orange, colors.orangeSoft);
        }

        function renderActiveView() {
            if (!currentStats) return;
            if (!chartLibraryAvailable) {
                showError('图表组件加载失败，请检查网络连接后刷新页面。');
                return;
            }
            destroyCharts();
            if (activeView === 'overview') renderOverview();
            else if (activeView === 'frequency_all') renderFrequency('all');
            else if (activeView === 'frequency_local') renderFrequency('local');
            else if (activeView === 'frequency_cloud') renderFrequency('cloud');
            else renderOverlap();
        }

        document.querySelectorAll('.nav-button').forEach((button) => {
            button.addEventListener('click', () => {
                activeView = button.dataset.view;
                document.querySelectorAll('.nav-button').forEach((item) => item.classList.toggle('active', item === button));
                updateUrl();
                renderActiveView();
            });
        });

        document.querySelectorAll('.category-button').forEach((button) => {
            button.addEventListener('click', () => selectCategory(button.dataset.categoryId));
        });

        document.getElementById('categorySearch')?.addEventListener('input', (event) => {
            const query = event.target.value.trim().toLocaleLowerCase('zh-CN');
            document.querySelectorAll('.category-button').forEach((button) => {
                button.hidden = query !== '' && !button.dataset.search.toLocaleLowerCase('zh-CN').includes(query);
            });
        });

        document.getElementById('resultArea').addEventListener('click', (event) => {
            const button = event.target.closest('[data-step]');
            if (!button) return;
            frequencyStep = Number(button.dataset.step);
            renderActiveView();
        });

        if (initialCategoryId) {
            selectCategory(initialCategoryId);
        }
    </script>
</body>
</html>
