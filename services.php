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
    if ($userType == 2) {
        header('Location: /worker.php');
        exit();
    } elseif ($userType == 3) {
        header('Location: /adminpanel.php');
        exit();
    } elseif ($userType != 1) {
        // Если user_type не соответствует ни одному из известных типов, перенаправить на login.php
        header('Location: login.php');
        exit();
    }

    // Определение роли пользователя
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

// Получение данных об услугах
$stmt = $mysql->prepare("SELECT service_id, title, price, service_details FROM services");
$stmt->execute();
$result = $stmt->get_result();
$services = $result->fetch_all(MYSQLI_ASSOC);

// Закрытие соединения с базой данных
$stmt->close();
$mysql->close();
?>

<!DOCTYPE html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Послуги</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/services.css">
    <script>
        function confirmSubscription(serviceId) {
            console.log('Service ID:', serviceId);  // Вывод service_id в консоль
            if (confirm('Ви впевнені, що хочете підключити цей тариф?')) {
                window.location.href = 'php/subscribe.php?service_id=' + serviceId;
            }
        }
    </script>
</head>
<body>
    <?php if ($isUserLoggedIn): ?>
        <header>
            <div class="header-container d-flex flex-column align-items-center">
                <div class="welcome-text">
                    <p>Вітаємо, <?= htmlspecialchars($userName) ?>!</p>
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
            <h2 align="center">Наші послуги</h2>
            <div class="service-section">
                <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <div class="service-name"><?= htmlspecialchars($service['title']) ?></div>
                        <div class="service-price"><?= htmlspecialchars($service['price']) ?> грн</div>
                        <div class="service-description"><?= htmlspecialchars($service['service_details']) ?></div>
                        <button class="btn btn-success mt-2" onclick="showConfirmationModal(<?= $service['service_id'] ?>)">Підключити цей тариф</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <footer> 
            <p>Виникли якісь проблеми?</p>
            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#problemModal">Повідомити про проблему</button>
        </footer>
        <!-- Модальное окно для подтверждения -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalLabel">Підтвердження</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Ви впевнені, що хочете підключити цей тариф?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Відмінити</button>
                        <button type="button" class="btn btn-primary" onclick="confirmSubscription()">Підтвердити</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Модальное окно для ввода заявки -->
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
    </body>
    <!-- Скрипт Bootstrap и скрипт для открытия модального окна и отправки запроса при подтверждении -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <script src="js/selectService.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/callCenterSend.js"></script>
    <?php endif; ?>
</body>
</html>

