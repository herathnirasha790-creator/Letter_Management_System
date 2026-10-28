<?php
// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
date_default_timezone_set('Asia/Colombo');

// JSON Response Header
header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception('Invalid request method');
    }

    if (!isset($_FILES['ocr_file'])) {
        throw new Exception('ෆයිල් එකක් ලැබී නැත');
    }

    if ($_FILES['ocr_file']['error'] != 0) {
        throw new Exception('ෆයිල් upload error code: ' . $_FILES['ocr_file']['error']);
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['ocr_file']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        throw new Exception('අවසර ලබා දී ඇත්තේ JPG, PNG පමණි');
    }

    $file_name = 'OCR_' . time() . '.' . $ext;
    $targetFile = $uploadDir . $file_name;

    if (!move_uploaded_file($_FILES['ocr_file']['tmp_name'], $targetFile)) {
        throw new Exception('ෆයිල් එක upload කිරීම අසාර්ථක විය');
    }

    // Tesseract Path එක (ඔබගේ පරිගණකයේ ස්ථාපිත කළ තැන)
    $tesseractPath = 'C:\Program Files\Tesseract-OCR\tesseract.exe';
    
    if (!file_exists($tesseractPath)) {
        throw new Exception('Tesseract.exe හමු නොවීය: ' . $tesseractPath);
    }

    // ==========================================
    // OCR ක්‍රියාත්මක කිරීම (Direct Shell Command හරහා)
    // -l sin+eng: සිංහල සහ ඉංග්‍රීසි දෙකම කියවන්න
    // ==========================================
    
    // Windows වල paths වල පොඩි ප්‍රශ්න එන නිසා, command එක හරියට හදමු
    $command = '"' . $tesseractPath . '" "' . $targetFile . '" stdout -l sin+eng 2>&1';
    
    // Tesseract රන් කරලා output එක ගන්නවා
    $text = shell_exec($command);
    
    // Shell Exec Error එකක්ද බලමු
    if ($text === null) {
        throw new Exception('Tesseract රන් කිරීමේදී දෝෂයක් ඇතිවිය. Paths හෝ Permissions පරීක්ෂා කරන්න.');
    }
    
    // Tesseract Error එකක් දෙනවද බලන්න (උදා: "Error opening data file...")
    if (stripos($text, 'Error opening data file') !== false || stripos($text, 'Error:') !== false) {
        // අපි එරර් එක නම්, ඒක text විදියට යවන්නේ නැතුව Error එකක් විදියට යවමු
        if (strpos($text, 'sin.traineddata') !== false) {
            throw new Exception('සිංහල භාෂා දත්ත (sin.traineddata) Tesseract වල නැත. කරුණාකර එය tessdata ෆෝල්ඩරයට එක් කරන්න.');
        }
    }

    // අවසාන ප්‍රතිඵලය
    echo json_encode([
        'success' => true,
        'text' => trim($text),
        'file_name' => $file_name
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Error $e) {
    echo json_encode([
        'success' => false,
        'error' => 'PHP Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>