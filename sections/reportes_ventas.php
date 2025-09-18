<?php
//require '../db.php';
//session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Filtros iniciales
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? '';
$filtro_fecha_fin    = $_GET['fecha_fin'] ?? '';
$filtro_cliente      = $_GET['cliente'] ?? '';
$filtro_producto     = $_GET['producto'] ?? '';

// Query base
$query = "
    SELECT 
        p.nombre AS producto,
        c.nombre AS cliente,
        vi.cantidad,
        (vi.precio_unitario * vi.cantidad) AS total,
        v.fecha
    FROM ventas_items vi
    INNER JOIN productos p ON vi.producto_id = p.id
    INNER JOIN ventas v ON vi.venta_id = v.id
    INNER JOIN clientes c ON v.cliente_id = c.id
    WHERE 1=1
";

// Flag para mostrar resultados
$mostrar_resultados = false;

// Aplicar filtros SOLO si se envió el formulario
if (!empty($_GET)) {
    if (!empty($filtro_fecha_inicio) && !empty($filtro_fecha_fin)) {
        $query .= " AND DATE(v.fecha) BETWEEN '$filtro_fecha_inicio' AND '$filtro_fecha_fin'";
    }
    if (!empty($filtro_cliente)) {
        $query .= " AND c.id = '" . $conn->real_escape_string($filtro_cliente) . "'";
    }
    if (!empty($filtro_producto)) {
        $query .= " AND p.id = '" . $conn->real_escape_string($filtro_producto) . "'";
    }

    $result = $conn->query($query);
    $mostrar_resultados = true;
}
?>

<div id="reportes-container">
    <h1>Reportes de Ventas</h1>

    <!-- Filtros -->
    <form method="GET" class="filtros" action="">
        <!-- Indicamos a qué subvista queremos volver -->
        <input type="hidden" name="page" value="reportes_ventas">

        <label for="fecha_inicio">📅 Fecha inicio:</label>
        <input type="date" name="fecha_inicio" value="<?= $filtro_fecha_inicio ?>">

        <label for="fecha_fin">📅 Fecha fin:</label>
        <input type="date" name="fecha_fin" value="<?= $filtro_fecha_fin ?>">

        <select name="cliente">
            <option value="">Todos los clientes</option>
            <?php
            $clientes = $conn->query("SELECT id, nombre FROM clientes");
            while ($row = $clientes->fetch_assoc()) {
                $selected = ($filtro_cliente == $row['id']) ? 'selected' : '';
                echo "<option value='{$row['id']}' $selected>{$row['nombre']}</option>";
            }
            ?>
        </select>

        <select name="producto">
            <option value="">Todos los productos</option>
            <?php
            $productos = $conn->query("SELECT id, nombre FROM productos");
            while ($row = $productos->fetch_assoc()) {
                $selected = ($filtro_producto == $row['id']) ? 'selected' : '';
                echo "<option value='{$row['id']}' $selected>{$row['nombre']}</option>";
            }
            ?>
        </select>

        <button type="submit" class="btn-primary">Generar</button>
       <!-- Botón limpiar -->
        <button id="btn-limpiar-reporte" class="btn-limpiar" type="button">
         Limpiar
        </button>
    </form>

    <!-- Columnas seleccionables -->
    <div class="columnas">
        <label><input type="checkbox" checked> Producto</label>
        <label><input type="checkbox" checked> Cliente</label>
        <label><input type="checkbox" checked> Cantidad</label>
        <label><input type="checkbox" checked> Total</label>
        <label><input type="checkbox" checked> Fecha</label>
    </div>

    <!-- Tabla -->
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cliente</th>
                <th>Cantidad</th>
                <th>Total</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody id="reportes-ventas-body">
            <?php if ($mostrar_resultados && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['producto'] ?></td>
                        <td><?= $row['cliente'] ?></td>
                        <td><?= $row['cantidad'] ?></td>
                        <td>$<?= number_format($row['total'], 2) ?></td>
                        <td><?= $row['fecha'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php elseif ($mostrar_resultados): ?>
                <tr><td colspan="5" style="text-align:center;">No se encontraron resultados</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Botones de exportación -->
    <div class="exportar">
        <form action="sections/exportar_pdf.php" method="post" style="display:inline;">
            <input type="hidden" name="fecha_inicio" value="<?= $filtro_fecha_inicio ?>">
            <input type="hidden" name="fecha_fin" value="<?= $filtro_fecha_fin ?>">
            <input type="hidden" name="cliente" value="<?= $filtro_cliente ?>">
            <input type="hidden" name="producto" value="<?= $filtro_producto ?>">
            <button type="submit" class="btn-report">📄 Exportar PDF</button>
        </form>

        <form action="sections/exportar_excel.php" method="post" style="display:inline;">
            <input type="hidden" name="fecha_inicio" value="<?= $filtro_fecha_inicio ?>">
            <input type="hidden" name="fecha_fin" value="<?= $filtro_fecha_fin ?>">
            <input type="hidden" name="cliente" value="<?= $filtro_cliente ?>">
            <input type="hidden" name="producto" value="<?= $filtro_producto ?>">
            <button type="submit" class="btn-report">📊 Exportar Excel</button>
        </form>
    </div>
</div>
