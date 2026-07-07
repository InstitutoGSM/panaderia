<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Política de Privacidad';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Política de Privacidad — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/global.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/terminos.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="terms-wrap">

  <div class="terms-header">
    <span class="emoji">🔒</span>
    <h1>Política de Privacidad</h1>
    <p>Última actualización: <?= date('d/m/Y') ?> — <?= SITE_NAME ?>, República Argentina</p>
  </div>

  <div class="terms-sec">
    <h2>1. 📋 Datos que recopilamos</h2>
    <p>Para poder ofrecerte el servicio de marketplace, recopilamos los siguientes datos:</p>
    <ul>
      <li><strong>Cuenta:</strong> nombre completo, email y contraseña (almacenada cifrada con bcrypt).</li>
      <li><strong>Perfil de vendedor:</strong> nombre de la panadería, descripción, foto de perfil, Instagram, teléfono y, si configura transferencias, CBU/alias/titular.</li>
      <li><strong>Pedidos:</strong> nombre, email, dirección, código postal y notas que ingreses al confirmar un pedido.</li>
      <li><strong>Medios de pago:</strong> si elegís pagar con tarjeta, guardamos únicamente el tipo y los <strong>últimos 4 dígitos</strong>. Nunca el número completo ni el CVV.</li>
      <li><strong>Carrito de compras:</strong> se guarda localmente en tu navegador (localStorage) y no en nuestros servidores hasta que confirmás el pedido.</li>
    </ul>
  </div>

  <div class="terms-sec">
    <h2>2. 🎯 Para qué usamos tus datos</h2>
    <p>Tus datos se usan exclusivamente para:</p>
    <ul>
      <li>Crear y gestionar tu cuenta.</li>
      <li>Procesar y mostrar tus pedidos al vendedor correspondiente.</li>
      <li>Mostrar tu perfil público de panadería si te registrás como vendedor.</li>
      <li>Permitirte calificar productos que compraste.</li>
      <li>Enviarte emails de recuperación de contraseña cuando lo solicites.</li>
    </ul>
    <div class="terms-highlight">
      🔔 No vendemos, alquilamos ni compartimos tus datos personales con terceros con fines publicitarios.
    </div>
  </div>

  <div class="terms-sec">
    <h2>3. 🗄️ Dónde se almacenan los datos</h2>
    <p>
      Los datos se almacenan en una base de datos MySQL en servidor propio.
      Las contraseñas se cifran con <code>bcrypt</code> y nunca se guardan en texto plano.
      Las imágenes (productos, avatares, documentos) se almacenan en la carpeta
      <code>uploads/</code> del servidor.
    </p>
  </div>

  <div class="terms-sec">
    <h2>4. 🍪 Almacenamiento local del navegador</h2>
    <p>
      Usamos <code>localStorage</code> únicamente para guardar el contenido de tu carrito
      de compras mientras navegás. Este dato no se envía a nuestros servidores hasta
      que confirmás un pedido.
    </p>
  </div>

  <div class="terms-sec">
    <h2>5. ⏳ Conservación de datos</h2>
    <p>
      Conservamos tus datos mientras tu cuenta esté activa. Si solicitás la eliminación,
      eliminaremos o anonimizaremos tus datos personales, salvo aquellos que debamos
      conservar por obligaciones legales (por ejemplo, historial de transacciones).
    </p>
  </div>

  <div class="terms-sec">
    <h2>6. ⚖️ Tus derechos</h2>
    <p>Conforme a la <strong>Ley N° 25.326 de Protección de los Datos Personales</strong> de la República Argentina, tenés derecho a:</p>
    <ul>
      <li>Acceder a los datos personales que tenemos sobre vos.</li>
      <li>Rectificar datos incorrectos o desactualizados.</li>
      <li>Solicitar la eliminación de tu cuenta y datos asociados.</li>
      <li>Retirar tu consentimiento en cualquier momento.</li>
    </ul>
    <p>Para ejercer cualquiera de estos derechos escribinos a <a href="mailto:soporte-lospuma@gmail.com" style="color:var(--naranja)">soporte-lospuma@gmail.com</a>.</p>
  </div>

  <div class="terms-sec">
    <h2>7. 🔐 Seguridad</h2>
    <p>
      Implementamos medidas técnicas razonables para proteger tu información:
      cifrado de contraseñas, sesiones PHP con cookies HttpOnly, validación
      de datos en servidor y consultas parametrizadas (PDO) para prevenir inyección SQL.
    </p>
    <div class="terms-highlight">
      ⚠️ Ningún sistema es 100% infalible. Si detectás alguna vulnerabilidad, por favor reportala a soporte-lospuma@gmail.com.
    </div>
  </div>

  <div class="terms-sec">
    <h2>8. 📬 Contacto</h2>
    <p>
      Para consultas sobre esta política:<br>
      📧 <a href="mailto:soporte-lospuma@gmail.com" style="color:var(--naranja)">soporte-lospuma@gmail.com</a>
    </p>
  </div>

  <div class="terms-footer">
    <span class="logo-emoji">🥖</span>
    <strong>PanaderiaMarket</strong>
    <p>Catamarca, Argentina</p>
    <p><a href="<?= SITE_URL ?>/terminos.php">Términos</a> · <a href="<?= SITE_URL ?>/nosotros.php">Sobre nosotros</a></p>
    <p class="copy">© <?= date('Y') ?> PanaderiaMarket. Todos los derechos reservados.</p>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>