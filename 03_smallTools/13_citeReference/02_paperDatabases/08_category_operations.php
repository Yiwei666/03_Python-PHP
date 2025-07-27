<?php
// 获取所有分类名称
function getCategories($mysqli) {
    $query = "SELECT * FROM categories ORDER BY category_name ASC";
    $result = $mysqli->query($query);

    if ($result) {
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        return $categories;
    } else {
        return "Error: " . $mysqli->error;
    }
}

// 新增分类
function addCategory($mysqli, $categoryName) {
    $query = "INSERT INTO categories (category_name) VALUES (?)";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param('s', $categoryName);
        if ($stmt->execute()) {
            return "分类 '$categoryName' 创建成功。";
        } else {
            return "错误: " . $stmt->error;
        }
    } else {
        return "错误: " . $mysqli->error;
    }
}

// 删除分类
function deleteCategory($mysqli, $categoryID) {
    $query = "DELETE FROM categories WHERE categoryID = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param('i', $categoryID);
        if ($stmt->execute()) {
            return "分类删除成功。";
        } else {
            return "错误: " . $stmt->error;
        }
    } else {
        return "错误: " . $mysqli->error;
    }
}

// 修改分类名称
function updateCategoryName($mysqli, $categoryID, $newCategoryName) {
    $query = "UPDATE categories SET category_name = ? WHERE categoryID = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param('si', $newCategoryName, $categoryID);
        if ($stmt->execute()) {
            return "分类更新成功。";
        } else {
            return "错误: " . $stmt->error;
        }
    } else {
        return "错误: " . $mysqli->error;
    }
}

/**
 * 获取特定分类下的论文，带可选的排序方式
 *
 * @param mysqli $mysqli
 * @param int    $categoryID
 * @param string $sort 用于决定 ORDER BY 的字段及顺序，默认 paperID_desc
 */
function getPapersByCategory($mysqli, $categoryID, $sort = 'paperID_desc') {
    // 根据 $sort 生成对应的 ORDER BY 子句
    switch ($sort) {
        case 'paperID_asc':
            $orderBy = 'ORDER BY p.paperID ASC';
            break;
        case 'paperID_desc':
            $orderBy = 'ORDER BY p.paperID DESC';
            break;
        case 'year_asc':
            $orderBy = 'ORDER BY p.publication_year ASC';
            break;
        case 'year_desc':
            $orderBy = 'ORDER BY p.publication_year DESC';
            break;
        case 'status_asc':
            $orderBy = 'ORDER BY p.status ASC';
            break;
        case 'status_desc':
            $orderBy = 'ORDER BY p.status DESC';
            break;
        case 'journal_asc':
            $orderBy = 'ORDER BY p.journal_name ASC';
            break;
        case 'journal_desc':
            $orderBy = 'ORDER BY p.journal_name DESC';
            break;
        case 'authors_asc':
            $orderBy = 'ORDER BY p.authors ASC';
            break;
        case 'authors_desc':
            $orderBy = 'ORDER BY p.authors DESC';
            break;
        case 'title_asc':
            $orderBy = 'ORDER BY p.title ASC';
            break;
        case 'title_desc':
            $orderBy = 'ORDER BY p.title DESC';
            break;
        case 'rating_asc': // [NEW CODE]
            $orderBy = 'ORDER BY p.rating ASC, p.paperID DESC';
            break;
        case 'rating_desc': // [NEW CODE]
            $orderBy = 'ORDER BY p.rating DESC, p.paperID DESC';
            break;
        default:
            // 默认使用 paperID 降序
            $orderBy = 'ORDER BY p.paperID DESC';
            break;
    }

    $query = "
        SELECT 
            p.paperID, p.title, p.authors, p.publication_year, 
            p.journal_name, p.doi, p.status
        FROM papers p
        JOIN paperCategories pc ON p.paperID = pc.paperID
        WHERE pc.categoryID = ?
        $orderBy
    ";

    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $categoryID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    } else {
        return null;
    }
}

