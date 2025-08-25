<?php
// URL pública del sistema (termina con /)
if (!defined('BASE_URL')) {
    define('BASE_URL', 'https://tickets.vw-potosina.uk/sistema_tickets/');
}

// Dominio permitido para registro
if (!defined('DOMINIO_PERMITIDO')) {
    define('DOMINIO_PERMITIDO', '@vw-potosina.com.mx');
}

// Email del administrador
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'antonio.munoz@vw-potosina.com.mx');
}

// Si usas subcarpetas, ajusta el path:
if (!defined('UPLOADS_DIR')) {
    // ruta absoluta al directorio de uploads
    define('UPLOADS_DIR', __DIR__ . '/../uploads/');
