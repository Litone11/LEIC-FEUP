<?php
require_once '../../database/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado. É necessário autenticação.']);
    exit;
}
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? '';
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
$minDelivery = $_GET['min_days'] ?? null;
$maxDelivery = $_GET['max_days'] ?? null;
$minRating = $_GET['min_rating'] ?? null;

$conditions = ["s.status = 'active'"];
$params = [];

if ($q !== '') {
    $conditions[] = "(LOWER(s.title) LIKE LOWER(?) OR LOWER(u.username) LIKE LOWER(?))";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if (!empty($category)) {
    $conditions[] = "c.id = ?";
    $params[] = $category;
}
if ($minPrice !== null && $minPrice !== '') {
    $conditions[] = "s.price >= ?";
    $params[] = $minPrice;
}
if ($maxPrice !== null && $maxPrice !== '') {
    $conditions[] = "s.price <= ?";
    $params[] = $maxPrice;
}
if ($minDelivery !== null && $minDelivery !== '') {
    $conditions[] = "s.delivery_time >= ?";
    $params[] = $minDelivery;
}
if ($maxDelivery !== null && $maxDelivery !== '') {
    $conditions[] = "s.delivery_time <= ?";
    $params[] = $maxDelivery;
}
$whereSQL = implode(" AND ", $conditions);

$orderSQL = "";
switch ($sort) {
    case 'price_asc':
        $orderSQL = "ORDER BY s.price ASC";
        break;
    case 'price_desc':
        $orderSQL = "ORDER BY s.price DESC";
        break;
    case 'rating':
        $orderSQL = "ORDER BY rating DESC";
        break;
    default:
        $orderSQL = "ORDER BY s.created_at DESC";
        break;
}

$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.description, s.price, s.delivery_time, s.main_image,
           c.name AS category,
           u.username, u.profile_picture,
           (
             SELECT ROUND(AVG(r2.rating), 1)
             FROM ServiceTransaction t2
             JOIN Review r2 ON t2.id = r2.transaction_id
             WHERE t2.service_id = s.id
           ) AS rating
    FROM Service s
    JOIN User u ON s.user_id = u.id
    LEFT JOIN Category c ON s.category_id = c.id
    WHERE $whereSQL
    $orderSQL
    LIMIT 20
");
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filteredResults = [];
foreach ($results as $service) {
    $avg = (float) ($service['rating'] ?? 0);
    if ($minRating !== null && $minRating !== '' && $avg < (float) $minRating) {
        continue;
    }

    $imageDir = __DIR__ . '/../../assets/img/service_images/' . $service['id'] . '/';
    $images = file_exists($imageDir) ? glob($imageDir . '*') : [];
    error_log("Service ID {$service['id']} - Images: " . print_r($images, true));
    $service['preview_image'] = !empty($images)
        ? '/assets/img/service_images/' . $service['id'] . '/' . basename($images[0])
        : '/assets/img/default-service.png';

    $profile = $service['profile_picture'] ?? '';
    $service['profile_picture'] = file_exists('../' . $profile)
        ? '../' . $profile
        : '../../assets/img/default-profile.png';

    $service['rating'] = $avg > 0 ? round($avg, 1) : 0;
    $filteredResults[] = $service;
}

$categories = $pdo->query("SELECT name FROM Category ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'results' => $filteredResults,
    'categories' => $categories
]);