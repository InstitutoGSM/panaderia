<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

// Solo admin
if (!esta_logueado() || ($_SESSION['user_tipo'] ?? '') !== 'admin') {
    header('Location: ' . SITE_URL . '/admin-login.php'); exit;
}

// ── Acciones POST (aprobar / rechazar / corregir) ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = $_POST['accion'] ?? '';
    $vid    = (int)($_POST['vendedor_id'] ?? 0);

    if (!$vid) { echo json_encode(['ok' => false, 'msg' => 'ID inválido']); exit; }

    switch ($accion) {
        case 'aprobar':
            db()->prepare("
                UPDATE usuarios
                SET estado_verificacion = 'aprobado', doc_notas_rechazo = NULL
                WHERE id = ? AND tipo = 'vendedor'
            ")->execute([$vid]);
            echo json_encode(['ok' => true, 'msg' => 'Vendedor aprobado ✅']);
            break;

        case 'rechazar':
            db()->prepare("
                UPDATE usuarios
                SET estado_verificacion = 'rechazado'
                WHERE id = ? AND tipo = 'vendedor'
            ")->execute([$vid]);
            echo json_encode(['ok' => true, 'msg' => 'Vendedor rechazado ❌']);
            break;

        case 'corregir':
            $notas = trim($_POST['notas'] ?? '');
            if (!$notas) { echo json_encode(['ok' => false, 'msg' => 'Escribí un mensaje']); exit; }
            db()->prepare("
                UPDATE usuarios
                SET estado_verificacion = 'sin_enviar', doc_notas_rechazo = ?
                WHERE id = ? AND tipo = 'vendedor'
            ")->execute([$notas, $vid]);
            echo json_encode(['ok' => true, 'msg' => 'Corrección registrada ✏️']);
            break;

        default:
            echo json_encode(['ok' => false, 'msg' => 'Acción desconocida']);
    }
    exit;
}

// ── Cargar vendedores ─────────────────────────────────────────────────────
$vendedores = db()->query("
    SELECT id, nombre, nombre_panaderia, email, email_contacto,
           avatar_url, estado_verificacion, doc_notas_rechazo,
           doc_bromatologia, doc_carnet_manipulador, doc_habilitacion_comercial,
           created_at
    FROM   usuarios
    WHERE  tipo = 'vendedor'
    ORDER  BY
      FIELD(estado_verificacion,'pendiente','sin_enviar','rechazado','aprobado'),
      created_at DESC
")->fetchAll();

// ── Stats ─────────────────────────────────────────────────────────────────
$stats = ['pendiente' => 0, 'aprobado' => 0, 'rechazado' => 0, 'sin_enviar' => 0];
foreach ($vendedores as $v) {
    $e = $v['estado_verificacion'] ?? 'sin_enviar';
    if (isset($stats[$e])) $stats[$e]++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Admin — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/global.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/admin.css">
</head>
<body>

<!-- ══ NAVBAR ADMIN ════════════════════════════════════════════════════════ -->
<nav class="admin-navbar">
  <div class="admin-navbar-brand">
    🥖 Panaderia<span>PUMA</span> — Admin
  </div>
  <div style="display:flex;align-items:center;gap:12px">
    <span style="color:rgba(255,255,255,0.6);font-size:0.82rem">
      <?= h($_SESSION['user_nombre'] ?? 'Admin') ?>
    </span>
    <a href="<?= SITE_URL ?>/logout.php"
       class="btn btn-ghost btn-sm"
       style="border-color:rgba(255,255,255,0.3);color:white">
      Salir 🚪
    </a>
  </div>
</nav>

<!-- ══ CONTENIDO ═══════════════════════════════════════════════════════════ -->
<div class="admin-wrap">

  <div style="margin-bottom:24px">
    <h1 style="font-family:'Playfair Display',serif">Panel de Administración</h1>
    <p style="color:var(--gris)">Verificación de vendedores y documentos</p>
  </div>

  <!-- Stats -->
  <div class="admin-stats">
    <div class="admin-stat">
      <div class="num" id="st-pendientes"><?= $stats['pendiente'] ?></div>
      <div class="lbl">Pendientes</div>
    </div>
    <div class="admin-stat">
      <div class="num" id="st-aprobados"><?= $stats['aprobado'] ?></div>
      <div class="lbl">Aprobados</div>
    </div>
    <div class="admin-stat">
      <div class="num" id="st-rechazados"><?= $stats['rechazado'] ?></div>
      <div class="lbl">Rechazados</div>
    </div>
    <div class="admin-stat">
      <div class="num" id="st-sin-enviar"><?= $stats['sin_enviar'] ?></div>
      <div class="lbl">Sin documentos</div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="admin-filtros">
    <button class="filtro on" data-estado="todos">Todos</button>
    <button class="filtro" data-estado="pendiente">🕐 Pendientes</button>
    <button class="filtro" data-estado="aprobado">✅ Aprobados</button>
    <button class="filtro" data-estado="rechazado">❌ Rechazados</button>
    <button class="filtro" data-estado="sin_enviar">📂 Sin docs</button>
  </div>

  <!-- Lista -->
  <div id="lista-vendedores">
    <?php foreach ($vendedores as $v):
      $estado    = $v['estado_verificacion'] ?? 'sin_enviar';
      $nom_pan   = $v['nombre_panaderia'] ?: $v['nombre'];
      $email_cnt = $v['email_contacto'] ?: $v['email'];
      $docs      = [
          'Habilitación Bromatológica'   => $v['doc_bromatologia']           ?? null,
          'Carnet Manipulador Alimentos' => $v['doc_carnet_manipulador']     ?? null,
          'Habilitación Comercial'       => $v['doc_habilitacion_comercial'] ?? null,
      ];
    ?>
      <div class="vendedor-card" data-estado="<?= h($estado) ?>">

        <!-- Cabecera -->
        <div class="vendedor-card-header">
          <div class="vendedor-avatar">
            <?php if ($v['avatar_url']): ?>
              <img src="<?= h($v['avatar_url']) ?>" alt="<?= h($nom_pan) ?>">
            <?php else: ?>
              <?= iniciales($nom_pan) ?>
            <?php endif; ?>
          </div>
          <div class="vendedor-info">
            <div class="vendedor-nombre"><?= h($nom_pan) ?></div>
            <div class="vendedor-email"><?= h($email_cnt) ?></div>
            <div class="vendedor-fecha">
              Registrado: <?= date('d/m/Y H:i', strtotime($v['created_at'])) ?>
            </div>
          </div>
          <span class="estado-badge-admin estado-<?= h($estado) ?>">
            <?= estado_label($estado) ?>
          </span>
        </div>

        <!-- Documentos -->
        <div class="vendedor-docs">
          <?php foreach ($docs as $label => $url): ?>
            <div class="doc-item">
              <div class="doc-label">📄 <?= h($label) ?></div>
              <?php if ($url): ?>
                <?php $es_img = preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $url); ?>
                <?php if ($es_img): ?>
                  <img src="<?= h($url) ?>"
                       alt="<?= h($label) ?>"
                       class="doc-preview">
                <?php else: ?>
                  <div style="font-size:0.78rem;color:var(--gris);margin-bottom:6px">
                    Archivo PDF
                  </div>
                <?php endif; ?>
                <a href="<?= h($url) ?>" target="_blank" class="ver-doc">
                  🔍 Ver completo
                </a>
              <?php else: ?>
                <div class="doc-vacio">Sin subir</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Notas de rechazo/correccion -->
        <?php if ($v['doc_notas_rechazo']): ?>
          <div style="padding:10px 20px;background:var(--crema);
                      border-top:1px solid var(--crema-dark);
                      font-size:0.82rem;color:var(--marron)">
            <strong>Nota enviada al vendedor:</strong>
            <?= h($v['doc_notas_rechazo']) ?>
          </div>
        <?php endif; ?>

        <!-- Acciones -->
        <div class="vendedor-acciones">
          <?php if ($estado !== 'aprobado'): ?>
            <button class="btn btn-verde btn-sm btn-accion"
                    data-accion="aprobar"
                    data-vid="<?= $v['id'] ?>"
                    data-nombre="<?= h($nom_pan) ?>">
              ✅ Aprobar
            </button>
          <?php endif; ?>

          <?php if ($estado !== 'rechazado'): ?>
            <button class="btn btn-sm btn-accion"
                    style="background:#FFEBEE;color:#C62828;border:none"
                    data-accion="rechazar"
                    data-vid="<?= $v['id'] ?>"
                    data-nombre="<?= h($nom_pan) ?>">
              ❌ Rechazar
            </button>
          <?php endif; ?>

          <button class="btn btn-ghost btn-sm btn-accion"
                  data-accion="corregir"
                  data-vid="<?= $v['id'] ?>"
                  data-nombre="<?= h($nom_pan) ?>"
                  data-email="<?= h($email_cnt) ?>">
            ✏️ Pedir corrección
          </button>

          <?php if ($email_cnt): ?>
            <a href="mailto:<?= h($email_cnt) ?>"
               class="btn btn-ghost btn-sm"
               style="margin-left:auto">
              ✉️ Contactar
            </a>
          <?php endif; ?>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <div id="empty-vendedores"
       style="display:none;text-align:center;padding:60px 0;color:var(--gris)">
    No hay vendedores que coincidan con el filtro.
  </div>

</div>

<!-- ══ MODAL CORRECCIÓN ════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-corregir" style="display:none"
     role="dialog" aria-modal="true" aria-labelledby="modal-titulo">
  <div class="modal-box">
    <h3 id="modal-titulo">✏️ Solicitar corrección</h3>
    <p style="font-size:0.88rem;color:var(--gris);margin-bottom:14px">
      Escribí un mensaje explicando a
      <strong id="modal-nombre-vendedor"></strong>
      qué debe corregir.
    </p>
    <textarea id="modal-mensaje"
              placeholder="Ej: El Carnet sanitario no se ve claramente, por favor resubí la imagen con mejor resolución..."
              rows="5"></textarea>
    <div class="modal-acciones">
      <button class="btn btn-naranja" id="btn-enviar-correccion">
        📧 Registrar y abrir email
      </button>
      <button class="btn btn-ghost" id="btn-cerrar-modal">Cancelar</button>
    </div>
  </div>
</div>

<div id="toast-box"></div>

<script>
const SITE_URL = '<?= SITE_URL ?>';
let vendedorSeleccionado = null;

/* ══ FILTROS ════════════════════════════════════════════════════════════════ */
document.querySelectorAll('.admin-filtros .filtro').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.admin-filtros .filtro').forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
    const estado = btn.dataset.estado;
    let visible  = 0;

    document.querySelectorAll('.vendedor-card').forEach(card => {
      const mostrar = estado === 'todos' || card.dataset.estado === estado;
      card.style.display = mostrar ? '' : 'none';
      if (mostrar) visible++;
    });

    document.getElementById('empty-vendedores').style.display =
      visible === 0 ? 'block' : 'none';
  });
});

