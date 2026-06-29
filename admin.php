<?php
require_once 'functions.php';
require_once 'db.php';

// Проверяем авторизацию администратора
checkAdminAuth();

$pdo = getDatabase();

// Обработка действий администратора
$message = '';
$messageType = '';

// Удаление заявки
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    deleteApplication($pdo, $_GET['id']);
    $message = 'Заявка успешно удалена';
    $messageType = 'success';
}

// Редактирование заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $errors = validateUserData($_POST);
    
    if (empty($errors)) {
        updateUserData($pdo, $_POST['edit_id'], $_POST);
        $message = 'Данные успешно обновлены';
        $messageType = 'success';
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}

// Получаем данные
$applications = getAllApplications($pdo);
$languageStats = getLanguageStats($pdo);
$editingApp = null;

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editingApp = getApplicationById($pdo, $_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #b86b82, #e9a7b9);
            color: white;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        
        .stats-section {
            background: #fff5f7;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(243, 183, 201, 0.15);
        }
        
        .stat-language {
            font-weight: 600;
            color: #b86b82;
            font-size: 16px;
        }
        
        .stat-count {
            font-size: 24px;
            font-weight: bold;
            color: #5f4b53;
            margin-top: 5px;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(243, 183, 201, 0.1);
        }
        
        .admin-table th {
            background: #e9a7b9;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        
        .admin-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f5d8e0;
            font-size: 14px;
        }
        
        .admin-table tr:hover {
            background: #fffafb;
        }
        
        .action-btn {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s;
        }
        
        .edit-btn {
            background: #bf6984;
            color: white;
        }
        
        .edit-btn:hover {
            background: #a8556f;
        }
        
        .delete-btn {
            background: #d65c7d;
            color: white;
        }
        
        .delete-btn:hover {
            background: #c24466;
        }
        
        .message {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        
        .message.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        
        .edit-form {
            background: #fffafb;
            border: 2px solid #f5d8e0;
            border-radius: 20px;
            padding: 25px;
            margin: 20px 0;
        }
        
        .edit-form h2 {
            color: #b86b82;
            margin-top: 0;
        }
        
        .edit-form select[multiple] {
            height: 150px;
        }
        
        .cancel-btn {
            background: #9e9e9e;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="card" style="max-width: 1400px;">
        <div class="admin-header">
            <h1 style="color: white; margin: 0;">Панель администратора</h1>
            <p style="margin: 10px 0 0 0;">Управление заявками и просмотр статистики</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <!-- Статистика -->
        <div class="stats-section">
            <h2 style="color: #b86b82; margin-top: 0;">Статистика по языкам программирования</h2>
            <div class="stats-grid">
                <?php foreach ($languageStats as $stat): ?>
                    <div class="stat-item">
                        <div class="stat-language"><?= htmlspecialchars($stat['title']) ?></div>
                        <div class="stat-count"><?= $stat['count'] ?> чел.</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Форма редактирования -->
        <?php if ($editingApp): ?>
            <div class="edit-form">
                <h2>Редактирование заявки #<?= htmlspecialchars($editingApp['id']) ?></h2>
                <form method="POST" action="admin.php">
                    <input type="hidden" name="edit_id" value="<?= $editingApp['id'] ?>">
                    
                    <label>ФИО</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($editingApp['full_name']) ?>" required>
                    
                    <label>Телефон</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($editingApp['phone']) ?>" required>
                    
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($editingApp['email']) ?>" required>
                    
                    <label>Дата рождения</label>
                    <input type="date" name="birth_date" value="<?= htmlspecialchars($editingApp['birth_date']) ?>" required>
                    
                    <label>Пол</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="gender" value="male" <?= $editingApp['gender'] === 'male' ? 'checked' : '' ?>>
                            Мужской
                        </label>
                        <label>
                            <input type="radio" name="gender" value="female" <?= $editingApp['gender'] === 'female' ? 'checked' : '' ?>>
                            Женский
                        </label>
                    </div>
                    
                    <label>Любимые языки программирования</label>
                    <select name="languages[]" multiple>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM languages ORDER BY title");
                        $languages = $stmt->fetchAll();
                        
                        $selectedLanguages = [];
                        $stmt = $pdo->prepare("SELECT language_id FROM application_language WHERE application_id = ?");
                        $stmt->execute([$editingApp['id']]);
                        $selectedLanguages = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        foreach ($languages as $language):
                        ?>
                            <option value="<?= $language['id'] ?>" <?= in_array($language['id'], $selectedLanguages) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($language['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>Биография</label>
                    <textarea name="biography" rows="5"><?= htmlspecialchars($editingApp['biography']) ?></textarea>
                    
                    <label class="checkbox">
                        <input type="checkbox" name="agreement" value="1" <?= $editingApp['agreement'] ? 'checked' : '' ?>>
                        С контрактом ознакомлен(а)
                    </label>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit">Сохранить изменения</button>
                        <a href="admin.php" class="cancel-btn">Отмена</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Таблица заявок -->
        <h2 style="color: #b86b82;">Все заявки (<?= count($applications) ?>)</h2>
        
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ФИО</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Дата рождения</th>
                        <th>Пол</th>
                        <th>Языки</th>
                        <th>Логин</th>
                        <th>Создано</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 20px;">Заявок пока нет</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($app['id']) ?></td>
                                <td><?= htmlspecialchars($app['full_name']) ?></td>
                                <td><?= htmlspecialchars($app['phone']) ?></td>
                                <td><?= htmlspecialchars($app['email']) ?></td>
                                <td><?= htmlspecialchars($app['birth_date']) ?></td>
                                <td><?= $app['gender'] === 'male' ? 'М' : 'Ж' ?></td>
                                <td><?= htmlspecialchars($app['languages']) ?></td>
                                <td><?= htmlspecialchars($app['login']) ?></td>
                                <td><?= htmlspecialchars($app['created_at']) ?></td>
                                <td>
                                    <a href="admin.php?action=edit&id=<?= $app['id'] ?>" class="action-btn edit-btn">Ред.</a>
                                    <a href="admin.php?action=delete&id=<?= $app['id'] ?>" 
                                       class="action-btn delete-btn" 
                                       onclick="return confirm('Удалить заявку #<?= $app['id'] ?>?')">Удал.</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="bottom-link" style="margin-top: 30px;">
            <a href="index.php">Вернуться на главную</a>
        </div>
    </div>
</body>
</html>