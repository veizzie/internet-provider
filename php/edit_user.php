<?php
// Подключение к базе данных
$mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');

// Проверка соединения
if ($mysql->connect_error) {
    die("Ошибка подключения: " . $mysql->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $userId = $_GET['id'];
    $stmt = $mysql->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['user_id'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $userType = $_POST['user_type'];
    $phoneNumber = $_POST['phone_number'];
    $adress = $_POST['adress'];

    $stmt = $mysql->prepare("UPDATE users SET first_name = ?, last_name = ?, user_type = ?, phone_number = ?, adress = ? WHERE user_id = ?");
    $stmt->bind_param("ssissi", $firstName, $lastName, $userType, $phoneNumber, $adress, $userId);
    $stmt->execute();
    $stmt->close();

    header('Location: ../adminpanel.php');
    exit();
}

$mysql->close();
?>