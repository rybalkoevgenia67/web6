<?php

session_start();

require_once 'db.php';

$pdo = getDatabase();

$stmt = $pdo->query("
    SELECT
        a.id,
        a.full_name,
        a.phone,
        a.email,
        a.birth_date,
        a.gender,
        a.biography,
        a.agreement,
        a.created_at,

        GROUP_CONCAT(
            l.title
            SEPARATOR ', '
        ) AS languages

    FROM applications a

    LEFT JOIN application_language al
        ON a.id = al.application_id

    LEFT JOIN languages l
        ON al.language_id = l.id

    GROUP BY a.id

    ORDER BY a.id DESC
");

$applications =
    $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="ru">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>

Сохраненные заявки

</title>

<link rel="stylesheet"
      href="style.css">

</head>

<body>

<div class="card big-card">

<h1>

Сохраненные заявки

</h1>

<p class="author">

Проект выполнила:
Рыбалко Евгения

</p>

<?php if (empty($applications)): ?>

<p>

Пока заявок нет

</p>

<?php else: ?>

<div class="applications-grid">

<?php foreach ($applications as $app): ?>

<div class="application-item">

<div class="app-id">

#<?= htmlspecialchars($app['id']) ?>

</div>

<h3>

<?= htmlspecialchars(
    $app['full_name']
) ?>

</h3>

<p>

<strong>Телефон:</strong>

<?= htmlspecialchars(
    $app['phone']
) ?>

</p>

<p>

<strong>Email:</strong>

<?= htmlspecialchars(
    $app['email']
) ?>

</p>

<p>

<strong>Дата рождения:</strong>

<?= htmlspecialchars(
    $app['birth_date']
) ?>

</p>

<p>

<strong>Пол:</strong>

<?= $app['gender'] === 'male'
    ? 'Мужской'
    : 'Женский' ?>

</p>

<p>

<strong>Языки:</strong>

<?= htmlspecialchars(
    $app['languages']
) ?>

</p>

<p>

<strong>Биография:</strong>

<br>

<?= nl2br(
    htmlspecialchars(
        $app['biography']
    )
) ?>

</p>

<p>

<strong>Согласие:</strong>

<?= $app['agreement']
    ? 'Да'
    : 'Нет' ?>

</p>

<div class="date">

<?= htmlspecialchars(
    $app['created_at']
) ?>

</div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

<div class="bottom-link">

<a href="index.php">

Вернуться к форме

</a>

</div>

</div>

</body>
</html>