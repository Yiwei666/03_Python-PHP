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

function getCategoryGraph($mysqli, $category, $nodeLimit, $minWeight, $depth, $perNode, $maxEdges, $focusId = null) {
    $categoryId = (int)$category['id'];
    $focusId = $focusId === null ? $categoryId : (int)$focusId;
    $selectedIds = [$categoryId => true];
    $levels = [$categoryId => 0];
    if ($focusId !== $categoryId) {
        $selectedIds[$focusId] = true;
        $levels[$focusId] = 0;
    }
    $frontier = [$focusId];
    $backbone = [];

    for ($level = 1; $level <= $depth && !empty($frontier) && count($selectedIds) < $nodeLimit; $level++) {
        $frontierList = implode(',', array_map('intval', $frontier));
        $neighborSql = "
            SELECT
                source_link.category_id AS source_id,
                target_link.category_id AS target_id,
                COUNT(DISTINCT source_link.image_id) AS shared_count
            FROM PicCategories AS source_link
            INNER JOIN PicCategories AS target_link
                ON target_link.image_id = source_link.image_id
                AND target_link.category_id <> source_link.category_id
            WHERE source_link.category_id IN ($frontierList)
            GROUP BY source_link.category_id, target_link.category_id
            HAVING shared_count >= " . (int)$minWeight . "
            ORDER BY source_id ASC, shared_count DESC, target_id ASC
        ";
        $neighborResult = $mysqli->query($neighborSql);
        if (!$neighborResult) {
            throw new RuntimeException('无法读取分层邻居：' . $mysqli->error);
        }

        $addedPerSource = [];
        $nextFrontier = [];
        while ($row = $neighborResult->fetch_assoc()) {
            $source = (int)$row['source_id'];
            $target = (int)$row['target_id'];
            $weight = (int)$row['shared_count'];
            if (isset($selectedIds[$target]) || ($addedPerSource[$source] ?? 0) >= $perNode) {
                continue;
            }
            if (count($selectedIds) >= $nodeLimit) {
                break;
            }

            $selectedIds[$target] = true;
            $levels[$target] = $level;
            $nextFrontier[$target] = true;
            $addedPerSource[$source] = ($addedPerSource[$source] ?? 0) + 1;
            $edgeSource = min($source, $target);
            $edgeTarget = max($source, $target);
            $backbone[$edgeSource . ':' . $edgeTarget] = [
                'source' => $edgeSource,
                'target' => $edgeTarget,
                'weight' => $weight,
                'backbone' => true
            ];
        }
        $frontier = array_map('intval', array_keys($nextFrontier));
    }

    $nodeIds = array_map('intval', array_keys($selectedIds));
    $idList = implode(',', $nodeIds);
    $nodeSql = "
        SELECT c.id, c.category_name, c.kindID, COUNT(pc.image_id) AS image_count
        FROM Categories AS c
        LEFT JOIN PicCategories AS pc ON pc.category_id = c.id
        WHERE c.id IN ($idList)
        GROUP BY c.id, c.category_name, c.kindID
    ";
    $nodeResult = $mysqli->query($nodeSql);
    if (!$nodeResult) {
        throw new RuntimeException('无法读取图谱节点信息：' . $mysqli->error);
    }

    $sharedWithSelected = [];
    if (count($nodeIds) > 1) {
        $sharedSql = "
            SELECT other_link.category_id, COUNT(DISTINCT selected_link.image_id) AS shared_count
            FROM PicCategories AS selected_link
            INNER JOIN PicCategories AS other_link
                ON other_link.image_id = selected_link.image_id
                AND other_link.category_id <> selected_link.category_id
            WHERE selected_link.category_id = $categoryId
              AND other_link.category_id IN ($idList)
            GROUP BY other_link.category_id
        ";
        $sharedResult = $mysqli->query($sharedSql);
        if (!$sharedResult) {
            throw new RuntimeException('无法读取中心分类共享数量：' . $mysqli->error);
        }
        while ($row = $sharedResult->fetch_assoc()) {
            $sharedWithSelected[(int)$row['category_id']] = (int)$row['shared_count'];
        }
    }

    $nodes = [];
    while ($row = $nodeResult->fetch_assoc()) {
        $id = (int)$row['id'];
        $nodes[] = [
            'id' => $id,
            'category_name' => (string)$row['category_name'],
            'kindID' => $row['kindID'] === null ? '' : (string)$row['kindID'],
            'image_count' => (int)$row['image_count'],
            'shared_with_selected' => $id === $categoryId
                ? (int)$row['image_count']
                : ($sharedWithSelected[$id] ?? 0),
            'selected' => $id === $categoryId,
            'focus' => $id === $focusId,
            'level' => $levels[$id] ?? $depth,
            'degree' => 0,
            'weighted_degree' => 0
        ];
    }
    usort($nodes, function ($a, $b) {
        if ($a['selected'] !== $b['selected']) {
            return $a['selected'] ? -1 : 1;
        }
        if ($a['level'] !== $b['level']) {
            return $a['level'] <=> $b['level'];
        }
        return $b['image_count'] <=> $a['image_count'];
    });

    $edgeSql = "
        SELECT
            first_link.category_id AS source_id,
            second_link.category_id AS target_id,
            COUNT(DISTINCT first_link.image_id) AS shared_count
        FROM PicCategories AS first_link
        INNER JOIN PicCategories AS second_link
            ON second_link.image_id = first_link.image_id
            AND first_link.category_id < second_link.category_id
        WHERE first_link.category_id IN ($idList)
          AND second_link.category_id IN ($idList)
        GROUP BY first_link.category_id, second_link.category_id
        HAVING shared_count >= " . (int)$minWeight . "
        ORDER BY shared_count DESC, source_id ASC, target_id ASC
        LIMIT " . (int)$maxEdges . "
    ";
    $edgeResult = $mysqli->query($edgeSql);
    if (!$edgeResult) {
        throw new RuntimeException('无法读取关系图谱连线：' . $mysqli->error);
    }

    $linkMap = $backbone;
    while ($row = $edgeResult->fetch_assoc()) {
        $source = (int)$row['source_id'];
        $target = (int)$row['target_id'];
        $key = $source . ':' . $target;
        $linkMap[$key] = [
            'source' => $source,
            'target' => $target,
            'weight' => (int)$row['shared_count'],
            'backbone' => isset($backbone[$key])
        ];
    }
    $links = array_values($linkMap);
    usort($links, function ($a, $b) {
        if ($a['backbone'] !== $b['backbone']) {
            return $a['backbone'] ? -1 : 1;
        }
        return $b['weight'] <=> $a['weight'];
    });
    if (count($links) > $maxEdges) {
        $links = array_slice($links, 0, $maxEdges);
    }

    $nodeIndex = [];
    foreach ($nodes as $index => $node) {
        $nodeIndex[$node['id']] = $index;
    }
    foreach ($links as $link) {
        $source = $link['source'];
        $target = $link['target'];
        $weight = $link['weight'];
        $nodes[$nodeIndex[$source]]['degree']++;
        $nodes[$nodeIndex[$target]]['degree']++;
        $nodes[$nodeIndex[$source]]['weighted_degree'] += $weight;
        $nodes[$nodeIndex[$target]]['weighted_degree'] += $weight;
    }

    return ['nodes' => $nodes, 'links' => $links];
}

