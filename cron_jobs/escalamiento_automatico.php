<?php
/**
 * Script para ejecutar escalamiento automático
 * Configurar en crontab para ejecutar cada hora:
 * 0 * * * * /usr/bin/php /ruta/al/proyecto/cron_jobs/escalamiento_automatico.php
 */

require_once dirname(__DIR__) . "/includes/db.php";
require_once dirname(__DIR__) . "/includes/funciones.php";

echo "Iniciando escalamiento automático...\n";

try {
    // Ejecutar escalamiento
    $resultado = escalamiento_automatico($pdo);
    
    if ($resultado) {
        echo "Escalamiento ejecutado correctamente.\n";
        
        // Log del proceso
        log_actividad($pdo, 1, "Escalamiento automático", "Proceso ejecutado por cron job");
        
        // Obtener tickets escalados para notificar
        $stmt = $pdo->query("
            SELECT t.*, u.correo, u.nombre 
            FROM tickets t 
            JOIN usuarios u ON t.id_usuario = u.id 
            WHERE t.tecnico_asignado = 1 
            AND t.fecha_limite IS NOT NULL 
            AND DATE(t.fecha_limite) = CURDATE()
        ");
        $tickets_escalados = $stmt->fetchAll();
        
        // Enviar notificaciones
        foreach ($tickets_escalados as $ticket) {
            // Notificación al usuario
            crear_notificacion(
                $pdo, 
                $ticket['id_usuario'], 
                "Ticket escalado", 
                "Tu ticket #{$ticket['numero_ticket']} ha sido asignado a un técnico especializado", 
                "info"
            );
            
            // Email al usuario
            $asunto = "Ticket escalado - #{$ticket['numero_ticket']}";
            $mensaje = "Hola {$ticket['nombre']},\n\n"
                     . "Tu ticket #{$ticket['numero_ticket']} ha sido escalado y asignado a nuestro equipo técnico.\n"
                     . "Recibirás una respuesta pronto.\n\n"
                     . "Título: {$ticket['titulo']}\n"
                     . "Prioridad: {$ticket['prioridad']}\n\n"
                     . "Saludos,\nEquipo de Soporte VW";
            
            enviar_notificacion_email($ticket['correo'], $asunto, $mensaje);
        }
        
        echo "Notificaciones enviadas: " . count($tickets_escalados) . "\n";
        
    } else {
        echo "Error en el escalamiento automático.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("Error en escalamiento automático: " . $e->getMessage());
}

echo "Proceso completado.\n";
?>