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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .welcome-message {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.2em;
            margin-bottom: 30px;
            font-weight: 700;
        }

        .admin-card {
            border: 2px solid #ed8936;
            background: linear-gradient(135deg, rgba(237, 137, 54, 0.05) 0%, rgba(221, 107, 32, 0.05) 100%);
        }

        .logout-card {
            border: 2px solid #e53e3e;
            background: linear-gradient(135deg, rgba(229, 62, 62, 0.05) 0%, rgba(197, 48, 48, 0.05) 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2 class="welcome-message">¡Bienvenido, <?php echo $_SESSION["usuario_nombre"]; ?>! 👋</h2>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-icon">🎫</div>
                    <div class="card-title">Nuevo Ticket</div>
                    <div class="card-description">Reporta un problema o solicita soporte técnico</div>
                    <a href="crear_ticket.php" class="card-link">Crear Ticket</a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon">📋</div>
                    <div class="card-title">Mis Tickets</div>
                    <div class="card-description">Consulta el estado y historial de tus solicitudes</div>
                    <a href="ver_ticket.php" class="card-link">Ver Historial</a>
                </div>

                <?php if ($es_admin): ?>
                <div class="dashboard-card admin-card">
                    <div class="card-icon">⚙️</div>
                    <div class="card-title">Panel Admin</div>
                    <div class="card-description">Gestión completa del sistema de tickets</div>
                    <a href="admin_tickets.php" class="card-link">Administrar</a>
                </div>
                <?php endif; ?>

                <div class="dashboard-card logout-card">
                    <div class="card-icon">🚪</div>
                    <div class="card-title">Cerrar Sesión</div>
                    <div class="card-description">Terminar sesión de forma segura</div>
                    <a href="logout.php" class="card-link">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
