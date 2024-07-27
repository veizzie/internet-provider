<?php
// Подключение к базе данных
$mysql = new mysqli('localhost', 'root', 'root', 'provider', 8889);

// Проверка соединения
if ($mysql->connect_error) {
    die("Ошибка подключения: " . $mysql->connect_error);
}

// Проверка, что идентификатор услуги передан через POST-запрос
if (isset($_POST['service_id'])) {
    $service_id = intval($_POST['service_id']);

    // Удаление услуги из базы данных
    $sql = "DELETE FROM services WHERE service_id = ?";
    $stmt = $mysql->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $service_id);

        if ($stmt->execute()) {
            echo "Услуга успешно удалена.";
        } else {
            echo "Ошибка при выполнении запроса: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Ошибка при подготовке запроса: " . $mysql->error;
    }
} else {
    echo "Не указан идентификатор услуги.";
}

$mysql->close();
?>
