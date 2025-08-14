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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .login-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .login-icon {
            font-size: 2.5em;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-footer {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }

        .form-footer a {
            color: #667eea;
            font-size: 0.95em;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <div class="login-header">
                <div class="login-icon">🔐</div>
                <h2>Iniciar Sesión</h2>
            </div>

            <?php if (!empty($mensaje)): ?>
                <p class="error"><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <form method="POST" onsubmit="return validarLogin();">
                <label>Correo:</label>
                <input type="email" name="correo" id="correo" placeholder="tu@email.com" required>

                <label>Contraseña:</label>
                <div class="input-group">
                    <input type="password" name="contraseña" id="contraseña" placeholder="Tu contraseña" required>
                    <span class="toggle-password" onclick="mostrarContrasena(this)">👁️</span>
                </div>

                <button type="submit">Entrar</button>
                
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
