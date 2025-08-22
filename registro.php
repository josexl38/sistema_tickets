<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = limpiar($_POST["nombre"]);
    $correo = limpiar($_POST["correo"]);
    $contraseña = password_hash($_POST["contraseña"], PASSWORD_DEFAULT);
    $departamento = limpiar($_POST["departamento"]);
    $token = bin2hex(random_bytes(16));

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->rowCount() > 0) {
        $mensaje = "El correo ya esta registrado.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, departamento, token_confirmacion) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$nombre, $correo, $contraseña, $departamento, $token])) {
            $enlace = BASE_URL . "confirmar.php?token=$token";
            $asunto = "Confirmacion de cuenta - Sistema de Tickets";
            $mensaje_correo = "Hola $nombre,\n\nPara activar tu cuenta, haz clic en el siguiente enlace:\n\n$enlace\n\nSaludos,\nSistema de Soporte VW Potosina";
            $cabeceras = "From: soporte@vw-potosina.com.mx";

            if (mail($correo, $asunto, $mensaje_correo, $cabeceras)) {
                $mensaje = "Registro exitoso. Se ha enviado un correo de confirmacion.";
            } else {
                $mensaje = "Registro realizado, pero no se pudo enviar el correo.";
            }
        } else {
            $mensaje = "Error al registrar el usuario.";
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
                <div class="success-message"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validarFormulario();">
                <label>Nombre completo:</label>
                <input type="text" name="nombre" placeholder="Ingresa tu nombre completo" required>

                <label>Correo:</label>
                <input type="email" name="correo" id="correo" placeholder="Ingresa tu correo electrónico" required>

                <label>Contraseña:</label>
                <div class="input-group">
                    <input type="password" name="contraseña" id="contraseña" placeholder="Crea una contraseña segura" required>
                    <span class="toggle-password" onclick="mostrarContrasena(this)">👁️</span>
                </div>

                <label>Repetir contraseña:</label>
                <div class="input-group">
                    <input type="password" id="repetir" placeholder="Repite la contraseña anterior" required>
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
                    <option value="Refacciones">Refacciones</option>
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

    if (!email.includes("@")) {
        alert("El correo debe contener '@'.");
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
