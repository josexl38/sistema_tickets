<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';

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
                    $mensaje_correo = "Has solicitado recuperar tu contrasena.\n\n"
                                    . "Haz clic en el siguiente enlace para crear una nueva:\n\n"
                                    . "$enlace\n\n"
                                    . "Este enlace expira en 1 hora por seguridad.\n\n"
                                    . "Si no solicitaste este cambio, ignora este correo.";
                    
                    // Usar el mismo formato directo que funciona
                    $cabeceras = "From: soporte@vw-potosina.com.mx";
                    if (@mail($correo, $asunto, $mensaje_correo, $cabeceras)) {
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
        .success-message {
            background: rgba(56, 161, 105, 0.1);
            color: #2f855a;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #38a169;
            margin-bottom: 20px;
            font-weight: 600;
            border: 1px solid rgba(56, 161, 105, 0.2);
        }
        .error-message {
            background: rgba(229, 62, 62, 0.1);
            color: #c53030;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #e53e3e;
            margin-bottom: 20px;
            font-weight: 600;
            border: 1px solid rgba(229, 62, 62, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Recuperar contraseña</h2>

            <?php if (!empty($mensaje)): ?>
                <?php if (strpos($mensaje, 'enviado') !== false): ?>
                    <div class="success-message"><?php echo $mensaje; ?></div>
                <?php else: ?>
                    <div class="error-message"><?php echo $mensaje; ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST" onsubmit="return validarCorreo();">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                
                <label>Ingrese su correo:</label>
                <input type="email" name="correo" id="correo" placeholder="ejemplo@vw-potosina.com.mx" required>

                <button type="submit">Enviar enlace</button>
            </form>

            <br>
            <div style="text-align: center;">
                <a href="login.php">← Volver al login</a>
                <span> | </span>
                <a href="index.php">🏠 Inicio</a>
            </div>
        </div>
    </div>

<script>
function validarCorreo() {
    const correo = document.getElementById("correo").value;
    if (!correo.includes("@")) {
        alert("El correo debe contener '@'.");
        return false;
    }
    if (!correo.includes("@vw-potosina.com.mx")) {
        alert("Solo se permiten correos del dominio @vw-potosina.com.mx");
        return false;
    }
    return true;
}
</script>
</body>
</html>
