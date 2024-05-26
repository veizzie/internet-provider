<?php
// Проверяем, был ли отправлен POST-запрос
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Подключаемся к базе данных
    $mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');

    // Проверяем соединение с базой данных
    if ($mysql->connect_error) {
        die("Ошибка подключения: " . $mysql->connect_error);
    }

    // Получаем данные из POST-запроса
    $workerId = $_POST["worker_id"];
    $workerDescription = $_POST["worker_description"];

    // Готовим SQL-запрос для обновления описания работника
    $stmt = $mysql->prepare("UPDATE workers SET about_worker = ? WHERE worker_id = ?");
    $stmt->bind_param("si", $workerDescription, $workerId);

    // Выполняем SQL-запрос
    if ($stmt->execute()) {
        // Если запрос выполнен успешно, перенаправляем обратно на страницу админской панели
        header('Location: ../adminpanel.php');
        exit();
    } else {
        // Если произошла ошибка при выполнении запроса, выводим сообщение об ошибке
        echo "Ошибка при обновлении описания работника: " . $stmt->error;
    }

    // Закрываем соединение с базой данных
    $stmt->close();
    $mysql->close();
}
?>
