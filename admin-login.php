<?php
require_once __DIR__ . '/config.php';

// Si ya es admin, redirigir al panel
if (esta_logueado() && ($_SESSION['user_tipo'] ?? '') === 'admin') {
    header('Location: ' . SITE_URL . '/admin.php'); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['pass']  ?? '');

    if ($email && $pass) {
        $stmt = db()->prepare("
            SELECT id, nombre, password, tipo
            FROM   usuarios
            WHERE  email = ? AND tipo = 'admin'
            LIMIT  1
        ");
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if ($u && password_verify($pass, $u['password_hash'])) {
            $_SESSION['user_id']     = $u['id'];
            $_SESSION['user_nombre'] = $u['nombre'];
            $_SESSION['user_tipo']   = 'admin';
            header('Location: ' . SITE_URL . '/admin.php'); exit;
        } else {
            $error = 'Credenciales incorrectas.';
        }
    } else {
        $error = 'Completá ambos campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso Admin — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/global.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/login.css">
  <style>
    body { background: var(--marron); }
    .admin-login-wrap {
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
      padding: 24px;
    }
    .admin-login-card {
      background: var(--blanco);
      border-radius: var(--radio-lg);
      padding: 40px 36px;
      width: 100%; max-width: 400px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.25);
    }
    .admin-brand {
      text-align: center;
      font-family: 'Playfair Display', serif;
      font-size: 1.5rem;
      font-weight: 900;
      color: var(--marron);
      margin-bottom: 6px;
    }
    .admin-brand span { color: var(--naranja); }
    .admin-sub {
      text-align: center;
      font-size: 0.82rem;
      color: var(--gris);
      margin-bottom: 28px;
    }
  </style>
</head>
<body>
<div class="admin-login-wrap">
  <div class="admin-login-card">
    <div class="admin-brand">🥖 Panaderia<span>PUMA</span></div>
    <p class="admin-sub">Panel de administración — acceso restringido</p>

    <?php if ($error): ?>
      <div class="alert alert-err" style="margin-bottom:18px">
        ⚠️ <?= h($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="field">
        <label for="email">Email de administrador</label>
        <input type="email" id="email" name="email"
               value="<?= h($_POST['email'] ?? '') ?>"
               placeholder="admin@panaderiamarket.com"
               autocomplete="email" required>
      </div>
      <div class="field">
        <label for="pass">Contraseña</label>
        <div class="pass-wrap">
          <input type="password" id="pass" name="pass"
                 placeholder="••••••••"
                 autocomplete="current-password" required>
          <button type="button" class="pass-toggle" aria-label="Mostrar contraseña"
                  onclick="const i=document.getElementById('pass');
                           i.type=i.type==='password'?'text':'password';
                           this.textContent=i.type==='password'?'👁':'🙈'">👁</button>
        </div>
      </div>
      <button type="submit" class="btn btn-naranja btn-full" style="margin-top:8px">
        Ingresar al panel
      </button>
    </form>
  </div>
</div>
</body>
</html>