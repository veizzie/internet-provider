<?php
session_start(); // Стартуем сессию, если она ещё не начата

// Очищаем данные сессии
$_SESSION = array();

// Если используется cookie для хранения идентификатора сессии, удаляем и его
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на страницу входа
header('Location: /login.html');
exit();
?>
