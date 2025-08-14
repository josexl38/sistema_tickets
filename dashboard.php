<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
redirigir_si_no_logueado();

function es_admin_local($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $res = $stmt->fetch();
    return $res && $res["rol"] === "admin";
}

$es_admin = es_admin_local($pdo, $_SESSION["usuario_id"]);
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Panel principal</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        ul.opciones {
            list-style: none;
            padding: 0;
            margin: 20px 0 0 0;
        }

        ul.opciones li {
            margin: 15px 0;
            font-size: 18px;
        }

        ul.opciones a {
            display: inline-block;
            background-color: #003366;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        ul.opciones a:hover {
            background-color: #005599;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Bienvenido, <?php echo $_SESSION["usuario_nombre"]; ?></h2>

            <ul class="opciones">
                <li><a href="crear_ticket.php">➕ Crear nuevo ticket</a></li>
                <li><a href="ver_ticket.php">📋 Ver mis tickets</a></li>
                <?php if ($es_admin): ?>
                    <li><a href="admin_tickets.php">🛠️ Panel de administrador</a></li>
                <?php endif; ?>
                <li><a href="logout.php">🔒 Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
