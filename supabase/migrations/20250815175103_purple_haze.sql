-- Actualización de la base de datos para las nuevas funcionalidades
-- Ejecutar este script para agregar las nuevas columnas y tablas

-- Agregar nuevas columnas a la tabla tickets
ALTER TABLE tickets 
ADD COLUMN prioridad ENUM('Baja', 'Media', 'Alta', 'Critica') DEFAULT 'Media' AFTER estado,
ADD COLUMN categoria ENUM('Bug', 'Feature Request', 'Consulta', 'Incidente') DEFAULT 'Consulta' AFTER prioridad,
ADD COLUMN numero_ticket VARCHAR(20) UNIQUE AFTER id,
ADD COLUMN tiempo_estimado INT DEFAULT NULL COMMENT 'Tiempo estimado en horas',
ADD COLUMN fecha_limite DATETIME DEFAULT NULL,
ADD COLUMN tecnico_asignado INT DEFAULT NULL,
ADD COLUMN rating INT DEFAULT NULL COMMENT 'Calificación del 1 al 5',
ADD COLUMN comentario_rating TEXT DEFAULT NULL;

-- Actualizar tickets existentes con números únicos
UPDATE tickets SET numero_ticket = CONCAT('VW-', YEAR(fecha_creacion), '-', LPAD(id, 4, '0')) WHERE numero_ticket IS NULL;

-- Crear tabla para logs de actividad
CREATE TABLE IF NOT EXISTS logs_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    detalles TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_fecha (usuario_id, fecha),
    INDEX idx_fecha (fecha)
);

-- Crear tabla para plantillas de respuestas
CREATE TABLE IF NOT EXISTS plantillas_respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    categoria VARCHAR(50),
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    creado_por INT,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Insertar plantillas predeterminadas
INSERT INTO plantillas_respuestas (titulo, contenido, categoria) VALUES
('Ticket Recibido', 'Hemos recibido tu ticket y lo estamos revisando. Te contactaremos pronto con una solución.', 'General'),
('Solicitar Más Información', 'Necesitamos más información para poder ayudarte mejor. Por favor proporciona más detalles sobre el problema.', 'General'),
('Problema Resuelto', 'El problema ha sido resuelto. Por favor confirma si todo funciona correctamente.', 'Resolución'),
('Escalamiento a Técnico', 'Tu ticket ha sido escalado a nuestro equipo técnico especializado. Te contactarán en las próximas 24 horas.', 'Escalamiento');

-- Crear tabla para notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_leida (usuario_id, leida),
    INDEX idx_fecha (fecha_creacion)
);

-- Crear tabla para configuraciones del sistema
CREATE TABLE IF NOT EXISTS configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion TEXT,
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar configuraciones predeterminadas
INSERT INTO configuraciones (clave, valor, descripcion, tipo) VALUES
('tiempo_respuesta_sla', '24', 'Tiempo máximo de respuesta en horas', 'number'),
('max_archivos_adjuntos', '5', 'Número máximo de archivos por ticket', 'number'),
('tamaño_max_archivo', '5242880', 'Tamaño máximo de archivo en bytes (5MB)', 'number'),
('notificaciones_email', 'true', 'Enviar notificaciones por email', 'boolean'),
('modo_mantenimiento', 'false', 'Activar modo de mantenimiento', 'boolean');

-- Crear tabla para FAQ/Base de conocimientos
CREATE TABLE IF NOT EXISTS base_conocimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(300) NOT NULL,
    contenido TEXT NOT NULL,
    categoria VARCHAR(100),
    tags VARCHAR(500),
    activo BOOLEAN DEFAULT TRUE,
    vistas INT DEFAULT 0,
    util_si INT DEFAULT 0,
    util_no INT DEFAULT 0,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    creado_por INT,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    FULLTEXT(titulo, contenido, tags)
);

-- Insertar algunos artículos de ejemplo
INSERT INTO base_conocimientos (titulo, contenido, categoria, tags) VALUES
('¿Cómo crear un ticket?', 'Para crear un ticket, inicia sesión en el sistema y haz clic en "Crear nuevo ticket". Completa todos los campos requeridos y describe detalladamente tu problema.', 'General', 'ticket,crear,nuevo,ayuda'),
('Tipos de prioridad', 'Baja: Problemas menores que no afectan el trabajo. Media: Problemas que afectan parcialmente el trabajo. Alta: Problemas que impiden trabajar. Crítica: Problemas que afectan a múltiples usuarios.', 'Prioridades', 'prioridad,urgencia,clasificación'),
('¿Qué archivos puedo adjuntar?', 'Puedes adjuntar archivos JPG, PNG, PDF, DOC, DOCX, TXT y ZIP. El tamaño máximo por archivo es de 5MB.', 'Archivos', 'adjuntos,archivos,formatos,tamaño');

-- Agregar índices para mejorar rendimiento
ALTER TABLE tickets ADD INDEX idx_prioridad (prioridad);
ALTER TABLE tickets ADD INDEX idx_categoria (categoria);
ALTER TABLE tickets ADD INDEX idx_estado_fecha (estado, fecha_creacion);
ALTER TABLE tickets ADD INDEX idx_tecnico_asignado (tecnico_asignado);

-- Agregar columna para preferencias de usuario
ALTER TABLE usuarios 
ADD COLUMN preferencias JSON DEFAULT NULL,
ADD COLUMN ultimo_acceso DATETIME DEFAULT NULL,
ADD COLUMN tema_oscuro BOOLEAN DEFAULT FALSE;

-- Crear tabla para sesiones (opcional, para mejor seguridad)
CREATE TABLE IF NOT EXISTS sesiones_usuario (
    id VARCHAR(128) PRIMARY KEY,
    usuario_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_expiracion (fecha_expiracion)
);