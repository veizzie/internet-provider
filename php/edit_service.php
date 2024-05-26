<?php
// Проверка, был ли отправлен POST-запрос
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Подключение к базе данных
    $mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');

    // Проверка соединения
    if ($mysql->connect_error) {
        die("Ошибка подключения: " . $mysql->connect_error);
    }

    // Получение данных из POST-запроса
    $serviceId = $_POST["service_id"];
    $serviceTitle = $_POST["service_title"];
    $servicePrice = $_POST["service_price"];
    $serviceDetails = $_POST["service_details"];

    // Готовим SQL-запрос для обновления описания услуги
    $stmt = $mysql->prepare("UPDATE services SET title = ?, price = ?, service_details = ? WHERE service_id = ?");
    $stmt->bind_param("sdsi", $serviceTitle, $servicePrice, $serviceDetails, $serviceId);

    // Выполняем SQL-запрос
    if ($stmt->execute()) {
        // Если запрос выполнен успешно, перенаправляем обратно на страницу админской панели
        header('Location: ../adminpanel.php');
        exit();
    } else {
        // Если произошла ошибка при выполнении запроса, выводим сообщение об ошибке
        echo "Ошибка при обновлении описания услуги: " . $stmt->error;
    }

    // Закрываем соединение с базой данных
    $stmt->close();
    $mysql->close();
}
?>
