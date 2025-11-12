<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_check($_POST["csrf"] ?? '')) {
        $mensaje = "Solicitud invalida. Por favor intenta de nuevo.";
    } else {
        $correo = trim($_POST["correo"]);
        
        // Validar dominios permitidos
        if (!validar_dominio_email($correo)) {
            $mensaje = "Solo se permiten correos de los dominios autorizados.";
        } else {
            // Buscar usuario no confirmado
            $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE correo = ? AND confirmado = 0");
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Generar nuevo token
                $nuevo_token = bin2hex(random_bytes(32));
                $token_expira = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY_TIME);
                
                // Actualizar token
                $stmt = $pdo->prepare("UPDATE usuarios SET token_confirmacion = ?, token_reset_expira = ? WHERE id = ?");
                if ($stmt->execute([$nuevo_token, $token_expira, $usuario["id"]])) {
                    $enlace = BASE_URL . "confirmar.php?token=$nuevo_token";
                    $asunto = "Reenvio de confirmacion de cuenta - Sistema de Tickets";
                    $mensaje_correo = "Hola " . $usuario["nombre"] . ",\n\n"
                                    . "Hemos recibido una solicitud para reenviar el enlace de confirmacion de tu cuenta.\n\n"
                                    . "Para activar tu cuenta, haz clic en el siguiente enlace:\n\n"
                                    . "$enlace\n\n"
                                    . "Este enlace expira en 1 hora por seguridad.\n\n"
                                    . "Si no solicitaste esto, ignora este correo.\n\n"
                                    . "Saludos,\n"
                                    . "Sistema de Soporte VW Potosina";
                    
                    $cabeceras = "From: soporte@vw-potosina.com.mx";
                    
                    if (@mail($correo, $asunto, $mensaje_correo, $cabeceras)) {
                        $mensaje = "Se ha reenviado el correo de confirmacion (valido por 1 hora).";
                        error_log("Correo de reconfirmacion enviado a: $correo");
                    } else {
                        $mensaje = "Error al enviar el correo de confirmacion.";
                        error_log("Error enviando reconfirmacion a: $correo");
                    }
                } else {
                    $mensaje = "Error al generar nuevo token.";
                }
            } else {
                // Por seguridad, no revelar si existe o ya está confirmado
                $mensaje = "Si el correo existe y no esta confirmado, recibiras un enlace.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-page="login">
<head>
    <meta charset="UTF-8">
    <title>Reenviar confirmación</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
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
        .info-box {
            background: rgba(66, 153, 225, 0.1);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            border-left: 4px solid #4299e1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>📧 Reenviar confirmación de cuenta</h2>

            <div class="info-box">
                <strong>¿Tu enlace de confirmación expiró?</strong><br>
                No te preocupes, puedes solicitar un nuevo enlace aquí. Ingresa el mismo correo con el que te registraste.
            </div>

            <?php if (!empty($mensaje)): ?>
                <?php if (strpos($mensaje, 'reenviado') !== false): ?>
                    <div class="success-message">
                        <?php echo $mensaje; ?>
                        <br><br>
                        <small><strong>💡 Revisa tu bandeja de entrada y spam.</strong></small>
                    </div>
                <?php else: ?>
                    <div class="error-message"><?php echo $mensaje; ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                
                <label>Correo electrónico:</label>
                <input type="email" name="correo" placeholder="tu-correo@dominio-autorizado.com.mx" required>

                <div style="background: rgba(255, 193, 7, 0.1); padding: 12px; border-radius: 8px; margin: 16px 0; font-size: 0.9em; color: #856404;">
                    <strong>📋 Dominios autorizados:</strong><br>
                    • @vw-potosina.com.mx<br>
                    • @seat-potosina.com.mx<br>
                    • @cupragarage-sanluis.com.mx<br>
                    • @abellauto.com.mx
                </div>

                <button type="submit">Reenviar confirmación</button>
            </form>

            <br>
            <div style="text-align: center;">
                <a href="login.php">← Iniciar sesión</a>
                <span> | </span>
                <a href="registro.php">📝 Crear cuenta nueva</a>
                <span> | </span>
                <a href="index.php">🏠 Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>
