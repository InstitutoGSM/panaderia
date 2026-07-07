<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autenticado']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

$uid  = $_SESSION['user_id'];
$tipo = $_SESSION['user_tipo'] ?? '';

if ($tipo !== 'vendedor') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'Solo vendedores pueden subir documentos']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/documentos/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$campos = [
    'doc1' => 'doc_bromatologia',
    'doc2' => 'doc_carnet_manipulador',
    'doc3' => 'doc_habilitacion_comercial',
];

$updates = [];
$errores = [];

foreach ($campos as $input => $columna) {
    if (!isset($_FILES[$input]) || $_FILES[$input]['error'] === UPLOAD_ERR_NO_FILE) {
        continue; // No enviado — no pisamos el que ya estaba
    }
    $file = $_FILES[$input];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errores[] = "Error al subir $input";
        continue;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        $errores[] = "El archivo $input supera los 5MB";
        continue;
    }

    $mime    = mime_content_type($file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    if (!in_array($mime, $allowed)) {
        $errores[] = "Tipo de archivo no permitido en $input";
        continue;
    }

    $ext      = ($mime === 'application/pdf') ? 'pdf' : pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre   = $uid . '_' . $input . '_' . time() . '.' . strtolower($ext);
    $destino  = $uploadDir . $nombre;

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        $errores[] = "No se pudo guardar el archivo $input";
        continue;
    }

    $updates[$columna] = 'uploads/documentos/' . $nombre;
}

if (!empty($errores)) {
    echo json_encode(['ok' => false, 'msg' => implode(' | ', $errores)]);
    exit;
}

if (empty($updates)) {
    echo json_encode(['ok' => false, 'msg' => 'No se recibió ningún archivo']);
    exit;
}

// Verificar que los 3 docs existan (combinando nuevos + existentes en BD)
$stmt = db()->prepare('SELECT doc_bromatologia, doc_carnet_manipulador, doc_habilitacion_comercial FROM usuarios WHERE id = ?');
$stmt->execute([$uid]);
$actual = $stmt->fetch();

$merged = array_merge(
    [
        'doc_bromatologia'          => $actual['doc_bromatologia'],
        'doc_carnet_manipulador'    => $actual['doc_carnet_manipulador'],
        'doc_habilitacion_comercial'=> $actual['doc_habilitacion_comercial'],
    ],
    $updates
);

$todosCompletos = $merged['doc_bromatologia']
    && $merged['doc_carnet_manipulador']
    && $merged['doc_habilitacion_comercial'];

if ($todosCompletos) {
    $updates['estado_verificacion'] = 'pendiente';
    $updates['doc_notas_rechazo']   = null;
}

// Construir el UPDATE dinámico
$sets   = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($updates)));
$vals   = array_values($updates);
$vals[] = $uid;

db()->prepare("UPDATE usuarios SET $sets WHERE id = ?")->execute($vals);

echo json_encode([
    'ok'      => true,
    'msg'     => $todosCompletos
        ? '¡Documentos enviados! Los revisaremos en 24–48hs 📬'
        : 'Documento guardado. Subí los 3 para enviar a revisión.',
    'estado'  => $todosCompletos ? 'pendiente' : 'sin_enviar',
    'urls'    => $updates,
]);