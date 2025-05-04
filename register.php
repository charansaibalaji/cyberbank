<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

ini_set('display_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents("php://input"));

$username = $data->username ?? '';
$password = $data->password ?? '';
$securityAnswers = $data->security_answers ?? [];

if (!$username || !$password || count($securityAnswers) < 10) {
    echo json_encode(["success" => false, "message" => "Incomplete data received."]);
    exit();
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT);

$host = '127.0.0.1';
$dbname = 'banking';
$dbUsername = 'root';
$dbPassword = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if username already exists
    $checkSql = "SELECT COUNT(*) FROM users WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$username]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Username already taken. Please choose another."]);
        exit();
    }

    $sql = "INSERT INTO users (
        username, password,
        security_answer_1, security_answer_2, security_answer_3, security_answer_4, security_answer_5,
        security_answer_6, security_answer_7, security_answer_8, security_answer_9, security_answer_10
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        $username, $passwordHash,
        $securityAnswers[0], $securityAnswers[1], $securityAnswers[2], $securityAnswers[3], $securityAnswers[4],
        $securityAnswers[5], $securityAnswers[6], $securityAnswers[7], $securityAnswers[8], $securityAnswers[9]
    ]);

    if ($result) {
        error_log("User registration successful for username: $username");
        echo json_encode(["success" => true, "message" => "Registration successful."]);
    } else {
        error_log("User registration failed for username: $username");
        echo json_encode(["success" => false, "message" => "Registration failed."]);
    }
} catch (PDOException $e) {
    error_log("Database error during registration for username $username: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
