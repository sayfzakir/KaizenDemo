<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Connect to your database
$host = '127.0.0.1';
$dbname = 'kaizen_temp_data';
$username = 'kaizen_user';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => "Database connection failed: " . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['responses']) || !is_array($data['responses'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or missing data']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        foreach ($data['responses'] as $questionId => $answer) {
            if (strpos($questionId, '-other') !== false) {
                continue;  // Skip direct processing of '-other' fields, they are processed with their main question.
            }
            if (is_array($answer)) {
                $answer = implode(',', $answer); // Handle array answers for checkboxes
            }
            $otherKey = $questionId . '-other';
            if (!empty($data['responses'][$otherKey])) {
                $answer = trim($answer . ', ' . $data['responses'][$otherKey], ', ');
            }
            // Log the type and value of questionId
            error_log("Question ID Type: " . gettype($questionId) . " - Value: " . $questionId);
            error_log("Answer Type: " . gettype($answer) . " - Value: " . $answer);
        
            try {
                $stmt = $pdo->prepare("INSERT INTO answers (user_id, question_id, answer_text) VALUES (:user_id, :question_id, :answer_text) ON DUPLICATE KEY UPDATE answer_text = :answer_text");
                $stmt->execute([
                    ':user_id' => 1,
                    ':question_id' => $questionId,
                    ':answer_text' => $answer
                ]);
            } catch (PDOException $e) {
                error_log("Error inserting data: " . $e->getMessage());
                throw $e; // Re-throw to handle it in the outer catch block
            }
        }
        
        $pdo->commit();
        echo json_encode(['message' => 'Responses saved successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save responses: ' . $e->getMessage()]);
    }
}
?>
