<?php
require_once '../database/db.php';

header('Content-Type: application/json');

$client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

if ($client_id <= 0) {
    echo json_encode([]);
    exit();
}

$params = [':client_id' => $client_id];
$sql = "SELECT t.id, t.status, t.created_at, s.title, u.username, u.profile_picture, u.id AS user_id
        FROM ServiceTransaction t
        JOIN Service s ON t.service_id = s.id
        JOIN User u ON s.user_id = u.id
        WHERE t.client_id = :client_id";

if ($status !== '') {
    $sql .= " AND t.status = :status";
    $params[':status'] = $status;
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($orders);
exit();