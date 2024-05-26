<?php
session_start();

// Проверка, установлена ли сессия
$isUserLoggedIn = isset($_SESSION['user_id']);
$userID = $isUserLoggedIn ? $_SESSION['user_id'] : '';
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

if (!$isUserLoggedIn) {
    header('Location: login.html');
    exit();
}

$mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');

if ($mysql->connect_error) {
    die("Ошибка подключения: " . $mysql->connect_error);
}

$stmt = $mysql->prepare("SELECT user_type FROM users WHERE user_id = ?");
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Получение имени пользователя по его ID
$stmt = $mysql->prepare("SELECT first_name FROM users WHERE user_id = ?");
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Получение идентификатора работника
$stmt_worker = $mysql->prepare("SELECT worker_id FROM workers WHERE user_id = ?");
$stmt_worker->bind_param("s", $userID);
$stmt_worker->execute();
$result_worker = $stmt_worker->get_result();
$workerData = $result_worker->fetch_assoc();

// Проверка наличия данных пользователя
if ($userData) {
    $userName = $userData['first_name'];
} else {
    // Если данные пользователя не найдены, установить имя по умолчанию или перенаправить на страницу входа
    $userName = 'Невідомий користувач';
}

// Проверка наличия данных работника
if ($workerData) {
    $workerID = $workerData['worker_id'];
} else {
    // Если данные работника не найдены, установить пустой ID или выполнить дополнительные действия по необходимости
    $workerID = '';
}

// Проверка наличия пользователя и его типа
if ($user) {
    $userType = $user['user_type'];

    // Перенаправление в зависимости от user_type
    if ($userType == 1) {
        header('Location: /home.php');
        exit();
    } elseif ($userType == 3) {
        header('Location: /adminpanel.php');
        exit();
    } elseif ($userType == 2) {
        // Оставляем пользователя на этой странице, т.к. это админ панель
    }

    $roles = [
        1 => 'Клієнт',
        2 => 'Працівник',
        3 => 'Адмін'
    ];
    $userRole = isset($roles[$userType]) ? $roles[$userType] : 'Невідома роль';
} else {
    // Если пользователь не найден, перенаправить на login.html
    header('Location: login.html');
    exit();
}

// Обработка изменения статуса заказа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $orderID = $_POST['order_id'];
    $newStatus = $_POST['new_status'];

    $stmt = $mysql->prepare("UPDATE orders SET status = ? WHERE order_id = ? AND worker_id = ?");
    $stmt->bind_param("sii", $newStatus, $orderID, $workerID);
    $stmt->execute();
}

// Получение заказов работника
$stmt_orders = $mysql->prepare("SELECT o.order_id, o.order_date, s.title, s.price, o.status FROM orders o 
JOIN services s ON o.service_id = s.service_id
WHERE o.worker_id = ?");
$stmt_orders->bind_param("i", $workerID);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();


$orders = [];
while ($row = $result_orders->fetch_assoc()) {
    $orders[] = $row;
}

// Закрытие соединения с базой данных
$stmt->close();
$mysql->close();
?>

<!DOCTYPE html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сторінка працівника</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/workerPage.css">
</head>
<body>
    <?php if ($isUserLoggedIn): ?>
        <header>
            <div class="header-container">
                <div class="welcome-text">
                    <p>Вітаємо, <?= htmlspecialchars($userName) ?>!</p>
                    <p>Ваша роль: <?= htmlspecialchars($userRole) ?></p>
                </div>
            </div>
            <div align="right">
                <form action="validation/logout.php" method="post">
                    <button type="submit" class="btn btn-danger">
                        <img src="media/exit-icon.png" alt="exit icon">
                    </button>
                </form>
            </div>
        </header>
        <div class="container mt-4">
            <h2 align="center" class="mt-4">Ваші замовлення</h2>
            <div class="user-list-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Дата</th>
                            <th scope="col">Послуга</th>
                            <th scope="col">Вартість</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Оновити статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_date']) ?></td>
                                    <td><?= htmlspecialchars($order['title']) ?></td>
                                    <td><?= htmlspecialchars($order['price']) ?></td>
                                    <td>
                                        <?php 
                                            $status = htmlspecialchars($order['status']);
                                            $statusColor = '';
                                            switch ($status) {
                                                case 'в обробці':
                                                    $statusColor = 'text-danger';
                                                    break;
                                                case 'виконується':
                                                    $statusColor = 'text-warning';
                                                    break;
                                                case 'виконано':
                                                    $statusColor = 'text-success';
                                                    break;
                                            }
                                        ?>
                                        <span class="<?= $statusColor ?>"><?= $status ?></span>
                                    </td>
                                    <td>
                                        <form action="worker.php" method="post">
                                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                                            <select name="new_status" class="form-select" required>
                                                <option value="в обробці" <?= $status == 'в обробці' ? 'selected' : '' ?>>В обробці</option>
                                                <option value="виконується" <?= $status == 'виконується' ? 'selected' : '' ?>>Виконується</option>
                                                <option value="виконано" <?= $status == 'виконано' ? 'selected' : '' ?>>Виконано</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-primary mt-2">Оновити</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">У вас немає замовлень</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <footer>
            
        </footer>
    <?php endif; ?>
</body>
</html>