/* ══ ACCIONES ═══════════════════════════════════════════════════════════════ */
document.querySelectorAll('.btn-accion').forEach(btn => {
  btn.addEventListener('click', () => {
    const accion = btn.dataset.accion;
    const vid    = btn.dataset.vid;
    const nombre = btn.dataset.nombre;

    if (accion === 'corregir') {
      vendedorSeleccionado = { vid, nombre, email: btn.dataset.email };
      document.getElementById('modal-nombre-vendedor').textContent = nombre;
      document.getElementById('modal-mensaje').value = '';
      document.getElementById('modal-corregir').style.display = 'flex';
      return;
    }

    const confirmar = accion === 'aprobar'
      ? `¿Aprobar a "${nombre}"? Sus productos serán visibles en el catálogo.`
      : `¿Rechazar a "${nombre}"? No podrá publicar productos.`;

    if (!confirm(confirmar)) return;
    ejecutarAccion(accion, vid, null, btn.closest('.vendedor-card'));
  });
});

/* ══ MODAL ══════════════════════════════════════════════════════════════════ */
document.getElementById('btn-cerrar-modal').addEventListener('click', () => {
  document.getElementById('modal-corregir').style.display = 'none';
  vendedorSeleccionado = null;
});

document.getElementById('modal-corregir').addEventListener('click', e => {
  if (e.target === e.currentTarget) {
    document.getElementById('modal-corregir').style.display = 'none';
    vendedorSeleccionado = null;
  }
});

