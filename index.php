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
    <title>Sistema de Tickets VW</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .box h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #003366;
        }

        .botones {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }

        .botones a {
            display: inline-block;
            background-color: #003366;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 18px;
            transition: background-color 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .botones a:hover {
            background-color: #005599;
        }

        .botones a:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Bienvenido al sistema de tickets</h2>
            <div class="botones">
                <a href="registro.php">📝 Crear cuenta</a>
                <a href="login.php">🔐 Iniciar sesión</a>
            </div>
        </div>
    </div>
</body>
</html>
