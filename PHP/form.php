<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

$host = '127.0.0.1';
$dbname = 'kaizen_temp_data';
$username = 'kaizen_user';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connection established.");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['error' => "Database connection failed: " . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['responses']) || !is_array($data['responses'])) {
        error_log("Invalid or missing 'responses' data.");
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or missing data']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        $sql = "INSERT INTO patient_info (name, date_of_birth, referring_physician, signature, ";
        $values = "VALUES (:name, :date_of_birth, :referring_physician, :signature, ";
        $params = [
            ':name' => base64_decode($data['name']),
            ':date_of_birth' => base64_decode($data['dateOfBirth']),
            ':referring_physician' => base64_decode($data['referringPhysician']),
            ':signature' => $data['signature']
        ];

        foreach ($data['responses'] as $questionId => $answer) {
            if (strpos($questionId, '-other') !== false) {
                continue;  
            }
            $columnName = "question_$questionId";
            $sql .= "`$columnName`, ";
            $values .= ":$columnName, ";

            if (is_array($answer)) {
                $answerText = implode(',', $answer);
            } else {
                $answerText = $answer;
            }
            
            $otherKey = $questionId . '-other';
            if (isset($data['responses'][$otherKey]) && !empty($data['responses'][$otherKey])) {
                $answerText .= ', ' . $data['responses'][$otherKey];
            }
            
            $params[":$columnName"] = $answerText;
            error_log("Processing $columnName with value $answerText");
        }
        
        $sql = rtrim($sql, ', ') . ') ' . rtrim($values, ', ') . ')';
        $stmt = $pdo->prepare($sql);
        error_log("Executing SQL: $sql with parameters " . json_encode($params));
        $stmt->execute($params);

        $pdo->commit();
        echo json_encode(['message' => 'Responses saved successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Failed to save responses: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save responses: ' . $e->getMessage()]);
    }
}
?>
