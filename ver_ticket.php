<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
redirigir_si_no_logueado();

$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id_usuario = ? ORDER BY fecha_creacion DESC");
$stmt->execute([$_SESSION["usuario_id"]]);
$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Mis Tickets</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .estado-abierto {
            color: green;
            font-weight: bold;
        }
        .estado-cerrado {
            color: red;
            font-weight: bold;
        }
        .wide-table {
            width: 100%;
            overflow-x: auto;
        }
        .wide-table table {
            width: 100%;
            min-width: 800px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 100%;">
        <div class="box" style="max-width: 95%;">
            <h2>Mis tickets</h2>

            <?php if (count($tickets) === 0): ?>
                <p>No has creado ningun ticket aun.</p>
            <?php else: ?>
                <div class="wide-table">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Tema</th>
                            <th>Titulo</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Ver</th>
                        </tr>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td><?php echo $t["id"]; ?></td>
                                <td><?php echo $t["tema"]; ?></td>
                                <td><?php echo $t["titulo"]; ?></td>
                                <td class="<?php echo $t["estado"] == 'Abierto' ? 'estado-abierto' : 'estado-cerrado'; ?>">
                                    <?php echo $t["estado"]; ?>
                                </td>
                                <td><?php echo $t["fecha_creacion"]; ?></td>
                                <td><a href="ver_ticket_detalle.php?id=<?php echo $t["id"]; ?>">Abrir</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>

            <br><a href="dashboard.php">Volver al inicio</a>
        </div>
    </div>
</body>
</html>
