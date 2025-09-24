<?php
//session_start();
//require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Total de usuarios
$usuarios = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

// Total de clientes
$clientes = $conn->query("SELECT COUNT(*) as total FROM clientes")->fetch_assoc()['total'];

// Total de productos
$productos = $conn->query("SELECT COUNT(*) as total FROM productos")->fetch_assoc()['total'];

// Total de ventas
$ventas = $conn->query("SELECT COUNT(*) as total FROM ventas")->fetch_assoc()['total'];

// Productos más vendidos (TOP 5)
$masVendidos = $conn->query("
SELECT p.nombre AS producto, 
SUM(vi.cantidad) AS cantidad_total,
SUM(vi.cantidad * vi.precio_unitario) AS total_vendido
FROM ventas_items vi
JOIN productos p ON vi.producto_id = p.id
GROUP BY p.id
ORDER BY cantidad_total DESC
LIMIT 5;
");

// Últimas ventas (TOP 5)
$ultimasVentas = $conn->query("
SELECT p.nombre AS producto, 
       c.nombre AS cliente,
       v.fecha,
       (vi.cantidad * vi.precio_unitario) AS total
FROM ventas v
JOIN ventas_items vi ON v.id = vi.venta_id
JOIN productos p ON vi.producto_id = p.id
JOIN clientes c ON v.cliente_id = c.id
ORDER BY v.fecha DESC
LIMIT 5;
");

// 🔹 Estado de cuentas por cobrar (pagadas, vigentes, vencidas)
$sqlCuentas = $conn->query("
    SELECT v.id,
           v.total,
           v.fecha_vencimiento,
           IFNULL(SUM(p.monto),0) AS pagado,
           (v.total - IFNULL(SUM(p.monto),0)) AS saldo
    FROM ventas v
    LEFT JOIN ventas_pagos p ON v.id = p.venta_id
    WHERE v.modalidad_pago = 'credito'
    GROUP BY v.id
");

$pagadas = 0;
$vigentes = 0;
$vencidas = 0;

while ($row = $sqlCuentas->fetch_assoc()) {
    if ($row['saldo'] <= 0) {
        $pagadas++;
    } elseif ($row['saldo'] > 0 && $row['fecha_vencimiento'] >= date('Y-m-d')) {
        $vigentes++;
    } elseif ($row['saldo'] > 0 && $row['fecha_vencimiento'] < date('Y-m-d')) {
        $vencidas++;
    }
}
?>

<div class="dashboard-container">
    <h1>Panel de Control</h1>

    <!-- ⚠️ Alerta de cuentas vencidas -->
    <?php if ($vencidas > 0): ?>
      <div class="alert alert-danger" style="padding:10px; margin-bottom:15px; border-radius:5px; background:#dc3545; color:#fff;">
        ⚠️ Tienes <strong><?= $vencidas ?></strong> cuentas por cobrar vencidas. 
        <a href="index.php?page=cuentas_por_cobrar" style="color:#fff; font-weight:bold; text-decoration:underline;">
          Ver detalles
        </a>
      </div>
    <?php endif; ?>

    <!-- Tarjetas de estadísticas -->
    <div class="kpi-cards">
        <div class="kpi-card">👤 Usuarios <span><?= $usuarios ?></span></div>
        <div class="kpi-card">📦 Productos <span><?= $productos ?></span></div>
        <div class="kpi-card">🧑‍🤝‍🧑 Clientes <span><?= $clientes ?></span></div>
        <div class="kpi-card">💰 Ventas <span><?= $ventas ?></span></div>
    </div>

    <!-- Resumen de tablas -->
    <div class="dashboard-tables">
        <div class="table-card">
            <h2>📊 Productos más vendidos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Total vendido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $masVendidos->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['producto'] ?></td>
                            <td><?= $row['cantidad_total'] ?></td>
                            <td>$<?= number_format($row['total_vendido'],2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="table-card">
            <h2>🕑 Últimas ventas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $ultimasVentas->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['producto'] ?></td>
                            <td><?= $row['cliente'] ?></td>
                            <td><?= $row['fecha'] ?></td>
                            <td>$<?= number_format($row['total'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Gráfico circular de cuentas por cobrar -->
    <div class="dashboard-charts">
        <div class="chart-card" style="text-align:center;">
            <h2>💳 Estado de Cuentas por Cobrar</h2>
            <canvas id="chartCuentas" style="max-width:400px; margin:auto;"></canvas>

            <!-- Totales debajo del gráfico -->
            <div style="margin-top:15px; font-size:16px;">
                <span style="color:green; font-weight:bold;">🟢 Pagadas: <?= $pagadas ?></span> &nbsp; | &nbsp;
                <span style="color:orange; font-weight:bold;">🟡 Vigentes: <?= $vigentes ?></span> &nbsp; | &nbsp;
                <span style="color:red; font-weight:bold;">🔴 Vencidas: <?= $vencidas ?></span>
            </div>
        </div>
    </div>
</div>

<!-- 📊 Scripts de Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// === Cuentas por cobrar (Doughnut) ===
const ctxCuentas = document.getElementById('chartCuentas').getContext('2d');
new Chart(ctxCuentas, {
    type: 'doughnut',
    data: {
        labels: ['Pagadas', 'Vigentes', 'Vencidas'],
        datasets: [{
            data: [<?= $pagadas ?>, <?= $vigentes ?>, <?= $vencidas ?>],
            backgroundColor: [
                'rgba(40, 167, 69, 0.7)',   // Verde - pagadas
                'rgba(255, 206, 86, 0.7)', // Amarillo - vigentes
                'rgba(220, 53, 69, 0.7)'   // Rojo - vencidas
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(220, 53, 69, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>