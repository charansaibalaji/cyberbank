<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['answers']) || !is_array($data['answers'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$answers = $data['answers'];

$mysqli = new mysqli('localhost', 'root', '', 'banking');
if ($mysqli->connect_errno) {
    error_log("Database connection failed: " . $mysqli->connect_error);
    echo json_encode(['success' => false, 'message' => 'Failed to connect to database']);
    exit;
}

// Fetch correct answers for the user for the given question IDs
$question_ids = array_keys($answers);
if (count($question_ids) === 0) {
    echo json_encode(['success' => false, 'message' => 'No questions provided']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($question_ids), '?'));

$sql = "SELECT question_id, answer FROM user_security_answers WHERE user_id = ? AND question_id IN ($placeholders)";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$types = str_repeat('i', count($question_ids) + 1);
$params = array_merge([$user_id], $question_ids);

$tmp = [];
foreach ($params as $key => $value) {
    $tmp[$key] = &$params[$key];
}
call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $tmp));

$stmt->execute();
$result = $stmt->get_result();

$correct_answers = [];
while ($row = $result->fetch_assoc()) {
    $correct_answers[$row['question_id']] = strtolower(trim($row['answer']));
}

$stmt->close();
$mysqli->close();

// Compare user answers with correct answers
foreach ($answers as $qid => $user_answer) {
    if (!isset($correct_answers[$qid]) || strtolower(trim($user_answer)) !== $correct_answers[$qid]) {
        echo json_encode(['success' => false, 'message' => 'Incorrect answer for one or more questions']);
        exit;
    }
}

echo json_encode(['success' => true, 'message' => 'Security questions validated successfully']);
exit;
?>