// 获取论文通过 DOI
function getPaperByDOI($mysqli, $doi) {
    $query = "SELECT * FROM papers WHERE doi = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param('s', $doi);
        $stmt->execute();
        $result = $stmt->get_result();
        $paper = $result->fetch_assoc();
        $stmt->close();
        return $paper;
    } else {
        return false;
    }
}

// 插入新的论文
function insertPaper($mysqli, $title, $authors, $journal_name, $publication_year, $volume, $issue, $pages, $article_number, $doi, $issn, $publisher) {
    $query = "INSERT INTO papers 
        (title, authors, journal_name, publication_year, volume, issue, pages, article_number, doi, issn, publisher) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param('sssisssssss', 
            $title, $authors, $journal_name, $publication_year, 
            $volume, $issue, $pages, $article_number, 
            $doi, $issn, $publisher
        );
        if ($stmt->execute()) {
            $paperID = $stmt->insert_id;
            $stmt->close();
            return ['success' => true, 'paperID' => $paperID];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    } else {
        return ['success' => false, 'message' => $mysqli->error];
    }
}

// 获取论文的分类ID
function getCategoriesByPaperID($mysqli, $paperID) {
    $query = "SELECT categoryID FROM paperCategories WHERE paperID = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param('i', $paperID);
        $stmt->execute();
        $result = $stmt->get_result();
        $categoryIDs = [];
        while ($row = $result->fetch_assoc()) {
            $categoryIDs[] = $row['categoryID'];
        }
        $stmt->close();
        return $categoryIDs;
    } else {
        return false;
    }
}

// 更新论文的分类
function updatePaperCategories($mysqli, $paperID, $categoryIDs) {
    // 开始事务
    $mysqli->begin_transaction();

    try {
        // 删除现有的分类
        $deleteQuery = "DELETE FROM paperCategories WHERE paperID = ?";
        $deleteStmt = $mysqli->prepare($deleteQuery);
        if (!$deleteStmt) {
            throw new Exception("准备删除分类失败: " . $mysqli->error);
        }
        $deleteStmt->bind_param('i', $paperID);
        if (!$deleteStmt->execute()) {
            throw new Exception("删除分类失败: " . $deleteStmt->error);
        }
        $deleteStmt->close();

        // 插入新的分类
        $insertQuery = "INSERT INTO paperCategories (paperID, categoryID) VALUES (?, ?)";
        $insertStmt = $mysqli->prepare($insertQuery);
        if (!$insertStmt) {
            throw new Exception("准备插入分类失败: " . $mysqli->error);
        }

        foreach ($categoryIDs as $categoryID) {
            $insertStmt->bind_param('ii', $paperID, $categoryID);
            if (!$insertStmt->execute()) {
                throw new Exception("插入分类失败: " . $insertStmt->error);
            }
        }
        $insertStmt->close();

        // 提交事务
        $mysqli->commit();
        return ['success' => true];
    } catch (Exception $e) {
        // 回滚事务
        $mysqli->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// 分配 "0 All papers" 分类给新论文
function assignAllPapersCategory($mysqli, $paperID) {
    $categoryID = 1; // All papers
    $query = "INSERT INTO paperCategories (paperID, categoryID) VALUES (?, ?)";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param('ii', $paperID, $categoryID);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => $stmt->error];
        }
    } else {
        return ['success' => false, 'message' => $mysqli->error];
    }
}

/**
 * 根据 paperID 更新论文状态
 * @param mysqli $mysqli
 * @param int    $paperID
 * @param string $newStatus
 * @return array
 */
function updatePaperStatus($mysqli, $paperID, $newStatus) {
    $query = "UPDATE papers SET status = ? WHERE paperID = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('si', $newStatus, $paperID);
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    } else {
        return ['success' => false, 'message' => $mysqli->error];
    }
}
?>
