<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';
redirigir_si_no_logueado();

$busqueda = $_GET['buscar'] ?? '';
$categoria = $_GET['categoria'] ?? '';

// Construir consulta de búsqueda
$sql = "SELECT * FROM base_conocimientos WHERE activo = TRUE";
$params = [];

if (!empty($busqueda)) {
    $sql .= " AND (titulo LIKE ? OR contenido LIKE ? OR tags LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params = array_merge($params, [$busqueda_param, $busqueda_param, $busqueda_param]);
}

if (!empty($categoria)) {
    $sql .= " AND categoria = ?";
    $params[] = $categoria;
}

$sql .= " ORDER BY vistas DESC, fecha_creacion DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articulos = $stmt->fetchAll();

// Obtener categorías disponibles
$stmt_cat = $pdo->query("SELECT DISTINCT categoria FROM base_conocimientos WHERE activo = TRUE ORDER BY categoria");
$categorias = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);

// Si se está viendo un artículo específico
$articulo_id = $_GET['id'] ?? null;
$articulo_detalle = null;

if ($articulo_id) {
    $stmt_detalle = $pdo->prepare("SELECT * FROM base_conocimientos WHERE id = ? AND activo = TRUE");
    $stmt_detalle->execute([$articulo_id]);
    $articulo_detalle = $stmt_detalle->fetch();
    
    if ($articulo_detalle) {
        // Incrementar contador de vistas
        $stmt_vista = $pdo->prepare("UPDATE base_conocimientos SET vistas = vistas + 1 WHERE id = ?");
        $stmt_vista->execute([$articulo_id]);
    }
}

