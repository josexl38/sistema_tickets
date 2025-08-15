<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Tickets VW</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .welcome-header {
            font-size: 2.8em;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .subtitle {
            color: #4a5568;
            font-size: 1.3em;
            margin-bottom: 30px;
            opacity: 0.9;
            font-weight: 500;
        }

        .logo-section {
            margin-bottom: 30px;
        }

        .logo-icon {
            font-size: 4.5em;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <div class="logo-section">
                <div class="logo-icon">🎫</div>
                <h2 class="welcome-header">Sistema de Tickets VW</h2>
                <p class="subtitle">Plataforma profesional de soporte técnico</p>
            </div>
            <div class="botones">
                <a href="registro.php">📝 Crear Nueva Cuenta</a>
                <a href="login.php">🔐 Acceder al Sistema</a>
            </div>
        </div>
    </div>
</body>
</html>
