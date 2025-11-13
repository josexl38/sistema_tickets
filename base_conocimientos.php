<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/funciones.php';
redirigir_si_no_logueado();

// Función para instalar artículos base
function instalar_articulos_base($pdo) {
    $articulos_base = [
        // WINDOWS BÁSICO
        [
            'titulo' => '¿Cómo reiniciar mi computadora correctamente?',
            'contenido' => "PASOS PARA REINICIAR TU COMPUTADORA:\n\n1. Guarda todos tus archivos abiertos (Word, Excel, etc.)\n2. Cierra todos los programas\n3. Haz clic en el botón de Inicio (esquina inferior izquierda)\n4. Haz clic en el botón de encendido ⚡\n5. Selecciona 'Reiniciar'\n\n⚠️ IMPORTANTE: Nunca apagues la computadora presionando el botón físico de encendido, a menos que sea una emergencia.\n\n¿CUÁNDO DEBO REINICIAR?\n• Si la computadora va lenta\n• Después de instalar actualizaciones\n• Si un programa no responde\n• Si te lo indica el técnico de soporte\n\nUN REINICIO RESUELVE EL 80% DE LOS PROBLEMAS COMUNES.",
            'categoria' => 'Windows Básico',
            'tags' => 'reiniciar,lento,no responde,windows'
        ],
        [
            'titulo' => 'Mi computadora está muy lenta, ¿qué hago?',
            'contenido' => "SOLUCIONES RÁPIDAS PARA COMPUTADORA LENTA:\n\n1. REINICIA LA COMPUTADORA\n   • Esto cierra programas que están usando memoria\n   • Limpia procesos temporales\n\n2. CIERRA PROGRAMAS QUE NO ESTÉS USANDO\n   • Revisa la barra de tareas (abajo)\n   • Cierra pestañas del navegador que no necesites\n   • No tengas muchos programas abiertos al mismo tiempo\n\n3. REVISA EL ESPACIO EN DISCO\n   • Abre 'Este equipo' o 'Mi PC'\n   • Revisa el disco C: (debe tener al menos 20% libre)\n   • Si está lleno, borra archivos que no necesites\n\n4. ESPERA LAS ACTUALIZACIONES\n   • A veces Windows se actualiza automáticamente\n   • Esto hace que vaya lento temporalmente\n   • Déjalo terminar y reinicia\n\n⚠️ SI SIGUE LENTO después de esto, crea un ticket para que soporte revise tu equipo.",
            'categoria' => 'Windows Básico',
            'tags' => 'lento,rendimiento,velocidad,ram,disco'
        ],
        [
            'titulo' => '¿Cómo conecto el WiFi en mi laptop?',
            'contenido' => "PASOS PARA CONECTAR A WIFI:\n\n1. BUSCA EL ÍCONO DE RED\n   • Está en la esquina inferior derecha (barra de tareas)\n   • Parece una señal de WiFi 📶 o un monitor 🖥️\n\n2. HAZ CLIC EN EL ÍCONO\n   • Se abrirá un menú con las redes disponibles\n\n3. SELECCIONA LA RED DE LA OFICINA\n   • Busca el nombre de la red (SSID) de tu empresa\n   • Haz clic sobre ella\n\n4. HAZ CLIC EN 'CONECTAR'\n   • Marca la casilla 'Conectar automáticamente' si quieres que se conecte siempre\n\n5. INGRESA LA CONTRASEÑA\n   • Escribe la contraseña del WiFi (pregunta a tu jefe o sistemas)\n   • Haz clic en 'Siguiente'\n\n✅ LISTO: Deberías estar conectado\n\n⚠️ PROBLEMAS COMUNES:\n• Si dice 'No se puede conectar', verifica la contraseña\n• Si no aparece la red, activa el WiFi (puede haber un botón físico o tecla Fn+F2)\n• Si sigue sin funcionar, crea un ticket",
            'categoria' => 'Conexión a Internet',
            'tags' => 'wifi,internet,conexion,red'
        ],
        [
            'titulo' => 'No tengo internet, ¿qué reviso primero?',
            'contenido' => "CHECKLIST PARA PROBLEMAS DE INTERNET:\n\n✓ PASO 1: ¿ESTÁS CONECTADO?\n   • Revisa el ícono de red en la esquina inferior derecha\n   • Si tiene una X roja o un signo de admiración (⚠️), no hay conexión\n\n✓ PASO 2: VERIFICA EL CABLE (si es conexión por cable)\n   • Revisa que el cable de red esté conectado a tu computadora\n   • Revisa que esté conectado al switch/router\n   • El cable no debe estar dañado\n\n✓ PASO 3: VERIFICA EL WIFI (si es conexión inalámbrica)\n   • ¿El WiFi está activado? (revisa el ícono)\n   • ¿Estás conectado a la red correcta?\n\n✓ PASO 4: REINICIA TU CONEXIÓN\n   • Haz clic en el ícono de red\n   • Desconecta de la red\n   • Vuelve a conectar\n\n✓ PASO 5: REINICIA LA COMPUTADORA\n   • Muchas veces esto soluciona el problema\n\n✓ PASO 6: PREGUNTA A TUS COMPAÑEROS\n   • ¿Ellos tienen internet?\n   • Si nadie tiene, puede ser problema general (avisa a sistemas)\n   • Si solo tú no tienes, crea un ticket\n\n⚠️ SI NADA FUNCIONA: Crea un ticket con soporte técnico",
            'categoria' => 'Conexión a Internet',
            'tags' => 'internet,sin conexion,red,cable,ethernet'
        ],
        
        // IMPRESORAS
        [
            'titulo' => '¿Cómo imprimir un documento?',
            'contenido' => "GUÍA BÁSICA PARA IMPRIMIR:\n\n1. ABRE TU DOCUMENTO\n   • Word, Excel, PDF, etc.\n\n2. USA CTRL + P (atajo rápido)\n   O ve a: Archivo → Imprimir\n\n3. SELECCIONA LA IMPRESORA\n   • Haz clic en el menú desplegable\n   • Elige la impresora correcta (pregunta cuál usar)\n\n4. CONFIGURA LAS OPCIONES:\n   • Número de copias (¿cuántas quieres?)\n   • Páginas (¿todas o solo algunas?)\n   • Color o blanco y negro\n   • Orientación (vertical u horizontal)\n\n5. HAZ CLIC EN 'IMPRIMIR'\n\n💡 CONSEJOS:\n• Revisa la vista previa antes de imprimir\n• Si son muchas páginas, pregunta si es necesario imprimir todo\n• Usa 'Imprimir a PDF' para guardar como archivo en lugar de imprimir\n\n⚠️ ¿NO APARECE TU IMPRESORA?\n• Crea un ticket para que la instalen en tu computadora",
            'categoria' => 'Impresoras',
            'tags' => 'imprimir,impresora,papel,documento'
        ],
        [
            'titulo' => 'La impresora no imprime, ¿qué hago?',
            'contenido' => "SOLUCIONES PARA PROBLEMAS DE IMPRESIÓN:\n\n1. VERIFICA LO BÁSICO:\n   ✓ ¿La impresora está encendida? (botón de encendido)\n   ✓ ¿Tiene papel?\n   ✓ ¿Los cables están conectados?\n   ✓ ¿Hay luces rojas o mensajes de error en la pantalla?\n\n2. REVISA EN LA COMPUTADORA:\n   • Ve a Panel de Control → Dispositivos e impresoras\n   • Busca tu impresora\n   • ¿Aparece como 'Sin conexión' o 'Error'?\n\n3. CANCELA TRABAJOS ATASCADOS:\n   • Haz doble clic en la impresora\n   • Se abrirá la cola de impresión\n   • Ve a 'Impresora' → 'Cancelar todos los documentos'\n   • Intenta imprimir de nuevo\n\n4. REINICIA LA IMPRESORA:\n   • Apágala completamente (desconecta el cable)\n   • Espera 30 segundos\n   • Enciéndela de nuevo\n   • Espera a que termine de iniciar\n\n5. REINICIA TU COMPUTADORA:\n   • A veces el problema está en la comunicación\n\n⚠️ ERRORES COMUNES:\n• 'Atasco de papel': Abre la impresora y retira cuidadosamente el papel atorado\n• 'Sin tinta': Revisa los niveles de tinta/tóner\n• 'Impresora fuera de línea': Ve al Paso 2\n\n❌ SI NADA FUNCIONA: Crea un ticket (indica el nombre de la impresora)",
            'categoria' => 'Impresoras',
            'tags' => 'impresora,no imprime,atasco,error,offline'
        ],
        
        // CORREO ELECTRÓNICO
        [
            'titulo' => '¿Cómo enviar un correo con archivo adjunto?',
            'contenido' => "PASOS PARA ENVIAR ARCHIVOS POR EMAIL:\n\n1. ABRE TU CORREO (Outlook, Gmail, etc.)\n\n2. HAZ CLIC EN 'NUEVO MENSAJE' o 'REDACTAR'\n\n3. LLENA LOS CAMPOS:\n   • Para: correo del destinatario\n   • Asunto: tema del correo (sé claro)\n   • Mensaje: escribe tu mensaje\n\n4. ADJUNTAR ARCHIVO:\n   • Busca el ícono de 📎 (clip)\n   • O ve a 'Insertar' → 'Adjuntar archivo'\n   • Selecciona el archivo de tu computadora\n   • Haz clic en 'Abrir'\n\n5. ESPERA A QUE SE CARGUE\n   • Verás una barra de progreso\n   • El nombre del archivo aparecerá en el correo\n\n6. HAZ CLIC EN 'ENVIAR'\n\n💡 CONSEJOS IMPORTANTES:\n• NO envíes archivos muy pesados (más de 20MB)\n• Revisa que adjuntaste el archivo correcto antes de enviar\n• Si el archivo es muy grande, usa servicios como OneDrive o Google Drive\n• Siempre revisa ortografía y destinatarios antes de enviar\n\n⚠️ 'EL ARCHIVO ES MUY GRANDE':\n• Comprime el archivo (ZIP)\n• O sube a la nube y comparte el enlace",
            'categoria' => 'Correo Electrónico',
            'tags' => 'email,correo,adjunto,archivo,enviar'
        ],
        [
            'titulo' => 'Olvidé mi contraseña de correo, ¿qué hago?',
            'contenido' => "SI OLVIDASTE TU CONTRASEÑA:\n\n❌ NO INTENTES ADIVINAR\n• Después de varios intentos fallidos, tu cuenta se puede bloquear\n\n✅ PASOS A SEGUIR:\n\n1. EN LA PANTALLA DE INICIO DE SESIÓN:\n   • Busca el enlace '¿Olvidaste tu contraseña?'\n   • Haz clic ahí\n\n2. SIGUE LAS INSTRUCCIONES:\n   • Te pedirá tu usuario o correo\n   • Puede enviarte un código a tu celular o correo alternativo\n   • Sigue los pasos para crear una nueva contraseña\n\n3. SI ESTO NO FUNCIONA:\n   • Crea un ticket de soporte\n   • Indica: 'Necesito restablecer mi contraseña de correo'\n   • Proporciona tu nombre completo y usuario\n\n4. CUANDO RECUPERES EL ACCESO:\n   • Cambia inmediatamente tu contraseña\n   • Usa una contraseña segura (al menos 8 caracteres, letras y números)\n   • Anótala en un lugar seguro\n\n💡 PREVENCIÓN:\n• No compartas tu contraseña con nadie\n• Cambia tu contraseña cada 3-6 meses\n• Usa contraseñas diferentes para cada servicio\n\n⚠️ IMPORTANTE: Los tickets de contraseña tienen prioridad alta",
            'categoria' => 'Correo Electrónico',
            'tags' => 'contraseña,password,olvide,reset,correo'
        ],
        
        // MICROSOFT OFFICE
        [
            'titulo' => '¿Cómo guardar un documento en Word/Excel?',
            'contenido' => "GUARDAR TUS DOCUMENTOS - MUY IMPORTANTE:\n\n🔴 REGLA DE ORO: GUARDA CADA 5-10 MINUTOS\n\n✅ MÉTODO 1: ATAJO RÁPIDO (MÁS FÁCIL)\n• Presiona CTRL + G (o CTRL + S)\n• Listo, guardado\n\n✅ MÉTODO 2: MENÚ\n1. Haz clic en 'Archivo' (esquina superior izquierda)\n2. Haz clic en 'Guardar' o 'Guardar como'\n3. Elige la ubicación (Escritorio, Documentos, etc.)\n4. Escribe un nombre descriptivo\n5. Haz clic en 'Guardar'\n\n💡 GUARDAR vs GUARDAR COMO:\n• GUARDAR: Actualiza el archivo actual\n• GUARDAR COMO: Crea una copia nueva con otro nombre\n\n📁 ¿DÓNDE GUARDAR?\n• Documentos: para archivos de trabajo\n• Escritorio: solo para acceso rápido temporal\n• Red/Servidor: si tu empresa usa carpetas compartidas\n\n⚠️ CONSEJOS:\n• Usa nombres descriptivos: 'Reporte_Ventas_Enero2025.xlsx'\n• NO uses solo 'Documento1' o 'Sin título'\n• Guarda en la carpeta correcta desde el principio\n• Si trabajas mucho tiempo, guarda frecuentemente\n\n❌ SI PIERDES TU ARCHIVO:\n• Revisa la carpeta 'Documentos'\n• Busca en el Escritorio\n• Usa la búsqueda de Windows (lupa en la barra de tareas)\n• Si no aparece, puede haberse perdido (la importancia de guardar)",
            'categoria' => 'Microsoft Office',
            'tags' => 'guardar,word,excel,documento,archivo'
        ],
        [
            'titulo' => 'Excel no abre o está dañado',
            'contenido' => "SOLUCIONES PARA PROBLEMAS CON EXCEL:\n\n🔍 PROBLEMA 1: 'EL ARCHIVO ESTÁ DAÑADO'\n\nSOLUCIÓN A: Reparar al abrir\n1. Abre Excel (el programa, no el archivo)\n2. Ve a Archivo → Abrir\n3. Busca tu archivo\n4. Haz clic en la flecha junto a 'Abrir'\n5. Selecciona 'Abrir y reparar'\n6. Elige 'Reparar'\n\nSOLUCIÓN B: Cambiar extensión\n1. Haz clic derecho en el archivo\n2. Selecciona 'Propiedades'\n3. Si el archivo se llama 'archivo.xls', intenta abrirlo como .xlsx\n\n🔍 PROBLEMA 2: EXCEL NO INICIA\n\n1. Cierra Excel completamente\n2. Abre el Administrador de tareas (CTRL + SHIFT + ESC)\n3. Busca 'Excel' en la lista\n4. Si aparece, haz clic derecho y selecciona 'Finalizar tarea'\n5. Intenta abrir Excel de nuevo\n6. Si no funciona, reinicia la computadora\n\n🔍 PROBLEMA 3: ARCHIVO EN MODO 'SOLO LECTURA'\n\n• Esto pasa cuando alguien más tiene el archivo abierto\n• O si el archivo está en una ubicación protegida\n• Solución: Haz clic en 'Habilitar edición' en la barra amarilla arriba\n• O copia el archivo a otra ubicación\n\n⚠️ PREVENCIÓN:\n• Siempre cierra los archivos cuando termines\n• Guarda copias de archivos importantes\n• No abras archivos directamente desde el correo (descárgalos primero)\n\n❌ SI NADA FUNCIONA: Crea un ticket (adjunta el archivo si es posible)",
            'categoria' => 'Microsoft Office',
            'tags' => 'excel,dañado,no abre,error,reparar'
        ],
        
        // CONTRASEÑAS Y SEGURIDAD
        [
            'titulo' => '¿Cómo crear una contraseña segura?',
            'contenido' => "GUÍA PARA CONTRASEÑAS SEGURAS:\n\n✅ UNA BUENA CONTRASEÑA DEBE TENER:\n• Al menos 8 caracteres (mejor si son 12+)\n• Letras mayúsculas y minúsculas\n• Números\n• Símbolos especiales (@, #, $, %, etc.)\n\n✅ EJEMPLOS DE CONTRASEÑAS BUENAS:\n• M1P3rr0*2025!\n• C@f3_M@ñ@n@99\n• Tr@b@jo#Segur0\n\n❌ NUNCA USES:\n• Tu nombre o apellido\n• Tu fecha de nacimiento\n• Palabras simples (password, 123456, qwerty)\n• Secuencias (abcd, 1234)\n• La misma contraseña para todo\n\n💡 MÉTODO FÁCIL:\n1. Piensa en una frase: 'Me gusta el café con leche'\n2. Toma las primeras letras: 'Mgeccl'\n3. Agrega números y símbolos: 'Mgeccl2025!'\n\n🔐 MEJORES PRÁCTICAS:\n• Cambia contraseñas cada 3-6 meses\n• NO las compartas con nadie (ni siquiera TI)\n• Usa contraseñas diferentes para cada cuenta\n• Anótalas en un lugar físico seguro (no en la computadora)\n• NO las envíes por correo o WhatsApp\n\n⚠️ SI SOSPECHAS QUE ALGUIEN SABE TU CONTRASEÑA:\n• Cámbiala INMEDIATAMENTE\n• Reporta el incidente a soporte\n\n💾 HERRAMIENTAS RECOMENDADAS:\n• Pregunta a TI si tienen un administrador de contraseñas aprobado",
            'categoria' => 'Seguridad',
            'tags' => 'contraseña,password,seguridad,crear'
        ],
        [
            'titulo' => '¿Qué hacer si recibo un correo sospechoso?',
            'contenido' => "CÓMO IDENTIFICAR Y MANEJAR CORREOS SOSPECHOSOS:\n\n🚨 SEÑALES DE ALERTA (Phishing):\n✓ El remitente es desconocido o extraño\n✓ Tiene faltas de ortografía o redacción mala\n✓ Te pide información personal (contraseñas, números de tarjeta)\n✓ Tiene un tono urgente o amenazante\n✓ Enlaces o archivos adjuntos sospechosos\n✓ Dice que ganaste algo que no pediste\n✓ Se hace pasar por tu banco o empresa (pero el correo no coincide)\n\n❌ NUNCA HAGAS:\n• Hacer clic en enlaces sospechosos\n• Descargar archivos adjuntos de remitentes desconocidos\n• Proporcionar contraseñas por correo\n• Responder a correos sospechosos\n\n✅ LO QUE SÍ DEBES HACER:\n\n1. NO ABRAS ARCHIVOS ADJUNTOS\n2. NO HAGAS CLIC EN ENLACES\n3. Marca el correo como SPAM/No deseado\n4. Borra el correo\n5. Si viene de alguien conocido, verifica con esa persona (llámala)\n6. Reporta a soporte si el correo parece peligroso\n\n💡 CÓMO VERIFICAR UN ENLACE (sin hacer clic):\n• Pasa el mouse sobre el enlace (sin hacer clic)\n• Mira la dirección que aparece abajo\n• ¿Es la página oficial? Ejemplo:\n  - Correcto: www.banco.com\n  - Sospechoso: www.banco-seguro.info.mx\n\n⚠️ EJEMPLOS COMUNES DE PHISHING:\n• 'Tu cuenta será bloqueada si no...'\n• 'Ganaste un premio, haz clic aquí'\n• 'Actualiza tus datos bancarios urgentemente'\n• 'Factura pendiente de pago' (de empresas que no conoces)\n\n🚨 SI ACCIDENTALMENTE HICISTE CLIC:\n1. NO ingreses ninguna información\n2. Cierra la página inmediatamente\n3. Cambia tus contraseñas\n4. Crea un ticket URGENTE en soporte\n5. Reporta lo sucedido\n\n📞 CUANDO DUDES: Es mejor preguntar que lamentar. Reenvía el correo a soporte.",
            'categoria' => 'Seguridad',
            'tags' => 'phishing,spam,correo,sospechoso,seguridad,virus'
        ],
        
        // ARCHIVOS Y CARPETAS
        [
            'titulo' => '¿Cómo buscar un archivo perdido en Windows?',
            'contenido' => "MÉTODOS PARA ENCONTRAR ARCHIVOS:\n\n🔍 MÉTODO 1: BÚSQUEDA RÁPIDA (MÁS USADO)\n1. Haz clic en la lupa 🔍 junto al botón de Inicio\n2. Escribe el nombre del archivo (o parte de él)\n3. Mira los resultados\n4. Haz clic en el archivo para abrirlo\n\n🔍 MÉTODO 2: EXPLORADOR DE ARCHIVOS\n1. Abre el Explorador (ícono de carpeta en la barra de tareas)\n2. Arriba a la derecha verás una casilla de búsqueda\n3. Escribe el nombre del archivo\n4. Presiona Enter\n5. Espera los resultados\n\n📁 LUGARES COMUNES DONDE BUSCAR:\n• Escritorio\n• Documentos\n• Descargas (archivos de internet)\n• OneDrive (si usas la nube)\n• Papelera de reciclaje (si lo borraste por error)\n\n💡 CONSEJOS PARA BUSCAR MEJOR:\n• Si no recuerdas el nombre completo, escribe parte del nombre\n• Busca por tipo de archivo: *.pdf, *.xlsx, *.docx\n• Ordena por fecha (archivos recientes primero)\n• Filtra por fecha de modificación\n\n🗓️ BUSCAR POR FECHA:\n1. En el Explorador de archivos\n2. Haz clic en la pestaña 'Búsqueda' arriba\n3. Selecciona 'Fecha de modificación'\n4. Elige un rango (Hoy, Esta semana, etc.)\n\n♻️ REVISAR LA PAPELERA:\n1. Haz doble clic en 'Papelera de reciclaje' en el Escritorio\n2. Busca tu archivo\n3. Haz clic derecho y selecciona 'Restaurar'\n\n⚠️ SI AÚN NO LO ENCUENTRAS:\n• Puede haberse borrado permanentemente\n• Puede estar en una red o servidor (pregunta a TI)\n• Crea un ticket para que soporte te ayude",
            'categoria' => 'Windows Básico',
            'tags' => 'buscar,archivo,perdido,encontrar,ubicar'
        ],
        [
            'titulo' => '¿Cómo hacer una captura de pantalla?',
            'contenido' => "MÉTODOS PARA CAPTURAR LA PANTALLA:\n\n⌨️ MÉTODO 1: TECLA IMPR PANT (Captura completa)\n1. Presiona la tecla 'Impr Pant' o 'PrtSc'\n2. Abre Paint o Word\n3. Presiona CTRL + V para pegar\n4. Guarda la imagen\n\n⌨️ MÉTODO 2: ALT + IMPR PANT (Solo ventana activa)\n1. Haz clic en la ventana que quieres capturar\n2. Presiona ALT + Impr Pant\n3. Pega en Paint o Word (CTRL + V)\n\n✂️ MÉTODO 3: RECORTE DE PANTALLA (Más control)\n1. Presiona Windows + SHIFT + S\n2. La pantalla se oscurecerá\n3. Arrastra para seleccionar el área que quieres capturar\n4. La captura se copia automáticamente\n5. Pega donde la necesites (CTRL + V)\n\n🎨 MÉTODO 4: HERRAMIENTA RECORTES\n1. Busca 'Herramienta Recortes' en el menú Inicio\n2. Haz clic en 'Nuevo'\n3. Selecciona el área\n4. Guarda la imagen\n\n💡 ¿PARA QUÉ SIRVEN LAS CAPTURAS?\n• Reportar errores al equipo de soporte\n• Guardar información importante\n• Compartir lo que ves en pantalla\n• Crear tutoriales o guías\n\n💾 GUARDAR LA CAPTURA:\n1. Abre Paint\n2. Pega la captura (CTRL + V)\n3. Ve a Archivo → Guardar como\n4. Elige 'PNG' o 'JPEG'\n5. Ponle un nombre descriptivo\n6. Guarda\n\n📧 ENVIAR POR CORREO:\n• Copia la captura\n• Abre tu correo\n• Pega directamente en el mensaje (CTRL + V)\n• O adjunta el archivo guardado\n\n⚠️ IMPORTANTE:\n• No captures información sensible (contraseñas, datos personales)\n• Revisa qué información se ve antes de compartir",
            'categoria' => 'Windows Básico',
            'tags' => 'captura,pantalla,screenshot,imprimir pantalla'
        ]
    ];
    
    // Verificar si ya existen artículos
    $stmt_check = $pdo->query("SELECT COUNT(*) FROM base_conocimientos");
    $count = $stmt_check->fetchColumn();
    
    if ($count > 0) {
        return 0; // Ya hay artículos, no instalar
    }
    
    // Insertar artículos base
    $contador = 0;
    $stmt = $pdo->prepare("
        INSERT INTO base_conocimientos 
        (titulo, contenido, categoria, tags, activo, vistas, util_si, util_no, fecha_creacion) 
        VALUES (?, ?, ?, ?, TRUE, 0, 0, 0, NOW())
    ");
    
    foreach ($articulos_base as $articulo) {
        $stmt->execute([
            $articulo['titulo'],
            $articulo['contenido'],
            $articulo['categoria'],
            $articulo['tags']
        ]);
        $contador++;
    }
    
    return $contador;
}

// Verificar e instalar artículos base si es necesario
$instalacion = instalar_articulos_base($pdo);
if ($instalacion > 0) {
    $_SESSION['mensaje'] = "✅ Se instalaron $instalacion artículos base en la base de conocimientos";
}

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
            flex-wrap: wrap;
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
            font-size: 1.05em;
            white-space: pre-line;
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
            flex-wrap: wrap;
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
            font-size: 1em;
        }
        
        .vote-yes {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            color: white;
        }
        
        .vote-yes:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.4);
        }
        
        .vote-no {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            color: white;
        }
        
        .vote-no:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(229, 62, 62, 0.4);
        }
        
        .breadcrumb {
            margin-bottom: 24px;
            color: #4a5568;
            font-size: 0.95em;
        }
        
        .breadcrumb a {
            color: #3182ce;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .category-filter {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        
        .category-tag {
            padding: 8px 16px;
            background: rgba(66, 153, 225, 0.1);
            color: #3182ce;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .category-tag:hover,
        .category-tag.active {
            background: #4299e1;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(66, 153, 225, 0.3);
        }
        
        .install-notice {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .cta-box {
            background: rgba(237, 137, 54, 0.1);
            padding: 24px;
            border-radius: 12px;
            margin-top: 32px;
            border-left: 4px solid #ed8936;
        }
        
        .cta-box h3 {
            color: #dd6b20;
            margin-bottom: 12px;
        }
        
        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 12px;
        }
        
        .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(237, 137, 54, 0.4);
        }
        
        .stats-box {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
            font-size: 0.95em;
            color: #4a5568;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .kb-article-meta, .vote-buttons, .category-filter {
                flex-direction: column;
            }
            
            .vote-btn {
                width: 100%;
                justify-content: center;
            }
        }

        body[data-page="base-conocimientos"] {
            background: none !important;
        }
        
        body[data-page="base-conocimientos"] .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            display: block;      /* por si en estilo.css está como flex */
            min-height: auto;
        }
        
        body[data-page="base-conocimientos"] .box {
            max-width: 100%;
            margin: 0 auto;
        }
        
        body[data-page="base-conocimientos"] .box::before {
            display: none;
        }
        
        body[data-page="base-conocimientos"] .kb-voting {
            background: rgba(66, 153, 225, 0.08);
            border-radius: 16px;
            border: 1px solid rgba(66, 153, 225, 0.25);
            overflow: hidden; /* evita que se vea corte raro en el centro */
        }
        
        body[data-page="base-conocimientos"] .kb-voting .vote-buttons {
            gap: 12px;
        }
        
        body[data-page="base-conocimientos"] .kb-voting .vote-btn {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
        }
        
    </style>
