<?php

session_start();

require_once 'db.php';

$pdo = getDatabase();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login =
        trim($_POST['login'] ?? '');

    $password =
        trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("
        SELECT *
        FROM applications
        WHERE login = ?
    ");

    $stmt->execute([
        $login
    ]);

    $user =
        $stmt->fetch();

    if (
        $user &&
        password_verify(
            $password,
            $user['password_hash']
        )
    ) {

        $_SESSION['user_id'] =
            $user['id'];

        header(
            'Location: index.php'
        );

        exit();

    } else {

        $error =
            'Неверный логин или пароль';
    }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>

<meta charset="UTF-8">

<title>Вход</title>

<link rel="stylesheet"
      href="style.css">

</head>

<body>

<div class="card">

<h1>Вход</h1>

<p class="author">
Проект выполнила:
Рыбалко Евгения
</p>

<?php if (!empty($error)): ?>

<div class="message">

<?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>

<form method="POST">

<label>

Логин

</label>

<input
type="text"
name="login"
required
>

<label>

Пароль

</label>

<input
type="password"
name="password"
required
>

<button type="submit">

Войти

</button>

</form>

<div class="bottom-link">

<a href="index.php">

Назад к форме

</a>

</div>

</div>

</body>
</html>