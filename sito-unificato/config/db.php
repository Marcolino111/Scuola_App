<?php
define('DB_HOST', 'sql.trybox.it');
define('DB_NAME', 'tryboxit80396');
define('DB_USER', 'tryboxit80396');
define('DB_PASS', 'tryb15351');

function getDBConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
    return $pdo;
}