<?php
session_start();

// Проверка, установлена ли сессия
$isUserLoggedIn = isset($_SESSION['user_id']);
$userID = $isUserLoggedIn ? $_SESSION['user_id'] : '';
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

if (!$isUserLoggedIn) {
    header('Location: login.html');
    exit();
}

$mysql = new mysqli('localhost', 'root', 'root', 'provider', '8889');

if ($mysql->connect_error) {
    die("Ошибка подключения: " . $mysql->connect_error);
}

$stmt = $mysql->prepare("SELECT user_type FROM users WHERE user_id = ?");
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Получение имени пользователя по его ID
$stmt = $mysql->prepare("SELECT first_name FROM users WHERE user_id = ?");
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Проверка наличия данных пользователя
if ($userData) {
    $userName = $userData['first_name'];
} else {
    // Если данные пользователя не найдены, установить имя по умолчанию или перенаправить на страницу входа
    $userName = 'Невідомий користувач';
}


// Проверка наличия пользователя и его типа
if ($user) {
    $userType = $user['user_type'];

    // Перенаправление в зависимости от user_type
    if ($userType == 1) {
        header('Location: /home.php');
        exit();
    } elseif ($userType == 2) {
        header('Location: /worker.php');
        exit();
    } elseif ($userType == 3) {
        // Оставляем пользователя на этой странице, т.к. это админ панель
    } else {
        // Если user_type не соответствует ни одному из известных типов, перенаправить на login.html
        header('Location: login.html');
        exit();
    }

    $roles = [
        1 => 'Клієнт',
        2 => 'Працівник',
        3 => 'Адмін'
    ];
    $userRole = isset($roles[$userType]) ? $roles[$userType] : 'Невідома роль';
} else {
    // Если пользователь не найден, перенаправить на login.html
    header('Location: login.html');
    exit();
}

// Получение списка всех пользователей
$users = [];
$result = $mysql->query("SELECT user_id, first_name, last_name, user_type, phone_number, adress FROM users");
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
// Получение имени пользователя по его ID
$stmt = $mysql->prepare("SELECT first_name FROM users WHERE user_id = ?");
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Проверка наличия данных пользователя
if ($userData) {
    $userName = $userData['first_name'];
} else {
    // Если данные пользователя не найдены, установить имя по умолчанию или перенаправить на страницу входа
    $userName = 'Невідомий користувач';
}

$workers = [];
$result = $mysql->query("SELECT * FROM workers");
while ($row = $result->fetch_assoc()) {
    $workers[] = $row;
}

// Получение списка всех услуг
$services = [];
$result = $mysql->query("SELECT service_id, title, price, service_details FROM services");
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

// Закрытие соединения с базой данных
$stmt->close();
$mysql->close();
?>


