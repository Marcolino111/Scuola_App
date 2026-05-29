<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';

try {
    $pdo = getDBConnection();

    $pdo->exec("CREATE TABLE IF NOT EXISTS documenti (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        cognome VARCHAR(255) NOT NULL,
        telefono VARCHAR(50) NOT NULL,
        residenza VARCHAR(500) NOT NULL,
        firma LONGBLOB,
        fototessera LONGBLOB,
        data_upload DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo json_encode(['success' => true, 'message' => 'Tabella creata o già esistente']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}