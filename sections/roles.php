<?php
// Requiere sesión activa
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Solo admin puede ver esta página
if ($_SESSION['role'] !== 'admin') {
    echo "<div style='padding:20px; color:red; font-weight:bold;'>🚫 No tienes permisos para acceder a esta página.</div>";
    exit;
}

// Obtener todos los usuarios
$usuarios = $conn->query("SELECT id, username, role FROM users ORDER BY id ASC");

// Roles disponibles
$rolesDisponibles = ['admin', 'vendedor', 'lector'];

// Si se actualiza un rol
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $user_id = intval($_POST['user_id']);
    $nuevoRol = $conn->real_escape_string($_POST['role']);
    
    if (in_array($nuevoRol, $rolesDisponibles)) {
        $conn->query("UPDATE users SET role = '$nuevoRol' WHERE id = $user_id");
        echo "<script>alert('✅ Rol actualizado correctamente'); window.location='index.php?page=roles';</script>";
        exit;
    }
}
?>

<div class="roles-container">
    <h1>👥 Gestión de Roles</h1>
    <p>Administra los permisos de cada usuario dentro del sistema.</p>

    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; text-align:left;">
        <thead style="background:#f4f4f4;">
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Rol Actual</th>
                <th>Asignar Nuevo Rol</th>
                <th>Acción</th> <!-- ✅ Nueva columna -->
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $usuarios->fetch_assoc()): ?>
                <tr>
                    <form method="POST">
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><strong><?= ucfirst($row['role']) ?></strong></td>
                        <td>
                            <select name="role" required>
                                <?php foreach ($rolesDisponibles as $rol): ?>
                                    <option value="<?= $rol ?>" <?= ($row['role'] === $rol) ? 'selected' : '' ?>>
                                        <?= ucfirst($rol) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn-primary">Actualizar</button>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
