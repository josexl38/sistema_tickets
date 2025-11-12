<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';

$mensaje = "";
$token_valido = false;

if (isset($_GET["token"])) {
    $token = $_GET["token"];
    
    // Verificar que el token existe y no ha expirado
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token_reset = ? AND token_reset_expira > NOW()");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        $mensaje = "Token invalido o expirado. Solicita un nuevo enlace de recuperacion.";
    } else {
        $token_valido = true;
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Verificar CSRF token
            if (!csrf_check($_POST["csrf"] ?? '')) {
                $mensaje = "Solicitud invalida. Por favor intenta de nuevo.";
            } else {
                $nueva_password = $_POST["nueva"];
                
                // Validar que la contraseña no esté vacía
                if (empty($nueva_password)) {
                    $mensaje = "La contraseña no puede estar vacía.";
                } else {
                    $nueva_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
                    
                    // Actualizar contraseña y limpiar tokens (un solo uso)
                    $stmt = $pdo->prepare("UPDATE usuarios SET contraseña = ?, token_reset = NULL, token_reset_expira = NULL WHERE id = ?");
                    if ($stmt->execute([$nueva_hash, $usuario["id"]])) {
                        $mensaje = "Contraseña actualizada correctamente. <a href='login.php'>Iniciar sesion</a>";
                        $token_valido = false; // Ya no mostrar el formulario
                    } else {
                        $mensaje = "Error al actualizar la contraseña.";
                    }
                }
            }
        }
    }
} else {
    $mensaje = "Token no valido.";
}
?>

<!DOCTYPE html>
<html lang="es" data-page="login">
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .input-group {
            position: relative;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }
        .input-group input {
            width: 100%;
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 8px;
            cursor: pointer;
            font-size: 18px;
            user-select: none;
        }
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
            <h2>Crear nueva contraseña</h2>

            <?php if (!empty($mensaje)): ?>
                <?php if (strpos($mensaje, 'correctamente') !== false): ?>
                    <div class="success-message"><?php echo $mensaje; ?></div>
                <?php else: ?>
                    <div class="error-message"><?php echo $mensaje; ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($token_valido): ?>
                <form method="POST" onsubmit="return validarPassword();">
                    <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                    
                    <label>Nueva contraseña:</label>
                    <div class="input-group">
                        <input type="password" name="nueva" id="nueva" placeholder="Ingresa tu nueva contraseña" required minlength="6">
                        <span class="toggle-password" onclick="mostrarContrasena(this)">👁️</span>
                    </div>
                    
                    <label>Confirmar contraseña:</label>
                    <div class="input-group">
                        <input type="password" id="confirmar" placeholder="Repite la nueva contraseña" required minlength="6">
                        <span class="toggle-password" onclick="mostrarConfirmar(this)">👁️</span>
                    </div>
                    
                    <button type="submit">Actualizar Contraseña</button>
                </form>
            <?php endif; ?>

            <br>
            <div style="text-align: center;">
                <a href="login.php">← Volver al login</a>
                <span> | </span>
                <a href="recuperar.php">🔄 Solicitar nuevo enlace</a>
            </div>
        </div>
    </div>

<script>
function mostrarContrasena(icon) {
    const input = document.getElementById("nueva");
    input.type = input.type === "password" ? "text" : "password";
}

function mostrarConfirmar(icon) {
    const input = document.getElementById("confirmar");
    input.type = input.type === "password" ? "text" : "password";
}

function validarPassword() {
    const nueva = document.getElementById("nueva").value;
    const confirmar = document.getElementById("confirmar").value;
    
    if (nueva.length < 6) {
        alert("La contraseña debe tener al menos 6 caracteres.");
        return false;
    }
    
    if (nueva !== confirmar) {
        alert("Las contraseñas no coinciden.");
        return false;
    }
    
    return true;
}
</script>
</body>
</html>
