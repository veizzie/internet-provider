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

// Получение информации о пользователе
$stmt = $mysql->prepare("SELECT first_name, last_name, phone_number, adress, password FROM users WHERE user_id = ?");
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if (!$userData) {
    // Если данные пользователя не найдены, перенаправить на страницу входа
    header('Location: login.php');
    exit();
}

$firstName = $userData['first_name'];
$lastName = $userData['last_name'];
$phoneNumber = $userData['phone_number'];
$address = $userData['adress'];
$currentPasswordHash = $userData['password'];

// Обработка формы для обновления информации
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $newFirstName = $_POST['first_name'];
    $newLastName = $_POST['last_name'];
    $newPhoneNumber = $_POST['phone_number'];
    $newAddress = $_POST['address'];

    $stmt = $mysql->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, adress = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $newFirstName, $newLastName, $newPhoneNumber, $newAddress, $userID);
    $stmt->execute();

    // Обновление данных для отображения
    $firstName = $newFirstName;
    $lastName = $newLastName;
    $phoneNumber = $newPhoneNumber;
    $address = $newAddress;
}

    // Обработка формы для смены пароля
    $passwordChangeMessage = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];
        $confirmNewPassword = $_POST['confirm_new_password'];

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

    // Получение заказов пользователя
    $stmt_orders = $mysql->prepare("SELECT o.order_id, o.order_date, s.title, s.price, o.status FROM orders o 
    JOIN services s ON o.service_id = s.service_id 
    WHERE o.user_id = ?");
    $stmt_orders->bind_param("s", $userID);
    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();

    // Получение заявок пользователя
    $stmt_appeals = $mysql->prepare("SELECT c.appeal_id, c.appeal_date, c.request_description, c.status FROM call_center c 
    WHERE c.user_id = ?");
    $stmt_appeals->bind_param("s", $userID);
    $stmt_appeals->execute();
    $result_appeals = $stmt_appeals->get_result();

    $orders = [];
    while ($row = $result_orders->fetch_assoc()) {
    $orders[] = $row;
    }
    
    $appeals = [];
    while ($row = $result_appeals->fetch_assoc()) {
    $appeals[] = $row;
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
    <title>Профіль</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <?php if ($isUserLoggedIn): ?>
        <header>
            <div class="header-container d-flex flex-column align-items-center">
                <div class="welcome-text">
                    <p>Вітаємо, <?= htmlspecialchars($firstName) ?>!</p>
                </div>
                <form action="validation/logout.php" method="post" class="mt-2">
                    <button type="submit" class="btn btn-danger">
                        <img src="media/exit-icon.png" alt="exit icon">
                    </button>
                </form>
                <nav class="nav mt-2">
                    <a class="nav-link btn btn-primary mx-1" href="home.php">Головна</a>
                    <a class="nav-link btn btn-primary mx-1" href="services.php">Послуги</a>
                    <a class="nav-link btn btn-primary mx-1" href="profile.php">Профіль</a>
                </nav>
            </div>
        </header>
        <div class="container mt-4">
            <h2 align="center" class="mt-4">Особиста інформація</h2>
            <form action="profile.php" method="post">
                <input type="hidden" name="update_info" value="1">
                <div class="mb-3">
                    <label for="first_name" class="form-label">Ім'я</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Прізвище</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Телефон</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= htmlspecialchars($phoneNumber) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Адреса</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($address) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Оновити інформацію</button>
            </form>
            <h2 align="center" class="mt-4">Змінити пароль</h2>
            <form action="profile.php" method="post">
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
            <h2 align="center" class="mt-4">Історія замовлень</h2>
            <div class="user-list-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Дата</th>
                            <th scope="col">Послуга</th>
                            <th scope="col">Вартість</th>
                            <th scope="col">Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <?php
                                $statusColor = '';
                                switch ($order['status']) {
                                    case 'В обробці':
                                        $statusColor = 'text-danger';
                                        break;
                                    case 'Виконується':
                                        $statusColor = 'text-warning';
                                        break;
                                    case 'Виконано':
                                        $statusColor = 'text-success';
                                        break;
                                    default:
                                        $statusColor = '';
                                        break;
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_date']) ?></td>
                                    <td><?= htmlspecialchars($order['title']) ?></td>
                                    <td><?= htmlspecialchars($order['price']) ?></td>
                                    <td class="<?= $statusColor ?>"><?= htmlspecialchars($order['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">У вас немає замовлень</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <h2 align="center" class="mt-4">Ваші зверненя</h2>
            <div class="user-list-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Дата</th>
                            <th scope="col">Текст заявки</th>
                            <th scope="col">Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($appeals)): ?>
                            <?php foreach ($appeals as $appeal): ?>
                                <?php
                                $statusColor = '';
                                switch ($appeal['status']) {
                                    case 'Не оброблено':
                                        $statusColor = 'text-danger';
                                        break;
                                    case 'Оброблено':
                                        $statusColor = 'text-success';
                                        break;
                                    default:
                                        $statusColor = '';
                                        break;
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($appeal['appeal_date']) ?></td>
                                    <td><?= htmlspecialchars($appeal['request_description']) ?></td>
                                    <td class="<?= $statusColor ?>"><?= htmlspecialchars($appeal['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Ви ще ні разу не звертались до Call-центру</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <footer>
            <p>Виникли якісь проблеми?</p>
            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#problemModal">Повідомити про проблему</button>
        </footer>
        <div class="modal fade" id="problemModal" tabindex="-1" aria-labelledby="problemModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="problemModalLabel">Повідомити про проблему</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="problemForm">
                            <div class="mb-3">
                                <label for="problemDescription" class="form-label">Опис проблеми</label>
                                <textarea class="form-control" id="problemDescription" name="problemDescription" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Відправити</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script></body>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="js/callCenterSend.js"></script>
    <?php endif; ?>
</body>
</html>
