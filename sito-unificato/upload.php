<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';

try {
    $pdo = getDBConnection();
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
$cognome = trim($_POST['cognome'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$residenza = trim($_POST['residenza'] ?? '');
$privacy = $_POST['privacy'] ?? '';

if ($nome === '' || $cognome === '' || $telefono === '' || $residenza === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tutti i campi anagrafici sono obbligatori']);
    exit;
}

if ($privacy !== 'on') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Devi accettare il trattamento dei dati personali']);
    exit;
}

$firmaBlob = null;
if (isset($_FILES['firma']) && $_FILES['firma']['error'] === UPLOAD_ERR_OK) {
    $firmaBlob = file_get_contents($_FILES['firma']['tmp_name']);
}

$fotoBlob = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fotoBlob = file_get_contents($_FILES['foto']['tmp_name']);
}

try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO documenti (nome, cognome, telefono, residenza, firma, fototessera, data_upload)
            VALUES (:nome, :cognome, :telefono, :residenza, :firma, :fototessera, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':cognome', $cognome);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':residenza', $residenza);
    $stmt->bindParam(':firma', $firmaBlob, PDO::PARAM_LOB);
    $stmt->bindParam(':fototessera', $fotoBlob, PDO::PARAM_LOB);
    $stmt->execute();

    $id = $pdo->lastInsertId();

    $pdo->commit();

    $to = 'admin@trybox.it';
    $subject = 'Nuova documentazione ricevuta - ID #' . $id;
    $body = "Nuova documentazione caricata:\n\n";
    $body .= "Nome: $nome\n";
    $body .= "Cognome: $cognome\n";
    $body .= "Telefono: $telefono\n";
    $body .= "Residenza: $residenza\n\n";
    $body .= "Data upload: " . date('d/m/Y H:i') . "\n";
    $body .= "ID Ricevuta: $id\n";
    $headers = "From: noreply@autoscuolads.it\r\n";
    $headers .= "Reply-To: noreply@autoscuolads.it\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    mail($to, $subject, $body, $headers);

    echo json_encode(['success' => true, 'id' => (int)$id]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore durante il salvataggio: ' . $e->getMessage()]);
    exit;
}