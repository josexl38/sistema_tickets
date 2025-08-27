<?php
// URL pública del sistema (termina con /)
if (!defined('BASE_URL')) {
    define('BASE_URL', 'https://tickets.vw-potosina.uk/sistema_tickets/');
}

// Dominio permitido para registro
if (!defined('DOMINIOS_PERMITIDOS')) {
    define('DOMINIOS_PERMITIDOS', [
        '@vw-potosina.com.mx',
        '@seat-potosina.com.mx', 
        '@cupragarage-sanluis.com.mx',
        '@abellauto.com.mx'
    ]);
}

// Email del administrador
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'antonio.munoz@vw-potosina.com.mx');
}

// Si usas subcarpetas, ajusta el path:
if (!defined('UPLOADS_DIR')) {
    // ruta absoluta al directorio de uploads
    define('UPLOADS_DIR', __DIR__ . '/../uploads/');
}

// Configuraciones de seguridad
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
}

if (!defined('LOGIN_LOCKOUT_TIME')) {
    define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos
}

if (!defined('TOKEN_EXPIRY_TIME')) {
    define('TOKEN_EXPIRY_TIME', 3600); // 1 hora
}
