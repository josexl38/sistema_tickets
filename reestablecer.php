<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$mensaje = "";

if (isset($_GET["token"])) {
    $token = $_GET["token"];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nueva = password_hash($_POST["nueva"], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET contraseña = ?, token_confirmacion = NULL WHERE token_confirmacion = ?");
        if ($stmt->execute([$nueva, $token])) {
            $mensaje = "✅ Contraseña actualizada correctamente. <a href='login.php'>Iniciar sesión</a>";
        } else {
            $mensaje = "❌ Error al actualizar la contraseña.";
        }
    }
} else {
    $mensaje = "❌ Token no válido.";
}
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
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
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Crear nueva contraseña</h2>

            <?php if (!empty($mensaje)): ?>
                <p class="mensaje"><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <?php if (isset($_GET["token"]) && empty($mensaje)): ?>
                <form method="POST">
                    <label>Nueva contraseña:</label>
                    <div class="input-group">
                        <input type="password" name="nueva" id="nueva" required>
                        <span class="toggle-password" onclick="mostrarContrasena(this)">👁️</span>
                    </div>
                    <button type="submit">Actualizar</button>
                </form>
            <?php endif; ?>

            <br><a href="login.php">Volver</a>
        </div>
    </div>

<script>
function mostrarContrasena(icon) {
    const input = document.getElementById("nueva");
    input.type = input.type === "password" ? "text" : "password";
}
</script>
</body>
</html>
