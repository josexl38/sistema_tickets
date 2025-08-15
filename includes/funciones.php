<?php
function limpiar($data) {
    return htmlspecialchars(strip_tags(trim($data)));
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
    switch (strtolower($prioridad)) {
        case 'critica':
            return 'priority-critica';
        case 'alta':
            return 'priority-alta';
        case 'media':
            return 'priority-media';
        case 'baja':
            return 'priority-baja';
        default:
            return 'priority-media';
    }
}

function obtener_categoria_clase($categoria) {
    switch (strtolower($categoria)) {
        case 'bug':
            return 'categoria-bug';
        case 'feature request':
            return 'categoria-feature';
        case 'consulta':
            return 'categoria-consulta';
        case 'incidente':
            return 'categoria-incidente';
        default:
            return 'categoria-consulta';
    }
}

function obtener_estado_clase($estado) {
    switch (strtolower($estado)) {
        case 'abierto':
            return 'estado-abierto';
        case 'cerrado':
            return 'estado-cerrado';
        case 'en progreso':
            return 'estado-en-progreso';
        case 'pendiente':
            return 'estado-pendiente';
        default:
            return 'estado-abierto';
    }
}

function tiempo_transcurrido($fecha) {
    $tiempo = time() - strtotime($fecha);
    
    if ($tiempo < 60) {
        return 'hace ' . $tiempo . ' segundos';
    } elseif ($tiempo < 3600) {
        return 'hace ' . round($tiempo/60) . ' minutos';
    } elseif ($tiempo < 86400) {
        return 'hace ' . round($tiempo/3600) . ' horas';
    } else {
        return 'hace ' . round($tiempo/86400) . ' días';
    }
}

function enviar_notificacion_email($destinatario, $asunto, $mensaje, $remitente = "soporte@vw-potosina.com.mx") {
    $cabeceras = "From: $remitente\r\n";
    $cabeceras .= "Reply-To: $remitente\r\n";
    $cabeceras .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    return mail($destinatario, $asunto, $mensaje, $cabeceras);
}

function generar_numero_ticket() {
    return 'VW-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function validar_archivo($archivo) {
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip'];
    $tamaño_maximo = 5 * 1024 * 1024; // 5MB
    
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $extensiones_permitidas)) {
        return "Tipo de archivo no permitido. Extensiones permitidas: " . implode(', ', $extensiones_permitidas);
    }
    
    if ($archivo['size'] > $tamaño_maximo) {
        return "El archivo es demasiado grande. Tamaño máximo: 5MB";
    }
    
    return true;
}

function comprimir_imagen($origen, $destino, $calidad = 75) {
    $info = getimagesize($origen);
    
    if ($info === false) {
        return false;
    }
    
    switch ($info['mime']) {
        case 'image/jpeg':
            $imagen = imagecreatefromjpeg($origen);
            break;
        case 'image/png':
            $imagen = imagecreatefrompng($origen);
            break;
        case 'image/gif':
            $imagen = imagecreatefromgif($origen);
            break;
        default:
            return false;
    }
    
    // Redimensionar si es muy grande
    $ancho_original = imagesx($imagen);
    $alto_original = imagesy($imagen);
    $max_dimension = 1200;
    
    if ($ancho_original > $max_dimension || $alto_original > $max_dimension) {
        if ($ancho_original > $alto_original) {
            $nuevo_ancho = $max_dimension;
            $nuevo_alto = ($alto_original * $max_dimension) / $ancho_original;
        } else {
            $nuevo_alto = $max_dimension;
            $nuevo_ancho = ($ancho_original * $max_dimension) / $alto_original;
        }
        
        $imagen_redimensionada = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
        imagecopyresampled($imagen_redimensionada, $imagen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho_original, $alto_original);
        $imagen = $imagen_redimensionada;
    }
    
    return imagejpeg($imagen, $destino, $calidad);
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
            // Estadísticas para admin
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tickets");
            $stats['total_tickets'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) as abiertos FROM tickets WHERE estado = 'Abierto'");
            $stats['tickets_abiertos'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) as cerrados FROM tickets WHERE estado = 'Cerrado'");
            $stats['tickets_cerrados'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) as en_progreso FROM tickets WHERE estado = 'En Progreso'");
            $stats['tickets_en_progreso'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) as usuarios FROM usuarios WHERE confirmado = 1");
            $stats['total_usuarios'] = $stmt->fetchColumn();
            
        } else {
            // Estadísticas para usuario normal
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tickets WHERE id_usuario = ?");
            $stmt->execute([$usuario_id]);
            $stats['total_tickets'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as abiertos FROM tickets WHERE id_usuario = ? AND estado = 'Abierto'");
            $stmt->execute([$usuario_id]);
            $stats['tickets_abiertos'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as cerrados FROM tickets WHERE id_usuario = ? AND estado = 'Cerrado'");
            $stmt->execute([$usuario_id]);
            $stats['tickets_cerrados'] = $stmt->fetchColumn();
        }
        
    } catch (Exception $e) {
        error_log("Error al obtener estadísticas: " . $e->getMessage());
    }
    
    return $stats;
}
?>