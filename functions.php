<?php

/**
 * Валидация данных пользователя
 */
function validateUserData($data)
{
    $errors = [];

    // ФИО
    if (
        empty($data['full_name']) ||
        !preg_match('/^[а-яА-Яa-zA-Z\s\-]{1,150}$/u', $data['full_name'])
    ) {
        $errors['full_name'] = 'Допустимы буквы, пробелы и дефис (до 150 символов)';
    }

    // Телефон
    if (
        empty($data['phone']) ||
        !preg_match('/^[0-9+\-\s()]{5,30}$/', $data['phone'])
    ) {
        $errors['phone'] = 'Допустимы цифры, +, -, пробелы и скобки';
    }

    // Email
    if (
        empty($data['email']) ||
        !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
    ) {
        $errors['email'] = 'Введите корректный email';
    }

    // Дата рождения
    if (empty($data['birth_date'])) {
        $errors['birth_date'] = 'Укажите дату рождения';
    }

    // Пол
    if (!in_array($data['gender'] ?? '', ['male', 'female'])) {
        $errors['gender'] = 'Выберите пол';
    }

    // Языки
    if (empty($data['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык';
    }

    // Биография
    if (
        !empty($data['biography']) &&
        !preg_match('/^[а-яА-Яa-zA-Z0-9\s.,!?()\-:;"]+$/u', $data['biography'])
    ) {
        $errors['biography'] = 'Биография содержит недопустимые символы';
    }

    // Согласие
    if (empty($data['agreement'])) {
        $errors['agreement'] = 'Необходимо согласиться с контрактом';
    }

    return $errors;
}

/**
 * Сохранение данных в куки
 */
function saveToCookies($data)
{
    $cookieFields = ['full_name', 'phone', 'email', 'birth_date', 'gender', 'biography', 'agreement'];
    
    foreach ($cookieFields as $field) {
        setcookie($field, $data[$field] ?? '', time() + 31536000, '/');
    }
    
    setcookie('languages', json_encode($data['languages'] ?? []), time() + 31536000, '/');
}

/**
 * Обновление данных пользователя
 */
function updateUserData($pdo, $userId, $data)
{
    $stmt = $pdo->prepare("
        UPDATE applications 
        SET full_name = ?, phone = ?, email = ?, birth_date = ?, 
            gender = ?, biography = ?, agreement = ? 
        WHERE id = ?
    ");
    
    $stmt->execute([
        $data['full_name'],
        $data['phone'],
        $data['email'],
        $data['birth_date'],
        $data['gender'],
        $data['biography'],
        $data['agreement'] ? 1 : 0,
        $userId
    ]);
    
    // Обновляем языки
    $stmt = $pdo->prepare("DELETE FROM application_language WHERE application_id = ?");
    $stmt->execute([$userId]);
    
    $stmt = $pdo->prepare("INSERT INTO application_language (application_id, language_id) VALUES (?, ?)");
    foreach ($data['languages'] as $languageId) {
        $stmt->execute([$userId, $languageId]);
    }
}

/**
 * Создание нового пользователя
 */
function createUser($pdo, $data)
{
    $login = 'user_' . random_int(1000, 9999);
    $password = substr(bin2hex(random_bytes(6)), 0, 8);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, agreement, login, password_hash)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['full_name'],
        $data['phone'],
        $data['email'],
        $data['birth_date'],
        $data['gender'],
        $data['biography'],
        $data['agreement'] ? 1 : 0,
        $login,
        $passwordHash
    ]);
    
    $applicationId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO application_language (application_id, language_id) VALUES (?, ?)");
    foreach ($data['languages'] as $languageId) {
        $stmt->execute([$applicationId, $languageId]);
    }
    
    return [
        'login' => $login,
        'password' => $password
    ];
}

/**
 * Получение статистики по языкам
 */
function getLanguageStats($pdo)
{
    $stmt = $pdo->query("
        SELECT l.title, COUNT(al.application_id) as count
        FROM languages l
        LEFT JOIN application_language al ON l.id = al.language_id
        GROUP BY l.id, l.title
        ORDER BY count DESC, l.title
    ");
    
    return $stmt->fetchAll();
}

/**
 * Получение всех заявок
 */
function getAllApplications($pdo)
{
    $stmt = $pdo->query("
        SELECT a.*, GROUP_CONCAT(l.title SEPARATOR ', ') AS languages
        FROM applications a
        LEFT JOIN application_language al ON a.id = al.application_id
        LEFT JOIN languages l ON al.language_id = l.id
        GROUP BY a.id
        ORDER BY a.id DESC
    ");
    
    return $stmt->fetchAll();
}

/**
 * Получение заявки по ID
 */
function getApplicationById($pdo, $id)
{
    $stmt = $pdo->prepare("
        SELECT a.*, GROUP_CONCAT(l.title SEPARATOR ', ') AS languages
        FROM applications a
        LEFT JOIN application_language al ON a.id = al.application_id
        LEFT JOIN languages l ON al.language_id = l.id
        WHERE a.id = ?
        GROUP BY a.id
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Удаление заявки
 */
function deleteApplication($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->execute([$id]);
}

/**
 * Проверка авторизации администратора
 */
function checkAdminAuth()
{
    if (
        !isset($_SERVER['PHP_AUTH_USER']) ||
        !isset($_SERVER['PHP_AUTH_PW'])
    ) {
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        die('Требуется авторизация администратора');
    }
    
    require_once 'db.php';
    $pdo = getDatabase();
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE login = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch();
    
    if (
        !$admin ||
        !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])
    ) {
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        die('Неверный логин или пароль администратора');
    }
    
    return true;
}