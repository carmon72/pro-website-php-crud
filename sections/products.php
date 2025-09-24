<?php
//session_start();
//require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<div id="products-container" class="card">
    <h1 class="section-title">Productos</h1>

    <!-- Formulario -->
    <form id="form-producto" enctype="multipart/form-data" class="form-grid">
    <input type="text" id="prod-nombre" name="nombre" placeholder="Nombre del producto" required>
    <div class="autocomplete-wrapper">
    <input type="text" name="marca" id="prod-marca" placeholder="Escribe la marca..." required>
    <div id="marca-suggestions" class="suggestions"></div>
    </div>
    <input type="text" id="prod-categoria" name="categoria" placeholder="Categoría" required>
    <input type="number" id="prod-precio" name="precio" placeholder="Precio" min="0" step="0.01" required>
    <input type="number" id="prod-stock" name="stock" placeholder="Stock" min="0" required>
    <input type="date" id="prod-fecha-ingreso" name="fecha_ingreso" required>
    <textarea id="prod-descripcion" name="descripcion" placeholder="Descripción"></textarea>
    <input type="file" id="prod-imagen" name="imagen" accept="image/*">
    <button type="submit" id="btn-create-product">Agregar producto</button>
</form>

    <!-- Buscador -->
    <div class="search-bar">
        <input type="text" id="search-product" placeholder="Buscar producto...">
    </div>

    <!-- Tabla -->
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Marca</th> <!-- 👈 Nueva columna -->
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Fecha ingreso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="products-table-body"></tbody>
    </table>

    <!-- Paginación -->
    <div id="pagination-products" class="pagination"></div>
</div>
