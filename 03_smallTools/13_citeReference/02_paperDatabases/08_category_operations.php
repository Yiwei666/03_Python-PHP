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
?>
