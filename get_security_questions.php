<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$mysqli = new mysqli('localhost', 'root', '', 'banking');
if ($mysqli->connect_errno) {
    error_log("Database connection failed: " . $mysqli->connect_error);
    echo json_encode(['success' => false, 'message' => 'Failed to connect to database']);
    exit;
}

// Fetch 10 random security questions
$result = $mysqli->query("SELECT id, question_text FROM security_questions ORDER BY RAND() LIMIT 10");
if (!$result) {
    error_log("Query failed: " . $mysqli->error);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch security questions']);
    exit;
}

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = ['id' => $row['id'], 'question' => $row['question_text']];
}

$mysqli->close();

echo json_encode(['success' => true, 'questions' => $questions]);
exit;
?>
