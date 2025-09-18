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
    <form id="form-venta">
        <!-- Selección cliente -->
        <div class="form-group">
            <label for="venta-cliente">Cliente</label>
            <select id="venta-cliente" name="cliente_id" required>
                <!-- Opciones se cargan vía JS -->
            </select>
        </div>

        <!-- Selección producto -->
        <div class="form-group">
            <label for="venta-producto">Producto</label>
            <select id="venta-producto" required>
                <!-- Opciones se cargan vía JS -->
            </select>

            <input type="number" id="venta-cantidad" min="1" placeholder="Cantidad" />

            <!-- 👇 Evita submit automático -->
            <button id="btn-add-producto" type="button" class="btn-primary">Agregar al carrito</button>
        </div>

        <!-- Carrito -->
        <h2>Carrito</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="venta-items"></tbody>
        </table>

        <!-- Total -->
        <h3>Total: $<span id="venta-total">0.00</span></h3>

        <!-- Confirmar venta -->
        <button id="btn-confirmar-venta" type="submit" class="btn-success">
            Confirmar Venta
        </button>
    </form>

    <!-- Historial de ventas -->
    <h2>Historial de Ventas</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="ventas-table-body"></tbody>
    </table>

    <!-- Paginación -->
    <div id="pagination-ventas" class="pagination"></div>
</div>
