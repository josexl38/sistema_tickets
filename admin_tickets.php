<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
redirigir_si_no_logueado();

if (!es_admin($pdo)) {
    echo "Acceso denegado.";
    exit();
}

// Si se recibe un ID para borrar
if (isset($_GET["eliminar"])) {
    $id = intval($_GET["eliminar"]);

    // Borrar respuestas primero
    $pdo->prepare("DELETE FROM respuestas WHERE id_ticket = ?")->execute([$id]);
    // Luego el ticket
    $pdo->prepare("DELETE FROM tickets WHERE id = ?")->execute([$id]);

    header("Location: admin_tickets.php");
    exit();
}

$stmt = $pdo->query("SELECT t.*, u.nombre FROM tickets t JOIN usuarios u ON t.id_usuario = u.id ORDER BY t.fecha_creacion DESC");
$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .wide-table {
            width: 100%;
            overflow-x: auto;
        }

        .wide-table table {
            width: 100%;
            min-width: 1000px;
        }

        .eliminar {
            color: red;
            margin-left: 10px;
        }

        .eliminar:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 100%;">
        <div class="box" style="max-width: 95%;">
            <h2>🛠️ Panel de administrador - Todos los tickets</h2>

            <div class="wide-table">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Tema</th>
                        <th>Titulo</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td><?php echo $t["id"]; ?></td>
                            <td><?php echo $t["nombre"]; ?></td>
                            <td><?php echo $t["tema"]; ?></td>
                            <td><?php echo $t["titulo"]; ?></td>
                            <td><?php echo $t["estado"]; ?></td>
                            <td><?php echo $t["fecha_creacion"]; ?></td>
                            <td>
                                <a href="admin_ver_ticket.php?id=<?php echo $t["id"]; ?>">Ver</a> |
                                <a href="admin_tickets.php?eliminar=<?php echo $t["id"]; ?>" class="eliminar" onclick="return confirm('¿Seguro que deseas eliminar este ticket y sus respuestas?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <br>
            <a href="dashboard.php">Volver al inicio</a>
        </div>
    </div>
</body>
</html>
