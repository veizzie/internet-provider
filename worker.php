<?php
session_start();

// Проверка, установлена ли сессия
$isUserLoggedIn = isset($_SESSION['user_id']);
$userID = $isUserLoggedIn ? $_SESSION['user_id'] : '';
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

if (!$isUserLoggedIn) {
    header('Location: login.php');
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
    // Если пользователь не найден, перенаправить на login.php
    header('Location: login.php');
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

// Обработка изменения статуса обращения
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_appealstatus'])) {
    $appealID = $_POST['appeal_id'];
    $newAppealStatus = $_POST['new_appealstatus'];

    $stmt = $mysql->prepare("UPDATE call_center SET status = ? WHERE appeal_id = ? AND worker_id = ?");
    $stmt->bind_param("sii", $newAppealStatus, $appealID, $workerID);
    $stmt->execute();
}

// Получение заказов работника
$stmt_orders = $mysql->prepare("SELECT o.order_id, o.user_id, u.user_id, u.first_name, u.phone_number, u.adress, o.order_date, s.title, s.price, o.status FROM orders o 
JOIN users u ON o.user_id = u.user_id
JOIN services s ON o.service_id = s.service_id
WHERE o.worker_id = ?");
$stmt_orders->bind_param("i", $workerID);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

$orders = [];
while ($row = $result_orders->fetch_assoc()) {
    $orders[] = $row;
}

// Получение заявок из таблицы call-center
$stmt_appeals = $mysql->prepare("SELECT c.appeal_id, c.user_id, c.worker_id, u.first_name, u.last_name, u.phone_number, u.adress, c.appeal_date, c.status, c.request_description FROM call_center c
JOIN users u ON c.user_id = u.user_id
WHERE worker_id = ?");
$stmt_appeals->bind_param("i", $workerID);
$stmt_appeals->execute();
$result_appeals = $stmt_appeals->get_result();

$appeals = [];
while ($row = $result_appeals->fetch_assoc()) {
    $appeals[] = $row;
}

// Получение описания работника
$stmt_description = $mysql->prepare("SELECT about_worker FROM workers WHERE worker_id = ?");
$stmt_description->bind_param("i", $workerID);
$stmt_description->execute();
$result_description = $stmt_description->get_result();
$workerDescription = $result_description->fetch_assoc()['about_worker'];

$isCallCenter = stripos($workerDescription, 'Call-центр') !== false;

// Обработка формы для смены пароля
$passwordChangeMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmNewPassword = $_POST['confirm_new_password'];
    
    // Получение текущего хеша пароля из базы данных
    $stmt = $mysql->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $currentPasswordHash = $userData['password'];

    // Хеширование введенного текущего пароля для сравнения
    $oldPasswordHash = md5($oldPassword);

    if ($oldPasswordHash === $currentPasswordHash) {
        if ($newPassword === $confirmNewPassword) {
            $newPasswordHash = md5($newPassword);

            $stmt = $mysql->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $newPasswordHash, $userID);
            $stmt->execute();

            $passwordChangeMessage = 'Пароль змінено';
        } else {
            $passwordChangeMessage = 'Нові паролі не збігаються';
        }
    } else {
        $passwordChangeMessage = 'Неправильний поточний пароль';
    }
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
            <div align="center">
                <form action="validation/logout.php" method="post">
                    <button type="submit" class="btn btn-danger">
                        <img src="media/exit-icon.png" alt="exit icon">
                    </button>
                </form>
            </div>
        </header>
        <div class="container mt-4">
            <h2 align="center" class="mt-4">
                <?php if ($isCallCenter): ?>
                    Ваші заявки
                <?php else: ?>
                    Ваші замовлення
                <?php endif; ?>
            </h2>
            <div class="user-list-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <?php if ($isCallCenter):?>
                            <th scope="col">Дата звернення</th>
                            <th scope="col">Ім'я користувача</th>
                            <th scope="col">Фамілія користувача</th>
                            <th scope="col">Номер телефону</th>
                            <th scope="col">Адреса</th>
                            <th scope="col">Текст звернення</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Оновити статус</th>
                            <?php else: ?>
                            <th scope="col">Дата</th>
                            <th scope="col">Ім'я</th>
                            <th scope="col">Номер телефону</th>
                            <th scope="col">Адреса</th>
                            <th scope="col">Послуга</th>
                            <th scope="col">Вартість</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Оновити статус</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($isCallCenter): ?>
                    <?php if (!empty($appeals)): ?>
                        <?php foreach ($appeals as $appeal): ?>
                            <tr>
                                <td><?= htmlspecialchars($appeal['appeal_date']) ?></td>
                                <td><?= htmlspecialchars($appeal['first_name']) ?></td>
                                <td><?= htmlspecialchars($appeal['last_name']) ?></td>
                                <td><?= htmlspecialchars($appeal['phone_number']) ?></td>
                                <td><?= htmlspecialchars($appeal['adress']) ?></td>
                                <td><?= htmlspecialchars($appeal['request_description']) ?></td>
                                <td>
                                        <?php 
                                            $status = htmlspecialchars($appeal['status']);
                                            $statusColor = '';
                                            switch ($status) {
                                                case 'Не оброблено':
                                                    $statusColor = 'text-danger';
                                                    break;
                                                case 'Оброблено':
                                                    $statusColor = 'text-success';
                                                    break;
                                            }
                                        ?>
                                        <span class="<?= $statusColor ?>"><?= $status ?></span>
                                    </td>
                                    <td>
                                        <form action="worker.php" method="post">
                                            <input type="hidden" name="appeal_id" value="<?= htmlspecialchars($appeal['appeal_id']) ?>">
                                            <select name="new_appealstatus" class="form-select" required>
                                                <option value="не оброблено" <?= $appeal['status'] == 'не оброблено' ? 'selected' : '' ?>>Не оброблено</option>
                                                <option value="оброблено" <?= $appeal['status'] == 'оброблено' ? 'selected' : '' ?>>Оброблено</option>
                                            </select>
                                            <button type="submit" name="update_appealstatus" class="btn btn-primary mt-2">Оновити</button>
                                        </form>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">У вас немає заявок</td>
                            </tr>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_date']) ?></td>
                                    <?php if (is_null($order['user_id'])): ?>
                                        <td colspan="2">Акаунт видалено</td>
                                        <td></td>
                                        <td></td>
                                    <?php else: ?>
                                    <td><?= htmlspecialchars($order['first_name']) ?></td>
                                    <td><?= htmlspecialchars($order['phone_number']) ?></td>
                                    <td><?= htmlspecialchars($order['adress']) ?></td>
                                    <td><?= htmlspecialchars($order['title']) ?></td>
                                    <td><?= htmlspecialchars($order['price']) ?></td>
                                    <td>
                                        <?php 
                                            $status = htmlspecialchars($order['status']);
                                            $statusColor = '';
                                            switch ($status) {
                                                case 'В обробці':
                                                    $statusColor = 'text-danger';
                                                    break;
                                                case 'Виконується':
                                                    $statusColor = 'text-warning';
                                                    break;
                                                case 'Виконано':
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
                                                <option value="в обробці" <?= $status == 'В обробці' ? 'selected' : '' ?>>В обробці</option>
                                                <option value="виконується" <?= $status == 'Виконується' ? 'selected' : '' ?>>Виконується</option>
                                                <option value="виконано" <?= $status == 'Виконано' ? 'selected' : '' ?>>Виконано</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-primary mt-2">Оновити</button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">У вас немає замовлень</td>
                            </tr>
                        <?php endif; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div align="center"><h3>Змінити пароль</h3></div>
            <div class="container">
                <form action="worker.php" method="post">
                    <input type="hidden" name="change_password" value="1">
                    <div class="mb-3">
                        <label for="old_password" class="form-label">Старий пароль</label>
                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Новий пароль</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label">Підтвердити новий пароль</label>
                        <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Змінити пароль</button>
                    <?php if ($passwordChangeMessage): ?>
                        <div class="alert alert-info mt-3"><?= htmlspecialchars($passwordChangeMessage) ?></div>
                    <?php endif; ?>
                </form>
            </div><br>
        </div>
        <footer>
            
        </footer>
    <?php endif; ?>
</body>
</html>