try {
    $categories = getCategoryRows($mysqli);

    if (isset($_GET['ajax']) && $_GET['ajax'] === 'category_graph') {
        $categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
        $requestedFocusId = filter_input(INPUT_GET, 'focus_id', FILTER_VALIDATE_INT);
        $requestedLimit = filter_input(INPUT_GET, 'node_limit', FILTER_VALIDATE_INT);
        $requestedWeight = filter_input(INPUT_GET, 'min_weight', FILTER_VALIDATE_INT);
        $requestedDepth = filter_input(INPUT_GET, 'depth', FILTER_VALIDATE_INT);
        $requestedPerNode = filter_input(INPUT_GET, 'per_node', FILTER_VALIDATE_INT);
        $requestedMaxEdges = filter_input(INPUT_GET, 'max_edges', FILTER_VALIDATE_INT);
        $nodeLimit = in_array($requestedLimit, [40, 100, 200, 500], true) ? $requestedLimit : 100;
        $minWeight = ($requestedWeight !== false && $requestedWeight >= 1 && $requestedWeight <= 100)
            ? $requestedWeight
            : 2;
        $depth = in_array($requestedDepth, [1, 2, 3], true) ? $requestedDepth : 2;
        $perNode = in_array($requestedPerNode, [5, 10, 20], true) ? $requestedPerNode : 10;
        $maxEdges = in_array($requestedMaxEdges, [500, 2000, 5000], true) ? $requestedMaxEdges : 2000;

        if (!$categoryId || $categoryId < 1) {
            jsonResponse(['ok' => false, 'message' => '分类 ID 无效。'], 400);
        }
        $category = getSelectedCategory($categories, (int)$categoryId);
        if ($category === null) {
            jsonResponse(['ok' => false, 'message' => '未找到该分类。'], 404);
        }
        $focusCategory = $requestedFocusId ? getSelectedCategory($categories, (int)$requestedFocusId) : $category;
        $focusId = $focusCategory === null ? (int)$categoryId : (int)$focusCategory['id'];

        $graph = getCategoryGraph($mysqli, $category, $nodeLimit, $minWeight, $depth, $perNode, $maxEdges, $focusId);
        jsonResponse([
            'ok' => true,
            'category' => $category,
            'node_limit' => $nodeLimit,
            'min_weight' => $minWeight,
            'depth' => $depth,
            'per_node' => $perNode,
            'max_edges' => $maxEdges,
            'focus_id' => $focusId,
            'nodes' => $graph['nodes'],
            'links' => $graph['links']
        ]);
    }

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
$allowedViews = ['overview', 'frequency_all', 'frequency_local', 'frequency_cloud', 'overlap', 'network'];
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
    <script src="https://cdn.jsdelivr.net/npm/force-graph@1.50.1/dist/force-graph.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/3d-force-graph@1.80.0/dist/3d-force-graph.min.js"></script>
    <script>
        window.forceCollide3DReady = import('https://cdn.jsdelivr.net/npm/d3-force-3d@3.0.6/+esm')
            .then((module) => { window.forceCollide3D = module.forceCollide; })
            .catch(() => null);
    </script>
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
        .network-toolbar { align-items: flex-end; }
        .network-settings { display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap; }
        .network-setting { display: grid; gap: 5px; color: var(--muted); font-size: 11px; font-weight: 700; }
        .network-select { min-width: 112px; border: 1px solid var(--line); border-radius: 8px; padding: 7px 28px 7px 9px; color: #46536a; background: #fff; outline: none; }
        .network-mode-buttons { display: flex; gap: 6px; }
        .network-mode-button { border: 1px solid var(--line); border-radius: 8px; padding: 8px 12px; color: #5e6a80; background: #fff; cursor: pointer; font-size: 12px; font-weight: 750; }
        .network-mode-button.active { color: #fff; border-color: var(--primary); background: var(--primary); }
        .network-layout { display: grid; grid-template-columns: minmax(0, 1fr) 260px; gap: 14px; }
        .network-stage { position: relative; height: 640px; overflow: hidden; border: 1px solid var(--line); border-radius: 14px; background: #f8fbff; }
        .network-stage.mode-3d { background: #07101d; }
        .network-stage:fullscreen { width: 100vw; height: 100vh; border: 0; border-radius: 0; }
        .network-stage canvas { display: block; }
        .network-dom-label-layer { position: absolute; inset: 0; z-index: 4; overflow: hidden; pointer-events: none; }
        .network-dom-label { position: absolute; max-width: 170px; overflow: hidden; border-radius: 5px; padding: 3px 6px; color: #e8f1ff; background: rgba(8, 20, 36, 0.76); font-size: 11px; font-weight: 750; line-height: 1.2; text-overflow: ellipsis; white-space: nowrap; transform: translate(-50%, -50%); }
        .network-dom-label.selected { color: #ffd29a; background: rgba(111, 45, 7, 0.84); }
        .network-actions { position: absolute; top: 12px; right: 12px; z-index: 6; display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; max-width: calc(100% - 24px); }
        .network-action { border: 1px solid rgba(207, 216, 232, 0.9); border-radius: 8px; padding: 7px 9px; color: #44526a; background: rgba(255, 255, 255, 0.9); box-shadow: 0 5px 16px rgba(30, 45, 75, 0.08); cursor: pointer; font-size: 11px; font-weight: 750; backdrop-filter: blur(8px); }
        .mode-3d .network-action { color: #dce7f8; border-color: rgba(148, 163, 184, 0.28); background: rgba(15, 28, 47, 0.82); }
        .network-status { min-height: 18px; margin: 9px 0 0; color: var(--muted); font-size: 11px; }
        .network-loading { position: absolute; inset: 0; z-index: 2; display: grid; place-items: center; color: var(--muted); background: rgba(248, 251, 255, 0.9); }
        .network-details { border: 1px solid var(--line); border-radius: 14px; padding: 17px; background: #fbfcff; }
        .network-details h3 { margin: 0 0 14px; font-size: 15px; }
        .node-detail-name { margin: 0 0 5px; color: var(--text); font-size: 17px; font-weight: 850; overflow-wrap: anywhere; }
        .node-detail-kind { margin: 0 0 16px; color: var(--muted); font-size: 12px; overflow-wrap: anywhere; }
        .node-detail-list { display: grid; gap: 10px; margin: 0; }
        .node-detail-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 9px; border-bottom: 1px solid var(--line); font-size: 12px; }
        .node-detail-row:last-child { border-bottom: 0; }
        .node-detail-row dt { color: var(--muted); }
        .node-detail-row dd { margin: 0; color: var(--text); font-weight: 800; text-align: right; }
        .network-help { margin: 16px 0 0; color: var(--muted); font-size: 11px; line-height: 1.65; }
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
            .network-layout { grid-template-columns: 1fr; }
            .network-stage { height: 540px; }
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
            .network-settings { align-items: stretch; width: 100%; }
            .network-setting { flex: 1; }
            .network-select { width: 100%; }
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
            <button class="nav-button<?php echo $initialView === 'network' ? ' active' : ''; ?>" type="button" data-view="network">关系图谱</button>
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
        let networkRequestController = null;
        let networkNodeLimit = 100;
        let networkMinWeight = 2;
        let networkDepth = 2;
        let networkPerNode = 10;
        let networkMaxEdges = 2000;
        let networkMode = '2d';
        let networkShowAllLabels = false;
        let network3DLabelMode = 'core';
        let currentNetworkData = null;
        let currentNetworkGraph = null;
        let networkResizeObserver = null;
        let networkLabelFrame = null;
        let networkLabelRefresh = null;
        let networkRenderVersion = 0;
        let lastRenderedNetworkMode = null;
        let lastNetworkNodeClick = { id: null, time: 0 };
        const cache = new Map();
        const networkCache = new Map();
        const charts = [];

        const viewNames = {
            overview: '数据概览',
            frequency_all: '全部频率',
            frequency_local: '本地频率',
            frequency_cloud: '云端频率',
            overlap: '分类关联',
            network: '关系图谱'
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
        const forceGraph2DAvailable = typeof ForceGraph !== 'undefined';
        const forceGraph3DAvailable = typeof ForceGraph3D !== 'undefined';
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

        function destroyNetworkGraph(invalidateRender = true) {
            if (invalidateRender) networkRenderVersion++;
            if (networkRequestController) {
                networkRequestController.abort();
                networkRequestController = null;
            }
            if (networkResizeObserver) {
                networkResizeObserver.disconnect();
                networkResizeObserver = null;
            }
            if (networkLabelFrame) {
                cancelAnimationFrame(networkLabelFrame);
                networkLabelFrame = null;
            }
            networkLabelRefresh = null;
            if (currentNetworkGraph && typeof currentNetworkGraph._destructor === 'function') {
                currentNetworkGraph._destructor();
            }
            currentNetworkGraph = null;
        }

        function destroyCharts() {
            destroyNetworkGraph();
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

        function networkToolbarHtml() {
            return `
                <div class="toolbar network-toolbar">
                    <div class="network-settings">
                        <label class="network-setting">节点上限
                            <select id="networkNodeLimit" class="network-select">
                                ${[40, 100, 200, 500].map((value) => `<option value="${value}"${networkNodeLimit === value ? ' selected' : ''}>${value}${value === 500 ? '（实验）' : ''}</option>`).join('')}
                            </select>
                        </label>
                        <label class="network-setting">探索深度
                            <select id="networkDepth" class="network-select">
                                ${[1, 2, 3].map((value) => `<option value="${value}"${networkDepth === value ? ' selected' : ''}>${value} 阶关系</option>`).join('')}
                            </select>
                        </label>
                        <label class="network-setting">每节点扩展
                            <select id="networkPerNode" class="network-select">
                                ${[5, 10, 20].map((value) => `<option value="${value}"${networkPerNode === value ? ' selected' : ''}>${value} 个邻居</option>`).join('')}
                            </select>
                        </label>
                        <label class="network-setting">最少共享图片
                            <select id="networkMinWeight" class="network-select">
                                ${[1, 2, 3, 5, 10].map((value) => `<option value="${value}"${networkMinWeight === value ? ' selected' : ''}>≥ ${value} 张</option>`).join('')}
                            </select>
                        </label>
                        <label class="network-setting">最大边数
                            <select id="networkMaxEdges" class="network-select">
                                ${[500, 2000, 5000].map((value) => `<option value="${value}"${networkMaxEdges === value ? ' selected' : ''}>${formatNumber(value)} 条</option>`).join('')}
                            </select>
                        </label>
                    </div>
                    <div class="network-mode-buttons" aria-label="图谱显示模式">
                        <button type="button" class="network-mode-button${networkMode === '2d' ? ' active' : ''}" data-network-mode="2d">二维网络</button>
                        <button type="button" class="network-mode-button${networkMode === '3d' ? ' active' : ''}" data-network-mode="3d">三维空间</button>
                    </div>
                </div>`;
        }

        function renderNetworkView() {
            const stats = currentStats;
            currentNetworkData = null;
            document.getElementById('resultArea').innerHTML = `
                <div class="content-card">
                    ${headerHtml(stats, '分类之间的二维网络与三维空间关系')}
                    <div class="metric-grid">
                        ${metricHtml('显示节点', '—', `${networkDepth} 阶 · 最多 ${networkNodeLimit} 个`)}
                        ${metricHtml('关联连线', '—', `最多 ${formatNumber(networkMaxEdges)} 条`)}
                        ${metricHtml('最大边权重', '—', '两分类共享图片数')}
                        ${metricHtml('当前分类图片', formatNumber(stats.totals.all), '节点大小按图片数计算')}
                    </div>
                    ${networkToolbarHtml()}
                    <div class="network-layout">
                        <div id="networkStage" class="network-stage${networkMode === '3d' ? ' mode-3d' : ''}">
                            <div class="network-loading"><div><div class="spinner"></div><div>正在构建关系图谱…</div></div></div>
                        </div>
                        <aside class="network-details">
                            <h3>节点详情</h3>
                            <div id="nodeDetailsBody"><p class="chart-subtitle">点击任意节点查看具体信息。</p></div>
                            <p class="network-help">节点越大，分类图片越多；连线越粗、颜色越深，共享图片越多。单击节点聚焦，双击节点会在当前网络上继续展开一层。</p>
                            <p id="networkStatus" class="network-status">双击任意节点可继续探索局部关系。</p>
                        </aside>
                    </div>
                </div>`;
            loadNetworkData();
        }

        async function loadNetworkData() {
            const categoryId = selectedCategoryId;
            const cacheKey = `${categoryId}:${networkNodeLimit}:${networkMinWeight}:${networkDepth}:${networkPerNode}:${networkMaxEdges}`;
            if (networkCache.has(cacheKey)) {
                currentNetworkData = networkCache.get(cacheKey);
                updateNetworkSummary(currentNetworkData);
                renderNetworkGraph(currentNetworkData);
                return;
            }

            networkRequestController = new AbortController();
            try {
                const url = new URL(window.location.href);
                url.search = '';
                url.searchParams.set('ajax', 'category_graph');
                url.searchParams.set('category_id', categoryId);
                url.searchParams.set('node_limit', networkNodeLimit);
                url.searchParams.set('min_weight', networkMinWeight);
                url.searchParams.set('depth', networkDepth);
                url.searchParams.set('per_node', networkPerNode);
                url.searchParams.set('max_edges', networkMaxEdges);
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    signal: networkRequestController.signal
                });
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('登录状态可能已失效，请刷新页面后重新登录。');
                }
                const data = await response.json();
                if (!response.ok || !data.ok) {
                    throw new Error(data.message || '关系图谱加载失败。');
                }
                if (activeView !== 'network' || selectedCategoryId !== categoryId) return;
                networkRequestController = null;
                networkCache.set(cacheKey, data);
                currentNetworkData = data;
                updateNetworkSummary(data);
                renderNetworkGraph(data);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    showNetworkError(error.message || '关系图谱加载失败。');
                }
            }
        }

        function showNetworkError(message) {
            const stage = document.getElementById('networkStage');
            if (stage) stage.innerHTML = `<div class="no-data">${escapeHtml(message)}</div>`;
        }

        function updateNetworkSummary(data) {
            const values = document.querySelectorAll('.metric-grid .metric-value');
            if (values.length < 4) return;
            values[0].textContent = formatNumber(data.nodes.length);
            values[1].textContent = formatNumber(data.links.length);
            values[2].textContent = formatNumber(Math.max(0, ...data.links.map((link) => link.weight)));
        }

        function prepareNetworkData(data) {
            const graphData = JSON.parse(JSON.stringify({ nodes: data.nodes, links: data.links }));
            const selectedId = data.category.id;
            const labels = new Map();
            const adjacency = new Map();
            graphData.nodes.forEach((node) => {
                labels.set(node.id, node.id);
                adjacency.set(node.id, []);
            });
            graphData.links.forEach((link) => {
                if (link.source === selectedId || link.target === selectedId) return;
                adjacency.get(link.source)?.push({ id: link.target, weight: link.weight });
                adjacency.get(link.target)?.push({ id: link.source, weight: link.weight });
            });
            const candidates = graphData.nodes.filter((node) => !node.selected).sort((a, b) => b.weighted_degree - a.weighted_degree);
            for (let iteration = 0; iteration < 8; iteration++) {
                candidates.forEach((node) => {
                    const scores = new Map();
                    adjacency.get(node.id).forEach((neighbor) => {
                        const label = labels.get(neighbor.id);
                        scores.set(label, (scores.get(label) || 0) + neighbor.weight);
                    });
                    let bestLabel = labels.get(node.id);
                    let bestScore = -1;
                    scores.forEach((score, label) => {
                        if (score > bestScore || (score === bestScore && label < bestLabel)) {
                            bestLabel = label;
                            bestScore = score;
                        }
                    });
                    labels.set(node.id, bestLabel);
                });
            }
            const communityNumbers = new Map();
            graphData.nodes.forEach((node) => {
                if (node.selected) {
                    node.community = -1;
                    return;
                }
                const label = labels.get(node.id);
                if (!communityNumbers.has(label)) communityNumbers.set(label, communityNumbers.size);
                node.community = communityNumbers.get(label);
            });
            return graphData;
        }

        function networkNodeColor(node) {
            if (node.selected) return '#f97316';
            const palette = ['#4f6ef7', '#20b7c9', '#8b6fe8', '#37b979', '#e65fa1', '#e0a12b', '#508fc9'];
            return palette[node.community % palette.length];
        }

        function networkNodeValue(node) {
            return Math.max(3, Math.log10(Number(node.image_count) + 10) * (node.selected ? 6 : 4.5));
        }

        function nodeTooltipHtml(node) {
            const shared = node.selected ? '当前选定分类' : `与当前分类共享 ${formatNumber(node.shared_with_selected)} 张`;
            return `<strong>${escapeHtml(node.category_name)}</strong><br>图片 ${formatNumber(node.image_count)} 张<br>${shared}<br>连接 ${formatNumber(node.degree)} 个分类`;
        }

        function linkTooltipHtml(link) {
            const sourceName = typeof link.source === 'object' ? link.source.category_name : link.source;
            const targetName = typeof link.target === 'object' ? link.target.category_name : link.target;
            return `${escapeHtml(sourceName)} ↔ ${escapeHtml(targetName)}<br>共享 ${formatNumber(link.weight)} 张图片`;
        }

        function updateNodeDetails(node) {
            const target = document.getElementById('nodeDetailsBody');
            if (!target || !node) return;
            target.innerHTML = `
                <p class="node-detail-name">${escapeHtml(node.category_name)}</p>
                <p class="node-detail-kind">${node.kindID ? `kindID：${escapeHtml(node.kindID)}` : 'kindID：未设置'} · ID ${node.id}</p>
                <dl class="node-detail-list">
                    <div class="node-detail-row"><dt>分类图片</dt><dd>${formatNumber(node.image_count)} 张</dd></div>
                    <div class="node-detail-row"><dt>与当前分类共享</dt><dd>${node.selected ? '当前分类' : `${formatNumber(node.shared_with_selected)} 张`}</dd></div>
                    <div class="node-detail-row"><dt>探索层级</dt><dd>${formatNumber(node.level || 0)} 阶</dd></div>
                    <div class="node-detail-row"><dt>直接连接</dt><dd>${formatNumber(node.degree)} 个</dd></div>
                    <div class="node-detail-row"><dt>累计边权重</dt><dd>${formatNumber(node.weighted_degree)}</dd></div>
                </dl>`;
        }

        function setNetworkStatus(message) {
            const target = document.getElementById('networkStatus');
            if (target) target.textContent = message;
        }

        function focusNetworkNode(node) {
            if (!currentNetworkGraph || !node) return;
            if (networkMode === '3d') {
                const distance = Math.hypot(node.x || 0, node.y || 0, node.z || 0);
                const ratio = distance > 1 ? 1 + 90 / distance : 1;
                const position = distance > 1
                    ? { x: node.x * ratio, y: node.y * ratio, z: node.z * ratio }
                    : { x: 0, y: 0, z: 90 };
                currentNetworkGraph.cameraPosition(position, node, 900);
            } else {
                currentNetworkGraph.centerAt(node.x, node.y, 500);
                currentNetworkGraph.zoom(4, 500);
            }
        }

        function handleNetworkNodeClick(node) {
            updateNodeDetails(node);
            focusNetworkNode(node);
            const now = Date.now();
            if (lastNetworkNodeClick.id === node.id && now - lastNetworkNodeClick.time < 360) {
                lastNetworkNodeClick = { id: null, time: 0 };
                expandNetworkNode(node);
            } else {
                lastNetworkNodeClick = { id: node.id, time: now };
            }
        }

        function recomputeNetworkDegrees(data) {
            const nodeMap = new Map(data.nodes.map((node) => {
                node.degree = 0;
                node.weighted_degree = 0;
                return [node.id, node];
            }));
            data.links.forEach((link) => {
                const sourceId = typeof link.source === 'object' ? link.source.id : link.source;
                const targetId = typeof link.target === 'object' ? link.target.id : link.target;
                const source = nodeMap.get(sourceId);
                const target = nodeMap.get(targetId);
                if (!source || !target) return;
                source.degree++;
                target.degree++;
                source.weighted_degree += link.weight;
                target.weighted_degree += link.weight;
                link.source = sourceId;
                link.target = targetId;
            });
        }

        function mergeExpandedNetwork(incoming, expandedNode) {
            const merged = JSON.parse(JSON.stringify({
                ...currentNetworkData,
                nodes: currentNetworkData.nodes,
                links: currentNetworkData.links
            }));
            const nodeMap = new Map(merged.nodes.map((node) => [node.id, node]));
            incoming.nodes.forEach((node) => {
                if (nodeMap.has(node.id) || merged.nodes.length >= networkNodeLimit) return;
                node.selected = node.id === selectedCategoryId;
                node.focus = false;
                node.level = Math.min(3, Number(expandedNode.level || 0) + 1);
                merged.nodes.push(node);
                nodeMap.set(node.id, node);
            });

            const linkMap = new Map();
            merged.links.forEach((link) => {
                const source = typeof link.source === 'object' ? link.source.id : link.source;
                const target = typeof link.target === 'object' ? link.target.id : link.target;
                link.source = Math.min(source, target);
                link.target = Math.max(source, target);
                linkMap.set(`${link.source}:${link.target}`, link);
            });
            incoming.links.forEach((link) => {
                const source = typeof link.source === 'object' ? link.source.id : link.source;
                const target = typeof link.target === 'object' ? link.target.id : link.target;
                if (!nodeMap.has(source) || !nodeMap.has(target)) return;
                const low = Math.min(source, target);
                const high = Math.max(source, target);
                const key = `${low}:${high}`;
                const existing = linkMap.get(key);
                if (!existing || link.weight > existing.weight) {
                    linkMap.set(key, { source: low, target: high, weight: link.weight, backbone: Boolean(link.backbone) });
                }
            });
            merged.links = [...linkMap.values()]
                .sort((a, b) => Number(b.backbone) - Number(a.backbone) || b.weight - a.weight)
                .slice(0, networkMaxEdges);
            recomputeNetworkDegrees(merged);
            return merged;
        }

        async function expandNetworkNode(node) {
            if (currentNetworkData.nodes.length >= networkNodeLimit) {
                setNetworkStatus(`已达到 ${networkNodeLimit} 个节点上限，请提高节点上限后再展开。`);
                return;
            }
            if (networkRequestController) return;
            setNetworkStatus(`正在展开“${node.category_name}”的一阶邻居…`);
            networkRequestController = new AbortController();
            try {
                const url = new URL(window.location.href);
                url.search = '';
                url.searchParams.set('ajax', 'category_graph');
                url.searchParams.set('category_id', selectedCategoryId);
                url.searchParams.set('focus_id', node.id);
                url.searchParams.set('node_limit', 40);
                url.searchParams.set('min_weight', networkMinWeight);
                url.searchParams.set('depth', 1);
                url.searchParams.set('per_node', networkPerNode);
                url.searchParams.set('max_edges', 500);
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    signal: networkRequestController.signal
                });
                const data = await response.json();
                if (!response.ok || !data.ok) throw new Error(data.message || '节点展开失败。');
                networkRequestController = null;
                if (activeView !== 'network') return;
                const previousCount = currentNetworkData.nodes.length;
                currentNetworkData = mergeExpandedNetwork(data, node);
                updateNetworkSummary(currentNetworkData);
                renderNetworkGraph(currentNetworkData);
                setNetworkStatus(`已新增 ${currentNetworkData.nodes.length - previousCount} 个节点；双击其他节点可继续展开。`);
            } catch (error) {
                if (error.name !== 'AbortError') setNetworkStatus(error.message || '节点展开失败。');
                networkRequestController = null;
            }
        }

        function shouldShow3DLabel(node) {
            if (network3DLabelMode === 'all') return true;
            if (network3DLabelMode === 'hidden') return false;
            return node.selected || node.labelRank < 8;
        }

        function networkLabelActionText() {
            if (networkMode !== '3d') return networkShowAllLabels ? '精简名称' : '显示全部名称';
            const labels = { core: '名称：核心', all: '名称：全部', hidden: '名称：隐藏' };
            return labels[network3DLabelMode];
        }

        function applyStable3DPositions(graphData, previousPositions) {
            const nodes = [...graphData.nodes].sort((a, b) => Number(a.id) - Number(b.id));
            const nodeMap = new Map(nodes.map((node) => [node.id, node]));
            const goldenAngle = Math.PI * (3 - Math.sqrt(5));
            const baseRadius = Math.max(170, Math.cbrt(Math.max(1, nodes.length)) * 82);

            nodes.forEach((node, index) => {
                const previous = previousPositions.get(node.id);
                if (previous && Number.isFinite(previous.x) && Number.isFinite(previous.y) && Number.isFinite(previous.z)) {
                    node.x = previous.x;
                    node.y = previous.y;
                    node.z = previous.z;
                } else if (node.selected) {
                    node.x = 0;
                    node.y = 0;
                    node.z = 0;
                } else {
                    const offsetIndex = index + 1;
                    const y = 1 - 2 * ((offsetIndex - 0.5) / Math.max(1, nodes.length));
                    const horizontalRadius = Math.sqrt(Math.max(0, 1 - y * y));
                    const angle = goldenAngle * offsetIndex + (Number(node.id) % 101) * 0.013;
                    const levelRadius = baseRadius + Math.min(3, Number(node.level || 0)) * 35;
                    node.x = Math.cos(angle) * horizontalRadius * levelRadius;
                    node.y = y * levelRadius;
                    node.z = Math.sin(angle) * horizontalRadius * levelRadius;
                }
                node.vx = 0;
                node.vy = 0;
                node.vz = 0;
            });

            if (previousPositions.size > 0) {
                graphData.links.forEach((link) => {
                    const sourceId = typeof link.source === 'object' ? link.source.id : link.source;
                    const targetId = typeof link.target === 'object' ? link.target.id : link.target;
                    const source = nodeMap.get(sourceId);
                    const target = nodeMap.get(targetId);
                    const newNode = previousPositions.has(sourceId) ? target : source;
                    const anchor = newNode === source ? target : source;
                    if (!newNode || !anchor || previousPositions.has(newNode.id) || !previousPositions.has(anchor.id)) return;
                    const angle = goldenAngle * (Number(newNode.id) + 1);
                    newNode.x = anchor.x + Math.cos(angle) * 120;
                    newNode.y = anchor.y + Math.sin(angle * 0.7) * 90;
                    newNode.z = anchor.z + Math.sin(angle) * 120;
                });
            }
        }

        function startNetwork3DLabels(stage, graphData, graph) {
            const layer = document.createElement('div');
            layer.className = 'network-dom-label-layer';
            const labels = new Map();
            graphData.nodes.forEach((node) => {
                const label = document.createElement('span');
                label.className = `network-dom-label${node.selected ? ' selected' : ''}`;
                label.textContent = node.category_name;
                label.title = node.category_name;
                layer.appendChild(label);
                labels.set(node.id, label);
            });
            stage.appendChild(layer);

            const renderLabels = () => {
                if (currentNetworkGraph !== graph || networkMode !== '3d') return;
                graphData.nodes.forEach((node) => {
                    const label = labels.get(node.id);
                    const shouldShow = shouldShow3DLabel(node);
                    if (!shouldShow || !Number.isFinite(node.x) || !Number.isFinite(node.y) || !Number.isFinite(node.z)) {
                        label.style.display = 'none';
                        return;
                    }
                    const position = graph.graph2ScreenCoords(node.x, node.y, node.z);
                    const inView = position.x >= -80 && position.x <= stage.clientWidth + 80
                        && position.y >= -30 && position.y <= stage.clientHeight + 30;
                    label.style.display = inView ? 'block' : 'none';
                    if (inView) {
                        label.style.left = `${position.x}px`;
                        label.style.top = `${position.y - 12}px`;
                    }
                });
            };
            const update = () => {
                if (currentNetworkGraph !== graph || networkMode !== '3d') return;
                renderLabels();
                networkLabelFrame = requestAnimationFrame(update);
            };
            networkLabelRefresh = renderLabels;
            update();
        }

        async function renderNetworkGraph(data) {
            const requestedMode = networkMode;
            const renderVersion = ++networkRenderVersion;
            const previousPositions = new Map();
            if (currentNetworkGraph && lastRenderedNetworkMode === requestedMode && typeof currentNetworkGraph.graphData === 'function') {
                currentNetworkGraph.graphData().nodes.forEach((node) => {
                    previousPositions.set(node.id, { x: node.x, y: node.y, z: node.z });
                });
            }
            if (requestedMode === '3d' && window.forceCollide3DReady) {
                await window.forceCollide3DReady;
            }
            if (renderVersion !== networkRenderVersion
                || networkMode !== requestedMode
                || activeView !== 'network'
                || selectedCategoryId !== Number(data.category.id)) return;
            destroyNetworkGraph(false);
            const stage = document.getElementById('networkStage');
            if (!stage) return;
            stage.replaceChildren();
            stage.classList.toggle('mode-3d', networkMode === '3d');
            const requiredLibraryAvailable = networkMode === '3d' ? forceGraph3DAvailable : forceGraph2DAvailable;
            if (!requiredLibraryAvailable) {
                const libraryName = networkMode === '3d' ? '3D Force Graph' : 'Force Graph';
                stage.innerHTML = `<div class="no-data">${libraryName} 未能加载。请检查浏览器是否拦截 cdn.jsdelivr.net，然后刷新页面。</div>`;
                return;
            }

            const graphData = prepareNetworkData(data);
            [...graphData.nodes]
                .sort((a, b) => b.weighted_degree - a.weighted_degree)
                .forEach((node, index) => { node.labelRank = index; });
            if (networkMode === '3d') {
                applyStable3DPositions(graphData, previousPositions);
            } else {
                graphData.nodes.forEach((node) => {
                    const position = previousPositions.get(node.id);
                    if (!position) return;
                    if (Number.isFinite(position.x)) node.x = position.x;
                    if (Number.isFinite(position.y)) node.y = position.y;
                });
            }
            const maximumWeight = Math.max(1, ...graphData.links.map((link) => link.weight));
            const logarithmicWeights = graphData.links.map((link) => Math.log1p(link.weight)).sort((a, b) => a - b);
            const lowerWeight = logarithmicWeights[Math.floor((logarithmicWeights.length - 1) * 0.1)] || 0;
            const upperWeight = logarithmicWeights[Math.floor((logarithmicWeights.length - 1) * 0.9)] || lowerWeight + 1;
            const normalizedWeight = (link) => Math.max(0, Math.min(1,
                (Math.log1p(link.weight) - lowerWeight) / Math.max(0.001, upperWeight - lowerWeight)
            ));
            const linkWidth = (link) => 0.6 + Math.sqrt(link.weight / maximumWeight) * (networkMode === '3d' ? 2.4 : 4.2);
            const linkColor = (link) => `rgba(79, 110, 247, ${0.18 + Math.sqrt(link.weight / maximumWeight) * 0.62})`;
            let fitted = false;

            if (networkMode === '3d') {
                const graph3D = new ForceGraph3D(stage, { controlType: 'trackball' })
                    .width(stage.clientWidth)
                    .height(stage.clientHeight)
                    .backgroundColor('#07101d')
                    .showNavInfo(false)
                    .nodeVal(networkNodeValue)
                    .nodeColor(networkNodeColor)
                    .nodeOpacity(0.92)
                    .nodeResolution(18)
                    .nodeLabel(nodeTooltipHtml)
                    .linkWidth(linkWidth)
                    .linkColor(linkColor)
                    .linkOpacity(0.72)
                    .linkLabel(linkTooltipHtml)
                    .d3AlphaDecay(0.018)
                    .d3VelocityDecay(0.35)
                    .cooldownTicks(280)
                    .onNodeClick(handleNetworkNodeClick)
                    .onEngineStop(() => {
                        if (currentNetworkGraph === graph3D && !fitted) {
                            fitted = true;
                            graph3D.zoomToFit(900, 160);
                        }
                    });
                currentNetworkGraph = graph3D;

                // Keep labels independent of the WebGL meshes and an external THREE global.
                startNetwork3DLabels(stage, graphData, graph3D);

                const linkForce = graph3D.d3Force('link');
                if (linkForce) {
                    linkForce
                        .distance((link) => 190 - normalizedWeight(link) * 90)
                        .strength((link) => 0.08 + normalizedWeight(link) * 0.22);
                }
                const chargeForce = graph3D.d3Force('charge');
                if (chargeForce && typeof chargeForce.strength === 'function') {
                    const nodeCount = graphData.nodes.length;
                    const chargeStrength = nodeCount <= 20 ? -520 : (nodeCount <= 100 ? -380 : (nodeCount <= 200 ? -280 : -210));
                    chargeForce.strength(chargeStrength);
                }
                if (typeof window.forceCollide3D === 'function') {
                    const collisionForce = window.forceCollide3D((node) => {
                        const size = Math.min(9, Math.log10(Number(node.image_count) + 10) * 2.2);
                        return (node.selected ? 19 : 14) + size;
                    }).strength(0.9).iterations(2);
                    graph3D.d3Force('collision', collisionForce);
                }
                graph3D.graphData(graphData);
                // graphData initializes the layout asynchronously and starts the simulation.
                // Reheating here can run an animation frame before the layout exists,
                // throwing in tickFrame and permanently stopping the WebGL render loop.
            } else {
                currentNetworkGraph = new ForceGraph(stage)
                    .width(stage.clientWidth)
                    .height(stage.clientHeight)
                    .backgroundColor('#f8fbff')
                    .graphData(graphData)
                    .nodeVal(networkNodeValue)
                    .nodeColor(networkNodeColor)
                    .nodeLabel(nodeTooltipHtml)
                    .nodeCanvasObjectMode(() => 'after')
                    .nodeCanvasObject((node, context, globalScale) => {
                        if (!networkShowAllLabels && !node.selected && node.labelRank >= 20 && globalScale < 1.45) return;
                        const fontSize = 11 / globalScale;
                        context.font = `700 ${fontSize}px "Microsoft YaHei", Arial`;
                        context.textAlign = 'center';
                        context.textBaseline = 'middle';
                        const label = node.category_name;
                        const width = context.measureText(label).width + 6 / globalScale;
                        const height = fontSize + 4 / globalScale;
                        const y = node.y + 10 / globalScale;
                        context.fillStyle = 'rgba(255,255,255,0.88)';
                        context.fillRect(node.x - width / 2, y - height / 2, width, height);
                        context.fillStyle = node.selected ? '#b84b0a' : '#263650';
                        context.fillText(label, node.x, y);
                    })
                    .linkWidth(linkWidth)
                    .linkColor(linkColor)
                    .linkLabel(linkTooltipHtml)
                    .cooldownTicks(150)
                    .onNodeClick(handleNetworkNodeClick)
                    .onEngineStop(() => {
                        if (!fitted) {
                            fitted = true;
                            currentNetworkGraph.zoomToFit(700, 60);
                        }
                    });
            }

            if (networkMode !== '3d') {
                const linkForce = currentNetworkGraph.d3Force('link');
                if (linkForce) {
                    linkForce
                        .distance((link) => Math.max(34, 125 - Math.log1p(link.weight) * 20))
                        .strength((link) => Math.min(0.95, 0.18 + Math.log1p(link.weight) * 0.14));
                }
                const chargeForce = currentNetworkGraph.d3Force('charge');
                if (chargeForce && typeof chargeForce.strength === 'function') {
                    chargeForce.strength(-145);
                }
                currentNetworkGraph.d3ReheatSimulation();
            }

            const actionBar = document.createElement('div');
            actionBar.className = 'network-actions';
            actionBar.innerHTML = `
                <button type="button" class="network-action" data-network-action="fit">适应全图</button>
                <button type="button" class="network-action" data-network-action="center">聚焦中心</button>
                <button type="button" class="network-action" data-network-action="reset">重置视角</button>
                <button type="button" class="network-action" data-network-action="labels">${networkLabelActionText()}</button>
                <button type="button" class="network-action" data-network-action="fullscreen">全屏</button>`;
            stage.appendChild(actionBar);

            const selectedNode = graphData.nodes.find((node) => node.selected) || graphData.nodes[0];
            updateNodeDetails(selectedNode);
            if (typeof ResizeObserver !== 'undefined') {
                networkResizeObserver = new ResizeObserver(() => {
                    if (currentNetworkGraph && stage.clientWidth > 0 && stage.clientHeight > 0) {
                        currentNetworkGraph.width(stage.clientWidth).height(stage.clientHeight);
                    }
                });
                networkResizeObserver.observe(stage);
            }
            lastRenderedNetworkMode = networkMode;
        }

        function renderActiveView() {
            if (!currentStats) return;
            if (activeView !== 'network' && !chartLibraryAvailable) {
                showError('图表组件加载失败，请检查网络连接后刷新页面。');
                return;
            }
            destroyCharts();
            if (activeView === 'overview') renderOverview();
            else if (activeView === 'frequency_all') renderFrequency('all');
            else if (activeView === 'frequency_local') renderFrequency('local');
            else if (activeView === 'frequency_cloud') renderFrequency('cloud');
            else if (activeView === 'overlap') renderOverlap();
            else renderNetworkView();
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
            const stepButton = event.target.closest('[data-step]');
            if (stepButton) {
                frequencyStep = Number(stepButton.dataset.step);
                renderActiveView();
                return;
            }

            const actionButton = event.target.closest('[data-network-action]');
            if (actionButton && currentNetworkGraph) {
                const action = actionButton.dataset.networkAction;
                if (action === 'fit') {
                    currentNetworkGraph.zoomToFit(900, networkMode === '3d' ? 160 : 60);
                } else if (action === 'center') {
                    const centerNode = currentNetworkGraph.graphData().nodes.find((node) => node.selected);
                    focusNetworkNode(centerNode);
                } else if (action === 'reset') {
                    if (networkMode === '3d') {
                        currentNetworkGraph.cameraPosition({ x: 0, y: 0, z: 350 }, { x: 0, y: 0, z: 0 }, 800);
                    } else {
                        currentNetworkGraph.centerAt(0, 0, 500);
                        currentNetworkGraph.zoom(1, 500);
                    }
                } else if (action === 'labels') {
                    if (networkMode === '3d') {
                        const modes = ['core', 'all', 'hidden'];
                        network3DLabelMode = modes[(modes.indexOf(network3DLabelMode) + 1) % modes.length];
                        actionButton.textContent = networkLabelActionText();
                        if (networkLabelRefresh) networkLabelRefresh();
                    } else {
                        networkShowAllLabels = !networkShowAllLabels;
                        renderNetworkGraph(currentNetworkData);
                    }
                } else if (action === 'fullscreen') {
                    const stage = document.getElementById('networkStage');
                    if (document.fullscreenElement) document.exitFullscreen();
                    else if (stage?.requestFullscreen) stage.requestFullscreen();
                }
                return;
            }

            const modeButton = event.target.closest('[data-network-mode]');
            if (modeButton && currentNetworkData) {
                networkMode = modeButton.dataset.networkMode;
                document.querySelectorAll('.network-mode-button').forEach((button) => {
                    button.classList.toggle('active', button === modeButton);
                });
                renderNetworkGraph(currentNetworkData);
            }
        });

        document.getElementById('resultArea').addEventListener('change', (event) => {
            if (event.target.id === 'networkNodeLimit') {
                networkNodeLimit = Number(event.target.value);
            } else if (event.target.id === 'networkDepth') {
                networkDepth = Number(event.target.value);
            } else if (event.target.id === 'networkPerNode') {
                networkPerNode = Number(event.target.value);
            } else if (event.target.id === 'networkMinWeight') {
                networkMinWeight = Number(event.target.value);
            } else if (event.target.id === 'networkMaxEdges') {
                networkMaxEdges = Number(event.target.value);
            } else {
                return;
            }
            destroyNetworkGraph();
            currentNetworkData = null;
            renderNetworkView();
        });

        if (initialCategoryId) {
            selectCategory(initialCategoryId);
        }
    </script>
</body>
</html>
