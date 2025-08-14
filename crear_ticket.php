<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
redirigir_si_no_logueado();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tema = limpiar($_POST["tema"]);
    $titulo = limpiar($_POST["titulo"]);
    $descripcion = limpiar($_POST["descripcion"]);
    $departamento = limpiar($_POST["departamento"]);
    $archivo = null;

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

    $stmt = $pdo->prepare("INSERT INTO tickets (id_usuario, tema, titulo, descripcion, archivo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION["usuario_id"], $tema, $titulo, $descripcion, $archivo]);

    // Notificación por correo
    $correo = $_SESSION["usuario_nombre"] . " <" . obtenerCorreoUsuario($_SESSION["usuario_id"], $pdo) . ">";
    $asunto = "Nuevo Ticket creado: $titulo";
    $mensaje = "Se ha creado un nuevo ticket con el siguiente detalle:\n\nTema: $tema\nTitulo: $titulo\nDepartamento: $departamento\n\nIngresa al sistema para ver mas detalles.";
    $cabeceras = "From: soporte@vw-potosina.com.mx";
    mail($correo, $asunto, $mensaje, $cabeceras);

    // Notificar al administrador
    $admin_email = "antonio.munoz@vw-potosina.com.mx";
    $asunto_admin = "Nuevo ticket creado: $titulo";
    $mensaje_admin = "Un nuevo ticket ha sido creado:\n\n"
        . "Usuario: " . $_SESSION["usuario_nombre"] . "\n"
        . "Tema: $tema\n"
        . "Titulo: $titulo\n"
        . "Departamento: $departamento\n"
        . "Detalles del problema:\n$descripcion\n\n"
        . "Ingresa al sistema para revisarlo.";
    $cabeceras_admin = "From: sistema.ticket@vw-potosina.com.mx";
    mail($admin_email, $asunto_admin, $mensaje_admin, $cabeceras_admin);


    echo "
    <!DOCTYPE html>
    <html lang='es' data-page='inicio'>
    <head>
        <meta charset='UTF-8'>
        <title>Ticket creado</title>
        <link rel='stylesheet' href='css/estilo.css'>
        <meta http-equiv='refresh' content='1;url=ver_ticket.php'>
        <style>
            .box h2 {
                color: #003366;
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
                <p class='mensaje-exito'>En unos segundos serás redirigido a la lista de tus tickets...</p>
                <p><a href='ver_ticket.php'>Haz clic aquí si no eres redirigido automáticamente</a></p>
            </div>
        </div>
    </body>
    </html>
    ";
    exit();

}

function obtenerCorreoUsuario($id, $pdo) {
    $stmt = $pdo->prepare("SELECT correo FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $res = $stmt->fetch();
    return $res ? $res["correo"] : "antonio.munoz@vw-potosina.com.mx";
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
            <h2>Crear nuevo ticket</h2>

            <form method="POST" enctype="multipart/form-data">
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

                <button type="submit">Crear ticket</button>
            </form>

            <br><a href="dashboard.php">Volver</a>
        </div>
    </div>
</body>
</html>
