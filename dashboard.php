<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';
redirigir_si_no_logueado();

function es_admin_local($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $res = $stmt->fetch();
    return $res && $res["rol"] === "admin";
}

$es_admin = es_admin_local($pdo, $_SESSION["usuario_id"]);

// Obtener estadísticas mejoradas
$stats = obtener_estadisticas_dashboard($pdo, $_SESSION["usuario_id"], $es_admin);

// Obtener tickets recientes del usuario
$stmt_recientes = $pdo->prepare("SELECT id, numero_ticket, titulo, estado, prioridad, fecha_creacion FROM tickets WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 3");
$stmt_recientes->execute([$_SESSION["usuario_id"]]);
$tickets_recientes = $stmt_recientes->fetchAll();

// Obtener notificaciones no leídas
$stmt_notif = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leida = FALSE");
$stmt_notif->execute([$_SESSION["usuario_id"]]);
$notificaciones_pendientes = $stmt_notif->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es" data-page="dashboard">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal</title>
    <link rel="stylesheet" href="css/estilo.css">
    <script>
        // Función para alternar modo oscuro
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            const icon = document.querySelector('.toggle-icon');
            
            if (isDark) {
                icon.textContent = '☀️';
            } else {
                icon.textContent = '🌙';
            }
            
            localStorage.setItem('darkMode', isDark);
        }
        
        // Cargar preferencia de modo oscuro
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = localStorage.getItem('darkMode') === 'true';
            const icon = document.querySelector('.toggle-icon');
            
            if (isDark) {
                document.body.classList.add('dark-mode');
                icon.textContent = '☀️';
            } else {
                icon.textContent = '🌙';
            }
        });
    </script>
</head>
<body>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Alternar modo oscuro">
        <span class="toggle-icon">🌙</span>
    </button>
    
    <div class="container">
        <div class="box">
            <div class="welcome-message">
                👋
                ¡Bienvenido, <?php echo $_SESSION["usuario_nombre"]; ?>!
            </div>
            
            <?php if ($notificaciones_pendientes > 0): ?>
            <div class="alert alert-info">
                Tienes <?php echo $notificaciones_pendientes; ?> notificación(es) pendiente(s)
            </div>
            <?php endif; ?>

            <!-- Estadísticas rápidas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">confirmation_number</div>
                    <div class="stat-number"><?php echo $stats['total_tickets'] ?? 0; ?></div>
                    <div class="stat-label">Total Tickets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">radio_button_unchecked</div>
                    <div class="stat-number"><?php echo $stats['tickets_abiertos'] ?? 0; ?></div>
                    <div class="stat-label">Abiertos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">check_circle</div>
                    <div class="stat-number"><?php echo $stats['tickets_cerrados'] ?? 0; ?></div>
                    <div class="stat-label">Resueltos</div>
                </div>
                <?php if ($es_admin && isset($stats['tickets_en_progreso'])): ?>
                <div class="stat-card">
                    <div class="stat-icon">hourglass_empty</div>
                    <div class="stat-number"><?php echo $stats['tickets_en_progreso']; ?></div>
                    <div class="stat-label">En Progreso</div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Tickets recientes -->
            <?php if (!empty($tickets_recientes)): ?>
            <div style="margin: 32px 0;">
                <h3 style="color: #2d3748; margin-bottom: 20px; font-size: 1.2em;">
                    <span class="icon" style="margin-right: 8px;">history</span>
                    Tickets Recientes
                </h3>
                <div style="display: grid; gap: 12px;">
                    <?php foreach ($tickets_recientes as $ticket): ?>
                    <div style="background: rgba(255, 255, 255, 0.95); padding: 16px; border-radius: 12px; border-left: 4px solid #4299e1; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?php echo $ticket['numero_ticket']; ?></strong> - <?php echo $ticket['titulo']; ?>
                            <br>
                            <small style="color: #4a5568;">
                                <span class="<?php echo obtener_prioridad_clase($ticket['prioridad']); ?>"><?php echo $ticket['prioridad']; ?></span>
                                • <?php echo tiempo_transcurrido($ticket['fecha_creacion']); ?>
                            </small>
                        </div>
                        <div>
                            <span class="<?php echo obtener_estado_clase($ticket['estado']); ?>"><?php echo $ticket['estado']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

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
                
                <div class="dashboard-card">
                    <div class="card-icon">help</div>
                    <div class="card-title">Base de Conocimientos</div>
                    <div class="card-description">Encuentra respuestas a preguntas frecuentes</div>
                    <a href="base_conocimientos.php" class="card-link">
                        <span class="icon">search</span>
                        Buscar Ayuda
                    </a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon">notifications</div>
                    <div class="card-title">Notificaciones</div>
                    <div class="card-description">Revisa tus notificaciones y actualizaciones</div>
                    <a href="notificaciones.php" class="card-link" style="position: relative;">
                        <span class="icon">notifications</span>
                        Ver Notificaciones
                        <?php if ($notificaciones_pendientes > 0): ?>
                            <span class="notification-badge"><?php echo $notificaciones_pendientes; ?></span>
                        <?php endif; ?>
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
                    <a href="base_conocimientos.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        <span class="icon">help</span>
                        FAQ
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
