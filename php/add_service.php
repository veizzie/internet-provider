<?php
// Подключение к базе данных
$mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');

// Проверка соединения
if ($mysql->connect_error) {
    die("Ошибка подключения: " . $mysql->connect_error);
}

// Проверка наличия данных в POST
if (isset($_POST['title'], $_POST['price'], $_POST['service_details'])) {
    $title = $mysql->real_escape_string($_POST['title']);
    $price = $mysql->real_escape_string($_POST['price']);
    $details = $mysql->real_escape_string($_POST['service_details']);

    // Запрос на добавление новой услуги
    $sql = "INSERT INTO services (title, price, service_details) VALUES (?, ?, ?)";
    $stmt = $mysql->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sis", $title, $price, $details);

        if ($stmt->execute()) {
            echo "Нова послуга успішно додана.";
        } else {
            echo "Помилка при додаванні послуги: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Помилка при підготовці запиту: " . $mysql->error;
    }
} else {
    echo "Всі поля форми обов'язкові.";
}

$mysql->close();
?>
