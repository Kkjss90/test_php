<?php
global $dbh;
include 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $captcha = $_POST['smart-token'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://captcha-api.yandex.ru/validate');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'secret' => 'ysc2_721cHKPkwk49gspQjiSFMmT5ff4tbU334lMCbeGj810848af',
        'token' => $captcha
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!$response['status']) {
        $errors[] = 'Ошибка капчи.';
    }

    if (empty($errors)) {
        if (!is_integer($login)){
            $query = "SELECT * FROM users WHERE email = '$login'";
            $result = pg_query($dbh, $query);
        } else{
            $query = "SELECT * FROM users WHERE email = $1 OR phone = $2";
            $result = pg_query_params($dbh, $query, [$login, $login]);
        }

        if (!$result) {
            die('Ошибка выполнения запроса: ' . pg_last_error());
        }

        $user = pg_fetch_assoc($result);
        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            header('Location: profile.php');
            exit();
        } else {
            $errors[] = 'Неправильный логин или пароль.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <script src="https://captcha-api.yandex.ru/captcha.js" defer></script>
</head>
<body>
<h2>Авторизация</h2>

<?php if (!empty($errors)) : ?>
    <ul>
        <?php foreach ($errors as $error) : ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="POST">
    <label>Email или телефон: <input type="text" name="login" required></label><br>
    <label>Пароль: <input type="password" name="password" required></label><br>
    <div
            id="captcha-container"
            class="smart-captcha"
            data-sitekey="ysc1_721cHKPkwk49gspQjiSFQ9muLRZEGNjFwes7FvPEbe0505fc"
    ></div>
    <input type="hidden" name="smart-token">
    <button type="submit">Войти</button>
</form>
<button onclick="document.location='register.php'">Регистрация</button>
</body>
</html>
