<?php
/**
 * Inicio de sesion centralizado para toda la app
 * (Se ejecuta en cualquier archivo que incluya este helper)
 */
if (session_status() === PHP_SESSION_NONE) {
    // Cookies seguras por defecto (ajusta 'secure' a true si usas HTTPS)
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,   // true si tienes HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** ----------------- Helpers generales ----------------- */

function limpiar($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirigir_si_no_logueado() {
    if (!isset($_SESSION["usuario_id"])) {
        header("Location: login.php");
        exit();
    }
}

function es_admin($pdo) {
    if (!isset($_SESSION["usuario_id"])) {
        return false;
    }
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION["usuario_id"]]);
    $usuario = $stmt->fetch();
    return $usuario && $usuario["rol"] === "admin";
}

function obtener_prioridad_clase($prioridad) {
    switch (strtolower((string)$prioridad)) {
        case 'critica': return 'priority-critica';
        case 'alta':    return 'priority-alta';
        case 'media':   return 'priority-media';
        case 'baja':    return 'priority-baja';
        default:        return 'priority-media';
    }
}

function obtener_categoria_clase($categoria) {
    switch (strtolower((string)$categoria)) {
        case 'bug':              return 'categoria-bug';
        case 'feature request':  return 'categoria-feature';
        case 'consulta':         return 'categoria-consulta';
        case 'incidente':        return 'categoria-incidente';
        default:                 return 'categoria-consulta';
    }
}

function obtener_estado_clase($estado) {
    switch (strtolower((string)$estado)) {
        case 'abierto':       return 'estado-abierto';
        case 'cerrado':       return 'estado-cerrado';
        case 'en progreso':   return 'estado-en-progreso';
        case 'pendiente':     return 'estado-pendiente';
        default:              return 'estado-abierto';
    }
}

function tiempo_transcurrido($fecha) {
    $t = time() - strtotime($fecha);
    if ($t < 60) return 'hace ' . $t . ' segundos';
    if ($t < 3600) return 'hace ' . round($t/60) . ' minutos';
    if ($t < 86400) return 'hace ' . round($t/3600) . ' horas';
    return 'hace ' . round($t/86400) . ' días';
}

function enviar_notificacion_email($destinatario, $asunto, $mensaje, $remitente = "soporte@vw-potosina.com.mx") {
    // Limpiar caracteres especiales para evitar problemas de codificacion
    $asunto_limpio = str_replace(['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'], ['a','e','i','o','u','n','A','E','I','O','U','N'], $asunto);
    $mensaje_limpio = str_replace(['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'], ['a','e','i','o','u','n','A','E','I','O','U','N'], $mensaje);
    
    $cabeceras  = "From: $remitente\r\n";
    $cabeceras .= "Reply-To: $remitente\r\n";
    $cabeceras .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
    return @mail($destinatario, $asunto_limpio, $mensaje_limpio, $cabeceras);
}

function generar_numero_ticket() {
    // Generar número único con timestamp para evitar duplicados
    return 'VW-' . date('Y') . '-' . str_pad(time() % 10000, 4, '0', STR_PAD_LEFT);
}

function validar_archivo($archivo) {
    $extensiones_permitidas = ['jpg','jpeg','png','gif','pdf','doc','docx','txt','zip'];
    $tam_max = 5 * 1024 * 1024; // 5MB
    $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));

    if (!$extension || !in_array($extension, $extensiones_permitidas)) {
        return "Tipo de archivo no permitido. Extensiones: " . implode(', ', $extensiones_permitidas);
    }
    if (($archivo['size'] ?? 0) > $tam_max) {
        return "El archivo es demasiado grande. Máximo: 5MB";
    }
    return true;
}

function comprimir_imagen($origen, $destino, $calidad = 75) {
    $info = @getimagesize($origen);
    if ($info === false) return false;

    switch ($info['mime']) {
        case 'image/jpeg': $img = imagecreatefromjpeg($origen); break;
        case 'image/png':  $img = imagecreatefrompng($origen);  break;
        case 'image/gif':  $img = imagecreatefromgif($origen);  break;
        default: return false;
    }

    $w = imagesx($img); $h = imagesy($img); $max = 1200;
    if ($w > $max || $h > $max) {
        if ($w > $h) { $nw = $max; $nh = (int)round($h * $max / $w); }
        else         { $nh = $max; $nw = (int)round($w * $max / $h); }
        $res = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($res, $img, 0,0,0,0, $nw,$nh, $w,$h);
        $img = $res;
    }
    return imagejpeg($img, $destino, $calidad);
}