<!DOCTYPE html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін панель</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/adminpanel.css">
</head>
<body>
    <?php if ($isUserLoggedIn): ?>
        <header>
            <div class="header-container">
                <div class="welcome-text">
                    <p>Вітаємо, <?= htmlspecialchars($userName) ?>!</p>
                    <p>Ваша роль: <?= htmlspecialchars($userRole) ?></p>
                </div>
            </div>
            <div align="right">
                <form action="validation/logout.php" method="post">
                    <button type="submit" class="btn btn-danger">
                        <img src="media/exit-icon.png" alt="exit icon">
                    </button>
                </form>
            </div>
        </header>
        <div align="center">
            <h1>Адмін панель</h1>
        </div>
        <div align="center"><h3>Усі користувачі</h3></div>
        <div class="container">
            <div align="center" class="user-list-container">
                <table>
                    <thead align="center">
                        <tr>
                            <th>Ім'я</th>
                            <th>Прізвище</th>
                            <th>Роль</th>
                            <th>Телефон</th>
                            <th>Адреса</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr onclick="selectUser(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['first_name']) ?>', '<?= htmlspecialchars($user['last_name']) ?>', <?= $user['user_type'] ?>, '<?= htmlspecialchars($user['phone_number']) ?>', '<?= htmlspecialchars($user['adress']) ?>')">
                                <td><?= htmlspecialchars($user['first_name']) ?></td>
                                <td><?= htmlspecialchars($user['last_name']) ?></td>
                                <td><?= htmlspecialchars($roles[$user['user_type']]) ?></td>
                                <td><?= htmlspecialchars($user['phone_number']) ?></td>
                                <td><?= htmlspecialchars($user['adress']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div><br>
        <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Управление пользователем</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="userFirstName" class="form-control mb-2" placeholder="Ім'я">
                        <input type="text" id="userLastName" class="form-control mb-2" placeholder="Прізвище">
                        <select id="userRole" class="form-select mb-2">
                            <option value="1">Клієнт</option>
                            <option value="2">Працівник</option>
                            <option value="3">Адмін</option>
                        </select>
                        <input type="text" id="userPhoneNumber" class="form-control mb-2" placeholder="Телефон">
                        <input type="text" id="userAdress" class="form-control mb-2" placeholder="Адреса">
                        <div class="modal-body">
                        <!-- Ваши инпуты для редактирования данных пользователя -->
                            <div class="d-grid gap-2">
                                <button onclick="editUser()" class="btn btn-primary">Змінити</button>
                                <button onclick="confirmDeleteUser()" class="btn btn-danger">Видалити</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div align="center"><h3>Усі працівники</h3></div>
        <div class="container">
            <div align="center" class="user-list-container">
                <table>
                    <thead align="center">
                        <tr>
                            <th>Ім'я</th>
                            <th>Прізвище</th>
                            <th>Телефон</th>
                            <th>Опис працівника</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workers as $worker): ?>
                            <tr onclick="selectWorker(<?= $worker['worker_id'] ?>, '<?= htmlspecialchars($worker['about_worker']) ?>')">
                                <td><?= htmlspecialchars($worker['first_name']) ?></td>
                                <td><?= htmlspecialchars($worker['last_name']) ?></td>
                                <td><?= htmlspecialchars($worker['phone_number']) ?></td>
                                <td><?= htmlspecialchars($worker['about_worker']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal fade" id="workerModal" tabindex="-1" aria-labelledby="workerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="workerModalLabel">Управління працівником</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea id="workerDescription" class="form-control" rows="4"></textarea>
                        <div class="d-grid gap-2">
                            <button type="button" onclick="editWorker()" class="btn btn-primary">Редагувати</button>
                        </div>
                    </div>
                </div>
            </div>
        </div><br>
        <div align="center"><h3>Послуги</h3></div>
        <div class="container">
            <div align="center" class="user-list-container">
                <table>
                    <thead align="center">
                        <tr>
                            <th>Назва послуги</th>
                            <th>Ціна</th>
                            <th>Деталі послуги</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr onclick="selectService(<?= $service['service_id'] ?>, '<?= htmlspecialchars($service['title']) ?>', '<?= htmlspecialchars($service['price']) ?>', '<?= htmlspecialchars($service['service_details']) ?>')">
                                <td><?= htmlspecialchars($service['title']) ?></td>
                                <td><?= htmlspecialchars($service['price']) ?></td>
                                <td><?= htmlspecialchars($service['service_details']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div><br>

        <!-- Модальное окно для редактирования услуг -->
        <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="serviceModalLabel">Управління послугою</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="serviceTitle" class="form-control mb-2" placeholder="Назва послуги">
                        <input type="text" id="servicePrice" class="form-control mb-2" placeholder="Ціна">
                        <textarea id="serviceDetails" class="form-control mb-2" rows="4" placeholder="Деталі послуги"></textarea>
                        <div class="d-grid gap-2">
                            <button onclick="editService()" class="btn btn-primary">Редагувати</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
    <script>
        let selectedUserId = null;

        function selectUser(userId, firstName, lastName, userRole, phoneNumber, adress) {
            selectedUserId = userId;
            document.getElementById('userFirstName').value = firstName;
            document.getElementById('userLastName').value = lastName;
            document.getElementById('userRole').value = userRole;
            document.getElementById('userPhoneNumber').value = phoneNumber;
            document.getElementById('userAdress').value = adress;
            $('#userModal').modal('show');
        }

        function editUser() {
            if (selectedUserId) {
                let firstName = document.getElementById('userFirstName').value;
                let lastName = document.getElementById('userLastName').value;
                let userRole = document.getElementById('userRole').value;
                let phoneNumber = document.getElementById('userPhoneNumber').value;
                let adress = document.getElementById('userAdress').value;

                let formData = new FormData();
                formData.append('user_id', selectedUserId);
                formData.append('first_name', firstName);
                formData.append('last_name', lastName);
                formData.append('user_type', userRole);
                formData.append('phone_number', phoneNumber);
                formData.append('adress', adress);

                fetch('php/edit_user.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    window.location.href = 'adminpanel.php';
                })
                .catch(error => {
                    console.error('There was an error!', error);
                });
            } else {
                alert('Будь ласка, оберіть користувача для редагування.');
            }
        }

        function confirmDeleteUser() {
        if (selectedUserId) {
            if (confirm('Ви впевнені, що хочете видалити цього користувача?')) {
                deleteUser();
            }
        } else {
            alert('Будь ласка, оберіть користувача для видалення.');
        }
    }

    function deleteUser() {
            if (selectedUserId) {
                if (confirm('Ви впевнені, що хочете видалити цього користувача?')) {
                    window.location.href = 'php/delete_user.php?id=' + selectedUserId;
                }
            } else {
                alert('Пожалуйста, выберите пользователя для удаления.');
            }
        }
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        let selectedWorkerId = null;

        function selectWorker(workerId, workerDescription) {
            selectedWorkerId = workerId;
            document.getElementById('workerDescription').value = workerDescription;
            $('#workerModal').modal('show');
        }

        function editWorker() {
            if (selectedWorkerId) {
                let newDescription = document.getElementById('workerDescription').value;
                let formData = new FormData();
                formData.append('worker_id', selectedWorkerId);
                formData.append('worker_description', newDescription);

                fetch('php/edit_worker.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log(response);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    console.log(data);
                    // Перезагружаем текущую страницу после успешного обновления
                    location.reload();
                })
                .catch(error => {
                    console.error('There was an error!', error);
                });
            } else {
                alert('Будь ласка, оберіть працівника для редагування.');
            }
        }
    </script>

    <script>
        let selectedServiceId = null;

        function selectService(serviceId, serviceTitle, servicePrice, serviceDetails) {
            selectedServiceId = serviceId;
            document.getElementById('serviceTitle').value = serviceTitle;
            document.getElementById('servicePrice').value = servicePrice;
            document.getElementById('serviceDetails').value = serviceDetails;
            $('#serviceModal').modal('show');
        }

        function editService() {
            if (selectedServiceId) {
                let serviceTitle = document.getElementById('serviceTitle').value;
                let servicePrice = document.getElementById('servicePrice').value;
                let serviceDetails = document.getElementById('serviceDetails').value;

                let formData = new FormData();
                formData.append('service_id', selectedServiceId);
                formData.append('service_title', serviceTitle);
                formData.append('service_price', servicePrice);
                formData.append('service_details', serviceDetails);

                fetch('php/edit_service.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    window.location.href = 'adminpanel.php';
                })
                .catch(error => {
                    console.error('There was an error!', error);
                });
            } else {
                alert('Будь ласка, оберіть послугу для редагування.');
            }
        }
    </script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>
</html>
