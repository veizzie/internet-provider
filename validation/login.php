<?php
session_start();  // Инициализация сессии

$login = filter_var(trim($_POST['login']), FILTER_SANITIZE_STRING);
$pass = filter_var(trim($_POST['pass']), FILTER_SANITIZE_STRING);

// Хеширование пароля перед проверкой в базе данных
$pass = md5($pass);

// Подключение к базе данных
$mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');

// Проверка соединения
if ($mysql->connect_error) {
    die("Ошибка подключения: " . $mysql->connect_error);
}

// Подготовленный запрос для безопасности
// Получение информации о пользователе на основе логина и пароля
$stmt = $mysql->prepare("SELECT user_id, user_type FROM users WHERE login = ? AND password = ?");
$stmt->bind_param("ss", $login, $pass);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Проверка наличия пользователя
if (!$user) {
    echo "Невірний логін або пароль";
    exit();
}

// Установка сессии с данными пользователя
session_start();
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['user_type'] = $user['user_type'];

// Перенаправление в зависимости от user_type
$userType = $user['user_type'];
$stmt->close();
$mysql->close();

if ($userType == 3) {
    header('Location: /adminpanel.php');
} elseif ($userType == 2) {
    header('Location: /worker.php');
} else {
    header('Location: /home.php');
}
exit();

?>
