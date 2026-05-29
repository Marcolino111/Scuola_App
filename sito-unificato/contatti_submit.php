<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';

// Crea la tabella contatti se non esiste
try {
    $pdo = getDBConnection();

    $pdo->exec("CREATE TABLE IF NOT EXISTS contatti (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        oggetto VARCHAR(255) NOT NULL,
        messaggio TEXT NOT NULL,
        data_invio DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore di connessione al database']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$oggetto = trim($_POST['oggetto'] ?? '');
$messaggio = trim($_POST['messaggio'] ?? '');

if ($nome === '' || $email === '' || $oggetto === '' || $messaggio === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tutti i campi sono obbligatori']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Indirizzo email non valido']);
    exit;
}

try {
    $sql = "INSERT INTO contatti (nome, email, oggetto, messaggio, data_invio)
            VALUES (:nome, :email, :oggetto, :messaggio, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':oggetto', $oggetto);
    $stmt->bindParam(':messaggio', $messaggio);
    $stmt->execute();

    $id = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'id' => (int)$id]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore durante il salvataggio del messaggio']);
    exit;
}
