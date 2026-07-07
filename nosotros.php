<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Sobre Nosotros';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sobre Nosotros — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/global.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/terminos.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="ns-wrap">

  <div class="ns-header">
    <span class="emoji">🥖</span>
    <h1>Sobre Nosotros</h1>
    <p>
      PanaderiaMarket nació para conectar a panaderías artesanales con la gente
      de Catamarca que busca pan, facturas, tortas y dulces hechos con dedicación,
      directo de quien los elabora.
    </p>
  </div>

  <!-- Misión / Visión / Valores -->
  <div class="ns-grid">
    <div class="ns-card">
      <span class="ico">🎯</span>
      <h3>Nuestra misión</h3>
      <p>Ser el puente digital entre panaderos artesanales y sus clientes, sin intermediarios que encarezcan el producto.</p>
    </div>
    <div class="ns-card">
      <span class="ico">🤝</span>
      <h3>Nuestros valores</h3>
      <p>Transparencia, cercanía con el productor local y compromiso con la calidad alimentaria de Catamarca.</p>
    </div>
    <div class="ns-card">
      <span class="ico">🌱</span>
      <h3>Nuestra visión</h3>
      <p>Que cada panadería artesanal de la provincia tenga su espacio para crecer y darse a conocer.</p>
    </div>
  </div>

  <!-- ¿Cómo funciona? -->
  <div class="ns-sec">
    <h2>🍞 ¿Cómo funciona?</h2>
    <p>
      <strong>Para compradores:</strong> explorá productos de distintas panaderías,
      armá tu carrito, elegí el medio de pago que prefieras y coordiná la entrega
      directamente con el vendedor.
    </p>
    <p>
      <strong>Para vendedores:</strong> creá tu perfil de panadería, publicá tus
      productos con fotos y precios, y gestioná tus pedidos desde un panel simple,
      todo desde el celular.
    </p>
  </div>

  <!-- Quiénes somos -->
  <div class="ns-sec">
    <h2>📍 Quiénes somos</h2>
    <p>
      Somos un equipo de Catamarca apasionados por la tecnología y el producto local.
      Desarrollamos esta plataforma como un proyecto para impulsar la economía artesanal
      de la región, facilitando la venta directa y el acceso a productos de calidad.
    </p>
    <p>
      Creemos que detrás de cada pan hay una historia, una familia y un oficio que
      merece ser visible. Por eso construimos PanaderiaMarket.
    </p>
  </div>

  <!-- Equipo -->
  <div class="ns-sec">
    <h2>👥 El equipo</h2>
    <div class="ns-equipo">
      <div class="ns-miembro">
        <div class="ns-miembro-avatar">LP</div>
        <strong>Los Pumas</strong>
        <span>Desarrollo &amp; diseño</span>
      </div>
      <div class="ns-miembro">
        <div class="ns-miembro-avatar">🥖</div>
        <strong>Comunidad</strong>
        <span>Panaderías artesanales de Catamarca</span>
      </div>
    </div>
  </div>

  <!-- Contacto -->
  <div class="ns-sec">
    <h2>✉️ Contacto</h2>
    <p>¿Tenés una panadería y querés sumarte? ¿Alguna consulta o sugerencia?</p>
    <p>
      📧 <a href="mailto:soporte-lospuma@gmail.com" style="color:var(--naranja)">soporte-lospuma@gmail.com</a><br>
      📞 <a href="tel:+5493834887766" style="color:var(--naranja)">+54 9 383 488-7766</a>
    </p>
  </div>

  <!-- Footer interno -->
  <div class="terms-footer">
    <span class="logo-emoji">🥖</span>
    <strong>PanaderiaMarket</strong>
    <p>Catamarca, Argentina</p>
    <p><a href="<?= SITE_URL ?>/terminos.php">Términos</a> · <a href="<?= SITE_URL ?>/privacidad.php">Privacidad</a></p>
    <p class="copy">© <?= date('Y') ?> PanaderiaMarket. Todos los derechos reservados.</p>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>