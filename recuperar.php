<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["correo"]);
    $token = bin2hex(random_bytes(16));

    $stmt = $pdo->prepare("UPDATE usuarios SET token_confirmacion = ? WHERE correo = ?");
    if ($stmt->execute([$token, $correo])) {
        $enlace = "http://192.168.45.6/sistema_tickets/reestablecer.php?token=$token";
        $asunto = "Recuperacion de contrasena";
        $mensaje_correo = "Has solicitado recuperar tu contrasena.\n\nHaz clic en el siguiente enlace para crear una nueva:\n\n$enlace";
        $cabeceras = "From: soporte@vw-potosina.com.mx";

        if (mail($correo, $asunto, $mensaje_correo, $cabeceras)) {
            $mensaje = "✅ Se ha enviado un correo con el enlace para recuperar tu contrasena.";
        } else {
            $mensaje = "❌ No se pudo enviar el correo.";
        }
    } else {
        $mensaje = "❌ Correo no encontrado o error al generar enlace.";
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .mensaje {
            margin-bottom: 15px;
            font-weight: bold;
            color: #003366;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Recuperar contraseña</h2>

            <?php if (!empty($mensaje)): ?>
                <p class="mensaje"><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <form method="POST" onsubmit="return validarCorreo();">
                <label>Ingrese su correo:</label>
                <input type="email" name="correo" id="correo" required>

                <button type="submit">Enviar enlace</button>
            </form>

            <br><a href="login.php">Volver</a>
        </div>
    </div>

<script>
function validarCorreo() {
    const correo = document.getElementById("correo").value;
    if (!correo.includes("@")) {
        alert("El correo debe contener '@'.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
