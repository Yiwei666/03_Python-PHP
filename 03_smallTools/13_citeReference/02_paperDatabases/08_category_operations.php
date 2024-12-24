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

// 获取特定分类下的论文
function getPapersByCategory($mysqli, $categoryID) {
    $query = "
        SELECT p.title, p.authors, p.publication_year, p.journal_name, p.doi 
        FROM papers p
        JOIN paperCategories pc ON p.paperID = pc.paperID
        WHERE pc.categoryID = ?
        ORDER BY p.title ASC
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
    $query = "INSERT INTO papers (title, authors, journal_name, publication_year, volume, issue, pages, article_number, doi, issn, publisher) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param('sssisssssss', $title, $authors, $journal_name, $publication_year, $volume, $issue, $pages, $article_number, $doi, $issn, $publisher);
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

?>

