<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
redirigir_si_no_logueado();

// ⚠️ Función necesaria para obtener el correo del usuario
function obtenerCorreoUsuario($id, $pdo) {
    $stmt = $pdo->prepare("SELECT correo FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $res = $stmt->fetch();
    return $res ? $res["correo"] : "soporte@vw-potosina.com.mx";
}

if (!isset($_GET["id"])) {
    echo "Ticket no especificado.";
    exit();
}

$ticket_id = intval($_GET["id"]);

$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND id_usuario = ?");
$stmt->execute([$ticket_id, $_SESSION["usuario_id"]]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo "Ticket no encontrado.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $respuesta = limpiar($_POST["respuesta"]);
    $autor = $_SESSION["usuario_nombre"];

    $stmt = $pdo->prepare("INSERT INTO respuestas (id_ticket, respuesta, autor) VALUES (?, ?, ?)");
    $stmt->execute([$ticket_id, $respuesta, $autor]);

    // Notificación por correo al usuario
    $correo = obtenerCorreoUsuario($_SESSION["usuario_id"], $pdo);
    $asunto = "Respuesta registrada en tu ticket #$ticket_id";
    $mensaje = "Tu respuesta ha sido registrada:\n\n$respuesta\n\nPuedes seguir el seguimiento en el sistema.";
    $cabeceras = "From: soporte@vw-potosina.com.mx";
    mail($correo, $asunto, $mensaje, $cabeceras);

    // Notificar al administrador
    $admin_email = "antonio.munoz@vw-potosina.com.mx";
    $asunto_admin = " Nueva respuesta en el ticket #$ticket_id";
    $mensaje_admin = "El usuario " . $_SESSION["usuario_nombre"] . " ha respondido al ticket:\n\n$respuesta\n\nIngresa al sistema para ver la conversacion.";
    $cabeceras_admin = "From: sistema.ticket@vw-potosina.com.mx";
    mail($admin_email, $asunto_admin, $mensaje_admin, $cabeceras_admin);


    // Redirigir para evitar reenvío del formulario
    header("Location: ver_ticket_detalle.php?id=$ticket_id");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM respuestas WHERE id_ticket = ? ORDER BY fecha_respuesta ASC");
$stmt->execute([$ticket_id]);
$respuestas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Detalle del ticket</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .respuesta-box {
            background-color: rgba(255,255,255,0.95);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            text-align: left;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .respuesta-box strong {
            color: #003366;
        }
        textarea {
            resize: vertical;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Ticket #<?php echo $ticket["id"]; ?> - <?php echo $ticket["titulo"]; ?></h2>

            <p><strong>Tema:</strong> <?php echo $ticket["tema"]; ?></p>
            <p><strong>Departamento:</strong> <?php echo $ticket["departamento"]; ?></p>
            <p><strong>Descripcion:</strong><br><?php echo nl2br($ticket["descripcion"]); ?></p>

            <?php if ($ticket["archivo"]): ?>
                <p><strong>Archivos adjuntos:</strong><br>
                <?php foreach (explode(",", $ticket["archivo"]) as $ruta): ?>
                    <a href="<?php echo $ruta; ?>" target="_blank"><?php echo basename($ruta); ?></a><br>
                <?php endforeach; ?>
                </p>
            <?php endif; ?>

            <hr>

            <h3>Conversacion</h3>
            <?php if (count($respuestas) == 0): ?>
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
            <h3>Agregar respuesta</h3>
            <form method="POST">
                <textarea name="respuesta" rows="4" required></textarea><br>
                <button type="submit">Enviar</button>
            </form>

            <br><a href="ver_ticket.php">Volver a mis tickets</a>
        </div>
    </div>
</body>
</html>
