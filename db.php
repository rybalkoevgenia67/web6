<?php

if (!function_exists('getDatabase')) {

    function getDatabase() {

        static $pdo = null;

        if ($pdo === null) {

            $host = 'localhost';

            /* ТВОЯ БД */
            $dbname = 'u82673';

            /* ЛОГИН MYSQL */
            $user = 'u82673';

            /* ПАРОЛЬ MYSQL */
            $password = '4038561';

            try {

                $pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $password
                );

                $pdo->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION
                );

                $pdo->setAttribute(
                    PDO::ATTR_DEFAULT_FETCH_MODE,
                    PDO::FETCH_ASSOC
                );

            } catch (PDOException $e) {

                die(
                    'Ошибка подключения: ' .
                    $e->getMessage()
                );
            }
        }

        return $pdo;
    }
}