document.getElementById('btn-enviar-correccion').addEventListener('click', async () => {
  const mensaje = document.getElementById('modal-mensaje').value.trim();
  if (!mensaje) { toast('Escribí un mensaje para el vendedor', 'err'); return; }
  if (!vendedorSeleccionado) return;

  const btn = document.getElementById('btn-enviar-correccion');
  btn.disabled = true; btn.textContent = 'Guardando...';

  const card = document.querySelector(
    `.vendedor-card [data-vid="${vendedorSeleccionado.vid}"][data-accion="aprobar"]`
  )?.closest('.vendedor-card');

  const ok = await ejecutarAccion('corregir', vendedorSeleccionado.vid, mensaje, card);

  if (ok && vendedorSeleccionado.email) {
    const asunto = encodeURIComponent('PanaderiaMarket — Corrección de documentos requerida');
    const cuerpo = encodeURIComponent(
      `Hola ${vendedorSeleccionado.nombre},\n\n` +
      `Revisamos tu documentación y encontramos lo siguiente:\n\n` +
      `${mensaje}\n\n` +
      `Por favor corregí los documentos e iniciá sesión en PanaderiaMarket ` +
      `para volver a subirlos desde "Mis Documentos".\n\n` +
      `Saludos,\nEquipo PanaderiaMarket`
    );
    window.open(
      `mailto:${vendedorSeleccionado.email}?subject=${asunto}&body=${cuerpo}`,
      '_blank'
    );
  }

  document.getElementById('modal-corregir').style.display = 'none';
  btn.disabled = false; btn.textContent = '📧 Registrar y abrir email';
  vendedorSeleccionado = null;
});

