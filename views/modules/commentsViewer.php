<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentarios - Algoritmo</title>
    <link rel="stylesheet" href="views/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="views/bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="icon" href="views/img/template/algoritmo-icon.png">
    <style>
        body {
            font-family: 'Source Sans Pro', Arial, sans-serif;
            background-color: #f4f6f9;
            padding: 20px;
        }
        .viewer-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .viewer-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #00013b;
        }
        .viewer-header h1 {
            color: #00013b;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .viewer-header p {
            color: #666;
            font-size: 16px;
        }
        .comment-card {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #fafafa;
        }
        .comment-platform {
            font-weight: bold;
            color: #00013b;
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .comment-section {
            margin-bottom: 20px;
        }
        .comment-section h4 {
            color: #444;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .comment-content {
            color: #555;
            line-height: 1.6;
            padding: 10px;
            background-color: white;
            border-radius: 4px;
        }
        .no-comments {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="viewer-container">
        <?php
        // Obtener parámetros de la URL
        $hashedCode = isset($_GET['code']) ? $_GET['code'] : null;
        $periodId = isset($_GET['period']) ? intval($_GET['period']) : null;

        if (!$hashedCode || !$periodId) {
            echo '<div class="error-message">
                    <strong>Error:</strong> Parámetros incorrectos. Se requiere code y period.
                  </div>';
            exit;
        }

        // Obtener comentarios desde la API
        $apiUrl = 'https://algoritmo.digital/backend/public/api/comments';
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            echo '<div class="error-message">
                    <strong>Error:</strong> No se pudieron cargar los comentarios.
                  </div>';
            exit;
        }

        $allComments = json_decode($response, true);

        // Filtrar comentarios por hashed_code y period_id
        $filteredComments = array_filter($allComments, function($comment) use ($hashedCode, $periodId) {
            return isset($comment['hashed_code']) && 
                   $comment['hashed_code'] === $hashedCode && 
                   $comment['period_id'] === $periodId;
        });

        if (empty($filteredComments)) {
            echo '<div class="no-comments">
                    <i class="fa fa-info-circle" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p>No se encontraron comentarios para este cliente en el periodo seleccionado.</p>
                  </div>';
            exit;
        }

        // Obtener información del primer comentario para el header
        $firstComment = reset($filteredComments);
        $clientName = htmlspecialchars($firstComment['client_name']);
        $periodName = htmlspecialchars($firstComment['period_name']);
        ?>

        <div class="viewer-header">
            <h1><?php echo $clientName; ?></h1>
            <p>Periodo: <?php echo $periodName; ?></p>
        </div>

        <?php foreach ($filteredComments as $comment): ?>
            <div class="comment-card">
                <div class="comment-platform">
                    <i class="fa fa-tv"></i> <?php echo htmlspecialchars($comment['platform_name']); ?>
                </div>

                <?php if (!empty($comment['recommendation'])): ?>
                    <div class="comment-section">
                        <h4><i class="fa fa-lightbulb-o"></i> Hallazgos - Recomendaciones</h4>
                        <div class="comment-content">
                            <?php echo $comment['recommendation']; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($comment['conclusion'])): ?>
                    <div class="comment-section">
                        <h4><i class="fa fa-check-circle"></i> Comentario - Conclusiones</h4>
                        <div class="comment-content">
                            <?php echo $comment['conclusion']; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="views/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="views/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>
