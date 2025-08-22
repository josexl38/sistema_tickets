<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';
redirigir_si_no_logueado();

// Filtros
$filtro_estado = $_GET['estado'] ?? '';
$filtro_prioridad = $_GET['prioridad'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';
$busqueda = $_GET['buscar'] ?? '';

// Construir consulta con filtros
$sql = "SELECT * FROM tickets WHERE id_usuario = ?";
$params = [$_SESSION["usuario_id"]];

if (!empty($filtro_estado)) {
    $sql .= " AND estado = ?";
    $params[] = $filtro_estado;
}

if (!empty($filtro_prioridad)) {
    $sql .= " AND prioridad = ?";
    $params[] = $filtro_prioridad;
}

if (!empty($filtro_categoria)) {
    $sql .= " AND categoria = ?";
    $params[] = $filtro_categoria;
}

if (!empty($busqueda)) {
    $sql .= " AND (titulo LIKE ? OR descripcion LIKE ? OR numero_ticket LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params = array_merge($params, [$busqueda_param, $busqueda_param, $busqueda_param]);
}

$sql .= " ORDER BY fecha_creacion DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tickets</title>
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
        .ticket-row {
            transition: all 0.3s ease;
        }
        .ticket-row:hover {
            background-color: rgba(66, 153, 225, 0.05);
            transform: scale(1.01);
        }
        .ticket-critico {
            background-color: rgba(229, 62, 62, 0.05);
            border-left: 4px solid #e53e3e;
        }
    </style>
</head>
<body>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Alternar modo oscuro"></button>
    
    <div class="container" style="max-width: 100%;">
        <div class="box" style="max-width: 95%;">
            <h2>🎫 Mis Tickets</h2>
            
            <!-- Filtros de búsqueda -->
            <div class="filters-container">
                <h3 style="margin-bottom: 16px; color: #2d3748;">🔍 Filtros de Búsqueda</h3>
                <form method="GET" style="background: none; padding: 0; box-shadow: none; border: none;">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Buscar:</label>
                            <input type="text" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Título, descripción o número...">
                        </div>
                        
                        <div class="filter-group">
                            <label>Estado:</label>
                            <select name="estado">
                                <option value="">Todos los estados</option>
                                <option value="Abierto" <?php echo $filtro_estado === 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                                <option value="En Progreso" <?php echo $filtro_estado === 'En Progreso' ? 'selected' : ''; ?>>En Progreso</option>
                                <option value="Cerrado" <?php echo $filtro_estado === 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Prioridad:</label>
                            <select name="prioridad">
                                <option value="">Todas las prioridades</option>
                                <option value="Baja" <?php echo $filtro_prioridad === 'Baja' ? 'selected' : ''; ?>>Baja</option>
                                <option value="Media" <?php echo $filtro_prioridad === 'Media' ? 'selected' : ''; ?>>Media</option>
                                <option value="Alta" <?php echo $filtro_prioridad === 'Alta' ? 'selected' : ''; ?>>Alta</option>
                                <option value="Critica" <?php echo $filtro_prioridad === 'Critica' ? 'selected' : ''; ?>>Crítica</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Categoría:</label>
                            <select name="categoria">
                                <option value="">Todas las categorías</option>
                                <option value="Bug" <?php echo $filtro_categoria === 'Bug' ? 'selected' : ''; ?>>Bug</option>
                                <option value="Feature Request" <?php echo $filtro_categoria === 'Feature Request' ? 'selected' : ''; ?>>Feature Request</option>
                                <option value="Consulta" <?php echo $filtro_categoria === 'Consulta' ? 'selected' : ''; ?>>Consulta</option>
                                <option value="Incidente" <?php echo $filtro_categoria === 'Incidente' ? 'selected' : ''; ?>>Incidente</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" style="margin-bottom: 0;">Filtrar</button>
                        </div>
                        
                        <div class="filter-group">
                            <a href="ver_ticket.php" class="clear-filters" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0; padding: 12px 20px;">
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (count($tickets) === 0): ?>
                <div class="alert alert-info">
                    <?php if (!empty($busqueda) || !empty($filtro_estado) || !empty($filtro_prioridad) || !empty($filtro_categoria)): ?>
                        No se encontraron tickets que coincidan con los filtros seleccionados.
                        <br><br>
                        <a href="ver_ticket.php">Ver todos los tickets</a> | 
                        <a href="crear_ticket.php">Crear nuevo ticket</a>
                    <?php else: ?>
                        No has creado ningún ticket aún.
                        <br><br>
                        <a href="crear_ticket.php">¡Crea tu primer ticket!</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 16px; color: #4a5568;">
                    📊 Mostrando <?php echo count($tickets); ?> ticket(s)
                    <?php if (!empty($busqueda) || !empty($filtro_estado) || !empty($filtro_prioridad) || !empty($filtro_categoria)): ?>
                        (filtrados)
                    <?php endif; ?>
                </div>
                
                <div class="wide-table">
                    <table>
                        <tr>
                            <th>Número</th>
                            <th>Categoría</th>
                            <th>Tema</th>
                            <th>Título</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Última Act.</th>
                            <th>Ver</th>
                        </tr>
                        <?php foreach ($tickets as $t): ?>
                            <tr class="ticket-row <?php echo $t['prioridad'] === 'Critica' ? 'ticket-critico' : ''; ?>">
                                <td><strong><?php echo $t["numero_ticket"] ?? 'VW-' . $t["id"]; ?></strong></td>
                                <td>
                                    <span class="<?php echo obtener_categoria_clase($t["categoria"] ?? 'Consulta'); ?>">
                                        <?php echo $t["categoria"] ?? 'Consulta'; ?>
                                    </span>
                                </td>
                                <td><?php echo $t["tema"]; ?></td>
                                <td><?php echo $t["titulo"]; ?></td>
                                <td>
                                    <span class="<?php echo obtener_prioridad_clase($t["prioridad"] ?? 'Media'); ?>">
                                        <?php echo $t["prioridad"] ?? 'Media'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?php echo obtener_estado_clase($t["estado"]); ?>">
                                        <?php echo $t["estado"]; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($t["fecha_creacion"])); ?></td>
                                <td>
                                    <small style="color: #4a5568;">
                                        <?php echo tiempo_transcurrido($t["fecha_creacion"]); ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="ver_ticket_detalle.php?id=<?php echo $t["id"]; ?>" 
                                       style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 0.9em;">
                                        <span class="icon">visibility</span>
                                        Abrir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                
                <!-- Resumen de tickets -->
                <div style="margin-top: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                    <?php
                    $resumen = [
                        'Abierto' => 0,
                        'En Progreso' => 0,
                        'Cerrado' => 0
                    ];
                    
                    foreach ($tickets as $ticket) {
                        if (isset($resumen[$ticket['estado']])) {
                            $resumen[$ticket['estado']]++;
                        }
                    }
                    ?>
                    
                    <?php foreach ($resumen as $estado => $cantidad): ?>
                        <div style="background: rgba(255, 255, 255, 0.95); padding: 16px; border-radius: 12px; text-align: center; border-left: 4px solid #4299e1;">
                            <div style="font-size: 1.5em; font-weight: 700; color: #2d3748;"><?php echo $cantidad; ?></div>
                            <div style="color: #4a5568; font-size: 0.9em;"><?php echo $estado; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Acciones rápidas -->
            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.1);">
                <h3 style="color: #2d3748; margin-bottom: 20px; font-size: 1.1em;">
                    ⚡ Acciones Rápidas
                </h3>
                <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;">
                    <a href="crear_ticket.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #38a169 0%, #2f855a 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        <span class="icon">add_circle</span>
                        Nuevo Ticket
                    </a>
                    <a href="base_conocimientos.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        <span class="icon">help</span>
                        Base de Conocimientos
                    </a>
                    <a href="?estado=Abierto" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        <span class="icon">filter_list</span>
                        Solo Abiertos
                    </a>
                </div>

            <br><a href="dashboard.php">🏠 Volver al inicio</a>
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
