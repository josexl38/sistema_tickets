<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';
redirigir_si_no_logueado();

// Marcar notificación como leída
if (isset($_POST['marcar_leida'])) {
    $notif_id = intval($_POST['notif_id']);
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$notif_id, $_SESSION["usuario_id"]]);
    header("Location: notificaciones.php");
    exit();
}

// Marcar todas como leídas
if (isset($_POST['marcar_todas_leidas'])) {
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE usuario_id = ?");
    $stmt->execute([$_SESSION["usuario_id"]]);
    header("Location: notificaciones.php");
    exit();
}

// Obtener notificaciones del usuario
$stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha_creacion DESC LIMIT 50");
$stmt->execute([$_SESSION["usuario_id"]]);
$notificaciones = $stmt->fetchAll();

// Contar no leídas
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leida = FALSE");
$stmt_count->execute([$_SESSION["usuario_id"]]);
$no_leidas = $stmt_count->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .notification-item {
            background: rgba(255, 255, 255, 0.95);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 12px;
            border-left: 4px solid #4299e1;
            transition: all 0.3s ease;
        }
        
        .notification-item.unread {
            background: rgba(66, 153, 225, 0.1);
            border-left-color: #3182ce;
        }
        
        .notification-item.success {
            border-left-color: #38a169;
        }
        
        .notification-item.warning {
            border-left-color: #ed8936;
        }
        
        .notification-item.error {
            border-left-color: #e53e3e;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .notification-title {
            font-weight: 600;
            color: #2d3748;
        }
        
        .notification-time {
            font-size: 0.85em;
            color: #4a5568;
        }
        
        .notification-message {
            color: #2d3748;
            line-height: 1.5;
        }
        
        .mark-read-btn {
            background: none;
            border: none;
            color: #4299e1;
            cursor: pointer;
            font-size: 0.8em;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .mark-read-btn:hover {
            background: rgba(66, 153, 225, 0.1);
        }
    </style>
</head>
<body  data-page="dashboard">
    <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Alternar modo oscuro"></button>
    
    <div class="container">
        <div class="box">
            <h2>🔔 Notificaciones</h2>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 16px; background: rgba(66, 153, 225, 0.1); border-radius: 12px;">
                <div>
                    <strong><?php echo $no_leidas; ?></strong> notificación(es) sin leer
                </div>
                <?php if ($no_leidas > 0): ?>
                <form method="POST" style="background: none; padding: 0; box-shadow: none; border: none; margin: 0;">
                    <button type="submit" name="marcar_todas_leidas" style="padding: 8px 16px; font-size: 0.9em; margin: 0;">
                        Marcar todas como leídas
                    </button>
                </form>
                <?php endif; ?>
            </div>
            
            <?php if (empty($notificaciones)): ?>
                <div class="alert alert-info">
                    No tienes notificaciones aún.
                </div>
            <?php else: ?>
                <?php foreach ($notificaciones as $notif): ?>
                    <div class="notification-item <?php echo !$notif['leida'] ? 'unread' : ''; ?> <?php echo $notif['tipo']; ?>">
                        <div class="notification-header">
                            <div class="notification-title">
                                <?php if (!$notif['leida']): ?>
                                    <span style="color: #4299e1; margin-right: 8px;">●</span>
                                <?php endif; ?>
                                <?php echo $notif['titulo']; ?>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="notification-time">
                                    <?php echo tiempo_transcurrido($notif['fecha_creacion']); ?>
                                </div>
                                <?php if (!$notif['leida']): ?>
                                    <form method="POST" style="background: none; padding: 0; box-shadow: none; border: none; margin: 0;">
                                        <input type="hidden" name="notif_id" value="<?php echo $notif['id']; ?>">
                                        <button type="submit" name="marcar_leida" class="mark-read-btn">
                                            Marcar como leída
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="notification-message">
                            <?php echo nl2br($notif['mensaje']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <br>
            <a href="dashboard.php">🏠 Volver al inicio</a>
        </div>
    </div>
    
    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.body.classList.add('dark-mode');
            }
        });
    </script>
</body>
</html>
