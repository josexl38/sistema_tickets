<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar CSRF token
    if (!csrf_check($_POST["csrf"] ?? '')) {
        $mensaje = "Solicitud invalida. Por favor intenta de nuevo.";
    } else {
        $correo = trim($_POST["correo"]);
        
        // Validar dominio de correo
        if (!validar_dominio_email($correo)) {
            $mensaje = "Solo se permiten correos del dominio @vw-potosina.com.mx";
        } else {
            $token = bin2hex(random_bytes(32));
            $token_expira = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY_TIME);

            // Verificar que el usuario existe
            $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $stmt_check->execute([$correo]);
            
            if ($stmt_check->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE usuarios SET token_reset = ?, token_reset_expira = ? WHERE correo = ?");
                if ($stmt->execute([$token, $token_expira, $correo])) {
                    $enlace = BASE_URL . "reestablecer.php?token=$token";
                    $asunto = "Recuperacion de contrasena";
                    $mensaje_correo = "Has solicitado recuperar tu contrasena.\n\nHaz clic en el siguiente enlace para crear una nueva:\n\n$enlace\n\nEste enlace expira en 1 hora por seguridad.\n\nSi no solicitaste este cambio, ignora este correo.";
                    
                    if (enviar_notificacion_email($correo, $asunto, $mensaje_correo)) {
                        $mensaje = "Se ha enviado un correo con el enlace para recuperar tu contrasena (valido por 1 hora).";
                    } else {
                        $mensaje = "No se pudo enviar el correo.";
                    }
                } else {
                    $mensaje = "Error al generar enlace de recuperacion.";
                }
            } else {
                // Por seguridad, no revelar si el correo existe o no
                $mensaje = "Si el correo existe en nuestro sistema, recibiras un enlace de recuperacion.";
            }
        }
    }
}
    $correo = trim($_POST["correo"]);
    $token = bin2hex(random_bytes(16));

    $stmt = $pdo->prepare("UPDATE usuarios SET token_confirmacion = ? WHERE correo = ?");
    if ($stmt->execute([$token, $correo])) {
        $enlace = BASE_URL . "reestablecer.php?token=$token";
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
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                
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
