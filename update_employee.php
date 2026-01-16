<?php
require_once 'auth.php';
require_admin_login();
require_once 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$office = $data['office'] ?? null;
$status = $data['status'] ?? null;

if (!$id || !$office || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE employees SET office = ?, employment_status = ? WHERE id = ?");
    $stmt->execute([$office, $status, $id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}