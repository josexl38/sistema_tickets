-- Script para verificar y corregir la estructura de la tabla tickets
-- Ejecutar este script si hay problemas con la creación de tickets

-- Verificar si las columnas existen antes de agregarlas
SET @sql = '';

-- Verificar columna numero_ticket
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'tickets' 
AND column_name = 'numero_ticket';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE tickets ADD COLUMN numero_ticket VARCHAR(20) UNIQUE AFTER id;');
END IF;

-- Verificar columna prioridad
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'tickets' 
AND column_name = 'prioridad';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE tickets ADD COLUMN prioridad ENUM(\'Baja\', \'Media\', \'Alta\', \'Critica\') DEFAULT \'Media\' AFTER estado;');
END IF;

-- Verificar columna categoria
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'tickets' 
AND column_name = 'categoria';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE tickets ADD COLUMN categoria ENUM(\'Bug\', \'Feature Request\', \'Consulta\', \'Incidente\') DEFAULT \'Consulta\' AFTER prioridad;');
END IF;

-- Ejecutar las alteraciones si hay alguna
IF LENGTH(@sql) > 0 THEN
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END IF;

-- Actualizar tickets existentes sin número
UPDATE tickets 
SET numero_ticket = CONCAT('VW-', YEAR(fecha_creacion), '-', LPAD(id, 4, '0')) 
WHERE numero_ticket IS NULL OR numero_ticket = '';

-- Verificar estructura final
DESCRIBE tickets;