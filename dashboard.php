<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
redirigir_si_no_logueado();

function es_admin_local($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $res = $stmt->fetch();
    return $res && $res["rol"] === "admin";
}

$es_admin = es_admin_local($pdo, $_SESSION["usuario_id"]);
?>

<!DOCTYPE html>
<html lang="es" data-page="inicio">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .welcome-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2em;
            margin-bottom: 30px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            backdrop-filter: blur(5px);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .card-icon {
            font-size: 3em;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-title {
            font-size: 1.2em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .card-description {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 20px;
        }

        .card-link {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .card-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .card-link::after {
            display: none;
        }

        .admin-card {
            border: 2px solid #f39c12;
            background: linear-gradient(135deg, rgba(243, 156, 18, 0.1) 0%, rgba(230, 126, 34, 0.1) 100%);
        }

        .logout-card {
            border: 2px solid #e74c3c;
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.1) 0%, rgba(192, 57, 43, 0.1) 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2 class="welcome-message">¡Hola, <?php echo $_SESSION["usuario_nombre"]; ?>! 👋</h2>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-icon">➕</div>
                    <div class="card-title">Crear Ticket</div>
                    <div class="card-description">Reporta un nuevo problema o solicitud</div>
                    <a href="crear_ticket.php" class="card-link">Crear nuevo</a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon">📋</div>
                    <div class="card-title">Mis Tickets</div>
                    <div class="card-description">Revisa el estado de tus solicitudes</div>
                    <a href="ver_ticket.php" class="card-link">Ver tickets</a>
                </div>

                <?php if ($es_admin): ?>
                <div class="dashboard-card admin-card">
                    <div class="card-icon">🛠️</div>
                    <div class="card-title">Administración</div>
                    <div class="card-description">Panel de control administrativo</div>
                    <a href="admin_tickets.php" class="card-link">Acceder</a>
                </div>
                <?php endif; ?>

                <div class="dashboard-card logout-card">
                    <div class="card-icon">🔒</div>
                    <div class="card-title">Cerrar Sesión</div>
                    <div class="card-description">Salir del sistema de forma segura</div>
                    <a href="logout.php" class="card-link">Salir</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
