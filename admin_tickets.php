<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';
redirigir_si_no_logueado();

if (!es_admin($pdo)) {
    echo "Acceso denegado.";
    exit();
}

// Ejecutar escalamiento automático
escalamiento_automatico($pdo);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_check($_POST["csrf"] ?? '')) {
        echo "Token CSRF inválido.";
        exit();
    }

    if (isset($_POST["eliminar"])) {
        $id = intval($_POST["eliminar"]);

        // Borrar respuestas primero
        $pdo->prepare("DELETE FROM respuestas WHERE id_ticket = ?")->execute([$id]);
        // Luego el ticket
        $pdo->prepare("DELETE FROM tickets WHERE id = ?")->execute([$id]);

        header("Location: admin_tickets.php");
        exit();
    }

    if (isset($_POST['asignar_tecnico'])) {
        $ticket_id = intval($_POST['ticket_id']);
        $stmt = $pdo->prepare("UPDATE tickets SET tecnico_asignado = 1, estado = 'En Progreso' WHERE id = ?");
        $stmt->execute([$ticket_id]);

        // Crear notificación
        $stmt_user = $pdo->prepare("SELECT id_usuario FROM tickets WHERE id = ?");
        $stmt_user->execute([$ticket_id]);
        $user_id = $stmt_user->fetchColumn();

        if ($user_id) {
            crear_notificacion($pdo, $user_id, "Ticket asignado", "Tu ticket ha sido asignado a un técnico especializado", "info");
        }

        header("Location: admin_tickets.php");
        exit();
    }
}

// Filtros para admin
$filtro_estado = $_GET['estado'] ?? '';
$filtro_prioridad = $_GET['prioridad'] ?? '';
$filtro_asignado = $_GET['asignado'] ?? '';

$sql = "SELECT t.*, u.nombre FROM tickets t JOIN usuarios u ON t.id_usuario = u.id WHERE 1=1";
$params = [];

if (!empty($filtro_estado)) {
    $sql .= " AND t.estado = ?";
    $params[] = $filtro_estado;
}

if (!empty($filtro_prioridad)) {
    $sql .= " AND t.prioridad = ?";
    $params[] = $filtro_prioridad;
}

if ($filtro_asignado === 'si') {
    $sql .= " AND t.tecnico_asignado IS NOT NULL";
} elseif ($filtro_asignado === 'no') {
    $sql .= " AND t.tecnico_asignado IS NULL";
}

$sql .= " ORDER BY 
    CASE t.prioridad 
        WHEN 'Critica' THEN 1 
        WHEN 'Alta' THEN 2 
        WHEN 'Media' THEN 3 
        WHEN 'Baja' THEN 4 
    END,
    t.fecha_creacion DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

