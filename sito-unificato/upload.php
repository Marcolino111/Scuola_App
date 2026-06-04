<?php
header('Content-Type: application/json; charset=utf-8');

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

$firmaPath = null;
if (isset($_FILES['firma']) && $_FILES['firma']['error'] === UPLOAD_ERR_OK) {
    $firmaPath = $_FILES['firma']['tmp_name'];
}

$fotoPath = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fotoPath = $_FILES['foto']['tmp_name'];
}

$id = uniqid('doc-');

$to = 'info@trybox.it';
$subject = 'Documentazione per rinnovo Patente';

$boundary = md5(time());

$headers = "From: noreply@autoscuolads.it\r\n";
$headers .= "Reply-To: noreply@autoscuolads.it\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

$body = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
$body .= "Nuova documentazione caricata:\n\n";
$body .= "Nome: $nome\n";
$body .= "Cognome: $cognome\n";
$body .= "Telefono: $telefono\n";
$body .= "Residenza: $residenza\n\n";
$body .= "Data upload: " . date('d/m/Y H:i') . "\n";
$body .= "ID Ricevuta: $id\n";

if ($firmaPath && file_exists($firmaPath)) {
    $firmaContent = chunk_split(base64_encode(file_get_contents($firmaPath)));
    $firmaName = $_FILES['firma']['name'];
    $body .= "\r\n--$boundary\r\n";
    $body .= "Content-Type: image/jpeg; name=\"$firmaName\"\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$firmaName\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= $firmaContent;
}

if ($fotoPath && file_exists($fotoPath)) {
    $fotoContent = chunk_split(base64_encode(file_get_contents($fotoPath)));
    $fotoName = $_FILES['foto']['name'];
    $body .= "\r\n--$boundary\r\n";
    $body .= "Content-Type: image/jpeg; name=\"$fotoName\"\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$fotoName\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= $fotoContent;
}

$body .= "\r\n--$boundary--";

try {
    $sent = mail($to, $subject, $body, $headers);
    if ($sent) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Invio email fallito']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore: ' . $e->getMessage()]);
}