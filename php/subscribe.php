<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit();
}

$userID = $_SESSION['user_id'];

if (!isset($_GET['service_id'])) {
    die('Невірний запит');
}

$serviceID = $_GET['service_id'];
echo 'Получен service_id: ' . htmlspecialchars($serviceID) . '<br>'; // Отладочный вывод

$mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');

if ($mysql->connect_error) {
    die("Ошибка подключения: " . $mysql->connect_error);
}

// Получаем title выбранного тарифа
$stmt = $mysql->prepare("SELECT title FROM services WHERE service_id = ?");
$stmt->bind_param("i", $serviceID);
$stmt->execute();
$stmt->bind_result($serviceTitle);
$stmt->fetch();
$stmt->close();

if (!$serviceTitle) {
    echo "Помилка: Тариф не знайдено для service_id: " . htmlspecialchars($serviceID);
    exit();
}

echo 'Найден serviceTitle: ' . htmlspecialchars($serviceTitle) . '<br>'; // Отладочный вывод

// Ищем рабочих, которые соответствуют ключевому слову из title
$stmt = $mysql->prepare("SELECT worker_id FROM workers WHERE about_worker LIKE ?");
$likeQuery = '%' . $serviceTitle . '%';  // Ищем ключевое слово в описании
$stmt->bind_param("s", $likeQuery);
$stmt->execute();
$stmt->bind_result($workerID);
$workerIDs = array();
while ($stmt->fetch()) {
    $workerIDs[] = $workerID;
}
$stmt->close();

if (empty($workerIDs)) {
    echo "Помилка: Відповідних робітників не знайдено для serviceTitle: " . htmlspecialchars($serviceTitle);
    exit();
}

// Выбираем случайного рабочего из списка
$randomWorkerID = $workerIDs[array_rand($workerIDs)];

echo 'Найден случайный workerID: ' . htmlspecialchars($randomWorkerID) . '<br>'; // Отладочный вывод

// Подключаем тариф и назначаем случайного рабочего
$stmt = $mysql->prepare("INSERT INTO orders (user_id, service_id, order_date, worker_id, status) VALUES (?, ?, NOW(), ?, 'В обробці')");
$stmt->bind_param("iii", $userID, $serviceID, $randomWorkerID);

if ($stmt->execute()) {
    echo "Тариф успішно підключено!";
} else {
    echo "Помилка при підключенні тарифу: " . $stmt->error;
}

$stmt->close();
$mysql->close();

header('Location: ../services.php');
exit();
?>
