<?php
/**
 * Файл для создания администратора
 * Запустите один раз, затем удалите!
 */
require_once 'db.php';

$pdo = getDatabase();

// Создаем таблицу admins, если её нет
$pdo->exec("
    CREATE TABLE IF NOT EXISTS admins (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        login VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL
    )
");

// Пароль администратора
$login = 'admin';
$password = 'admin123';

// Создаем правильный хеш
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

echo "Логин: $login<br>";
echo "Пароль: $password<br>";
echo "Хеш: $passwordHash<br><br>";

// Проверяем, существует ли уже администратор
$stmt = $pdo->prepare("SELECT id FROM admins WHERE login = ?");
$stmt->execute([$login]);
$existing = $stmt->fetch();

if ($existing) {
    // Обновляем пароль существующего администратора
    $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE login = ?");
    $stmt->execute([$passwordHash, $login]);
    echo "Пароль администратора обновлен!<br>";
} else {
    // Создаем нового администратора
    $stmt = $pdo->prepare("INSERT INTO admins (login, password_hash) VALUES (?, ?)");
    $stmt->execute([$login, $passwordHash]);
    echo "Администратор создан!<br>";
}

// Проверяем, что хеш работает
$stmt = $pdo->prepare("SELECT * FROM admins WHERE login = ?");
$stmt->execute([$login]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password_hash'])) {
    echo "<br>✅ Проверка прошла успешно! Пароль работает.<br>";
    echo "Хеш в базе данных: " . $admin['password_hash'] . "<br>";
} else {
    echo "<br>❌ Ошибка проверки пароля!<br>";
}

echo "<br><strong>ВАЖНО: Удалите этот файл после использования!</strong>";
?>