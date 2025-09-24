<?php
// Validar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener productos (con marca)
$productos = $conn->query("SELECT id, nombre, stock, marca FROM productos")->fetch_all(MYSQLI_ASSOC);

// Obtener historial de movimientos (con marca)
$movimientos = $conn->query("
    SELECT m.id, p.nombre as producto, m.tipo, m.cantidad, m.fecha, m.marca, u.username as usuario
    FROM inventario_movimientos m
    JOIN productos p ON m.producto_id = p.id
    JOIN users u ON m.usuario_id = u.id
    ORDER BY m.fecha DESC
    LIMIT 20
")->fetch_all(MYSQLI_ASSOC);
?>

<h2>📊 Movimientos de Inventario</h2>

<form id="form-movimiento" style="margin-bottom:20px;">
    <label>Producto:</label>
    <select name="producto_id" required>
        <?php foreach ($productos as $p): ?>
            <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?> (Stock: <?= $p['stock'] ?>)</option>
        <?php endforeach; ?>
    </select>

    <label>Tipo:</label>
    <select name="tipo" required>
        <option value="entrada">Entrada (+)</option>
        <option value="salida">Salida (-)</option>
    </select>

    <label>Cantidad:</label>
    <input type="number" name="cantidad" min="1" required>

    
    <label>Marca:</label> 
    <div style="position: relative; display: inline-block;">  
    <input type="text" id="inv-marca" name="marca" placeholder="Escribe la marca..." required>
    <div id="inv-marca-suggestions" class="suggestions"></div>
    </div>


    <button type="submit" class="btn-primary-registrar">Registrar Movimiento</button>
</form>

<div class="carga-excel" style="margin-top:25px;">
    <h3>📂 Carga masiva desde Excel</h3>
    <form id="form-upload-excel" enctype="multipart/form-data">
        <input type="file" name="file" accept=".xlsx,.xls" required>
        <button type="submit" class="btn-primary-registrar">Subir Plantilla</button>
    </form>
</div>

<!-- Botón de descarga de plantilla -->
<a href="sections/download_inventory_template.php" class="btn-primary-registrar" style="text-decoration:none;">
    📥 Descargar Plantilla
</a>

<table border="1" cellpadding="8" width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Tipo</th>
            <th>Cantidad</th>
            <th>Fecha</th>
            <th>Marca</th>
            <th>Usuario</th>
        </tr>
    </thead>
    <tbody id="inventory-table-body">
        <?php foreach ($movimientos as $m): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><?= $m['producto'] ?></td>
                <td>
                    <?= $m['tipo'] === 'entrada' 
                        ? "📥 Entrada" 
                        : "📤 Salida" ?>
                </td>
                <td><?= $m['cantidad'] ?></td>
                <td><?= $m['fecha'] ?></td>
                <td><?= $m['marca'] ?: "—" ?></td>
                <td><?= $m['usuario'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
