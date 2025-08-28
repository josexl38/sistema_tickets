<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';
redirigir_si_no_logueado();

if (!es_admin($pdo)) {
    echo "Acceso denegado.";
    exit();
}

$id = $_GET["id"] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo "Ticket no encontrado.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_check($_POST["csrf"] ?? '')) {
        echo "Token CSRF inválido.";
        exit();
    }

    if (isset($_POST["respuesta"])) {
        $respuesta = limpiar($_POST["respuesta"]);
        $autor = "Soporte VW";
        
        // Usar plantilla si se seleccionó
        if (isset($_POST["plantilla_id"]) && !empty($_POST["plantilla_id"])) {
            $stmt_plantilla = $pdo->prepare("SELECT contenido FROM plantillas_respuestas WHERE id = ?");
            $stmt_plantilla->execute([$_POST["plantilla_id"]]);
            $plantilla = $stmt_plantilla->fetch();
            if ($plantilla) {
                $respuesta = $plantilla['contenido'] . "\n\n" . $respuesta;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO respuestas (id_ticket, respuesta, autor) VALUES (?, ?, ?)");
        $stmt->execute([$id, $respuesta, $autor]);

        // Crear notificación para el usuario
        crear_notificacion($pdo, $ticket["id_usuario"], "Nueva respuesta", "Soporte ha respondido a tu ticket #$id", "info");
        
        // Log de actividad
        log_actividad($pdo, $_SESSION["usuario_id"], "Respuesta agregada", "Ticket #$id - Respuesta de soporte");
        
        // Email al usuario
        $stmt_user = $pdo->prepare("SELECT correo FROM usuarios WHERE id = ?");
        $stmt_user->execute([$ticket["id_usuario"]]);
        $correo = $stmt_user->fetchColumn();
        if ($correo) {
            $asunto = "Respuesta de soporte en tu ticket #$id";
            $mensaje = "Soporte ha respondido a tu ticket:\n\n$respuesta\n\nPuedes revisarlo en el sistema.";
            enviar_notificacion_email($correo, $asunto, $mensaje);
        }
    }

    if (isset($_POST["cerrar"])) {
        $stmt = $pdo->prepare("UPDATE tickets SET estado = 'Cerrado' WHERE id = ?");
        $stmt->execute([$id]);
        
        // Crear notificación
        crear_notificacion($pdo, $ticket["id_usuario"], "Ticket cerrado", "Tu ticket #$id ha sido marcado como resuelto", "success");
        
        // Log de actividad
        log_actividad($pdo, $_SESSION["usuario_id"], "Ticket cerrado", "Ticket #$id cerrado por administrador");
    }

    if (isset($_POST["cambiar_estado"])) {
        $nuevo_estado = limpiar($_POST["nuevo_estado"]);
        $stmt = $pdo->prepare("UPDATE tickets SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id]);
        
        // Crear notificación
        crear_notificacion($pdo, $ticket["id_usuario"], "Estado actualizado", "Tu ticket #$id cambió a: $nuevo_estado", "info");
        
        // Log de actividad
        log_actividad($pdo, $_SESSION["usuario_id"], "Estado cambiado", "Ticket #$id cambió a: $nuevo_estado");
    }

    header("Location: admin_ver_ticket.php?id=$id");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM respuestas WHERE id_ticket = ?");
$stmt->execute([$id]);
$respuestas = $stmt->fetchAll();

// Obtener plantillas de respuestas
$plantillas = obtener_plantillas_respuestas($pdo);
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo $ticket["id"]; ?></title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .respuesta-box {
            background-color: rgba(255,255,255,0.95);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            text-align: left;
        }
        .respuesta-box strong {
            color: #003366;
        }
        textarea {
            resize: vertical;
        }
        .acciones {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .acciones button {
            flex: 1;
        }
    </style>
</head>
<body>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Alternar modo oscuro">
        <span class="toggle-icon">🌙</span>
    </button>
    
    <div class="container">
        <div class="box">
            <h2>🎫 Ticket #<?php echo $ticket["numero_ticket"] ?? $ticket["id"]; ?> - <?php echo $ticket["titulo"]; ?></h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; padding: 16px; background: rgba(66, 153, 225, 0.1); border-radius: 12px;">
                <div><strong>Estado:</strong> 
                    <span class="<?php echo obtener_estado_clase($ticket["estado"]); ?>">
                        <?php echo $ticket["estado"]; ?>
                    </span>
                </div>
                <div><strong>Prioridad:</strong> 
                    <span class="<?php echo obtener_prioridad_clase($ticket["prioridad"] ?? 'Media'); ?>">
                        <?php echo $ticket["prioridad"] ?? 'Media'; ?>
                    </span>
                </div>
                <div><strong>Categoría:</strong> 
                    <span class="<?php echo obtener_categoria_clase($ticket["categoria"] ?? 'Consulta'); ?>">
                        <?php echo $ticket["categoria"] ?? 'Consulta'; ?>
                    </span>
                </div>
                <div><strong>Asignado:</strong> 
                    <?php echo $ticket["tecnico_asignado"] ? "✅ Sí" : "❌ No"; ?>
                </div>
            </div>
            
            <p><strong>Tema:</strong> <?php echo $ticket["tema"]; ?></p>
            <p><strong>Descripcion:</strong><br><?php echo nl2br($ticket["descripcion"]); ?></p>

            <?php if ($ticket["archivo"]): ?>
                <p><strong>Archivos adjuntos:</strong><br>
                <?php foreach (explode(",", $ticket["archivo"]) as $ruta): ?>
                    <a href="<?php echo trim($ruta); ?>" target="_blank" style="display: inline-block; margin: 4px 8px 4px 0; padding: 4px 8px; background: #4299e1; color: white; border-radius: 4px; text-decoration: none;">
                        📎 <?php echo basename(trim($ruta)); ?>
                    </a>
                <?php endforeach; ?>
                </p>
            <?php endif; ?>

            <hr>

            <h3>Conversacion</h3>
            <?php if (count($respuestas) === 0): ?>
                <p>No hay respuestas aun.</p>
            <?php else: ?>
                <?php foreach ($respuestas as $r): ?>
                    <div class="respuesta-box">
                        <strong><?php echo $r["autor"]; ?></strong> (<?php echo $r["fecha_respuesta"]; ?>):<br>
                        <?php echo nl2br($r["respuesta"]); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <hr>
            
            <!-- Acciones administrativas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div style="background: rgba(255, 255, 255, 0.95); padding: 16px; border-radius: 12px; border-left: 4px solid #4299e1;">
            <h4>🔄 Cambiar Estado</h4>
            <form method="POST" style="background: none; padding: 0; box-shadow: none; border: none;">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                <select name="nuevo_estado" style="margin-bottom: 8px;">
                    <option value="Abierto" <?php echo $ticket["estado"] === 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                    <option value="En Progreso" <?php echo $ticket["estado"] === 'En Progreso' ? 'selected' : ''; ?>>En Progreso</option>
                    <option value="Cerrado" <?php echo $ticket["estado"] === 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                </select>
                <button type="submit" name="cambiar_estado" style="width: 100%; margin: 0;">Actualizar Estado</button>
            </form>
        </div>
                
                <div style="background: rgba(255, 255, 255, 0.95); padding: 16px; border-radius: 12px; border-left: 4px solid #38a169;">
                    <h4>⏱️ Información de Tiempo</h4>
                    <p><strong>Creado:</strong> <?php echo tiempo_transcurrido($ticket["fecha_creacion"]); ?></p>
                    <?php if ($ticket["fecha_limite"]): ?>
                        <p><strong>Límite:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket["fecha_limite"])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
        <h3>💬 Responder como soporte</h3>
        <form method="POST">
            <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
            <?php if (!empty($plantillas)): ?>
            <label>Plantilla de respuesta (opcional):</label>
            <select name="plantilla_id" onchange="cargarPlantilla(this.value)" style="margin-bottom: 16px;">
                    <option value="">-- Seleccionar plantilla --</option>
                    <?php foreach ($plantillas as $plantilla): ?>
                        <option value="<?php echo $plantilla['id']; ?>" data-contenido="<?php echo htmlspecialchars($plantilla['contenido']); ?>">
                            <?php echo $plantilla['titulo']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                
                <label>Respuesta:</label>
                <textarea name="respuesta" id="respuesta" rows="6" required placeholder="Escribe tu respuesta aquí..."></textarea>
                <div class="acciones">
                    <button type="submit">Responder</button>
                    <?php if ($ticket["estado"] != "Cerrado"): ?>
                        <button name="cerrar" value="1">🔒 Cerrar ticket</button>
                    <?php endif; ?>
                </div>
            </form>

            <br><a href="admin_tickets.php">Volver al panel</a>
        </div>
    </div>

    <script>
        function cargarPlantilla(plantillaId) {
            if (!plantillaId) return;
            
            const select = document.querySelector('select[name="plantilla_id"]');
            const option = select.querySelector(`option[value="${plantillaId}"]`);
            const textarea = document.getElementById('respuesta');
            
            if (option && textarea) {
                const contenido = option.getAttribute('data-contenido');
                textarea.value = contenido;
            }
        }
        
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
