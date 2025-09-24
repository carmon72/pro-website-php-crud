<?php
//session_start();
//require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<div id="ventas-container">
    <h1>Registrar Venta</h1>

   
<!-- Formulario de venta -->
<form id="form-venta" class="venta-form">
  <div class="venta-fields">
    <!-- Cliente -->
    <div class="venta-row">
      <label for="venta-cliente">Cliente</label>
      <select id="venta-cliente" name="cliente_id" required></select>
    </div>

    <!-- Producto -->
    <div class="venta-row">
      <label for="venta-producto">Producto</label>
      <select id="venta-producto" required></select>
    </div>

    <!-- Modalidad -->
    <div class="venta-row">
      <label for="venta-modalidad">Modalidad de pago</label>
      <select id="venta-modalidad" name="modalidad_pago" required>
        <option value="contado">Contado</option>
        <option value="credito">Crédito</option>
      </select>
    </div>

    <!-- Fecha de vencimiento -->
    <div class="venta-row" id="credito-extra" style="display:none;">
      <label for="venta-fecha-vencimiento">Fecha de vencimiento</label>
      <input type="date" id="venta-fecha-vencimiento" name="fecha_vencimiento">
    </div>

    <!-- Cantidad -->
    <div class="venta-row">
      <label for="venta-cantidad">Cantidad</label>
      <input type="number" id="venta-cantidad" min="1" placeholder="Cantidad" />
    </div>

    <!-- Botón Agregar -->
    <div class="venta-row">
      <button id="btn-add-producto" type="button" class="btn-primary">
        Agregar al carrito
      </button>
    </div>
  </div>

  <!-- Carrito -->
  <div class="carrito-container">
    <h2>Carrito</h2>
    <table>
      <thead>
        <tr>
          <th>Producto</th>
          <th>Marca</th>
          <th>Cantidad</th>
          <th>Precio Unitario</th>
          <th>Subtotal</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="venta-items"></tbody>
    </table>

    <!-- Total + Botón Confirmar -->
<div class="venta-total-row">
  <div>
    <h3>Total: $<span id="venta-total">0.00</span></h3>
    <button id="btn-confirmar-venta" type="submit" class="btn-primary">
      Confirmar Venta
    </button>
  </div>
</div>
</form>



    <!-- Historial de ventas -->
<h2>Historial de Ventas</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Marca</th> <!-- 👈 nueva -->
            <th>Fecha</th>
            <th>Total</th>
            <th>Modalidad</th>
            <th>Fecha Vencimiento</th>
            <th>Pagado</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody id="ventas-table-body"></tbody>
</table>


<!-- Paginación -->
<div id="pagination-ventas" class="pagination"></div>
</div>
