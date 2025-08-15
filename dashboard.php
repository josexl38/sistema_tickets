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

// Obtener estadísticas para el dashboard
$stmt_total = $pdo->prepare("SELECT COUNT(*) as total FROM tickets WHERE id_usuario = ?");
$stmt_total->execute([$_SESSION["usuario_id"]]);
$total_tickets = $stmt_total->fetchColumn();

$stmt_abiertos = $pdo->prepare("SELECT COUNT(*) as abiertos FROM tickets WHERE id_usuario = ? AND estado = 'Abierto'");
$stmt_abiertos->execute([$_SESSION["usuario_id"]]);
$tickets_abiertos = $stmt_abiertos->fetchColumn();

$stmt_cerrados = $pdo->prepare("SELECT COUNT(*) as cerrados FROM tickets WHERE id_usuario = ? AND estado = 'Cerrado'");
$stmt_cerrados->execute([$_SESSION["usuario_id"]]);
$tickets_cerrados = $stmt_cerrados->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <div class="box">
            <div class="welcome-message">
                <span class="icon" style="font-size: 1.2em;">waving_hand</span>
                ¡Bienvenido, <?php echo $_SESSION["usuario_nombre"]; ?>!
            </div>

            <!-- Estadísticas rápidas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">confirmation_number</div>
                    <div class="stat-number"><?php echo $total_tickets; ?></div>
                    <div class="stat-label">Total Tickets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">radio_button_unchecked</div>
                    <div class="stat-number"><?php echo $tickets_abiertos; ?></div>
                    <div class="stat-label">Abiertos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">check_circle</div>
                    <div class="stat-number"><?php echo $tickets_cerrados; ?></div>
                    <div class="stat-label">Resueltos</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-icon">confirmation_number</div>
                    <div class="card-title">Nuevo Ticket</div>
                    <div class="card-description">Reporta un problema o solicita soporte técnico</div>
                    <a href="crear_ticket.php" class="card-link">
                        <span class="icon">add</span>
                        Crear Ticket
                    </a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon">list_alt</div>
                    <div class="card-title">Mis Tickets</div>
                    <div class="card-description">Consulta el estado y historial de tus solicitudes</div>
                    <a href="ver_ticket.php" class="card-link">
                        <span class="icon">visibility</span>
                        Ver Historial
                    </a>
                </div>

                <?php if ($es_admin): ?>
                <div class="dashboard-card admin-card">
                    <div class="card-icon">admin_panel_settings</div>
                    <div class="card-title">Panel Admin</div>
                    <div class="card-description">Gestión completa del sistema de tickets</div>
                    <a href="admin_tickets.php" class="card-link">
                        <span class="icon">settings</span>
                        Administrar
                    </a>
                </div>
                <?php endif; ?>

                <div class="dashboard-card logout-card">
                    <div class="card-icon">logout</div>
                    <div class="card-title">Cerrar Sesión</div>
                    <div class="card-description">Terminar sesión de forma segura</div>
                    <a href="logout.php" class="card-link">
                        <span class="icon">exit_to_app</span>
                        Cerrar Sesión
                    </a>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.1);">
                <h3 style="color: #2d3748; margin-bottom: 20px; font-size: 1.2em;">
                    <span class="icon" style="margin-right: 8px;">flash_on</span>
                    Acciones Rápidas
                </h3>
                <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;">
                    <a href="crear_ticket.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #38a169 0%, #2f855a 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        <span class="icon">add_circle</span>
                        Nuevo Ticket
                    </a>
                    <a href="ver_ticket.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        <span class="icon">search</span>
                        Buscar Tickets
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
