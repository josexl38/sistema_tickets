<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
redirigir_si_no_logueado();

if (!es_admin($pdo)) {
    echo "Acceso denegado.";
    exit();
}

// Manejar descarga de reporte
if (isset($_GET['descargar']) && $_GET['descargar'] === 'csv') {
    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
    
    $csv_data = generar_reporte_tickets($pdo, $fecha_inicio, $fecha_fin, 'csv');
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_tickets_' . date('Y-m-d') . '.csv"');
    echo $csv_data;
    exit();
}

// Obtener datos para el reporte
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');

$datos_reporte = generar_reporte_tickets($pdo, $fecha_inicio, $fecha_fin);

// Calcular estadísticas del reporte
$total_tickets = count($datos_reporte);
$tiempo_promedio = 0;
$tickets_por_estado = ['Abierto' => 0, 'En Progreso' => 0, 'Cerrado' => 0];
$tickets_por_prioridad = ['Baja' => 0, 'Media' => 0, 'Alta' => 0, 'Critica' => 0];

if ($total_tickets > 0) {
    $suma_tiempos = 0;
    foreach ($datos_reporte as $ticket) {
        $tickets_por_estado[$ticket['estado']]++;
        $tickets_por_prioridad[$ticket['prioridad'] ?? 'Media']++;
        $suma_tiempos += $ticket['tiempo_resolucion_horas'];
    }
    $tiempo_promedio = round($suma_tiempos / $total_tickets, 1);
}
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Estadísticas</title>
    <link rel="stylesheet" href="css/estilo.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 24px;
            border-radius: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin: 24px 0;
        }
        
        .report-summary {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            padding: 24px;
            border-radius: 16px;
            margin: 20px 0;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 16px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-number {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .summary-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Alternar modo oscuro"></button>
    
    <div class="container" style="max-width: 100%;">
        <div class="box" style="max-width: 95%;">
            <h2>📊 Reportes y Estadísticas</h2>
            
            <!-- Filtros de fecha -->
            <div class="filters-container">
                <h3 style="margin-bottom: 16px; color: #2d3748;">📅 Período del Reporte</h3>
                <form method="GET" style="background: none; padding: 0; box-shadow: none; border: none;">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Fecha Inicio:</label>
                            <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label>Fecha Fin:</label>
                            <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" style="margin-bottom: 0;">Generar Reporte</button>
                        </div>
                        
                        <div class="filter-group">
                            <a href="?fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&descargar=csv" 
                               style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #38a169 0%, #2f855a 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; margin-bottom: 0;">
                                <span class="icon">download</span>
                                Descargar CSV
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Resumen del reporte -->
            <div class="report-summary">
                <h3 style="margin-bottom: 16px;">📈 Resumen del Período</h3>
                <p>Del <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> al <?php echo date('d/m/Y', strtotime($fecha_fin)); ?></p>
                
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-number"><?php echo $total_tickets; ?></div>
                        <div class="summary-label">Total Tickets</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-number"><?php echo $tiempo_promedio; ?>h</div>
                        <div class="summary-label">Tiempo Promedio</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-number"><?php echo $tickets_por_estado['Cerrado']; ?></div>
                        <div class="summary-label">Resueltos</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-number"><?php echo $tickets_por_prioridad['Critica']; ?></div>
                        <div class="summary-label">Críticos</div>
                    </div>
                </div>
            </div>
            
            <!-- Gráficos -->
            <div class="chart-grid">
                <div class="chart-container">
                    <h3 style="margin-bottom: 16px; color: #2d3748;">📊 Tickets por Estado</h3>
                    <canvas id="chartEstados" width="400" height="200"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3 style="margin-bottom: 16px; color: #2d3748;">⚡ Tickets por Prioridad</h3>
                    <canvas id="chartPrioridades" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Tabla detallada -->
            <?php if (!empty($datos_reporte)): ?>
            <div class="wide-table">
                <h3 style="margin-bottom: 16px; color: #2d3748;">📋 Detalle de Tickets</h3>
                <table>
                    <tr>
                        <th>Número</th>
                        <th>Usuario</th>
                        <th>Departamento</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Respuestas</th>
                        <th>Tiempo (hrs)</th>
                        <th>Fecha</th>
                    </tr>
                    <?php foreach ($datos_reporte as $ticket): ?>
                        <tr>
                            <td><strong><?php echo $ticket["numero_ticket"] ?? 'VW-' . $ticket["id"]; ?></strong></td>
                            <td><?php echo $ticket["usuario_nombre"]; ?></td>
                            <td><?php echo $ticket["usuario_departamento"]; ?></td>
                            <td><?php echo $ticket["titulo"]; ?></td>
                            <td>
                                <span class="<?php echo obtener_estado_clase($ticket["estado"]); ?>">
                                    <?php echo $ticket["estado"]; ?>
                                </span>
                            </td>
                            <td>
                                <span class="<?php echo obtener_prioridad_clase($ticket["prioridad"] ?? 'Media'); ?>">
                                    <?php echo $ticket["prioridad"] ?? 'Media'; ?>
                                </span>
                            </td>
                            <td><?php echo $ticket["total_respuestas"]; ?></td>
                            <td><?php echo $ticket["tiempo_resolucion_horas"]; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($ticket["fecha_creacion"])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>
            
            <br>
            <a href="admin_tickets.php">← Volver al panel de administrador</a>
        </div>
    </div>
    
    <script>
        // Gráfico de estados
        const ctxEstados = document.getElementById('chartEstados').getContext('2d');
        new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: ['Abierto', 'En Progreso', 'Cerrado'],
                datasets: [{
                    data: [<?php echo $tickets_por_estado['Abierto']; ?>, <?php echo $tickets_por_estado['En Progreso']; ?>, <?php echo $tickets_por_estado['Cerrado']; ?>],
                    backgroundColor: ['#4299e1', '#ed8936', '#38a169'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Gráfico de prioridades
        const ctxPrioridades = document.getElementById('chartPrioridades').getContext('2d');
        new Chart(ctxPrioridades, {
            type: 'bar',
            data: {
                labels: ['Baja', 'Media', 'Alta', 'Crítica'],
                datasets: [{
                    label: 'Cantidad',
                    data: [<?php echo $tickets_por_prioridad['Baja']; ?>, <?php echo $tickets_por_prioridad['Media']; ?>, <?php echo $tickets_por_prioridad['Alta']; ?>, <?php echo $tickets_por_prioridad['Critica']; ?>],
                    backgroundColor: ['#38a169', '#ed8936', '#e53e3e', '#742a2a'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
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