<?php

session_start();

require_once 'db.php';

$pdo = getDatabase();

$errors = [];

/* ПОЛЯ */

$full_name =
    trim($_POST['full_name'] ?? '');

$phone =
    trim($_POST['phone'] ?? '');

$email =
    trim($_POST['email'] ?? '');

$birth_date =
    trim($_POST['birth_date'] ?? '');

$gender =
    trim($_POST['gender'] ?? '');

$biography =
    trim($_POST['biography'] ?? '');

$agreement =
    isset($_POST['agreement']);

$languages =
    $_POST['languages'] ?? [];

/* ---------- ВАЛИДАЦИЯ ---------- */

/* ФИО */

if (
    empty($full_name) ||
    !preg_match(
        '/^[а-яА-Яa-zA-Z\s\-]{1,150}$/u',
        $full_name
    )
) {

    $errors['full_name'] =
        'Допустимы буквы, пробелы и дефис (до 150 символов)';
}

/* Телефон */

if (
    empty($phone) ||
    !preg_match(
        '/^[0-9+\-\s()]{5,30}$/',
        $phone
    )
) {

    $errors['phone'] =
        'Допустимы цифры, +, -, пробелы и скобки';
}

/* Email */

if (
    empty($email) ||
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    $errors['email'] =
        'Введите корректный email';
}

/* Дата */

if (empty($birth_date)) {

    $errors['birth_date'] =
        'Укажите дату рождения';
}

/* Пол */

if (
    !in_array(
        $gender,
        ['male', 'female']
    )
) {

    $errors['gender'] =
        'Выберите пол';
}

/* Языки */

if (
    empty($languages)
) {

    $errors['languages'] =
        'Выберите хотя бы один язык';
}

/* Биография */

if (
    !empty($biography) &&
    !preg_match(
        '/^[а-яА-Яa-zA-Z0-9\s.,!?()\-:;"]+$/u',
        $biography
    )
) {

    $errors['biography'] =
        'Биография содержит недопустимые символы';
}

/* Контракт */

if (!$agreement) {

    $errors['agreement'] =
        'Необходимо согласиться с контрактом';
}

/* ---------- COOKIE ИЗ ЛР4 ---------- */

$formData = [
    'full_name' => $full_name,
    'phone' => $phone,
    'email' => $email,
    'birth_date' => $birth_date,
    'gender' => $gender,
    'biography' => $biography,
    'agreement' => $agreement,
];

foreach ($formData as $key => $value) {

    setcookie(
        $key,
        $value,
        time() + 31536000,
        '/'
    );
}

setcookie(
    'languages',
    json_encode($languages),
    time() + 31536000,
    '/'
);

/* ---------- ЕСЛИ ОШИБКИ ---------- */

if (!empty($errors)) {

    foreach (
        $errors as $field => $error
    ) {

        setcookie(
            $field . '_error',
            $error,
            0,
            '/'
        );
    }

    header(
        'Location: index.php'
    );

    exit();
}

try {

    $pdo->beginTransaction();

    /* ---------- UPDATE ---------- */

    if (
        isset($_SESSION['user_id'])
    ) {

        $stmt = $pdo->prepare("
            UPDATE applications
            SET
                full_name = ?,
                phone = ?,
                email = ?,
                birth_date = ?,
                gender = ?,
                biography = ?,
                agreement = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $full_name,
            $phone,
            $email,
            $birth_date,
            $gender,
            $biography,
            $agreement ? 1 : 0,
            $_SESSION['user_id']
        ]);

        /* очищаем языки */

        $stmt = $pdo->prepare("
            DELETE FROM
            application_language
            WHERE application_id = ?
        ");

        $stmt->execute([
            $_SESSION['user_id']
        ]);

        /* заново вставляем */

        $stmt = $pdo->prepare("
            INSERT INTO
            application_language
            (
                application_id,
                language_id
            )
            VALUES (?, ?)
        ");

        foreach (
            $languages as $languageId
        ) {

            $stmt->execute([
                $_SESSION['user_id'],
                $languageId
            ]);
        }

        setcookie(
            'success',
            'Данные успешно обновлены!',
            0,
            '/'
        );

    } else {

        /* ---------- НОВЫЙ ПОЛЬЗОВАТЕЛЬ ---------- */

        $login =
            'user_' .
            random_int(
                1000,
                9999
            );

        $password =
            substr(
                bin2hex(
                    random_bytes(6)
                ),
                0,
                8
            );

        $passwordHash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        $stmt = $pdo->prepare("
            INSERT INTO applications
            (
                full_name,
                phone,
                email,
                birth_date,
                gender,
                biography,
                agreement,
                login,
                password_hash
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $full_name,
            $phone,
            $email,
            $birth_date,
            $gender,
            $biography,
            $agreement ? 1 : 0,
            $login,
            $passwordHash
        ]);

        $applicationId =
            $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO
            application_language
            (
                application_id,
                language_id
            )
            VALUES (?, ?)
        ");

        foreach (
            $languages as $languageId
        ) {

            $stmt->execute([
                $applicationId,
                $languageId
            ]);
        }

        /* показать 1 раз */

        setcookie(
            'generated_login',
            $login,
            0,
            '/'
        );

        setcookie(
            'generated_password',
            $password,
            0,
            '/'
        );

        setcookie(
            'success',
            'Регистрация успешно завершена!',
            0,
            '/'
        );
    }

    $pdo->commit();

} catch (Exception $e) {

    $pdo->rollBack();

    die(
        'Ошибка: ' .
        $e->getMessage()
    );
}

header(
    'Location: index.php'
);

exit();