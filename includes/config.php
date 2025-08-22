<?php
// URL pública del sistema (termina con /)
if (!defined('BASE_URL')) {
    define('BASE_URL', 'https://tickets.vw-potosina.uk/sistema_tickets/');
}

// Si usas subcarpetas, ajusta el path:
if (!defined('UPLOADS_DIR')) {
    // ruta absoluta al directorio de uploads
    define('UPLOADS_DIR', __DIR__ . '/../uploads/');
}
