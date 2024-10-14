<?php
global $dbh;
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM users WHERE id=$user_id";
$result = pg_query($dbh, $query);

if (!$result) {
    die('Ошибка выполнения запроса: ' . pg_last_error());
}

$user = pg_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $new_password = $_POST['new_password'];
    $new_password_confirm = $_POST['new_password_confirm'];

    if ($new_password && $new_password !== $new_password_confirm) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (empty($errors)) {
        $query = "UPDATE users SET name = $1, email = $2, phone = $3 WHERE id = $4";
        $result = pg_query_params($dbh, $query, [$name, $email, $phone, $user_id]);
        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error());
        }

        if ($new_password) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $query = "UPDATE users SET password = $1 WHERE id = $2";
            $result = pg_query_params($dbh, $query, [$hashed_password, $user_id]);
            if (!$result) {
                die('Ошибка выполнения запроса: ' . pg_last_error());
            }
        }

        header('Location: profile.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
</head>
<body>
<h2>Ваш профиль</h2>

<?php if (!empty($errors)) : ?>
    <ul>
        <?php foreach ($errors as $error) : ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="POST">
    <label>Имя: <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required></label><br>
    <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></label><br>
    <label>Телефон: <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"
                           required></label><br>
    <label>Новый пароль: <input type="password" name="new_password"></label><br>
    <label>Повторите новый пароль: <input type="password" name="new_password_confirm"></label><br>
    <button type="submit">Сохранить изменения</button>
</form>
<button type="button" onclick="document.location='logout.php'">Выход</button>
</body>
</html>
