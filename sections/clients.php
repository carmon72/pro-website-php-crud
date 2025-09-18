<?php
//session_start();
//require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<div id="clients-container">
    <h1 style="color:#0b5cff;">Clientes</h1>

    <!-- Formulario para crear/editar -->
    <form id="form-cliente" class="form-grid">
    <input type="text" id="cli-nombre" name="nombre" placeholder="Nombre del cliente" required>
    <input type="email" id="cli-correo" name="correo" placeholder="Correo" required>
    <input type="tel" id="cli-telefono" name="telefono" placeholder="Teléfono" pattern="[0-9]{8,15}" required>
    <input type="text" id="cli-direccion" name="direccion" placeholder="Dirección">
    <button id="btn-create-cliente" type="submit" class="btn-primary">Agregar cliente</button>
    </form>

    <!-- Buscador -->
    <div style="margin:16px 0;">
        <input type="text" id="search-client" placeholder="Buscar cliente..." class="input-search">
    </div>

    <!-- Tabla -->
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Registrado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="clients-table-body">
            <!-- Aquí se cargan las filas por JS -->
        </tbody>
    </table>

    <!-- Paginación -->
    <div id="pagination-clients" class="pagination"></div>
</div>
