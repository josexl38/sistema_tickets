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
    
    // Generar número de ticket único
    $numero_ticket = 'VW-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Manejar múltiples archivos
    if (!empty($_FILES["archivo"]["name"][0])) {
        $archivos_subidos = [];
        foreach ($_FILES["archivo"]["name"] as $key => $nombre_original) {
            if (!empty($nombre_original)) {
                $nombre_archivo = basename($nombre_original);
                $ruta_archivo = "uploads/" . time() . "_" . $nombre_archivo;
                if (move_uploaded_file($_FILES["archivo"]["tmp_name"][$key], $ruta_archivo)) {
                    $archivos_subidos[] = $ruta_archivo;
                }
            }
        }
        if (!empty($archivos_subidos)) {
            $archivo = implode(",", $archivos_subidos);
        }
    }

    try {
        // Insertar ticket con la estructura exacta de tu BD
        $stmt = $pdo->prepare("INSERT INTO tickets (numero_ticket, id_usuario, tema, titulo, descripcion, archivo, prioridad, categoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $resultado = $stmt->execute([$numero_ticket, $_SESSION["usuario_id"], $tema, $titulo, $descripcion, $archivo, $prioridad, $categoria]);
        
        if ($resultado) {
            $ticket_id = $pdo->lastInsertId();
            
            // Registrar actividad (solo si la función existe)
            if (function_exists('log_actividad')) {
                log_actividad($pdo, $_SESSION["usuario_id"], "Ticket creado", "Ticket #$numero_ticket creado");
            }

            // Notificación por correo al usuario
            $correo = obtenerCorreoUsuario($_SESSION["usuario_id"], $pdo);
            $asunto = "Nuevo Ticket #$numero_ticket creado: $titulo";
            $mensaje = "Se ha creado un nuevo ticket con el siguiente detalle:\n\n"
                     . "Numero: $numero_ticket\n"
                     . "Tema: $tema\n"
                     . "Titulo: $titulo\n"
                     . "Prioridad: $prioridad\n"
                     . "Categoria: $categoria\n\n"
                     . "Ingresa al sistema para ver mas detalles.";
            $cabeceras = "From: soporte@vw-potosina.com.mx";
            
            // Enviar email (con manejo de errores)
            @mail($correo, $asunto, $mensaje, $cabeceras);

            // Notificar al administrador
            $admin_email = "antonio.munoz@vw-potosina.com.mx";
            $asunto_admin = "Nuevo ticket #$numero_ticket creado: $titulo";
            $mensaje_admin = "Un nuevo ticket ha sido creado:\n\n"
                . "Numero: $numero_ticket\n"
                . "Usuario: " . $_SESSION["usuario_nombre"] . "\n"
                . "Tema: $tema\n"
                . "Titulo: $titulo\n"
                . "Prioridad: $prioridad\n"
                . "Categoria: $categoria\n"
                . "Detalles del problema:\n$descripcion\n\n"
                . "Ingresa al sistema para revisarlo.";
            $cabeceras_admin = "From: sistema.ticket@vw-potosina.com.mx";
            
            @mail($admin_email, $asunto_admin, $mensaje_admin, $cabeceras_admin);

            // Mostrar mensaje de éxito
            $mensaje_exito = "
            <!DOCTYPE html>
            <html lang='es' data-page='inicio'>
            <head>
                <meta charset='UTF-8'>
                <title>Ticket creado</title>
                <link rel='stylesheet' href='css/estilo.css'>
                <meta http-equiv='refresh' content='3;url=ver_ticket.php'>
                <style>
                    .box h2 {
                        color: #2d3748 !important;
                        margin-bottom: 15px;
                    }
                    .mensaje-exito {
                        font-size: 18px;
                        background-color: rgba(56, 161, 105, 0.1);
                        padding: 20px;
                        border-radius: 12px;
                        border-left: 4px solid #38a169;
                        color: #2f855a;
                        font-weight: 600;
                        border: 1px solid rgba(56, 161, 105, 0.2);
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='box'>
                        <h2>✅ Ticket creado correctamente</h2>
                        <div class='mensaje-exito'>
                            Tu ticket #$numero_ticket ha sido creado exitosamente.<br><br>
                            <strong>Detalles:</strong><br>
                            • Tema: $tema<br>
                            • Prioridad: $prioridad<br>
                            • Categoría: $categoria<br><br>
                            En unos segundos serás redirigido a la lista de tus tickets...
                        </div>
                        <br>
                        <p><a href='ver_ticket.php'>Haz clic aquí si no eres redirigido automáticamente</a></p>
                    </div>
                </div>
            </body>
            </html>
            ";
            echo $mensaje_exito;
            exit();
        } else {
            throw new Exception("Error al insertar el ticket en la base de datos");
        }
        
    } catch (Exception $e) {
        // Mostrar error detallado para debugging
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Error</title>
            <link rel='stylesheet' href='css/estilo.css'>
        </head>
        <body>
            <div class='container'>
                <div class='box'>
                    <h2>❌ Error al crear ticket</h2>
                    <div class='alert alert-error'>
                        Error: " . $e->getMessage() . "
                    </div>
                    <br>
                    <a href='crear_ticket.php'>Volver a intentar</a> | 
                    <a href='dashboard.php'>Ir al inicio</a>
                </div>
            </div>
        </body>
        </html>";
        exit();
    }
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
    <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Alternar modo oscuro"></button>
    
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
                <input type="text" name="titulo" required placeholder="Describe brevemente el problema">

                <label>Detalles del problema:</label>
                <textarea name="descripcion" rows="5" required placeholder="Describe detalladamente el problema que tienes"></textarea>

                <label>Adjuntar archivos (opcional, max 5):</label>
                <input type="file" name="archivo[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">
                
                <div style="background: rgba(66, 153, 225, 0.1); padding: 16px; border-radius: 8px; margin: 16px 0; font-size: 0.9em; color: #2d3748;">
                    <strong>💡 Consejos para un mejor soporte:</strong><br>
                    • Describe el problema con el mayor detalle posible<br>
                    • Incluye capturas de pantalla si es necesario<br>
                    • Menciona qué estabas haciendo cuando ocurrió el problema<br>
                    • Si es un error, incluye el mensaje exacto que aparece
                </div>

                <button type="submit">Crear ticket</button>
            </form>

            <br><a href="dashboard.php">🏠 Volver al inicio</a>
        </div>
    </div>
    
    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.body.classList.add('dark-mode');
            }
        });
    </script>
</body>
</html>