/* ══ FETCH ACCION ════════════════════════════════════════════════════════════ */
async function ejecutarAccion(accion, vid, notas, card) {
  const fd = new FormData();
  fd.append('accion', accion);
  fd.append('vendedor_id', vid);
  if (notas) fd.append('notas', notas);

  try {
    const res  = await fetch('<?= SITE_URL ?>/admin.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.ok) {
      toast(data.msg, 'ok');
      if (card) {
        const nuevoEstado = accion === 'aprobar'   ? 'aprobado'
                          : accion === 'rechazar'  ? 'rechazado'
                          : 'sin_enviar';
        card.dataset.estado = nuevoEstado;
        const badge = card.querySelector('.estado-badge-admin');
        if (badge) {
          badge.className = `estado-badge-admin estado-${nuevoEstado}`;
          badge.textContent = { aprobado:'Aprobado', rechazado:'Rechazado', sin_enviar:'Sin documentos' }[nuevoEstado];
        }
        actualizarStats();
      }
      return true;
    } else {
      toast(data.msg || 'Error', 'err');
      return false;
    }
  } catch {
    toast('Error de conexión', 'err');
    return false;
  }
}

/* ══ STATS EN VIVO ═══════════════════════════════════════════════════════════ */
function actualizarStats() {
  const cards = document.querySelectorAll('.vendedor-card');
  const c = { pendiente: 0, aprobado: 0, rechazado: 0, sin_enviar: 0 };
  cards.forEach(card => {
    const e = card.dataset.estado;
    if (c[e] !== undefined) c[e]++;
  });
  document.getElementById('st-pendientes').textContent  = c.pendiente;
  document.getElementById('st-aprobados').textContent   = c.aprobado;
  document.getElementById('st-rechazados').textContent  = c.rechazado;
  document.getElementById('st-sin-enviar').textContent  = c.sin_enviar;
}

/* ══ TOAST ════════════════════════════════ */
function toast(msg, tipo) {
  const box = document.getElementById('toast-box');
  if (!box) return;
  const t = document.createElement('div');
  t.className = `toast toast-${tipo === 'ok' ? 'ok' : tipo === 'err' ? 'err' : 'inf'}`;
  t.textContent = msg;
  box.appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3500);
}
</script>

</body>
</html>