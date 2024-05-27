<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Користувач не авторизований.']);
    exit();
}

$userID = $_SESSION['user_id'];
$problemDescription = $_POST['problemDescription'];

$mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');

if ($mysql->connect_error) {
    echo json_encode(['message' => 'Помилка підключення: ' . $mysql->connect_error]);
    exit();
}

// Поиск всех рабочих с ключевыми словами в описании
$stmt = $mysql->prepare("SELECT worker_id FROM workers WHERE about_worker LIKE ?");
$likeQuery = '%Call-центр%'; // Ищем слово "Call-центр" в описании
$stmt->bind_param("s", $likeQuery);
$stmt->execute();
$result = $stmt->get_result();
$workerIDs = [];

while ($row = $result->fetch_assoc()) {
    $workerIDs[] = $row['worker_id'];
}

$stmt->close();

if (empty($workerIDs)) {
    echo json_encode(['message' => 'Помилка: Відповідного робітника не знайдено для заявки.']);
    exit();
}

// Выбор случайного рабочего
$workerID = $workerIDs[array_rand($workerIDs)];

// Вставка заявки с указанием рабочего
$stmt = $mysql->prepare("INSERT INTO call_center (user_id, worker_id, appeal_date, request_description) VALUES (?, ?, NOW(), ?)");
$stmt->bind_param("iis", $userID, $workerID, $problemDescription);

if ($stmt->execute()) {
    echo json_encode(['message' => 'Вашу заявку відправлено. З вами скоро зв\'яжуться.']);
} else {
    echo json_encode(['message' => 'Виникла помилка при відправці заявки. Спробуйте пізніше.']);
}

$stmt->close();
$mysql->close();
?>
