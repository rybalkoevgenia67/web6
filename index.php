<?php

session_start();

require_once 'db.php';

$pdo = getDatabase();

$errors = [];
$values = [];

$formFields = [
    'full_name',
    'phone',
    'email',
    'birth_date',
    'gender',
    'biography',
    'agreement',
    'languages'
];

/* АВТОРИЗОВАН ПОЛЬЗОВАТЕЛЬ */

if (isset($_SESSION['user_id'])) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM applications
        WHERE id = ?
    ");

    $stmt->execute([
        $_SESSION['user_id']
    ]);

    $user = $stmt->fetch();

    if ($user) {

        $values = $user;

        $stmt = $pdo->prepare("
            SELECT language_id
            FROM application_language
            WHERE application_id = ?
        ");

        $stmt->execute([
            $_SESSION['user_id']
        ]);

        $values['languages'] =
            $stmt->fetchAll(
                PDO::FETCH_COLUMN
            );
    }

} else {

    /* COOKIE ИЗ ЛР4 */

    foreach ($formFields as $field) {

        $values[$field] =
            $_COOKIE[$field] ?? '';
    }
}

/* ОШИБКИ */

foreach ($formFields as $field) {

    $errorKey =
        $field . '_error';

    if (isset($_COOKIE[$errorKey])) {

        $errors[$field] =
            $_COOKIE[$errorKey];

        setcookie(
            $errorKey,
            '',
            time() - 3600,
            '/'
        );
    }
}

/* УСПЕХ */

$successMessage = '';

if (isset($_COOKIE['success'])) {

    $successMessage =
        $_COOKIE['success'];

    setcookie(
        'success',
        '',
        time() - 3600,
        '/'
    );
}

/* ЛОГИН/ПАРОЛЬ */

$generatedLogin =
    $_COOKIE['generated_login']
    ?? '';

$generatedPassword =
    $_COOKIE['generated_password']
    ?? '';

if ($generatedLogin) {

    setcookie(
        'generated_login',
        '',
        time() - 3600,
        '/'
    );

    setcookie(
        'generated_password',
        '',
        time() - 3600,
        '/'
    );
}

/* ЯЗЫКИ */

$stmt = $pdo->query("
    SELECT *
    FROM languages
    ORDER BY title
");

$languages =
    $stmt->fetchAll();

include 'form.php';