<?php
// auth.php
// Requiere que exista $conn (mysqli) — include 'db.php' antes donde lo uses
// Uso: require_once 'auth.php'; init_auth(); luego has_permission('reports.view')

if (!function_exists('init_auth')) {
    function init_auth() {
        // Asume que session_start() ya fue llamado
        global $conn;

        if (!isset($_SESSION)) session_start();

        // Si usuario logueado y no hemos cargado permisos aún, cargarlos
        if (isset($_SESSION['user_id']) && empty($_SESSION['user_permissions'])) {
            $user_id = (int) $_SESSION['user_id'];

            $sql = "SELECT p.name
                    FROM permissions p
                    JOIN role_permissions rp ON rp.permission_id = p.id
                    JOIN user_roles ur ON ur.role_id = rp.role_id
                    WHERE ur.user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $res = $stmt->get_result();

            $perms = [];
            while ($row = $res->fetch_assoc()) {
                $perms[$row['name']] = true;
            }
            $_SESSION['user_permissions'] = $perms;

            // Cargar roles (nombres) del usuario
            $sql2 = "SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param('i', $user_id);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $roles = [];
            while ($r = $res2->fetch_assoc()) {
                $roles[] = $r['name'];
            }
            $_SESSION['user_roles'] = $roles;
        }
    }
}

if (!function_exists('has_permission')) {
    function has_permission($perm) {
        if (!isset($_SESSION)) session_start();
        if (!isset($_SESSION['user_permissions'])) return false;
        return !empty($_SESSION['user_permissions'][$perm]);
    }
}

if (!function_exists('has_role')) {
    function has_role($rolename) {
        if (!isset($_SESSION)) session_start();
        if (!isset($_SESSION['user_roles'])) return false;
        return in_array($rolename, $_SESSION['user_roles']);
    }
}

if (!function_exists('require_permission')) {
    function require_permission($perm) {
        if (!has_permission($perm)) {
            header('HTTP/1.1 403 Forbidden');
            echo "<h2>403 - Acceso denegado</h2><p>No tienes permisos para ver esta sección.</p>";
            exit;
        }
    }
}

if (!function_exists('require_role')) {
    function require_role($rolename) {
        if (!has_role($rolename)) {
            header('HTTP/1.1 403 Forbidden');
            echo "<h2>403 - Acceso denegado</h2><p>Se requiere rol: " . htmlspecialchars($rolename) . ".</p>";
            exit;
        }
    }
}
