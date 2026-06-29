<?php

$selectedLanguages =
    $values['languages'] ?? [];

if (!is_array($selectedLanguages)) {

    $selectedLanguages =
        explode(
            ',',
            $selectedLanguages
        );
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Лабораторная работа №5
    </title>

    <link rel="stylesheet"
          href="style.css">

</head>

<body>

<div class="card">

    <h1>

        <?php if (isset($_SESSION['user_id'])): ?>

            Редактирование анкеты

        <?php else: ?>

            Анкета пользователя

        <?php endif; ?>

    </h1>

    <p class="author">

        Проект выполнила:
        Рыбалко Евгения

    </p>

    <!-- УСПЕШНО -->

    <?php if (!empty($successMessage)): ?>

        <div class="success">

            <?= htmlspecialchars(
                $successMessage
            ) ?>

        </div>

    <?php endif; ?>

    <!-- ЛОГИН И ПАРОЛЬ -->

    <?php if (!empty($generatedLogin)): ?>

        <div class="credentials">

            <h3>

                Ваши данные для входа

            </h3>

            <p>

                <strong>Логин:</strong>

                <?= htmlspecialchars(
                    $generatedLogin
                ) ?>

            </p>

            <p>

                <strong>Пароль:</strong>

                <?= htmlspecialchars(
                    $generatedPassword
                ) ?>

            </p>

            <p class="hint">

                Сохраните логин и пароль.
                Они показываются только один раз.

            </p>

        </div>

    <?php endif; ?>

    <!-- ВХОД / ВЫХОД -->

    <div class="auth-links">

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="logout.php">

                Выйти из аккаунта

            </a>

        <?php else: ?>

            <a href="login.php">

                Войти в аккаунт

            </a>

        <?php endif; ?>

    </div>

    <!-- ФОРМА -->

    <form
        action="submit.php"
        method="POST"
    >

        <!-- ФИО -->

        <label>

            ФИО

        </label>

        <input
            type="text"
            name="full_name"

            value="<?= htmlspecialchars(
                $values['full_name'] ?? ''
            ) ?>"

            class="<?= isset($errors['full_name'])
                ? 'error'
                : '' ?>"
        >

        <?php if (isset($errors['full_name'])): ?>

            <div class="message">

                <?= htmlspecialchars(
                    $errors['full_name']
                ) ?>

            </div>

        <?php endif; ?>

        <!-- ТЕЛЕФОН -->

        <label>

            Телефон

        </label>

        <input
            type="tel"
            name="phone"

            value="<?= htmlspecialchars(
                $values['phone'] ?? ''
            ) ?>"

            class="<?= isset($errors['phone'])
                ? 'error'
                : '' ?>"
        >

        <?php if (isset($errors['phone'])): ?>

            <div class="message">

                <?= htmlspecialchars(
                    $errors['phone']
                ) ?>

            </div>

        <?php endif; ?>

        <!-- EMAIL -->

        <label>

            Email

        </label>

        <input
            type="email"
            name="email"

            value="<?= htmlspecialchars(
                $values['email'] ?? ''
            ) ?>"

            class="<?= isset($errors['email'])
                ? 'error'
                : '' ?>"
        >

        <?php if (isset($errors['email'])): ?>

            <div class="message">

                <?= htmlspecialchars(
                    $errors['email']
                ) ?>

            </div>

        <?php endif; ?>

        <!-- ДАТА -->

        <label>

            Дата рождения

        </label>

        <input
            type="date"
            name="birth_date"

            value="<?= htmlspecialchars(
                $values['birth_date'] ?? ''
            ) ?>"

            class="<?= isset($errors['birth_date'])
                ? 'error'
                : '' ?>"
        >

        <?php if (isset($errors['birth_date'])): ?>

            <div class="message">

                <?= htmlspecialchars(
                    $errors['birth_date']
                ) ?>

            </div>

        <?php endif; ?>

        <!-- ПОЛ -->

        <label>

            Пол

        </label>

        <div class="radio-group">

            <label>

                <input
                    type="radio"
                    name="gender"
                    value="male"

                    <?= ($values['gender'] ?? '')
                        === 'male'
                        ? 'checked'
                        : '' ?>
                >

                Мужской

            </label>

            <label>

                <input
                    type="radio"
                    name="gender"
                    value="female"

                    <?= ($values['gender'] ?? '')
                        === 'female'
                        ? 'checked'
                        : '' ?>
                >

                Женский

            </label>

        </div>

        <?php if (isset($errors['gender'])): ?>

            <div class="message">

                <?= htmlspecialchars(
                    $errors['gender']
                ) ?>

            </div>

        <?php endif; ?>

        <!-- ЯЗЫКИ -->

        <label>

            Любимые языки программирования

        </label>

        <select
            name="languages[]"
            multiple
        >

            <?php foreach ($languages as $language): ?>

                <option
                    value="<?= $language['id'] ?>"

                    <?= in_array(
                        $language['id'],
                        $selectedLanguages
                    )
                    ? 'selected'
                    : '' ?>
                >

                    <?= htmlspecialchars(
                        $language['title']
                    ) ?>

                </option>

            <?php endforeach; ?>

        </select>

        <?php if (isset($errors['languages'])): ?>

            <div class="message">

                <?= htmlspecialchars(
                    $errors['languages']
                ) ?>

            </div>

        <?php endif; ?>

        <!-- БИО -->

        <label>

            Биография

        </label>

        <textarea
            name="biography"
            rows="5"

            class="<?= isset($errors['biography'])
                ? 'error'
                : '' ?>"
        ><?= htmlspecialchars(
            $values['biography'] ?? ''
        ) ?></textarea>

        <?php if (isset($errors['biography'])): ?>

            <div class="message">

                <?= htmlspecialchars(
                    $errors['biography']
                ) ?>

            </div>

        <?php endif; ?>

        <!-- СОГЛАСИЕ -->

        <label class="checkbox">

            <input
                type="checkbox"
                name="agreement"

                <?= !empty(
                    $values['agreement']
                )
                    ? 'checked'
                    : '' ?>
            >

            С контрактом ознакомлен(а)

        </label>

        <?php if (isset($errors['agreement'])): ?>

            <div class="message">

                <?= htmlspecialchars(
                    $errors['agreement']
                ) ?>

            </div>

        <?php endif; ?>

        <button type="submit">

            <?php if (isset($_SESSION['user_id'])): ?>

                Сохранить изменения

            <?php else: ?>

                Сохранить

            <?php endif; ?>

        </button>

    </form>

    <div class="bottom-link">

        <a href="view.php">

            Просмотреть заявки

        </a>

    </div>

</div>

</body>
</html>