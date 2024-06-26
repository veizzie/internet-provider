<?php
$login = filter_var(trim($_POST['login']), FILTER_SANITIZE_STRING);
$pass = filter_var(trim($_POST['pass']), FILTER_SANITIZE_STRING);
$name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
$last_name = filter_var(trim($_POST['last_name']), FILTER_SANITIZE_STRING);
$phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
$street = filter_var(trim($_POST['street']), FILTER_SANITIZE_STRING);
$address_number = filter_var(trim($_POST['address_number']), FILTER_SANITIZE_STRING);
$adress = 'вул.' . $street . ', ' . $address_number;

// Добавляем префикс +380 к номеру телефона
$phone = '+380' . $phone;

// Валидация
if (mb_strlen($login) < 5 || mb_strlen($login) > 25) {
    header('Location: /registration.html?error=Неприпустима довжина логіну (від 5 до 25 символів)');
    exit();
} else if (mb_strlen($pass) < 8 || mb_strlen($pass) > 25) {
    header('Location: /registration.html?error=Неприпустима довжина пароля (від 8 до 25 символів)');
    exit();
} else if (mb_strlen($name) < 2 || mb_strlen($name) > 45) {
    header('Location: /registration.html?error=Неприпустима довжина імені (від 2 до 45 символів)');
    exit();
} else if (mb_strlen($last_name) < 2 || mb_strlen($last_name) > 45) {
    header('Location: /registration.html?error=Неприпустима довжина фамілії (від 2 до 45 символів)');
    exit();
} else if (mb_strlen($phone) != 13) {
    header('Location: /registration.html?error=Неприпустима довжина номеру телефону (повинно бути 13 символів включно з +380)');
    exit();
} else if (mb_strlen($adress) < 8 || mb_strlen($adress) > 100) {
    header('Location: /registration.html?error=Неприпустима довжина адреси');
    exit();
}

// Соединение с базой данных
$mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');
if ($mysql->connect_error) {
    die('Ошибка подключения: ' . $mysql->connect_error);
}

// Проверка существующего логина
$result = $mysql->query("SELECT * FROM `users` WHERE `login` = '$login'");
if ($result->num_rows > 0) {
    $mysql->close();
    header('Location: /index.php?error=Логін вже існує');
    exit();
}

// Шифрование пароля
$pass = md5($pass);

// Вставка данных в базу данных
$mysql->query("INSERT INTO `users` (`login`, `password`, `first_name`, `last_name`, `phone_number`, `adress`) VALUES ('$login', '$pass', '$name', '$last_name', '$phone', '$adress')");

// Закрытие соединения с базой данных
$mysql->close();

// Перенаправление на страницу логина
header('Location: /login.php');
exit();
?>
