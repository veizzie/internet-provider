<!DOCTYPE html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Логін</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/register_and_login.css">
</head>
<body>
    <div align="center" class="container mt-5">
        <h1>Логін</h1>
        <?php
            if (isset($_GET['error'])) {
                echo "<div class='alert alert-danger'>" . $_GET['error'] . "</div>";
            }
        ?>
        <form action="validation/login.php" method="post" onsubmit="return validateLoginForm()">
            <input type="text" class="form-control" name="login" id="login" placeholder="Введіть логін">
            <span id="loginError" class="error-message"></span><br>
            
            <input type="password" class="form-control" name="pass" id="pass" placeholder="Введіть пароль">
            <span id="passError" class="error-message"></span><br>
            
            <button class="btn btn-success" type="submit">Увійти</button>
        </form>
    </div><br>
    <div align="center">
        <h6>Ще не маєте акаунту?</h6>
        <a href="index.php">Реєстрація</a>
    </div>

    <script src="js/loginErrors.js"></script>
</body>
</html>
