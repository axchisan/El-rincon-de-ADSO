<?php
session_start();
require_once "../../database/conexionDB.php";

// Verificar si se proporcionó un ID de documento y tipo
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['tipo']) || empty($_GET['tipo'])) {
    header("Location: ../repositorio/repositorio.php");
    exit();
}

$documento_id = intval($_GET['id']);
$tipo = $_GET['tipo'];
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;

try {
    $db = conexionDB::getConexion();
    
    // Obtener información del documento
    $query = "
        SELECT d.*, u.nombre_usuario AS autor_nombre
        FROM documentos d
        JOIN usuarios u ON d.autor_id = u.id
        WHERE d.id = :documento_id
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$documento) {
        header("Location: ../repositorio/repositorio.php");
        exit();
    }
    
    // Verificar permisos de acceso
    if ($documento['visibilidad'] === 'Private' && $documento['autor_id'] != $usuario_id) {
        header("Location: ../repositorio/repositorio.php");
        exit();
    }
    
    if ($documento['visibilidad'] === 'Group' && $usuario_id) {
        $query = "SELECT COUNT(*) FROM usuario_grupo WHERE usuario_id = :usuario_id AND grupo_id = :grupo_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id, ':grupo_id' => $documento['grupo_id']]);
        $es_miembro = $stmt->fetchColumn();
        
        if (!$es_miembro) {
            header("Location: ../repositorio/repositorio.php");
            exit();
        }
    }
    
    // Registrar vista
    if ($usuario_id) {
        $query = "
            INSERT INTO recientemente_vistos (usuario_id, documento_id, fecha_vista)
            VALUES (:usuario_id, :documento_id, NOW())
            ON CONFLICT (usuario_id, documento_id) DO UPDATE
            SET fecha_vista = NOW()
        ";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id, ':documento_id' => $documento_id]);
    }
    
} catch (PDOException $e) {
    die("Error al cargar el documento: " . $e->getMessage());
}

// Determinar la página de retorno según el tipo
$return_page = "ver_documento.php";
if ($tipo === 'libro') {
    $return_page = "ver_libro.php";
} elseif ($tipo === 'video') {
    $return_page = "ver_video.php";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($documento['titulo']); ?> - El Rincón de ADSO</title>
    <link rel="icon" type="image/png" href="../inicio/img/icono.png">
    <link rel="stylesheet" href="../repositorio/css/repositorio.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }
        
        .file-viewer {
            position: relative;
            width: 100%;
            height: 100vh;
            background-color: #1a1a1a;
        }
        
        .file-viewer__toolbar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            transition: opacity 0.3s;
        }
        
        .file-viewer:hover .file-viewer__toolbar {
            opacity: 1;
        }
        
        .file-viewer__title {
            font-size: 1.1rem;
            font-weight: 500;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 60%;
        }
        
        .file-viewer__actions {
            display: flex;
            gap: 1rem;
        }
        
        .file-viewer__btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .file-viewer__btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .file-viewer__content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .file-viewer__iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .file-viewer__image {
            max-width: 95%;
            max-height: 95%;
            object-fit: contain;
        }
        
        @media (max-width: 768px) {
            .file-viewer__title {
                max-width: 40%;
            }
        }
    </style>
</head>
<body>
    <div class="file-viewer">
        <div class="file-viewer__toolbar">
            <h1 class="file-viewer__title"><?php echo htmlspecialchars($documento['titulo']); ?></h1>
            <div class="file-viewer__actions">
                <?php if (!empty($documento['url_archivo']) && pathinfo($documento['url_archivo'], PATHINFO_EXTENSION) === 'pdf'): ?>
                <a href="<?php echo htmlspecialchars($documento['url_archivo']); ?>" class="file-viewer__btn" download>
                    <i class="fas fa-download"></i> Descargar
                </a>
                <?php endif; ?>
                <a href="<?php echo $return_page; ?>?id=<?php echo $documento_id; ?>" class="file-viewer__btn">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        
        <div class="file-viewer__content">
            <?php 
            $extension = pathinfo($documento['url_archivo'], PATHINFO_EXTENSION);
            $isPdf = $extension === 'pdf';
            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
            
            if ($isPdf): 
            ?>
                <iframe src="<?php echo htmlspecialchars($documento['url_archivo']); ?>" class="file-viewer__iframe" allowfullscreen></iframe>
            <?php elseif ($isImage): ?>
                <img src="<?php echo htmlspecialchars($documento['url_archivo']); ?>" alt="<?php echo htmlspecialchars($documento['titulo']); ?>" class="file-viewer__image">
            <?php else: ?>
                <div style="color: white; text-align: center; padding: 2rem;">
                    <i class="fas fa-exclamation-circle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>Este tipo de archivo no se puede previsualizar directamente.</p>
                    <a href="<?php echo htmlspecialchars($documento['url_archivo']); ?>" class="file-viewer__btn" download style="display: inline-flex; margin-top: 1rem;">
                        <i class="fas fa-download"></i> Descargar Archivo
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>