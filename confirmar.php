<?php
require_once "includes/db.php";

$mensaje = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token_confirmacion = ?");
    $stmt->execute([$token]);

    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch();
        $stmt = $pdo->prepare("UPDATE usuarios SET confirmado = 1, token_confirmacion = NULL WHERE id = ?");
        $stmt->execute([$usuario["id"]]);
        $mensaje = "✅ Cuenta confirmada correctamente. <a href='login.php'>Iniciar sesión</a>";
    } else {
        $mensaje = "❌ Token invalido o ya fue usado.";
    }
} else {
    $mensaje = "❌ Token no proporcionado.";
}
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Confirmar cuenta</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Confirmación de cuenta</h2>
            <p><?php echo $mensaje; ?></p>
        </div>
    </div>
</body>
</html>
