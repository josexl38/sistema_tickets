<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
redirigir_si_no_logueado();
if (!es_admin($pdo)) {
    echo "Acceso denegado.";
    exit();
}

$id = $_GET["id"] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo "Ticket no encontrado.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["respuesta"])) {
        $respuesta = limpiar($_POST["respuesta"]);
        $autor = "Soporte VW";
        $stmt = $pdo->prepare("INSERT INTO respuestas (id_ticket, respuesta, autor) VALUES (?, ?, ?)");
        $stmt->execute([$id, $respuesta, $autor]);

        $stmt_user = $pdo->prepare("SELECT correo FROM usuarios WHERE id = ?");
        $stmt_user->execute([$ticket["id_usuario"]]);
        $correo = $stmt_user->fetchColumn();
        $asunto = "Respuesta de soporte en tu ticket #$id";
        $mensaje = "Soporte ha respondido a tu ticket:\n\n$respuesta\n\nPuedes revisarlo en el sistema.";
        mail($correo, $asunto, $mensaje, "From: soporte@vw-potosina.com.mx");
    }

    if (isset($_POST["cerrar"])) {
        $stmt = $pdo->prepare("UPDATE tickets SET estado = 'Cerrado' WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: admin_ver_ticket.php?id=$id");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM respuestas WHERE id_ticket = ?");
$stmt->execute([$id]);
$respuestas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo $ticket["id"]; ?></title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .respuesta-box {
            background-color: rgba(255,255,255,0.95);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            text-align: left;
        }
        .respuesta-box strong {
            color: #003366;
        }
        textarea {
            resize: vertical;
        }
        .acciones {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .acciones button {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Ticket #<?php echo $ticket["id"]; ?> - <?php echo $ticket["titulo"]; ?></h2>

            <p><strong>Tema:</strong> <?php echo $ticket["tema"]; ?></p>
            <p><strong>Descripcion:</strong><br><?php echo nl2br($ticket["descripcion"]); ?></p>
            <p><strong>Departamento:</strong> <?php echo $ticket["departamento"]; ?></p>
            <p><strong>Estado:</strong> <?php echo $ticket["estado"]; ?></p>

            <?php if ($ticket["archivo"]): ?>
                <p><strong>Archivo:</strong> <a href="<?php echo $ticket["archivo"]; ?>" target="_blank">Ver</a></p>
            <?php endif; ?>

            <hr>

            <h3>Conversacion</h3>
            <?php if (count($respuestas) === 0): ?>
                <p>No hay respuestas aun.</p>
            <?php else: ?>
                <?php foreach ($respuestas as $r): ?>
                    <div class="respuesta-box">
                        <strong><?php echo $r["autor"]; ?></strong> (<?php echo $r["fecha_respuesta"]; ?>):<br>
                        <?php echo nl2br($r["respuesta"]); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <hr>
            <h3>Responder como soporte</h3>
            <form method="POST">
                <textarea name="respuesta" rows="4" cols="60" required></textarea><br>
                <div class="acciones">
                    <button type="submit">Responder</button>
                    <?php if ($ticket["estado"] == "Abierto"): ?>
                        <button name="cerrar" value="1">🔒 Cerrar ticket</button>
                    <?php endif; ?>
                </div>
            </form>

            <br><a href="admin_tickets.php">Volver al panel</a>
        </div>
    </div>
</body>
</html>
