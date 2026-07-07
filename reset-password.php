<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (esta_logueado()) { header('Location: ' . SITE_URL . '/'); exit; }

/* ════════════════════════════════════════════════════════════════════════════
   PROCESAR SOLICITUD DE RESET
   ════════════════════════════════════════════════════════════════════════════ */
$modo    = 'solicitar';
$ok_msg  = '';
$err_msg = '';

$token_get = trim($_GET['token'] ?? '');

// ── solicita reset ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$token_get) {
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err_msg = 'Ingresá un email válido.';
    } else {
        // Verifica si el usuario existe
        $u = db()->prepare("SELECT id, nombre FROM usuarios WHERE email = ? LIMIT 1");
        $u->execute([$email]);
        $usuario = $u->fetch();

        if ($usuario) {
            // Generar token seguro
            $token   = bin2hex(random_bytes(32));
            $expira  = date('Y-m-d H:i:s', time() + 3600);

            // Guardar 
            db()->prepare("
                DELETE FROM password_resets WHERE email = ?
            ")->execute([$email]);

            db()->prepare("
                INSERT INTO password_resets (email, token, expires_at, created_at)
                VALUES (?, ?, ?, NOW())
            ")->execute([$email, $token, $expira]);

            // link
            $link = SITE_URL . '/reset-password.php?token=' . $token;

            // ── enviar email ──
            $asunto  = '=?UTF-8?B?' . base64_encode('Recuperá tu contraseña — ' . SITE_NAME) . '?=';
            $cuerpo  = "Hola {$usuario['nombre']},\n\n"
                     . "Recibimos una solicitud para restablecer tu contraseña en " . SITE_NAME . ".\n\n"
                     . "Hacé clic en el siguiente link (válido por 1 hora):\n"
                     . $link . "\n\n"
                     . "Si no solicitaste esto, podés ignorar este mensaje.\n\n"
                     . "Saludos,\nEl equipo de " . SITE_NAME;
            $headers = "From: no-reply@panaderiamarket.com\r\n"
                     . "Content-Type: text/plain; charset=UTF-8\r\n";

            $enviado = @mail($email, $asunto, $cuerpo, $headers);
        }

        // En localhost mostramos el link directamente (mail() no funciona en XAMPP sin config)
        $es_local = str_contains(SITE_URL, 'localhost') || str_contains(SITE_URL, '127.0.0.1');

        if ($es_local && isset($link)) {
            $ok_msg = '__LOCAL__' . $link; 
        } else {
            $ok_msg = 'Si tu email está registrado, recibirás un link en los próximos minutos. Revisá también la carpeta de spam.';
        }
    }
}

/* ════════════════════════════════════════════════════════════════════════════
   PROCESAR NUEVA CONTRASEÑA
   ════════════════════════════════════════════════════════════════════════════ */
if ($token_get) {
    $modo = 'nueva';

    // Validar token
    $row = db()->prepare("
        SELECT pr.email, u.id AS user_id
        FROM   password_resets pr
        JOIN   usuarios        u  ON u.email = pr.email
        WHERE  pr.token = ? AND pr.expires_at > NOW()
        LIMIT  1
    ");
    $row->execute([$token_get]);
    $reset = $row->fetch();

    if (!$reset) {
        $err_msg = 'El link es inválido o ya expiró. Solicitá uno nuevo.';
        $modo    = 'solicitar';
        $token_get = '';
    }

    // POST con nueva contraseña
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
        $pass1 = $_POST['pass1'] ?? '';
        $pass2 = $_POST['pass2'] ?? '';

        if (strlen($pass1) < 8) {
            $err_msg = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif ($pass1 !== $pass2) {
            $err_msg = 'Las contraseñas no coinciden.';
        } else {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);

            db()->prepare("
                UPDATE usuarios SET password_hash = ? WHERE id = ?
            ")->execute([$hash, $reset['user_id']]);

            db()->prepare("
                DELETE FROM password_resets WHERE token = ?
            ")->execute([$token_get]);

            // Redirigir al login con mensaje de exito
            $_SESSION['flash_ok'] = '✅ Contraseña actualizada. Ya podés iniciar sesión.';
            header('Location: ' . SITE_URL . '/login.php'); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    <?= $modo === 'nueva' ? 'Nueva contraseña' : 'Recuperar contraseña' ?>
    — <?= SITE_NAME ?>
  </title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/global.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/login.css">
</head>
<body>

<div class="login-wrap" style="grid-template-columns:1fr">
  <div class="login-col">

    <div class="logo-wrap">
      <div class="logo-circle">
        <img src="<?= SITE_URL ?>/assets/logo.png" alt="Logo"
             onerror="this.style.display='none'">
      </div>
      <div class="logo-nombre">
        Panaderia<span>PUMA</span>
      </div>
      <p class="logo-tagline">
        <?= $modo === 'nueva' ? 'Creá tu nueva contraseña' : 'Recuperar contraseña' ?>
      </p>
    </div>

    <!-- ══ MENSAJES ══════════════════════════════════════════════════════════ -->
    <?php if ($err_msg): ?>
      <div class="alert alert-err"><?= h($err_msg) ?></div>
    <?php endif; ?>

    <?php if ($ok_msg && !str_starts_with($ok_msg, '__LOCAL__')): ?>
      <div class="alert alert-ok"><?= h($ok_msg) ?></div>
    <?php endif; ?>

    <?php
    // Entorno local: mostrar link directamente en pantalla
    if ($ok_msg && str_starts_with($ok_msg, '__LOCAL__')):
        $dev_link = substr($ok_msg, 9);
    ?>
      <div class="alert alert-ok">
        <strong>Modo desarrollo (localhost):</strong><br>
        El servidor no envía emails. Usá este link para continuar:<br><br>
        <a href="<?= h($dev_link) ?>" style="word-break:break-all;color:var(--naranja)">
          <?= h($dev_link) ?>
        </a>
      </div>
    <?php endif; ?>

    <!-- ══ SOLICITAR RESET ════════════════════════════════════════ -->
    <?php if ($modo === 'solicitar' && !$ok_msg): ?>
      <p style="color:var(--gris);font-size:0.9rem;margin-bottom:20px;text-align:center">
        Ingresá tu email y te enviamos un link para restablecer tu contraseña.
      </p>
      <form method="POST" novalidate>
        <div class="field">
          <label for="email">Email de tu cuenta</label>
          <input type="email" id="email" name="email"
                 value="<?= h($_POST['email'] ?? '') ?>"
                 placeholder="tu@email.com"
                 autocomplete="email" required autofocus>
        </div>
        <button type="submit" class="btn btn-naranja btn-full" style="margin-top:4px">
          Enviar link de recuperación
        </button>
      </form>

      <p style="text-align:center;margin-top:20px;font-size:0.88rem;color:var(--gris)">
        <a href="<?= SITE_URL ?>/login.php" style="color:var(--naranja)">
          ← Volver al login
        </a>
      </p>

    <!-- ══ mostrar confirmacion ════════════════════════ -->
    <?php elseif ($modo === 'solicitar' && $ok_msg): ?>
      <p style="text-align:center;margin-top:16px;font-size:0.88rem;color:var(--gris)">
        <a href="<?= SITE_URL ?>/login.php" style="color:var(--naranja)">
          ← Volver al login
        </a>
      </p>

    <!-- ══ NUEVA CONTRASEÑA ═══════════════════════════════════════ -->
    <?php elseif ($modo === 'nueva' && $reset): ?>
      <p style="color:var(--gris);font-size:0.9rem;margin-bottom:20px;text-align:center">
        Elegí una nueva contraseña para
        <strong><?= h($reset['email']) ?></strong>.
      </p>
      <form method="POST" action="<?= SITE_URL ?>/reset-password.php?token=<?= h($token_get) ?>"
            novalidate id="form-nueva">
        <div class="field">
          <label for="pass1">Nueva contraseña</label>
          <div class="pass-wrap">
            <input type="password" id="pass1" name="pass1"
                   placeholder="Mínimo 8 caracteres"
                   autocomplete="new-password" required>
            <button type="button" class="pass-toggle" aria-label="Mostrar"
                    onclick="togglePass('pass1',this)">👁</button>
          </div>
          <!-- Barra de fuerza -->
          <div class="pass-strength" id="pass-strength" style="margin-top:6px">
            <div class="pass-bar" id="pass-bar"></div>
          </div>
          <span class="pass-hint" id="pass-hint"></span>
        </div>

        <div class="field">
          <label for="pass2">Repetir contraseña</label>
          <div class="pass-wrap">
            <input type="password" id="pass2" name="pass2"
                   placeholder="Repetí la contraseña"
                   autocomplete="new-password" required>
            <button type="button" class="pass-toggle" aria-label="Mostrar"
                    onclick="togglePass('pass2',this)">👁</button>
          </div>
          <span id="match-hint" style="font-size:0.78rem"></span>
        </div>

        <button type="submit" class="btn btn-naranja btn-full"
                style="margin-top:4px" id="btn-guardar">
          Guardar nueva contraseña
        </button>
      </form>
    <?php endif; ?>

  </div>
</div>

<div id="toast-box"></div>

<script>
/* ── Mostrar/ocultar contraseña ─────────────────────────────────────────── */
function togglePass(id, btn) {
  const inp = document.getElementById(id);
  const show = inp.type === 'password';
  inp.type   = show ? 'text' : 'password';
  btn.textContent = show ? '🙈' : '👁';
}

/* ── Barra de fuerza ────────────────────────────────────────────────────── */
const pass1 = document.getElementById('pass1');
if (pass1) {
  pass1.addEventListener('input', () => {
    const v     = pass1.value;
    const bar   = document.getElementById('pass-bar');
    const hint  = document.getElementById('pass-hint');
    let score   = 0;
    if (v.length >= 8)            score++;
    if (/[A-Z]/.test(v))          score++;
    if (/[0-9]/.test(v))          score++;
    if (/[^A-Za-z0-9]/.test(v))   score++;

    const niveles = [
      { lbl: '',          color: '#eee',        width: '0%'   },
      { lbl: 'Muy débil', color: '#ef5350',      width: '25%'  },
      { lbl: 'Débil',     color: '#ff7043',      width: '50%'  },
      { lbl: 'Buena',     color: '#ffa726',      width: '75%'  },
      { lbl: 'Fuerte',    color: '#66bb6a',      width: '100%' },
    ];
    const n = niveles[Math.min(score, 4)];
    bar.style.width      = v.length ? n.width  : '0%';
    bar.style.background = n.color;
    hint.textContent     = v.length ? n.lbl    : '';
    hint.style.color     = n.color;
  });
}

/* ── Validar coincidencia ───────────────────────────────────────────────── */
const pass2 = document.getElementById('pass2');
if (pass2) {
  pass2.addEventListener('input', () => {
    const span = document.getElementById('match-hint');
    if (!pass2.value) { span.textContent = ''; return; }
    const igual = pass1.value === pass2.value;
    span.textContent = igual ? '✓ Coinciden' : '✗ No coinciden';
    span.style.color = igual ? 'var(--verde)' : '#ef5350';
  });
}

/* ── Validación antes de submit ─────────────────────────────────────────── */
document.getElementById('form-nueva')?.addEventListener('submit', e => {
  const p1 = document.getElementById('pass1').value;
  const p2 = document.getElementById('pass2').value;
  if (p1.length < 8) {
    e.preventDefault();
    alert('La contraseña debe tener al menos 8 caracteres.');
    return;
  }
  if (p1 !== p2) {
    e.preventDefault();
    alert('Las contraseñas no coinciden.');
  }
});
</script>

</body>
</html>