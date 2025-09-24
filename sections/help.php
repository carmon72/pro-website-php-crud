<div class="about-container">
  <h1><i class="fas fa-info-circle"></i> Ayuda</h1>
  <p>
    Bienvenida al <strong>Sistema de Ventas e Inventario Pro Dashboard</strong> 🎉  
    Este panel te permitirá <b>gestionar ventas, clientes, productos e inventario</b> de forma sencilla.
  </p>

  <hr>

  <h2><i class="fas fa-rocket"></i> Primeros pasos</h2>
  <ul>
    <li>Inicia sesión con tu usuario y contraseña.</li>
    <li>Usa el menú lateral para acceder a cada sección:</li>
    <ul>
      <li>🏠 Inicio → Dashboard con estadísticas</li>
      <li>📦 Productos → Lista, gestión y actualización de inventario</li>
      <li>👥 Clientes → Gestión de clientes</li>
      <li>🛒 Ventas → Registrar ventas</li>
      <li>💳 Cuentas por cobrar → Control de créditos y abonos</li>
      <li>📊 Reportes → Exportar información</li>
      <li>ℹ️ Acerca de → Información del negocio</li>
      <li>❓ Ayuda → Consejos rápidos</li>
    </ul>
  </ul>

  <hr>

  <h2><i class="fas fa-shopping-cart"></i> Cómo registrar una venta</h2>
  <ol>
    <li>Ve al menú <b>Ventas → Registrar venta</b>.</li>
    <li>Selecciona el <b>cliente</b> y el <b>producto</b>.</li>
    <li>Ingresa la <b>cantidad</b> y la <b>modalidad de pago</b>:</li>
    <ul>
      <li><b>Contado</b> → pago inmediato.</li>
      <li><b>Crédito</b> → define una <b>fecha de vencimiento</b> y registra abonos.</li>
    </ul>
    <li>Haz clic en <b>Agregar al carrito</b>.</li>
    <li>Presiona <b>Confirmar venta</b> para finalizar.</li>
  </ol>

  <hr>

  <h2><i class="fas fa-box-open"></i> Cómo administrar productos</h2>
  <p>En <b>Productos</b> puedes:</p>
  <ul>
    <li><i class="fas fa-plus-circle text-success"></i> Agregar nuevos productos (nombre, marca, precio, stock).</li>
    <li><i class="fas fa-edit text-warning"></i> Editar productos existentes.</li>
    <li><i class="fas fa-trash text-danger"></i> Eliminar productos.</li>
    <li><i class="fas fa-warehouse text-primary"></i> Actualizar inventario (entrada o salida de productos).</li>
  </ul>

  <hr>

  <h2><i class="fas fa-users"></i> Cómo administrar clientes</h2>
  <p>En <b>Clientes</b> puedes registrar, editar o eliminar clientes.  
     Solo los clientes guardados aquí podrán usarse en ventas.</p>

  <hr>

  <h2><i class="fas fa-credit-card"></i> Cuentas por cobrar</h2>
  <ol>
    <li>Accede a <b>Ventas → Cuentas por cobrar</b>.</li>
    <li>Verás todas las ventas a crédito pendientes.</li>
    <li>Registra <b>abonos</b> parciales o totales.</li>
    <li>Si un cliente paga todo → el saldo se actualiza a <b>$0</b>.</li>
    <li>Si la fecha venció y no pagó → aparecerá una <span class="alerta">alerta roja</span> en el Dashboard.</li>
  </ol>

  <hr>

  <h2><i class="fas fa-chart-line"></i> Reportes</h2>
  <p>Desde <b>Reportes</b> puedes:</p>
  <ul>
    <li>Ver información detallada de ventas e inventario.</li>
    <li>Exportar a <b>PDF</b> o <b>Excel</b> para tu control.</li>
  </ul>

  <hr>

  <h2><i class="fas fa-question-circle"></i> Preguntas Frecuentes (FAQ)</h2>
  <ul>
    <li><b>❓ No puedo iniciar sesión</b><br>
      ✅ Verifica que el usuario y la contraseña estén correctos.  
      ✅ Si olvidaste tu clave, pide al administrador que la restablezca.</li>

    <li><b>❓ El stock no se actualiza</b><br>
      ✅ Asegúrate de registrar las entradas y salidas desde <b>Inventario</b>.  
      ✅ Cada venta también descuenta automáticamente el stock.</li>

    <li><b>❓ Una venta aparece como vencida aunque ya se pagó</b><br>
      ✅ Revisa que se haya registrado el <b>abono total</b> en la sección de <b>Cuentas por cobrar</b>.  
      ✅ Si está pagada al 100%, desaparecerá de la alerta.</li>

    <li><b>❓ Cómo exporto mis datos</b><br>
      ✅ Desde el módulo de <b>Reportes</b> puedes generar un archivo en <b>Excel</b> o <b>PDF</b>.</li>

    <li><b>❓ Cómo cierro sesión correctamente</b><br>
      ✅ Usa siempre el botón <b>🔒 Logout</b> en la esquina superior para salir de forma segura.</li>
  </ul>

  <hr>

  <h2><i class="fas fa-lightbulb"></i> Consejos importantes</h2>
  <ul>
    <li>✅ Confirma siempre las ventas al finalizar.</li>
    <li>✅ Revisa el Dashboard al iniciar sesión.</li>
    <li>✅ Usa el botón <b>🔒 Logout</b> para salir del sistema de forma segura.</li>
  </ul>
</div>

<!-- 🔹 Estilos personalizados -->
<style>
  .about-container {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    font-size: 16px;
    line-height: 1.6;
  }

  .about-container h1, 
  .about-container h2 {
    color: #1E3A8A;
    margin-bottom: 10px;
  }

  .about-container h1 i,
  .about-container h2 i {
    margin-right: 8px;
    color: #6D28D9;
  }

  .about-container ul, 
  .about-container ol {
    margin: 10px 0 20px 20px;
  }

  .about-container .alerta {
    font-weight: bold;
    color: #dc3545;
  }
</style>
