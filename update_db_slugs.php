<?php
include("common/db.php");

function create_slug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    $slug = preg_replace('/-+/', '-', $slug);
    return rtrim($slug, '-');
}

// Add slug column to questions
$conn->query("ALTER TABLE questions ADD COLUMN slug VARCHAR(255) AFTER title");
$conn->query("CREATE UNIQUE INDEX idx_question_slug ON questions(slug)");

// Add slug column to category
$conn->query("ALTER TABLE category ADD COLUMN slug VARCHAR(255) AFTER name");
$conn->query("CREATE UNIQUE INDEX idx_category_slug ON category(slug)");

// Generate slugs for existing questions
$res = $conn->query("SELECT id, title FROM questions");
while ($row = $res->fetch_assoc()) {
    $slug = create_slug($row['title']) . '-' . $row['id'];
    $stmt = $conn->prepare("UPDATE questions SET slug = ? WHERE id = ?");
    $stmt->bind_param("si", $slug, $row['id']);
    $stmt->execute();
}

// Generate slugs for existing categories
$res = $conn->query("SELECT id, name FROM category");
while ($row = $res->fetch_assoc()) {
    $slug = create_slug($row['name']);
    // Ensure uniqueness for categories
    $check = $conn->prepare("SELECT id FROM category WHERE slug = ? AND id != ?");
    $check->bind_param("si", $slug, $row['id']);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $slug .= '-' . $row['id'];
    }
    $stmt = $conn->prepare("UPDATE category SET slug = ? WHERE id = ?");
    $stmt->bind_param("si", $slug, $row['id']);
    $stmt->execute();
}

echo "Slugs generated and columns added successfully.\n";
?>