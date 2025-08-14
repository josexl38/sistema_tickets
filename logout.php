<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Sesión cerrada</title>
    <meta http-equiv="refresh" content="1;url=index.php">
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .box p {
            font-size: 18px;
            color: #003366;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Sesión cerrada</h2>
            <p>Redirigiendo al inicio...  Bye</p>
        </div>
    </div>
</body>
</html>