</head>
<body data-page="admin-tickets">
    <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Alternar modo oscuro"></button>
    
    <div class="container" style="max-width: 100%;">
        <div class="box" style="max-width: 95%;">
            
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="install-notice">
                    <span style="font-size: 1.5em;">✅</span>
                    <span><?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($articulo_detalle): ?>
                <!-- Vista de artículo individual -->
                <div class="breadcrumb">
                    <a href="base_conocimientos.php">📚 Base de Conocimientos</a> › 
                    <span><?php echo htmlspecialchars($articulo_detalle['categoria']); ?></span> › 
                    <strong><?php echo htmlspecialchars($articulo_detalle['titulo']); ?></strong>
                </div>
                
                <div class="kb-detail">
                    <h1><?php echo htmlspecialchars($articulo_detalle['titulo']); ?></h1>
                    
                    <div class="stats-box">
                        <span>📂 <?php echo htmlspecialchars($articulo_detalle['categoria']); ?></span>
                        <span>👁️ <?php echo $articulo_detalle['vistas']; ?> vistas</span>
                        <span>📅 <?php echo date('d/m/Y', strtotime($articulo_detalle['fecha_creacion'])); ?></span>
                        <?php 
                        $total_votos = $articulo_detalle['util_si'] + $articulo_detalle['util_no'];
                        if ($total_votos > 0):
                            $porcentaje = round(($articulo_detalle['util_si'] / $total_votos) * 100);
                        ?>
                        <span>📊 <?php echo $porcentaje; ?>% útil</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="kb-detail-content">
                        <?php echo htmlspecialchars($articulo_detalle['contenido']); ?>
                    </div>
                    
                    <?php if ($articulo_detalle['tags']): ?>
                    <div style="margin-top: 32px; padding-top: 24px; border-top: 2px solid rgba(0,0,0,0.1);">
                        <strong style="color: #2d3748;">🏷️ Etiquetas:</strong><br><br>
                        <?php foreach (explode(',', $articulo_detalle['tags']) as $tag): ?>
                            <span style="background: rgba(66, 153, 225, 0.15); padding: 6px 12px; border-radius: 16px; margin: 4px; font-size: 0.9em; display: inline-block;"><?php echo htmlspecialchars(trim($tag)); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="kb-voting">
                        <h3 style="color: #2d3748; margin-bottom: 8px;">¿Te resultó útil este artículo?</h3>
                        <p style="color: #4a5568;">Tu opinión nos ayuda a mejorar nuestro contenido</p>
                        
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="articulo_id" value="<?php echo $articulo_detalle['id']; ?>">
                            <div class="vote-buttons">
                                <button type="submit" name="voto" value="si" class="vote-btn vote-yes">
                                    <span>👍</span>
                                    Sí, me ayudó (<?php echo $articulo_detalle['util_si']; ?>)
                                </button>
                                <button type="submit" name="voto" value="no" class="vote-btn vote-no">
                                    <span>👎</span>
                                    No me ayudó (<?php echo $articulo_detalle['util_no']; ?>)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div style="margin-top: 24px; text-align: center;">
                    <a href="base_conocimientos.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                        <span>←</span>
                        Volver a la base de conocimientos
                    </a>
                </div>
                
            <?php else: ?>
                <!-- Vista de lista de artículos -->
                <h2 style="color: #2d3748; margin-bottom: 24px;">📚 Base de Conocimientos</h2>
                
                <div class="kb-search">
                    <form method="GET" style="background: none; padding: 0; box-shadow: none; border: none; margin: 0;">
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: end;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; color: #2d3748; font-weight: 600;">🔍 Buscar en la base de conocimientos:</label>
                                <input type="text" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Ej: reiniciar computadora, wifi, imprimir..." style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1em;">
                            </div>
                            <button type="submit" style="margin-bottom: 0; padding: 12px 24px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Buscar</button>
                        </div>
                        
                        <div class="category-filter">
                            <a href="base_conocimientos.php" class="category-tag <?php echo empty($categoria) ? 'active' : ''; ?>">
                                📋 Todas las categorías
                            </a>
                            <?php foreach ($categorias as $cat): ?>
                                <a href="?categoria=<?php echo urlencode($cat); ?><?php echo $busqueda ? '&buscar=' . urlencode($busqueda) : ''; ?>" 
                                   class="category-tag <?php echo $categoria === $cat ? 'active' : ''; ?>">
                                    <?php 
                                    $iconos = [
                                        'Windows Básico' => '💻',
                                        'Conexión a Internet' => '🌐',
                                        'Impresoras' => '🖨️',
                                        'Correo Electrónico' => '📧',
                                        'Microsoft Office' => '📄',
                                        'Seguridad' => '🔒'
                                    ];
                                    echo ($iconos[$cat] ?? '📁') . ' ' . htmlspecialchars($cat);
                                    ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>
                
                <?php if (empty($articulos)): ?>
                    <div style="background: rgba(66, 153, 225, 0.1); padding: 24px; border-radius: 12px; border-left: 4px solid #4299e1;">
                        <h3 style="color: #2c5282; margin-bottom: 12px;">🔍 No se encontraron artículos</h3>
                        <p style="color: #2d3748; margin-bottom: 16px;">
                            No encontramos artículos que coincidan con tu búsqueda.
                        </p>
                        <strong style="color: #2d3748;">Sugerencias:</strong>
                        <ul style="margin-top: 8px; color: #4a5568; line-height: 1.8;">
                            <li>Intenta con palabras clave diferentes</li>
                            <li>Revisa la ortografía</li>
                            <li>Usa términos más generales</li>
                            <li>Selecciona "Todas las categorías"</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div style="margin-bottom: 20px; color: #4a5568; font-size: 0.95em;">
                        📊 Se encontraron <strong style="color: #2d3748;"><?php echo count($articulos); ?></strong> artículo<?php echo count($articulos) != 1 ? 's' : ''; ?>
                        <?php if ($busqueda): ?>
                            para "<strong style="color: #2d3748;"><?php echo htmlspecialchars($busqueda); ?></strong>"
                        <?php endif; ?>
                        <?php if ($categoria): ?>
                            en la categoría "<strong style="color: #2d3748;"><?php echo htmlspecialchars($categoria); ?></strong>"
                        <?php endif; ?>
                    </div>
                    
                    <?php foreach ($articulos as $articulo): ?>
                        <div class="kb-article" onclick="location.href='base_conocimientos.php?id=<?php echo $articulo['id']; ?>'">
                            <h3><?php echo htmlspecialchars($articulo['titulo']); ?></h3>
                            <p style="color: #4a5568; line-height: 1.6;"><?php echo htmlspecialchars(substr(strip_tags($articulo['contenido']), 0, 200)) . '...'; ?></p>
                            
                            <div class="kb-article-meta">
                                <span>📂 <?php echo htmlspecialchars($articulo['categoria']); ?></span>
                                <span>👁️ <?php echo $articulo['vistas']; ?> vistas</span>
                                <span>👍 <?php echo $articulo['util_si']; ?> útiles</span>
                                <span>📅 <?php echo date('d/m/Y', strtotime($articulo['fecha_creacion'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="cta-box">
                    <h3>💡 ¿No encontraste lo que buscabas?</h3>
                    <p style="color: #2d3748; margin-bottom: 16px; line-height: 1.6;">
                        Si no pudiste resolver tu problema con estos artículos, no te preocupes. 
                        Nuestro equipo de soporte está aquí para ayudarte.
                    </p>
                    <a href="crear_ticket.php" class="cta-btn">
                        <span>🎫</span>
                        Crear Ticket de Soporte
                    </a>
                </div>
            <?php endif; ?>
            
            <br>
            <a href="dashboard.php" style="display: inline-flex; align-items: center; gap: 8px; color: #3182ce; text-decoration: none; font-weight: 600;">
                🏠 Volver al inicio
            </a>
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
