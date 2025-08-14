<?php
session_start();
require_once "includes/db.php";
require_once "includes/funciones.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = limpiar($_POST["correo"]);
    $contraseña = $_POST["contraseña"];

    $stmt = $pdo->prepare("SELECT id, nombre, contraseña, confirmado FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($contraseña, $usuario["contraseña"])) {
        if (!$usuario["confirmado"]) {
            $mensaje = "Tu cuenta no ha sido confirmada. Revisa tu correo.";
        } else {
            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["usuario_nombre"] = $usuario["nombre"];
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $mensaje = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
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

        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Iniciar sesión</h2>

            <?php if (!empty($mensaje)): ?>
                <p class="error"><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <form method="POST" onsubmit="return validarLogin();">
                <label>Correo:</label>
                <input type="email" name="correo" id="correo" required>

                <label>Contraseña:</label>
                <div class="input-group">
                    <input type="password" name="contraseña" id="contraseña" required>
                    <span class="toggle-password" onclick="mostrarContrasena(this)">👁️</span>
                </div>

                <button type="submit">Entrar</button>
                <br>
                <a href="recuperar.php">¿Olvidaste tu contraseña?</a>
            </form>

            <br><a href="index.php">Volver</a>
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
