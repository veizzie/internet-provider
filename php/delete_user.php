<?php
// Подключение к базе данных
$mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');

// Проверка соединения
if ($mysql->connect_error) {
    die("Ошибка подключения: " . $mysql->connect_error);
}

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Начало транзакции
    $mysql->begin_transaction();

    try {
        // Удаление связанных записей в таблице workers
        $stmt = $mysql->prepare("DELETE FROM workers WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // Удаление записи из таблицы users
        $stmt = $mysql->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // Подтверждение транзакции
        $mysql->commit();
    } catch (Exception $e) {
        // Откат транзакции в случае ошибки
        $mysql->rollback();
        die("Ошибка при удалении пользователя: " . $e->getMessage());
    }
}

$mysql->close();

header('Location: ../adminpanel.php');
exit();
?>
