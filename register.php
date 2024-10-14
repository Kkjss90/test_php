<?php
global $dbh;
include 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_repeat'];
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $query = "SELECT * FROM users WHERE email = $1 OR phone = $2";
    $result = pg_query_params($dbh, $query, [$email, $phone]);

    if (pg_num_rows($result) > 0) {
        $result_array = pg_fetch_all($result);
        for ($i = 0; $i < count($result_array); $i++) {
            if ($result_array[$i]["email"] === $email) {
                $errors[] = "Email уже используется.";
            } elseif ($result_array[$i]["phone"] === $phone) {
                $errors[] = 'Телефон уже используется.';
            }
        }
    }

    if ($password !== $password_confirm) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (empty($errors)) {
        $query = "INSERT INTO users (name, email, phone, password) VALUES ($1, $2, $3, $4)";
        $result = pg_query_params($dbh, $query, [$name, $email, $phone, $hashed_password]);

        if ($result) {
            header('Location: login.php');
            exit();
        } else {
            $errors[] = 'Ошибка при регистрации: ' . pg_last_error($dbh);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
</head>
<body>
<h2>Регистрация</h2>
<?php if (!empty($errors)) : ?>
    <ul>
        <?php foreach ($errors as $error) : ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="POST">
    <label>Имя: <input type="text" name="name" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Телефон: <input type="text" name="phone" required></label><br>
    <label>Пароль: <input type="password" name="password" required></label><br>
    <label>Повторите пароль: <input type="password" name="password_repeat" required></label><br>
    <button type="submit">Зарегистрироваться</button>
</form>
<button onclick="document.location='login.php'">Вход</button>
</body>
</html>
