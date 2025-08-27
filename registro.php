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
        $nombre = limpiar($_POST["nombre"]);
        $correo = limpiar($_POST["correo"]);
        
        // Validar dominio de correo
        if (!validar_dominio_email($correo)) {
            $mensaje = "Solo se permiten correos de los dominios autorizados";
        } else {
            $contraseña = password_hash($_POST["contraseña"], PASSWORD_DEFAULT);
            $departamento = limpiar($_POST["departamento"]);
            $token = bin2hex(random_bytes(32));
            $token_expira = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY_TIME);

            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            if ($stmt->rowCount() > 0) {
                $mensaje = "El correo ya esta registrado.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, departamento, token_confirmacion, token_reset_expira) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$nombre, $correo, $contraseña, $departamento, $token, $token_expira])) {
                    // Usar el mismo formato que funciona en crear_ticket.php
                    $enlace = BASE_URL . "confirmar.php?token=$token";
                    $asunto = "Confirmacion de cuenta - Sistema de Tickets";
                    $mensaje_correo = "Hola $nombre,\n\n"
                                    . "Para activar tu cuenta, haz clic en el siguiente enlace:\n\n"
                                    . "$enlace\n\n"
                                    . "Este enlace expira en 1 hora por seguridad.\n\n"
                                    . "Si no solicitaste esta cuenta, ignora este correo.\n\n"
                                    . "Saludos,\n"
                                    . "Sistema de Soporte VW Potosina";
                    
                    // Usar exactamente el mismo formato que funciona en crear_ticket.php
                    $cabeceras = "From: soporte@vw-potosina.com.mx";
                    
                    if (@mail($correo, $asunto, $mensaje_correo, $cabeceras)) {
                        $mensaje = "Registro exitoso. Se ha enviado un correo de confirmacion (valido por 1 hora).";
                        
                        // Log para debugging
                        error_log("Correo de registro enviado a: $correo con token: $token");
                    } else {
                        $mensaje = "Registro realizado, pero no se pudo enviar el correo de confirmacion.";
                        error_log("Error enviando correo de registro a: $correo");
                    }
                } else {
                    $mensaje = "Error al registrar el usuario.";
                    error_log("Error al insertar usuario: " . print_r($stmt->errorInfo(), true));
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .registro-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .registro-icon {
            font-size: 2.5em;
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-footer {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <div class="registro-header">
                <div class="registro-icon">📝</div>
                <h2>Registro de Usuario</h2>
            </div>

            <?php if (isset($mensaje)): ?>
                <?php if (strpos($mensaje, 'exitoso') !== false): ?>
                    <div class="success-message">
                        <?php echo $mensaje; ?>
                        <br><br>
                        <small><strong>💡 Revisa tu bandeja de entrada y spam.</strong> El correo puede tardar unos minutos en llegar.</small>
                    </div>
                <?php else: ?>
                    <div class="error-message"><?php echo $mensaje; ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST" onsubmit="return validarFormulario();">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                
                <label>Nombre completo:</label>
                <input type="text" name="nombre" placeholder="Ingresa tu nombre completo" required>

                <label>Correo:</label>
                <input type="email" name="correo" id="correo" placeholder="ejemplo@vw-potosina.com.mx" required>

                <label>Contraseña:</label>
                <div class="input-group">
                    <input type="password" name="contraseña" id="contraseña" placeholder="Crea una contraseña segura" required minlength="6">
                    <span class="toggle-password" onclick="mostrarContrasena(this)">👁️</span>
                </div>

                <label>Repetir contraseña:</label>
                <div class="input-group">
                    <input type="password" id="repetir" placeholder="Repite la contraseña anterior" required minlength="6">
                    <span class="toggle-password" onclick="mostrarRepetir(this)">👁️</span>
                </div>

                <label>Departamento:</label>
                <select name="departamento" required>
                    <option value="">-- Selecciona tu departamento --</option>
                    <option value="Administracion">Administracion</option>
                    <option value="Servicio VW">Servicio VW</option>
                    <option value="Servicio Seat">Servicio Seat</option>
                    <option value="Seminuevos">Seminuevos</option>
                    <option value="Ventas VW">Ventas VW</option>
                    <option value="Ventas Seat">Ventas Seat</option>
                    <option value="Ventas Digitales">Ventas Digitales</option>
                    <option value="Refacciones">Refacciones</option>
                    <option value="Marketing">Marketing</option>
                </select>

                <button type="submit">Crear Cuenta</button>
                
                <div class="form-footer">
                    <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
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

function mostrarRepetir(icon) {
    const input = document.getElementById("repetir");
    input.type = input.type === "password" ? "text" : "password";
}

function validarFormulario() {
    const email = document.getElementById("correo").value;
    const pass1 = document.getElementById("contraseña").value;
    const pass2 = document.getElementById("repetir").value;
    
    const dominiosPermitidos = [
        '@vw-potosina.com.mx',
        '@seat-potosina.com.mx', 
        '@cupragarage-sanluis.com.mx',
        '@abellauto.com.mx'
    ];
    
    let dominioValido = false;
    for (let dominio of dominiosPermitidos) {
        if (email.includes(dominio)) {
            dominioValido = true;
            break;
        }
    }
    
    if (!dominioValido) {
        alert("Solo se permiten correos de los dominios autorizados:\n• @vw-potosina.com.mx\n• @seat-potosina.com.mx\n• @cupragarage-sanluis.com.mx\n• @abellauto.com.mx");
        return false;
    }
    
    if (pass1.length < 6) {
        alert("La contraseña debe tener al menos 6 caracteres.");
        return false;
    }
    
    if (pass1 !== pass2) {
        alert("Las contraseñas no coinciden.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
