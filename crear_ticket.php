<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
redirigir_si_no_logueado();

function obtenerCorreoUsuario($id, $pdo) {
    $stmt = $pdo->prepare("SELECT correo FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $res = $stmt->fetch();
    return $res ? $res["correo"] : "antonio.munoz@vw-potosina.com.mx";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tema = limpiar($_POST["tema"]);
    $titulo = limpiar($_POST["titulo"]);
    $descripcion = limpiar($_POST["descripcion"]);
    $departamento = limpiar($_POST["departamento"]);
    $prioridad = limpiar($_POST["prioridad"] ?? 'Media');
    $categoria = limpiar($_POST["categoria"] ?? 'Consulta');
    $archivo = null;
    
    // Generar número de ticket simple
    $numero_ticket = 'VW-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Manejar múltiples archivos (solo guarda el primero por ahora)
    if (!empty($_FILES["archivo"]["name"][0])) {
        $archivos_subidos = [];
        foreach ($_FILES["archivo"]["name"] as $key => $nombre_original) {
            $nombre_archivo = basename($nombre_original);
            $ruta_archivo = "uploads/" . time() . "_" . $nombre_archivo;
            if (move_uploaded_file($_FILES["archivo"]["tmp_name"][$key], $ruta_archivo)) {
                $archivos_subidos[] = $ruta_archivo;
            }
        }
        // Guarda como texto separado por comas si se subieron varios
        if (!empty($archivos_subidos)) {
            $archivo = implode(",", $archivos_subidos);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO tickets (id_usuario, tema, titulo, descripcion, archivo, departamento, numero_ticket, prioridad, categoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION["usuario_id"], $tema, $titulo, $descripcion, $archivo, $departamento, $numero_ticket, $prioridad, $categoria]);
    
    $ticket_id = $pdo->lastInsertId();
    
    // Registrar actividad
    if (function_exists('log_actividad')) {
        log_actividad($pdo, $_SESSION["usuario_id"], "Ticket creado", "Ticket #$numero_ticket creado");
    }

    // Notificación por correo al usuario
    $correo = $_SESSION["usuario_nombre"] . " <" . obtenerCorreoUsuario($_SESSION["usuario_id"], $pdo) . ">";
    $asunto = "Nuevo Ticket #$numero_ticket creado: $titulo";
    $mensaje = "Se ha creado un nuevo ticket con el siguiente detalle:\n\nNúmero: $numero_ticket\nTema: $tema\nTitulo: $titulo\nPrioridad: $prioridad\nCategoría: $categoria\nDepartamento: $departamento\n\nIngresa al sistema para ver mas detalles.";
    $cabeceras = "From: soporte@vw-potosina.com.mx";
    
    // Enviar email solo si la función existe
    if (function_exists('enviar_notificacion_email')) {
        enviar_notificacion_email($correo, $asunto, $mensaje);
    } else {
        // Fallback con mail() básico
        @mail($correo, $asunto, $mensaje, $cabeceras);
    }

    // Notificar al administrador
    $admin_email = "soporte@vw-potosina.com.mx";
    $asunto_admin = "Nuevo ticket #$numero_ticket creado: $titulo";
    $mensaje_admin = "Un nuevo ticket ha sido creado:\n\n"
        . "Número: $numero_ticket\n"
        . "Usuario: " . $_SESSION["usuario_nombre"] . "\n"
        . "Tema: $tema\n"
        . "Titulo: $titulo\n"
        . "Prioridad: $prioridad\n"
        . "Categoría: $categoria\n"
        . "Departamento: $departamento\n"
        . "Detalles del problema:\n$descripcion\n\n"
        . "Ingresa al sistema para revisarlo.";
    $cabeceras_admin = "From: sistema.ticket@vw-potosina.com.mx";
    
    if (function_exists('enviar_notificacion_email')) {
        enviar_notificación_email($admin_email, $asunto_admin, $mensaje_admin);
    } else {
        @mail($admin_email, $asunto_admin, $mensaje_admin, $cabeceras_admin);
    }

    $mensaje_exito = "
    <!DOCTYPE html>
    <html lang='es' data-page='inicio'>
    <head>
        <meta charset='UTF-8'>
        <title>Ticket creado</title>
        <link rel='stylesheet' href='css/estilo.css'>
        <meta http-equiv='refresh' content='1;url=ver_ticket.php'>
        <style>
            .box h2 {
                color: #003366 !important;
                margin-bottom: 15px;
            }
            .mensaje-exito {
                font-size: 18px;
                background-color: rgba(255,255,255,0.9);
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='box'>
                <h2>✅ Ticket creado correctamente</h2>
                <div class='alert alert-success'>
                    Tu ticket #$numero_ticket ha sido creado exitosamente
                </div>
                <p class='mensaje-exito'>En unos segundos serás redirigido a la lista de tus tickets...</p>
                <p><a href='ver_ticket.php'>Haz clic aquí si no eres redirigido automáticamente</a></p>
            </div>
        </div>
    </body>
    </html>
    ";
    echo $mensaje_exito;
    exit();
}
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <title>Crear nuevo ticket</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>📝 Crear nuevo ticket</h2>
            
            <div class="alert alert-info">
                Completa todos los campos para que podamos ayudarte de la mejor manera
            </div>

            <form method="POST" enctype="multipart/form-data">
                <label>Categoría del problema:</label>
                <select name="categoria" required>
                    <option value="">-- Selecciona el tipo --</option>
                    <option value="Bug">🐛 Bug/Error del sistema</option>
                    <option value="Feature Request">✨ Solicitud de nueva función</option>
                    <option value="Consulta">❓ Consulta/Pregunta</option>
                    <option value="Incidente">⚠️ Incidente/Problema urgente</option>
                </select>
                
                <label>Prioridad:</label>
                <select name="prioridad" required>
                    <option value="">-- Selecciona la urgencia --</option>
                    <option value="Baja">🟢 Baja - No es urgente</option>
                    <option value="Media">🟡 Media - Moderadamente importante</option>
                    <option value="Alta">🟠 Alta - Necesita atención pronto</option>
                    <option value="Critica">🔴 Crítica - Requiere atención inmediata</option>
                </select>
                <label>Tema de ayuda:</label>
                <select name="tema" required>
                    <option value="">-- Selecciona --</option>
                    <option value="Total Dealer">Total Dealer</option>
                    <option value="Software">Software</option>
                    <option value="Hardware">Hardware</option>
                    <option value="Telefonia">Telefonia</option>
                    <option value="Salesforce">Salesforce</option>
                    <option value="Otros">Otros</option>
                </select>

                <label>Departamento:</label>
                <select name="departamento" required>
                    <option value="">-- Selecciona --</option>
                    <option value="Administracion">Administracion</option>
                    <option value="Servicio VW">Servicio VW</option>
                    <option value="Servicio Seat">Servicio Seat</option>
                    <option value="Seminuevos">Seminuevos</option>
                    <option value="Ventas VW">Ventas VW</option>
                    <option value="Ventas Seat">Ventas Seat</option>
                    <option value="Refacciones">Refacciones</option>
                    <option value="Marketing">Marketing</option>
                </select>

                <label>Titulo del ticket:</label>
                <input type="text" name="titulo" required>

                <label>Detalles del problema:</label>
                <textarea name="descripcion" rows="5" required></textarea>

                <label>Adjuntar archivos (max 5):</label>
                <input type="file" name="archivo[]" multiple>
                
                <div style="background: rgba(66, 153, 225, 0.1); padding: 16px; border-radius: 8px; margin: 16px 0; font-size: 0.9em; color: #2d3748;">
                    <strong>💡 Consejos para un mejor soporte:</strong><br>
                    • Describe el problema con el mayor detalle posible<br>
                    • Incluye capturas de pantalla si es necesario<br>
                    • Menciona qué estabas haciendo cuando ocurrió el problema<br>
                    • Si es un error, incluye el mensaje exacto que aparece
                </div>

                <button type="submit">Crear ticket</button>
            </form>

            <br><a href="dashboard.php">Volver</a>
        </div>
    </div>
</body>
</html>