// Obtener estadísticas para admin
$stats = obtener_estadisticas_dashboard($pdo, null, true);
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .wide-table {
            width: 100%;
            overflow-x: auto;
        }

        .wide-table table {
            width: 100%;
            min-width: 1000px;
        }

        .eliminar {
            color: red;
            margin-left: 10px;
        }

        .eliminar:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Alternar modo oscuro">
        <span class="toggle-icon">🌙</span>
    </button>
    
    <div class="container" style="max-width: 100%;">
        <div class="box" style="max-width: 95%;">
            <h2>🛠️ Panel de administrador - Todos los tickets</h2>

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
                    <div class="stat-icon">warning</div>
                    <div class="stat-number"><?php echo $stats['tickets_criticos'] ?? 0; ?></div>
                    <div class="stat-label">Críticos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">schedule</div>
                    <div class="stat-number"><?php echo $stats['tickets_sin_respuesta'] ?? 0; ?></div>
                    <div class="stat-label">Sin Respuesta +48h</div>
                </div>
            </div>

            <!-- Filtros para admin -->
            <div class="filters-container">
                <h3 style="margin-bottom: 16px; color: #2d3748;">🔍 Filtros Administrativos</h3>
                <form method="GET" style="background: none; padding: 0; box-shadow: none; border: none;">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Estado:</label>
                            <select name="estado">
                                <option value="">Todos</option>
                                <option value="Abierto" <?php echo $filtro_estado === 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                                <option value="En Progreso" <?php echo $filtro_estado === 'En Progreso' ? 'selected' : ''; ?>>En Progreso</option>
                                <option value="Cerrado" <?php echo $filtro_estado === 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Prioridad:</label>
                            <select name="prioridad">
                                <option value="">Todas</option>
                                <option value="Critica" <?php echo $filtro_prioridad === 'Critica' ? 'selected' : ''; ?>>Crítica</option>
                                <option value="Alta" <?php echo $filtro_prioridad === 'Alta' ? 'selected' : ''; ?>>Alta</option>
                                <option value="Media" <?php echo $filtro_prioridad === 'Media' ? 'selected' : ''; ?>>Media</option>
                                <option value="Baja" <?php echo $filtro_prioridad === 'Baja' ? 'selected' : ''; ?>>Baja</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Asignación:</label>
                            <select name="asignado">
                                <option value="">Todos</option>
                                <option value="si" <?php echo $filtro_asignado === 'si' ? 'selected' : ''; ?>>Asignados</option>
                                <option value="no" <?php echo $filtro_asignado === 'no' ? 'selected' : ''; ?>>Sin asignar</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" style="margin-bottom: 0;">Filtrar</button>
                        </div>
                        
                        <div class="filter-group">
                            <a href="admin_tickets.php" class="clear-filters" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0; padding: 12px 20px;">
                                Limpiar
                            </a>
                        </div>
                        
                        <div class="filter-group">
                            <a href="reportes.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #38a169 0%, #2f855a 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; margin-bottom: 0;">
                                <span class="icon">assessment</span>
                                Reportes
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="wide-table">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Número</th>
                        <th>Usuario</th>
                        <th>Prioridad</th>
                        <th>Categoría</th>
                        <th>Tema</th>
                        <th>Titulo</th>
                        <th>Estado</th>
                        <th>Asignado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($tickets as $t): ?>
                        <tr class="<?php echo $t['prioridad'] === 'Critica' ? 'ticket-critico' : ''; ?>">
                            <td><?php echo $t["id"]; ?></td>
                            <td><strong><?php echo $t["numero_ticket"] ?? 'VW-' . $t["id"]; ?></strong></td>
                            <td><?php echo $t["nombre"]; ?></td>
                            <td>
                                <span class="<?php echo obtener_prioridad_clase($t["prioridad"] ?? 'Media'); ?>">
                                    <?php echo $t["prioridad"] ?? 'Media'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="<?php echo obtener_categoria_clase($t["categoria"] ?? 'Consulta'); ?>">
                                    <?php echo $t["categoria"] ?? 'Consulta'; ?>
                                </span>
                            </td>
                            <td><?php echo $t["tema"]; ?></td>
                            <td><?php echo $t["titulo"]; ?></td>
                            <td>
                                <span class="<?php echo obtener_estado_clase($t["estado"]); ?>">
                                    <?php echo $t["estado"]; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($t["tecnico_asignado"]): ?>
                                    <span style="color: #38a169; font-weight: 600;">✓ Asignado</span>
                                <?php else: ?>
                                    <form method="POST" style="display: inline; background: none; padding: 0; box-shadow: none; border: none;">
                                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                                        <input type="hidden" name="ticket_id" value="<?php echo $t['id']; ?>">
                                        <button type="submit" name="asignar_tecnico" style="padding: 4px 8px; font-size: 0.8em; margin: 0;">
                                            Asignar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('d/m/Y H:i', strtotime($t["fecha_creacion"])); ?>
                                <br><small style="color: #4a5568;"><?php echo tiempo_transcurrido($t["fecha_creacion"]); ?></small>
                            </td>
                            <td>
                                <a href="admin_ver_ticket.php?id=<?php echo $t['id']; ?>" 
                                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 0.9em; margin-right: 8px;">
                                    <span class="icon">visibility</span>
                                    Ver
                                </a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este ticket y sus respuestas?')">
                                    <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                                    <input type="hidden" name="eliminar" value="<?php echo $t['id']; ?>">
                                    <button type="submit" class="eliminar"
                                        style="background:none; border:none; padding:8px 16px; color:#e53e3e; cursor:pointer; border-radius:4px; transition: all 0.2s;"
                                        onmouseover="this.style.backgroundColor='rgba(229,62,62,0.1)'"
                                        onmouseout="this.style.backgroundColor='transparent'">
                                    🗑️ Eliminar                                                        
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <br>
            <a href="dashboard.php">Volver al inicio</a>
        </div>
    </div>
    
    <script>
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
</body>
</html>