function log_actividad($pdo, $usuario_id, $accion, $detalles = '') {
    try {
        $stmt = $pdo->prepare("INSERT INTO logs_actividad (usuario_id, accion, detalles, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$usuario_id, $accion, $detalles]);
        return true;
    } catch (Exception $e) {
        error_log("Error al registrar log: " . $e->getMessage());
        return false;
    }
}

function obtener_estadisticas_dashboard($pdo, $usuario_id = null, $es_admin = false) {
    $stats = [];
    try {
        if ($es_admin) {
            $stats['total_tickets']       = (int)$pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
            $stats['tickets_abiertos']    = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Abierto'")->fetchColumn();
            $stats['tickets_cerrados']    = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Cerrado'")->fetchColumn();
            $stats['tickets_en_progreso'] = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE estado = 'En Progreso'")->fetchColumn();
            $stats['total_usuarios']      = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE confirmado = 1")->fetchColumn();
            $stats['tickets_criticos']   = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE prioridad = 'Critica' AND estado != 'Cerrado'")->fetchColumn();
            $stats['tickets_sin_respuesta'] = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Abierto' AND DATEDIFF(NOW(), fecha_creacion) > 2")->fetchColumn();
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE id_usuario = ?");
            $stmt->execute([$usuario_id]);
            $stats['total_tickets'] = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE id_usuario = ? AND estado = 'Abierto'");
            $stmt->execute([$usuario_id]);
            $stats['tickets_abiertos'] = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE id_usuario = ? AND estado = 'Cerrado'");
            $stmt->execute([$usuario_id]);
            $stats['tickets_cerrados'] = (int)$stmt->fetchColumn();
        }
    } catch (Exception $e) {
        error_log("Error stats dashboard: " . $e->getMessage());
    }
    return $stats;
}

function escalamiento_automatico($pdo) {
    try {
        // Escalar tickets abiertos sin respuesta por más de 48 horas
        $stmt = $pdo->prepare("
            UPDATE tickets 
            SET estado = 'En Progreso', 
                tecnico_asignado = 1,
                fecha_limite = DATE_ADD(NOW(), INTERVAL 24 HOUR)
            WHERE estado = 'Abierto' 
            AND TIMESTAMPDIFF(HOUR, fecha_creacion, NOW()) > 48
            AND tecnico_asignado IS NULL
        ");
        $escalados = $stmt->execute();
        
        // Escalar tickets críticos inmediatamente
        $stmt = $pdo->prepare("
            UPDATE tickets 
            SET tecnico_asignado = 1,
                fecha_limite = DATE_ADD(NOW(), INTERVAL 4 HOUR)
            WHERE prioridad = 'Critica' 
            AND estado != 'Cerrado'
            AND tecnico_asignado IS NULL
        ");
        $stmt->execute();
        
        return true;
    } catch (Exception $e) {
        error_log("Error escalamiento automático: " . $e->getMessage());
        return false;
    }
}

function crear_notificacion($pdo, $usuario_id, $titulo, $mensaje, $tipo = 'info') {
    try {
        $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$usuario_id, $titulo, $mensaje, $tipo]);
    } catch (Exception $e) {
        error_log("Error crear notificación: " . $e->getMessage());
        return false;
    }
}

function obtener_plantillas_respuestas($pdo, $categoria = null) {
    try {
        $sql = "SELECT * FROM plantillas_respuestas WHERE activa = TRUE";
        $params = [];
        
        if ($categoria) {
            $sql .= " AND categoria = ?";
            $params[] = $categoria;
        }
        
        $sql .= " ORDER BY titulo";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error obtener plantillas: " . $e->getMessage());
        return [];
    }
}

function generar_reporte_tickets($pdo, $fecha_inicio, $fecha_fin, $formato = 'array') {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                t.*,
                u.nombre as usuario_nombre,
                u.departamento as usuario_departamento,
                COUNT(r.id) as total_respuestas,
                TIMESTAMPDIFF(HOUR, t.fecha_creacion, COALESCE(
                    (SELECT fecha_respuesta FROM respuestas WHERE id_ticket = t.id AND autor = 'Soporte VW' ORDER BY fecha_respuesta DESC LIMIT 1),
                    NOW()
                )) as tiempo_resolucion_horas
            FROM tickets t
            LEFT JOIN usuarios u ON t.id_usuario = u.id
            LEFT JOIN respuestas r ON t.id = r.id_ticket
            WHERE t.fecha_creacion BETWEEN ? AND ?
            GROUP BY t.id
            ORDER BY t.fecha_creacion DESC
        ");
        
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $datos = $stmt->fetchAll();
        
        if ($formato === 'csv') {
            return generar_csv_reporte($datos);
        }
        
        return $datos;
    } catch (Exception $e) {
        error_log("Error generar reporte: " . $e->getMessage());
        return [];
    }
}

function generar_csv_reporte($datos) {
    $csv = "ID,Número,Usuario,Departamento,Título,Estado,Prioridad,Categoría,Fecha Creación,Respuestas,Tiempo Resolución (hrs)\n";
    
    foreach ($datos as $ticket) {
        $csv .= sprintf(
            "%d,%s,%s,%s,\"%s\",%s,%s,%s,%s,%d,%d\n",
            $ticket['id'],
            $ticket['numero_ticket'] ?? 'VW-' . $ticket['id'],
            $ticket['usuario_nombre'],
            $ticket['usuario_departamento'],
            str_replace('"', '""', $ticket['titulo']),
            $ticket['estado'],
            $ticket['prioridad'] ?? 'Media',
            $ticket['categoria'] ?? 'Consulta',
            $ticket['fecha_creacion'],
            $ticket['total_respuestas'],
            $ticket['tiempo_resolucion_horas']
        );
    }
    
    return $csv;
}