// Manejar votación de utilidad
if ($_POST['voto'] ?? false) {
    $voto = $_POST['voto'];
    $articulo_voto_id = $_POST['articulo_id'];
    
    if ($voto === 'si') {
        $stmt_voto = $pdo->prepare("UPDATE base_conocimientos SET util_si = util_si + 1 WHERE id = ?");
    } else {
        $stmt_voto = $pdo->prepare("UPDATE base_conocimientos SET util_no = util_no + 1 WHERE id = ?");
    }
    $stmt_voto->execute([$articulo_voto_id]);
    
    header("Location: base_conocimientos.php?id=$articulo_voto_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $articulo_detalle ? $articulo_detalle['titulo'] : 'Base de Conocimientos'; ?></title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .kb-search {
            background: rgba(255, 255, 255, 0.95);
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        
        .kb-article {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 16px;
            border-left: 4px solid #4299e1;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .kb-article:hover {
            transform: translateX(8px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .kb-article h3 {
            color: #2d3748;
            margin-bottom: 12px;
            font-size: 1.2em;
        }
        
        .kb-article-meta {
            display: flex;
            gap: 16px;
            font-size: 0.85em;
            color: #4a5568;
            margin-top: 12px;
        }
        
        .kb-detail {
            background: rgba(255, 255, 255, 0.95);
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .kb-detail h1 {
            color: #2d3748;
            margin-bottom: 24px;
            font-size: 2em;
        }
        
        .kb-detail-content {
            line-height: 1.8;
            color: #2d3748;
            font-size: 1.1em;
        }
        
        .kb-voting {
            background: rgba(66, 153, 225, 0.1);
            padding: 20px;
            border-radius: 12px;
            margin-top: 32px;
            text-align: center;
        }
        
        .vote-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 16px;
        }
        
        .vote-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .vote-yes {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            color: white;
        }
        
        .vote-no {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            color: white;
        }
        
        .breadcrumb {
            margin-bottom: 24px;
            color: #4a5568;
        }
        
        .breadcrumb a {
            color: #3182ce;
            text-decoration: none;
        }
        
        .category-filter {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        
        .category-tag {
            padding: 6px 12px;
            background: rgba(66, 153, 225, 0.1);
            color: #3182ce;
            border-radius: 16px;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }
        
        .category-tag:hover,
        .category-tag.active {
            background: #4299e1;
            color: white;
        }
    </style>
</head>
<body data-page="ver-ticket">
    <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Alternar modo oscuro"></button>
    
    <div class="container" style="max-width: 100%;">
        <div class="box" style="max-width: 95%;">
            <?php if ($articulo_detalle): ?>
                <!-- Vista de artículo individual -->
                <div class="breadcrumb">
                    <a href="base_conocimientos.php">📚 Base de Conocimientos</a> > 
                    <span><?php echo $articulo_detalle['categoria']; ?></span> > 
                    <strong><?php echo $articulo_detalle['titulo']; ?></strong>
                </div>
                
                <div class="kb-detail">
                    <h1><?php echo $articulo_detalle['titulo']; ?></h1>
                    
                    <div style="display: flex; gap: 20px; margin-bottom: 24px; font-size: 0.9em; color: #4a5568;">
                        <span>📂 <?php echo $articulo_detalle['categoria']; ?></span>
                        <span>👁️ <?php echo $articulo_detalle['vistas']; ?> vistas</span>
                        <span>📅 <?php echo date('d/m/Y', strtotime($articulo_detalle['fecha_creacion'])); ?></span>
                    </div>
                    
                    <div class="kb-detail-content">
                        <?php echo nl2br($articulo_detalle['contenido']); ?>
                    </div>
                    
                    <?php if ($articulo_detalle['tags']): ?>
                    <div style="margin-top: 24px;">
                        <strong>🏷️ Etiquetas:</strong>
                        <?php foreach (explode(',', $articulo_detalle['tags']) as $tag): ?>
                            <span style="background: rgba(66, 153, 225, 0.1); padding: 4px 8px; border-radius: 12px; margin: 0 4px; font-size: 0.85em;"><?php echo trim($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="kb-voting">
                        <h3>¿Te resultó útil este artículo?</h3>
                        <p>Tu opinión nos ayuda a mejorar nuestro contenido</p>
                        
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="articulo_id" value="<?php echo $articulo_detalle['id']; ?>">
                            <div class="vote-buttons">
                                <button type="submit" name="voto" value="si" class="vote-btn vote-yes">
                                    <span class="icon">thumb_up</span>
                                    Sí (<?php echo $articulo_detalle['util_si']; ?>)
                                </button>
                                <button type="submit" name="voto" value="no" class="vote-btn vote-no">
                                    <span class="icon">thumb_down</span>
                                    No (<?php echo $articulo_detalle['util_no']; ?>)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div style="margin-top: 24px; text-align: center;">
                    <a href="base_conocimientos.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border-radius: 8px; text-decoration: none;">
                        <span class="icon">arrow_back</span>
                        Volver a la base de conocimientos
                    </a>
                </div>
                
            <?php else: ?>
                <!-- Vista de lista de artículos -->
                <h2>📚 Base de Conocimientos</h2>
                
                <div class="kb-search">
                    <form method="GET" style="background: none; padding: 0; box-shadow: none; border: none;">
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: end;">
                            <div>
                                <label>🔍 Buscar en la base de conocimientos:</label>
                                <input type="text" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Escribe tu pregunta o palabras clave...">
                            </div>
                            <button type="submit" style="margin-bottom: 0;">Buscar</button>
                        </div>
                        
                        <div class="category-filter">
                            <a href="base_conocimientos.php" class="category-tag <?php echo empty($categoria) ? 'active' : ''; ?>">
                                Todas las categorías
                            </a>
                            <?php foreach ($categorias as $cat): ?>
                                <a href="?categoria=<?php echo urlencode($cat); ?><?php echo $busqueda ? '&buscar=' . urlencode($busqueda) : ''; ?>" 
                                   class="category-tag <?php echo $categoria === $cat ? 'active' : ''; ?>">
                                    <?php echo $cat; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>
                
                <?php if (empty($articulos)): ?>
                    <div class="alert alert-info">
                        No se encontraron artículos que coincidan con tu búsqueda.
                        <br><br>
                        <strong>Sugerencias:</strong><br>
                        • Intenta con palabras clave diferentes<br>
                        • Revisa la ortografía<br>
                        • Usa términos más generales<br>
                        • <a href="crear_ticket.php">Crea un ticket</a> si no encuentras lo que buscas
                    </div>
                <?php else: ?>
                    <div style="margin-bottom: 16px; color: #4a5568;">
                        📊 Se encontraron <?php echo count($articulos); ?> artículo(s)
                        <?php if ($busqueda): ?>
                            para "<strong><?php echo htmlspecialchars($busqueda); ?></strong>"
                        <?php endif; ?>
                        <?php if ($categoria): ?>
                            en la categoría "<strong><?php echo $categoria; ?></strong>"
                        <?php endif; ?>
                    </div>
                    
                    <?php foreach ($articulos as $articulo): ?>
                        <div class="kb-article" onclick="location.href='base_conocimientos.php?id=<?php echo $articulo['id']; ?>'">
                            <h3><?php echo $articulo['titulo']; ?></h3>
                            <p><?php echo substr(strip_tags($articulo['contenido']), 0, 200) . '...'; ?></p>
                            
                            <div class="kb-article-meta">
                                <span>📂 <?php echo $articulo['categoria']; ?></span>
                                <span>👁️ <?php echo $articulo['vistas']; ?> vistas</span>
                                <span>👍 <?php echo $articulo['util_si']; ?> útiles</span>
                                <span>📅 <?php echo date('d/m/Y', strtotime($articulo['fecha_creacion'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div style="background: rgba(237, 137, 54, 0.1); padding: 20px; border-radius: 12px; margin-top: 24px; border-left: 4px solid #ed8936;">
                    <h3 style="color: #dd6b20; margin-bottom: 12px;">💡 ¿No encontraste lo que buscabas?</h3>
                    <p style="color: #2d3748; margin-bottom: 16px;">
                        Si no pudiste resolver tu problema con estos artículos, no te preocupes. 
                        Nuestro equipo de soporte está aquí para ayudarte.
                    </p>
                    <a href="crear_ticket.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">
                        <span class="icon">support_agent</span>
                        Crear Ticket de Soporte
                    </a>
                </div>
            <?php endif; ?>
            
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
