<?php
require_once __DIR__ . '/config.php';

$nombre = 'Administrador';
$email  = 'admin@panaderiamarket.com';
$pass   = 'Admin1234!';

$hash = password_hash($pass, PASSWORD_DEFAULT);

try {
    $stmt = db()->prepare("
        INSERT INTO usuarios (nombre, email, password_hash, tipo, created_at)
        VALUES (?, ?, ?, 'admin', NOW())
        ON DUPLICATE KEY UPDATE
            password_hash = VALUES(password_hash),
            tipo          = 'admin'
    ");
    $stmt->execute([$nombre, $email, $hash]);
    echo "<h2 style='font-family:sans-serif;color:green'>✅ Admin creado correctamente</h2>";
    echo "<p style='font-family:sans-serif'>Email: <strong>$email</strong><br>Contraseña: <strong>$pass</strong></p>";
    echo "<p style='font-family:sans-serif;color:red'><strong>⚠️ Borrá este archivo ahora.</strong></p>";
    echo "<p><a href='admin-login.php'>→ Ir al login admin</a></p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}