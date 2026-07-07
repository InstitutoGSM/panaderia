<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

function resp(bool $ok, string $msg = ''): void {
    echo json_encode(['ok' => $ok, 'msg' => $msg]); exit;
}

if (!esta_logueado()) resp(false, 'Necesitás iniciar sesión para calificar.');

$body      = json_decode(file_get_contents('php://input'), true);
$pid       = (int)($body['producto_id'] ?? 0);
$estrellas = (int)($body['estrellas']   ?? 0);
$uid       = (int)$_SESSION['user_id'];

if (!$pid || $estrellas < 1 || $estrellas > 5) resp(false, 'Datos inválidos.');

// Solo compradores pueden calificar
if (($_SESSION['user_tipo'] ?? '') !== 'comprador') {
    resp(false, 'Solo los compradores pueden calificar productos.');
}

// Verificar que el producto existe
$prod = db()->prepare("SELECT id FROM productos WHERE id = ? AND activo = 1");
$prod->execute([$pid]);
if (!$prod->fetch()) resp(false, 'Producto no encontrado.');

// si ya califico, actualiza; si no, inserta
$stmt = db()->prepare("
    INSERT INTO calificaciones (producto_id, comprador_id, estrellas, created_at)
    VALUES (?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE estrellas = VALUES(estrellas), created_at = NOW()
");
$stmt->execute([$pid, $uid, $estrellas]);

resp(true, 'Calificación guardada.');