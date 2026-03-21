<?php

function getCategoryById(PDO $pdo, int $id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addCategory(PDO $pdo, string $name): bool {
    $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
    return $stmt->execute([trim($name)]);
}

function updateCategory(PDO $pdo, int $id, string $name): bool {
    $stmt = $pdo->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
    return $stmt->execute([trim($name), $id]);
}

function deleteCategory(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
    return $stmt->execute([$id]);
}

function categoryHasItems(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE category_id = ?");
    $stmt->execute([$id]);
    return (int) $stmt->fetchColumn() > 0;
}

function categoryNameExists(PDO $pdo, string $name, int $excludeId = 0): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_name = ? AND category_id != ?");
    $stmt->execute([trim($name), $excludeId]);
    return (int) $stmt->fetchColumn() > 0;
}
