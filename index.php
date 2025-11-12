<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Tickets VW</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body data-page="index">
    <div class="container">
        <div class="box">
            <div class="logo-section">
                <div class="logo-icon">confirmation_number</div>
                <h2 class="welcome-header">Sistema de Tickets VW</h2>
                <p class="subtitle">Plataforma profesional de soporte técnico</p>
            </div>
            
            <div style="background: rgba(66, 153, 225, 0.1); padding: 24px; border-radius: 16px; margin: 24px 0; border: 1px solid rgba(66, 153, 225, 0.2);">
                <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 16px;">
                    <span class="icon" style="color: #4299e1; font-size: 24px;">info</span>
                    <h3 style="color: #2d3748; margin: 0; font-size: 1.1em;">¿Qué puedes hacer?</h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; text-align: left; color: #4a5568;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="icon" style="color: #38a169;">check_circle</span>
                        <span>Reportar problemas técnicos</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="icon" style="color: #38a169;">check_circle</span>
                        <span>Solicitar soporte IT</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="icon" style="color: #38a169;">check_circle</span>
                        <span>Seguimiento en tiempo real</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="icon" style="color: #38a169;">check_circle</span>
                        <span>Historial completo</span>
                    </div>
                </div>
            </div>
            
            <div class="botones">
                <a href="registro.php">
                    <span class="icon">person_add</span>
                    Crear Nueva Cuenta
                </a>
                <a href="login.php">
                    <span class="icon">login</span>
                    Acceder al Sistema
                </a>
            </div>
            
            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.1); color: #4a5568; font-size: 0.9em;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <span class="icon">security</span>
                    <span>Sistema seguro y confiable para VW Potosina</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
