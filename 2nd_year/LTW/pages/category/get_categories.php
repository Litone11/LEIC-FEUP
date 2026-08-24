<?php
require_once '../../database/db.php';

session_start();

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, name FROM Category ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao buscar categorias: ' . $e->getMessage()]);
}
?>