<!DOCTYPE html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/register_and_login.css">
</head>
<body>
    <div align="center" class="container mt-5">
        <h1>Реєстрація</h1>
        <?php
            if (isset($_GET['error'])) {
                echo "<div class='alert alert-danger'>" . $_GET['error'] . "</div>";
            }
        ?>
        <form id="registrationForm" action="validation/register.php" method="post" onsubmit="return validateForm()">
            <input type="text" class="form-control" name="login" id="login" placeholder="Введіть логін">
            <span id="loginError" class="error-message"></span><br>
            
            <input type="password" class="form-control" name="pass" id="pass" placeholder="Введіть пароль">
            <span id="passError" class="error-message"></span><br>
            
            <input type="text" class="form-control" name="name" id="name" placeholder="Введіть ім'я">
            <span id="nameError" class="error-message"></span><br>
            
            <input type="text" class="form-control" name="last_name" id="l_name" placeholder="Введіть прізвище">
            <span id="l_nameError" class="error-message"></span><br>
            
            <div class="phone-group">
                <span class="phone-prefix">+380</span>
                <input type="text" class="form-control phone-input" name="phone" id="phone" placeholder="Введіть номер телефону">
            </div>
            <span id="phoneError" class="error-message"></span><br>

            <div class="address-group">
                <span class="address-prefix">вул.</span>
                <input type="text" class="form-control address-street" name="street" id="street" placeholder="Введіть назву вулиці">
                <input type="text" class="form-control address-number" name="address_number" id="address_number" placeholder="№ будинку чи квартири">
            </div>
            <span id="addressError" class="error-message"></span><br>
            
            <button class="btn btn-success" type="submit">Зареєструватись</button>
        </form>
    </div><br>
    <div align="center">
        <h6>Вже маєте акаунт?</h6>
        <a href="login.php">Логін</a>
    </div>
    <script src="js/registrationErrors.js"></script>
</body>
</html>
