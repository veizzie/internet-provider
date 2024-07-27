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

// Проверка наличия данных пользователя
if ($userData) {
    $userName = $userData['first_name'];
} else {
    // Если данные пользователя не найдены, установить имя по умолчанию или перенаправить на страницу входа
    $userName = 'Невідомий користувач';
}


// Проверка наличия пользователя и его типа
if ($user) {
    $userType = $user['user_type'];

    // Перенаправление в зависимости от user_type
    if ($userType == 1) {
        header('Location: /home.php');
        exit();
    } elseif ($userType == 2) {
        header('Location: /worker.php');
        exit();
    } elseif ($userType == 3) {
        // Оставляем пользователя на этой странице, т.к. это админ панель
    } else {
        // Если user_type не соответствует ни одному из известных типов, перенаправить на login.php
        header('Location: login.php');
        exit();
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

// Получение списка всех пользователей
$users = [];
$result = $mysql->query("SELECT user_id, first_name, last_name, user_type, phone_number, adress FROM users");
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
// Получение имени пользователя по его ID
$stmt = $mysql->prepare("SELECT first_name FROM users WHERE user_id = ?");
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Проверка наличия данных пользователя
if ($userData) {
    $userName = $userData['first_name'];
} else {
    // Если данные пользователя не найдены, установить имя по умолчанию или перенаправить на страницу входа
    $userName = 'Невідомий користувач';
}

$workers = [];
$result = $mysql->query("SELECT * FROM workers");
while ($row = $result->fetch_assoc()) {
    $workers[] = $row;
}

// Получение списка всех услуг
$services = [];
$result = $mysql->query("SELECT service_id, title, price, service_details FROM services");
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

$orders = [];
$result = $mysql->query("SELECT o.order_date, s.title, s.price, o.status, u.first_name, u.last_name, u.phone_number, u.adress, o.user_id
                         FROM orders o
                         LEFT JOIN services s ON o.service_id = s.service_id
                         LEFT JOIN users u ON o.user_id = u.user_id
                         ORDER BY o.order_date");
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

$appeals = [];
$result = $mysql->query("SELECT c.appeal_date, c.request_description, c.status, u.first_name, u.last_name, u.phone_number, u.adress, c.user_id
                         FROM call_center c 
                         LEFT JOIN users u ON c.user_id = u.user_id
                         ORDER BY c.appeal_date");
while ($row = $result->fetch_assoc()) {
    $appeals[] = $row;
}

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
    <title>Адмін панель</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/adminpanel.css">
</head>
<body>
    <?php if ($isUserLoggedIn): ?>
        <header>
        <div class="header-container">
            <div class="logo-container">
                <img width="150px" src="media/logo.png" alt="logo">
            </div>
            <div class="welcome-text">
                <p>Вітаємо, <?= htmlspecialchars($userName) ?>!</p>
                <p>Ваша роль: <?= htmlspecialchars($userRole) ?></p>
            </div>
            <div class="logout-container">
                <form action="validation/logout.php" method="post" class="mt-2">
                    <button type="submit" class="btn btn-danger">
                        <img src="media/exit-icon.png" alt="exit icon">
                    </button>
                </form>
            </div>
        </div>
        </header>
        <div align="center">
            <h1>Адмін панель</h1>
        </div>
        <div align="center"><h3>Усі користувачі</h3></div>
        <div class="container">
            <div align="center" class="user-list-container">
                <table>
                    <thead align="center">
                        <tr>
                            <th>Ім'я</th>
                            <th>Прізвище</th>
                            <th>Роль</th>
                            <th>Телефон</th>
                            <th>Адреса</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr onclick="selectUser(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['first_name']) ?>', '<?= htmlspecialchars($user['last_name']) ?>', <?= $user['user_type'] ?>, '<?= htmlspecialchars($user['phone_number']) ?>', '<?= htmlspecialchars($user['adress']) ?>')">
                                <td><?= htmlspecialchars($user['first_name']) ?></td>
                                <td><?= htmlspecialchars($user['last_name']) ?></td>
                                <td><?= htmlspecialchars($roles[$user['user_type']]) ?></td>
                                <td><?= htmlspecialchars($user['phone_number']) ?></td>
                                <td><?= htmlspecialchars($user['adress']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div><br>
        <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Керування користувачем</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="userFirstName" class="form-control mb-2" placeholder="Ім'я">
                        <input type="text" id="userLastName" class="form-control mb-2" placeholder="Прізвище">
                        <select id="userRole" class="form-select mb-2">
                            <option value="1">Клієнт</option>
                            <option value="2">Працівник</option>
                            <option value="3">Адмін</option>
                        </select>
                        <input type="text" id="userPhoneNumber" class="form-control mb-2" placeholder="Телефон">
                        <input type="text" id="userAdress" class="form-control mb-2" placeholder="Адреса">
                        <div class="modal-body">
                        <!-- Ваши инпуты для редактирования данных пользователя -->
                            <div class="d-grid gap-2">
                                <button onclick="editUser()" class="btn btn-primary">Змінити</button>
                                <button onclick="confirmDeleteUser()" class="btn btn-danger">Видалити</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div align="center"><h3>Усі працівники</h3></div>
        <div class="container">
            <div align="center" class="user-list-container">
                <table>
                    <thead align="center">
                        <tr>
                            <th>Ім'я</th>
                            <th>Прізвище</th>
                            <th>Телефон</th>
                            <th>Опис працівника</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workers as $worker): ?>
                            <tr onclick="selectWorker(<?= $worker['worker_id'] ?>, '<?= htmlspecialchars($worker['about_worker']) ?>')">
                                <td><?= htmlspecialchars($worker['first_name']) ?></td>
                                <td><?= htmlspecialchars($worker['last_name']) ?></td>
                                <td><?= htmlspecialchars($worker['phone_number']) ?></td>
                                <td><?= htmlspecialchars($worker['about_worker']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal fade" id="workerModal" tabindex="-1" aria-labelledby="workerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="workerModalLabel">Керування працівником</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea id="workerDescription" class="form-control" rows="4"></textarea>
                        <div class="d-grid gap-2">
                            <button type="button" onclick="editWorker()" class="btn btn-primary">Редагувати</button>
                        </div>
                    </div>
                </div>
            </div>
        </div><br>
        <div align="center"><h3>Послуги</h3></div>
        <div class="container">
            <div align="center" class="user-list-container">
                <table>
                    <thead align="center">
                        <tr>
                            <th>Назва послуги</th>
                            <th>Ціна</th>
                            <th>Деталі послуги</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr onclick="selectService(<?= $service['service_id'] ?>, '<?= htmlspecialchars($service['title']) ?>', '<?= htmlspecialchars($service['price']) ?>', '<?= htmlspecialchars($service['service_details']) ?>')">
                                <td><?= htmlspecialchars($service['title']) ?></td>
                                <td><?= htmlspecialchars($service['price']) ?></td>
                                <td><?= htmlspecialchars($service['service_details']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button style="margin-top: 20px; margin-bottom: 10px;" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">Додати послугу</button>
            </div>
        </div><br>

        <!-- Модальное окно для редактирования услуг -->
        <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="serviceModalLabel">Керування послугою</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="serviceTitle" class="form-control mb-2" placeholder="Назва послуги">
                        <input type="text" id="servicePrice" class="form-control mb-2" placeholder="Ціна">
                        <textarea id="serviceDetails" class="form-control mb-2" rows="4" placeholder="Деталі послуги"></textarea>
                        <div class="d-grid gap-2">
                            <button onclick="editService()" class="btn btn-primary">Редагувати</button>
                            <button onclick="confirmDeleteService()" class="btn btn-danger">Видалити</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Модальное окно для добавления новой услуги -->
        <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addServiceModalLabel">Додати нову послугу</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="newServiceTitle" class="form-control mb-2" placeholder="Назва послуги">
                        <input type="text" id="newServicePrice" class="form-control mb-2" placeholder="Ціна">
                        <textarea id="newServiceDetails" class="form-control mb-2" rows="4" placeholder="Деталі послуги"></textarea>
                        <div class="d-grid gap-2">
                            <button onclick="addService()" class="btn btn-primary">Додати</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div align="center"><h3>Усі замовлення</h3></div>
        <div class="container">
            <div align="center" class="user-list-container">
                <table>
                    <thead align="center">
                        <tr>
                            <th>Дата замовлення</th>
                            <th>Назва послуги</th>
                            <th>Ціна</th>
                            <th>Статус</th>
                            <th>Ім'я клієнта</th>
                            <th>Прізвище клієнта</th>
                            <th>Телефон клієнта</th>
                            <th>Адреса клієнта</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_date']) ?></td>
                                <td><?= htmlspecialchars($order['title']) ?></td>
                                <td><?= htmlspecialchars($order['price']) ?></td>
                                <td><?= htmlspecialchars($order['status']) ?></td>
                                <?php if (is_null($order['user_id'])): ?>
                                    <td colspan="2">Акаунт видалено</td>
                                    <td></td>
                                    <td></td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars($order['first_name']) ?></td>
                                    <td><?= htmlspecialchars($order['last_name']) ?></td>
                                    <td><?= htmlspecialchars($order['phone_number']) ?></td>
                                    <td><?= htmlspecialchars($order['adress']) ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div><br>
        <div align="center"><h3>Усі зверненя</h3></div>
        <div class="container">
            <div align="center" class="user-list-container">
                <table>
                    <thead align="center">
                        <tr>
                            <th>Дата зверненя</th>
                            <th>Текст заявки</th>
                            <th>Статус</th>
                            <th>Ім'я клієнта</th>
                            <th>Прізвище клієнта</th>
                            <th>Телефон клієнта</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appeals as $appeal): ?>
                            <tr>
                                <td><?= htmlspecialchars($appeal['appeal_date']) ?></td>
                                <td><?= htmlspecialchars($appeal['request_description']) ?></td>
                                <td><?= htmlspecialchars($appeal['status']) ?></td>
                                <?php if (is_null($appeal['user_id'])): ?>
                                    <td colspan="2">Акаунт видалено</td>
                                    <td></td>
                                    <td></td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars($appeal['first_name']) ?></td>
                                    <td><?= htmlspecialchars($appeal['last_name']) ?></td>
                                    <td><?= htmlspecialchars($appeal['phone_number']) ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div><br>
        <div align="center"><h3>Змінити пароль</h3></div>
            <div class="container">
                <form action="adminpanel.php" method="post">
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
        <footer></footer>
    <?php endif; ?>
    <script src="js/adminpanelUserEdit.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="js/adminpanelWorkerEdit.js"></script>

    <script src="js/adminpanelServicesEdit.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>
</html>
