<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please login.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit();
}

$letter_id = isset($_POST['letter_id']) ? (int)$_POST['letter_id'] : 0;
if (!$letter_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid Letter ID.']);
    exit();
}

require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit();
}

try {
    $conn->beginTransaction();

    // 1. Explicitly delete child assignments to ensure full cleanup
    $stmt1 = $conn->prepare("DELETE FROM primary_assignments WHERE letter_id = :id");
    $stmt1->execute([':id' => $letter_id]);

    $stmt2 = $conn->prepare("DELETE FROM secondary_assignments WHERE letter_id = :id");
    $stmt2->execute([':id' => $letter_id]);

    $stmt3 = $conn->prepare("DELETE FROM assignments WHERE letter_id = :id");
    $stmt3->execute([':id' => $letter_id]);

    // 2. Delete letter record
    $stmt = $conn->prepare("DELETE FROM letters WHERE id = :id");
    $stmt->execute([':id' => $letter_id]);

    $conn->commit();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'ලිපිය සහ ඊට අදාළ සියලු දත්ත සාර්ථකව මකා දමන ලදී! (Letter deleted successfully)'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'ලිපිය හමු නොවීය. (Letter not found or already deleted)'
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'error' => 'Database Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
