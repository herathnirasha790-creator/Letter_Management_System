<?php
// get_institute_details.php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'engineering_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Failed']);
    exit;
}

if (isset($_GET['institute']) && !empty($_GET['institute'])) {
    $institute = $_GET['institute'];
    
    $stmt = $pdo->prepare("SELECT person_name, designation, email FROM institute_contacts WHERE institute_name = :institute LIMIT 1");
    $stmt->execute([':institute' => $institute]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'person_name' => $result['person_name'],
            'designation' => $result['designation'],
            'email' => $result['email'] ?? ''
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No contact found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No institute provided']);
}
?>