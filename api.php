<?php
// CORS and JSON Headers Configuration
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle OPTIONS preflight request securely
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 1. Database Connection Configuration
$host = "localhost";
$user = "root";  // Default XAMPP user
$pass = "";      // Default XAMPP password is empty
$dbname = "exam_portal_db"; 

$conn = new mysqli($host, $user, $pass, $dbname);

// Validate Connection Architecture
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database Connection Failed: " . $conn->connect_error]);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 2. Action: Custom Safe Login
if ($action === 'custom_login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $conn->real_escape_string($data['email']);
    $password = $data['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['password'] === $password) {
            echo json_encode(["status" => "success", "user" => ["email" => $user['email'], "name" => $user['name'], "role" => $user['role']]]);
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect Password."]);
        }
    } else {
        // Auto-register student if account doesn't exist
        $name = strstr($email, '@', true); 
        $conn->query("INSERT INTO users (email, name, password, role) VALUES ('$email', '$name', '$password', 'student')");
        echo json_encode(["status" => "success", "user" => ["email" => $email, "name" => $name, "role" => "student"]]);
    }
    exit;
}

// 3. Action: Admin Upload or Update Exam MCQs
if ($action === 'upload_exam' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $subject = $conn->real_escape_string($data['subject']);
    $questions = $conn->real_escape_string(json_encode($data['questions']));

    $sql = "INSERT INTO exams (subject, questions_json) VALUES ('$subject', '$questions') 
            ON DUPLICATE KEY UPDATE questions_json='$questions'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit;
}

// 4. Action: Fetch All Available Exams (Used by Dashboard Grid)
if ($action === 'get_all_exams' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT subject FROM exams ORDER BY subject ASC");
    $exams = [];
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $exams[] = $row['subject'];
        }
    }
    echo json_encode($exams); // Returns a clean array of subjects
    exit;
}

// 5. Action: User Fetch Questions for Selected Exam
if ($action === 'get_exam' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $subject = $conn->real_escape_string($_GET['subject']);
    $result = $conn->query("SELECT questions_json FROM exams WHERE subject='$subject'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(["status" => "success", "questions" => json_decode($row['questions_json'])]);
    } else {
        echo json_encode(["status" => "error", "message" => "No questions found."]);
    }
    exit;
}

// 6. Action: User Submit Exam Score
if ($action === 'submit_score' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $conn->real_escape_string($data['email']);
    $subject = $conn->real_escape_string($data['subject']);
    $score = (int)$data['score'];

    $conn->query("INSERT INTO results (email, subject, score) VALUES ('$email', '$subject', $score)");
    echo json_encode(["status" => "success"]);
    exit;
}

// 7. Action: Fetch Global Performance Leaderboard
if ($action === 'get_leaderboard' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT email, subject, score FROM results ORDER BY score DESC LIMIT 50");
    $leaderboard = [];
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $leaderboard[] = $row;
        }
    }
    echo json_encode($leaderboard);
    exit;
}

// Close connection bridge
$conn->close();
?>