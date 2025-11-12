<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF
    if (!csrf_check($_POST["csrf"] ?? '')) {
        $mensaje = "Solicitud inválida. Por favor intenta de nuevo.";
    } else {
        $correo = strtolower(trim($_POST["correo"] ?? ''));

        // Dominio permitido
        if (!validar_dominio_email($correo)) {
            // lista bonita para el mensaje
            $doms = is_array(DOMINIOS_PERMITIDOS) ? implode(', ', DOMINIOS_PERMITIDOS) : (string)DOMINIOS_PERMITIDOS;
            $mensaje = "Solo se permiten correos de los dominios autorizados: {$doms}";
        } else {
            try {
                // ¿existe el usuario?
                $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
                $stmt_check->execute([$correo]);
                $usuario = $stmt_check->fetch();

                // Por seguridad: mismo mensaje si existe o no
                if ($usuario) {
                    $token        = bin2hex(random_bytes(32));
                    $token_expira = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY_TIME);

                    $stmt = $pdo->prepare("UPDATE usuarios SET token_reset = ?, token_reset_expira = ? WHERE id = ?");
                    $stmt->execute([$token, $token_expira, $usuario['id']]);

                    $enlace = BASE_URL . "reestablecer.php?token=" . urlencode($token);

                    $asunto = "Recuperacion de contrasena";
                    $cuerpo = "Has solicitado recuperar tu contrasena.\n\n"
                            . "Haz clic en el siguiente enlace para crear una nueva (válido por 1 hora):\n"
                            . "$enlace\n\n"
                            . "Si no solicitaste este cambio, ignora este correo.";
                    $cabeceras = "From: soporte@vw-potosina.com.mx";

                    @mail($correo, $asunto, $cuerpo, $cabeceras);
                }

                $mensaje = "Si el correo existe en nuestro sistema, recibirás un enlace de recuperación (válido por 1 hora).";
            } catch (Throwable $e) {
                error_log("RECUPERAR ERROR: " . $e->getMessage());
                $mensaje = "Ocurrió un error al generar el enlace. Intenta más tarde.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .success-message{background:rgba(56,161,105,.1);color:#2f855a;padding:20px;border-radius:12px;border-left:4px solid #38a169;margin-bottom:20px;font-weight:600;border:1px solid rgba(56,161,105,.2)}
        .error-message{background:rgba(229,62,62,.1);color:#c53030;padding:20px;border-radius:12px;border-left:4px solid #e53e3e;margin-bottom:20px;font-weight:600;border:1px solid rgba(229,62,62,.2)}
    </style>
</head>
<body data-page="login">
<div class="container">
    <div class="box">
        <h2>Recuperar contraseña</h2>

        <?php if (!empty($mensaje)): ?>
            <?php if (stripos($mensaje, 'recibirás') !== false || stripos($mensaje, 'recibiras') !== false): ?>
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
        <div style="text-align:center">
            <a href="login.php">← Volver al login</a>
            <span> | </span>
            <a href="index.php">🏠 Inicio</a>
        </div>
    </div>
</div>
<script>
function validarCorreo(){
    const c = document.getElementById('correo').value.trim().toLowerCase();
    if(!c.includes('@')){ alert("El correo debe contener '@'."); return false; }
    // Validación simple del dominio principal (ayuda a UX, seguridad real está en PHP)
    if(!c.endsWith('@vw-potosina.com.mx') && !c.endsWith('@seat-potosina.com.mx') && !c.endsWith('@cupragarage-sanluis.com.mx') && !c.endsWith('@abellauto.com.mx')){
        alert("Solo se permiten correos de dominios autorizados.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
