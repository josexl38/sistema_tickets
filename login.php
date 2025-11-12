<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ip_cliente = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Verificar rate limiting
    if (!verificar_rate_limit($pdo, $ip_cliente)) {
        $mensaje = "Demasiados intentos fallidos. Intenta de nuevo en 15 minutos.";
    } else {
        // Verificar CSRF token
        if (!csrf_check($_POST["csrf"] ?? '')) {
            $mensaje = "Solicitud invalida. Por favor intenta de nuevo.";
        } else {
            $correo      = limpiar($_POST["correo"] ?? '');
            $contrasena  = $_POST["contraseña"] ?? '';

            // Validar dominio de correo
            if (!validar_dominio_email($correo)) {
                $mensaje = "Solo se permiten correos de los dominios autorizados";
                registrar_intento_login($pdo, $ip_cliente, false);
            } else {
                // Buscar usuario por correo
                $stmt = $pdo->prepare("SELECT id, nombre, contraseña, confirmado FROM usuarios WHERE correo = ?");
                $stmt->execute([$correo]);
                $usuario = $stmt->fetch();

                if ($usuario && password_verify($contrasena, $usuario["contraseña"])) {
                    if (!(int)$usuario["confirmado"]) {
                        $mensaje = "Tu cuenta no ha sido confirmada. <a href='reenviar_confirmacion.php'>Reenviar correo de confirmacion</a>";
                        registrar_intento_login($pdo, $ip_cliente, false);
                    } else {
                        // Login exitoso
                        registrar_intento_login($pdo, $ip_cliente, true);
                        
                        // Seguridad: regenerar ID sesion
                        session_regenerate_id(true);
                        $_SESSION["usuario_id"]     = (int)$usuario["id"];
                        $_SESSION["usuario_nombre"] = $usuario["nombre"];
                        $_SESSION['last_regeneration'] = time();
                        
                        // Actualizar último acceso
                        $stmt_acceso = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                        $stmt_acceso->execute([$usuario["id"]]);
                        
                        header("Location: dashboard.php");
                        exit();
                    }
                } else {
                    $mensaje = "Correo o contraseña incorrectos.";
                    registrar_intento_login($pdo, $ip_cliente, false);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .login-header {
            display: flex; align-items: center; justify-content: center;
            gap: 15px; margin-bottom: 30px;
        }
        .login-icon { font-size: 2.5em; color: #3182ce; }
        .form-footer { margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.1); }
        .form-footer a { color: #3182ce; font-size: .95em; margin: 0 10px; font-weight: 500; }
        .error { color: #c53030; background: rgba(229,62,62,.08); padding: 8px 12px; border-radius: 6px; margin-bottom: 12px; }
        /* Por si tu CSS no tiene estos estilos aún */
        .input-group { position: relative; width: 100%; max-width: 300px; margin: 0 auto; }
        .input-group input { width: 100%; padding-right: 40px; }
        .toggle-password { position: absolute; right: 10px; top: 8px; cursor: pointer; font-size: 18px; user-select: none; }
    </style>
</head>
<body data-page="login">
    <div class="container">
        <div class="box">
            <div class="login-header">
                <div class="login-icon">🔐</div>
                <h2>Acceso al Sistema</h2>
            </div>

            <?php if (!empty($mensaje)): ?>
                <p class="error"><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <form method="POST" onsubmit="return validarLogin();">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                
                <label>Correo:</label>
                <input type="email" name="correo" id="correo" placeholder="Ingresa tu correo" required>

                <label>Contraseña:</label>
                <div class="input-group">
                    <input type="password" name="contraseña" id="contraseña" placeholder="Ingresa tu contraseña" required>
                    <span class="toggle-password" onclick="mostrarContrasena(this)">👁️</span>
                </div>

                <button type="submit">Iniciar Sesión</button>

                <div class="form-footer">
                    <a href="recuperar.php">¿Olvidaste tu contraseña?</a>
                    <span>|</span>
                    <a href="index.php">Volver al inicio</a>
                </div>
            </form>
        </div>
    </div>

<script>
function mostrarContrasena(icon) {
    const input = document.getElementById("contraseña");
    input.type = input.type === "password" ? "text" : "password";
}
function validarLogin() {
    const email = document.getElementById("correo").value;
    if (!email.includes("@")) {
        alert("El correo debe contener '@'.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
