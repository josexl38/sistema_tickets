<?php
require_once "includes/db.php";
require_once "includes/funciones.php";

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
            $enlace = "http://192.168.45.6/sistema_tickets/confirmar.php?token=$token";
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
    <title>Registro</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Registro</h2>

            <?php if (isset($mensaje)): ?>
                <p><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <form method="POST" onsubmit="return validarFormulario();">
                <label>Nombre completo:</label>
                <input type="text" name="nombre" required>

                <label>Correo:</label>
                <input type="email" name="correo" id="correo" required>

                <label>Contraseña:</label>
                <div class="input-group">
                    <input type="password" name="contraseña" id="contraseña" required>
                    <span class="toggle-password" onclick="mostrarContrasena(this)">👁️</span>
                </div>

                <label>Repetir contraseña:</label>
                <div class="input-group">
                    <input type="password" id="repetir" required>
                    <span class="toggle-password" onclick="mostrarRepetir(this)">👁️</span>
                </div>

                <label>Departamento:</label>
                <select name="departamento" required>
                    <option value="">-- Selecciona --</option>
                    <option value="Administracion">Administracion</option>
                    <option value="Servicio VW">Servicio VW</option>
                    <option value="Servicio Seat">Servicio Seat</option>
                    <option value="Seminuevos">Seminuevos</option>
                    <option value="Ventas VW">Ventas VW</option>
                    <option value="Ventas Seat">Ventas Seat</option>
                    <option value="Refacciones">Refacciones</option>
                </select>

                <button type="submit">Registrarse</button>
            </form>

            <br><a href="index.php">Volver</a>
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
