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
    $cabeceras  = "From: $remitente\r\n";
    $cabeceras .= "Reply-To: $remitente\r\n";
    $cabeceras .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return @mail($destinatario, $asunto, $mensaje, $cabeceras);
}

function generar_numero_ticket() {
    return 'VW-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
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
