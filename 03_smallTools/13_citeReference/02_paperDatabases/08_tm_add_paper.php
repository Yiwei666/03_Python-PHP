<?php
// 08_tm_add_paper.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // 允许跨域请求，生产环境请根据需求调整
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// 引入数据库配置和操作模块
require_once '08_db_config.php';
require_once '08_category_operations.php';

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => '无效的请求数据。']);
    exit();
}

$doi = isset($data['doi']) ? trim($data['doi']) : '';

if (empty($doi)) {
    echo json_encode(['success' => false, 'message' => 'DOI不能为空。']);
    exit();
}

// 检查DOI是否存在
$existingPaper = getPaperByDOI($mysqli, $doi);
if ($existingPaper) {
    echo json_encode(['success' => true, 'paperID' => $existingPaper['paperID']]);
    exit();
}

// 插入新的论文
$title = isset($data['title']) ? $data['title'] : null;
$authors = isset($data['authors']) ? $data['authors'] : null;
$journal_name = isset($data['journal_name']) ? $data['journal_name'] : null;
$publication_year = isset($data['publication_year']) ? intval($data['publication_year']) : null;
$volume = isset($data['volume']) ? $data['volume'] : null;
$issue = isset($data['issue']) ? $data['issue'] : null;
$pages = isset($data['pages']) ? $data['pages'] : null;
$article_number = isset($data['article_number']) ? $data['article_number'] : null;
$issn = isset($data['issn']) ? $data['issn'] : null;
$publisher = isset($data['publisher']) ? $data['publisher'] : null;

// 插入论文
$insertResult = insertPaper($mysqli, $title, $authors, $journal_name, $publication_year, $volume, $issue, $pages, $article_number, $doi, $issn, $publisher);

if ($insertResult['success']) {
    $paperID = $insertResult['paperID'];
    
    // 分配 "0 All papers" 分类
    $assignCategoryResult = assignAllPapersCategory($mysqli, $paperID);
    if ($assignCategoryResult['success']) {
        echo json_encode(['success' => true, 'paperID' => $paperID]);
    } else {
        // 如果分配分类失败，可以选择删除刚插入的论文，或者返回部分成功的信息
        echo json_encode(['success' => false, 'message' => '论文添加成功，但分配 "All papers" 分类失败: ' . $assignCategoryResult['message']]);
    }
} else {
    echo json_encode(['success' => false, 'message' => $insertResult['message']]);
}
?>
