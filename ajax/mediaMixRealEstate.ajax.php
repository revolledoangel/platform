<?php
require_once "../controllers/mediaMixRealEstate.controller.php";

class AjaxMediaMixRealEstate
{
    public $mediaMixId;

    public function ajaxEditMediaMixRealEstate()
    {
        $url = "https://algoritmo.digital/backend/public/api/mmres/" . $this->mediaMixId;
        $response = file_get_contents($url);
        echo $response;
    }

    public function ajaxListMediaMixRealEstate()
    {
        $records = MediaMixRealEstate_Controller::ctrShowMediaMixRealEstate();
        if (empty($records)) {
            echo json_encode(["data" => []]);
            return;
        }
        
        $data = [];
        foreach ($records as $key => $record) {
            $acciones = '<div class="btn-group">
                <a href="mediaMixRealEstateDetails?mediaMixId=' . $record["id"] . '" class="btn btn-info">
                    <i class="fa fa-eye"></i>
                </a>
                <button type="button" class="btn btn-warning btn-editMediaMix" mediaMixId="' . $record["id"] . '" data-toggle="modal" data-target="#editMediaMixRealEstateModal">
                    <i class="fa fa-pencil"></i>
                </button>
                <button type="button" class="btn btn-success btn-cloneMediaMix" mediaMixId="' . $record["id"] . '">
                    <i class="fa fa-clone"></i>
                </button>
                <button type="button" class="btn btn-danger btn-deleteMediaMix" mediaMixId="' . $record["id"] . '">
                    <i class="fa fa-trash"></i>
                </button>
            </div>';

            // Formatear fee según su tipo
            $feeDisplay = '';
            if (isset($record["fee_type"]) && $record["fee_type"] === 'fixed') {
                $feeDisplay = htmlspecialchars($record["currency"]) . ' ' . number_format(floatval($record["fee"]), 2);
            } else {
                $feeDisplay = htmlspecialchars($record["fee"]) . '%';
            }

            $data[] = [
                ($key + 1),
                htmlspecialchars($record["name"]),
                htmlspecialchars($record["client_id"]),
                htmlspecialchars($record["period_id"]),
                htmlspecialchars($record["currency"]),
                $feeDisplay,
                htmlspecialchars($record["igv"]),
                $acciones
            ];
        }
        echo json_encode(["data" => $data]);
    }

    public function ajaxCloneMediaMixRealEstate()
    {
        $url = "https://algoritmo.digital/backend/public/api/mmres/" . $this->mediaMixId . "/clone";
        $body = ['period_id' => $_POST['period_id']];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        echo $response;
    }

    // Método para obtener períodos
    public function ajaxGetPeriods()
    {
        $host = 'srv1013.hstgr.io';
        $port = 3306;
        $db   = 'u961992735_plataforma';
        $user = 'u961992735_plataforma';
        $pass = 'Peru+*963.';
        
        try {
            $conn = new mysqli($host, $user, $pass, $db, $port);
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            $sql = "SELECT id, name FROM periods ORDER BY year DESC, month_number DESC";
            $result = $conn->query($sql);
            
            $periods = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $periods[] = $row;
                }
            }
            
            $conn->close();
            echo json_encode($periods);
            
        } catch (Exception $e) {
            echo json_encode([]);
        }
    }

    // Método para obtener períodos disponibles para un cliente
    public function ajaxGetAvailablePeriods()
    {
        $host = 'srv1013.hstgr.io';
        $port = 3306;
        $db   = 'u961992735_plataforma';
        $user = 'u961992735_plataforma';
        $pass = 'Peru+*963.';
        
        try {
            $conn = new mysqli($host, $user, $pass, $db, $port);
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            $client_id = intval($_POST['client_id']);
            
            // Obtener períodos que NO tienen un mix de medios asignado a este cliente
            $sql = "SELECT p.id, p.name 
                    FROM periods p
                    WHERE p.id NOT IN (
                        SELECT period_id 
                        FROM mediamixrealestates 
                        WHERE client_id = $client_id
                    )
                    ORDER BY p.year DESC, p.month_number DESC";
            
            $result = $conn->query($sql);
            
            $periods = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $periods[] = $row;
                }
            }
            
            $conn->close();
            echo json_encode($periods);
            
        } catch (Exception $e) {
            echo json_encode([]);
        }
    }

    // Método para clonar un Mix de Medios completo
    public function ajaxCloneMediaMix()
    {
        $host = 'srv1013.hstgr.io';
        $port = 3306;
        $db   = 'u961992735_plataforma';
        $user = 'u961992735_plataforma';
        $pass = 'Peru+*963.';
        
        try {
            $conn = new mysqli($host, $user, $pass, $db, $port);
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            $conn->begin_transaction();
            
            // Obtener datos del POST
            $source_mix_id = intval($_POST['mix_id']);
            $new_period_id = intval($_POST['period_id']);
            $only_aon = intval($_POST['only_aon']);
            $new_name = trim($_POST['new_name'] ?? '');
            
            // 1. Obtener datos del Mix original
            $sqlOriginal = "SELECT * FROM mediamixrealestates WHERE id = $source_mix_id";
            $resultOriginal = $conn->query($sqlOriginal);
            
            if (!$resultOriginal || $resultOriginal->num_rows === 0) {
                throw new Exception("Mix de Medios original no encontrado");
            }
            
            $originalMix = $resultOriginal->fetch_assoc();
            
            // 2. Generar nombre automático si está vacío
            if (empty($new_name)) {
                // Obtener nombre del cliente
                $sqlClient = "SELECT name FROM clients WHERE id = {$originalMix['client_id']}";
                $resultClient = $conn->query($sqlClient);
                $clientName = ($resultClient && $resultClient->num_rows > 0) ? $resultClient->fetch_assoc()['name'] : 'Cliente';
                
                // Obtener nombre del período
                $sqlPeriod = "SELECT name FROM periods WHERE id = $new_period_id";
                $resultPeriod = $conn->query($sqlPeriod);
                $periodName = ($resultPeriod && $resultPeriod->num_rows > 0) ? $resultPeriod->fetch_assoc()['name'] : 'Período';
                
                $new_name = $clientName . ' - ' . $periodName;
            }
            
            $new_name = $conn->real_escape_string($new_name);
            
            // 3. Insertar nuevo Mix de Medios
            $sqlInsertMix = "INSERT INTO mediamixrealestates 
                            (name, period_id, client_id, currency, fee, fee_type, igv, created_at, updated_at) 
                            VALUES 
                            ('$new_name', $new_period_id, {$originalMix['client_id']}, '{$originalMix['currency']}', 
                             {$originalMix['fee']}, '{$originalMix['fee_type']}', {$originalMix['igv']}, NOW(), NOW())";
            
            if (!$conn->query($sqlInsertMix)) {
                throw new Exception("Error al crear el nuevo Mix: " . $conn->error);
            }
            
            $new_mix_id = $conn->insert_id;
            
            // 4. Copiar detalles (con o sin filtro AON)
            $aonCondition = $only_aon ? "AND aon = 1" : "";
            $sqlDetails = "SELECT * FROM mediamixrealestate_details 
                          WHERE mediamixrealestate_id = $source_mix_id $aonCondition";
            $resultDetails = $conn->query($sqlDetails);
            
            $copiedDetails = 0;
            
            if ($resultDetails && $resultDetails->num_rows > 0) {
                while ($detail = $resultDetails->fetch_assoc()) {
                    $old_detail_id = $detail['id'];
                    
                    // Insertar detalle
                    $sqlInsertDetail = "INSERT INTO mediamixrealestate_details 
                                       (mediamixrealestate_id, project_id, channel_id, campaign_type_id, 
                                        segmentation, result_type, projection, investment, aon, comments, state, created_at, updated_at) 
                                       VALUES 
                                       ($new_mix_id, {$detail['project_id']}, {$detail['channel_id']}, {$detail['campaign_type_id']}, 
                                        '{$conn->real_escape_string($detail['segmentation'])}', '{$conn->real_escape_string($detail['result_type'])}', 
                                        {$detail['projection']}, {$detail['investment']}, {$detail['aon']}, 
                                        " . ($detail['comments'] ? "'{$conn->real_escape_string($detail['comments'])}'" : "NULL") . ", 
                                        '{$detail['state']}', NOW(), NOW())";
                    
                    if (!$conn->query($sqlInsertDetail)) {
                        throw new Exception("Error al copiar detalle: " . $conn->error);
                    }
                    
                    $new_detail_id = $conn->insert_id;
                    $copiedDetails++;
                    
                    // 5. Copiar relaciones de formatos
                    $sqlFormats = "SELECT format_id FROM mmre_details_formats WHERE mmre_detail_id = $old_detail_id";
                    $resultFormats = $conn->query($sqlFormats);
                    
                    if ($resultFormats && $resultFormats->num_rows > 0) {
                        while ($format = $resultFormats->fetch_assoc()) {
                            $sqlInsertFormat = "INSERT INTO mmre_details_formats (mmre_detail_id, format_id) 
                                               VALUES ($new_detail_id, {$format['format_id']})";
                            $conn->query($sqlInsertFormat);
                        }
                    }
                    
                    // 6. Copiar relaciones de objetivos
                    $sqlObjectives = "SELECT objective_id FROM mmre_details_objectives WHERE mmre_detail_id = $old_detail_id";
                    $resultObjectives = $conn->query($sqlObjectives);
                    
                    if ($resultObjectives && $resultObjectives->num_rows > 0) {
                        while ($objective = $resultObjectives->fetch_assoc()) {
                            $sqlInsertObjective = "INSERT INTO mmre_details_objectives (mmre_detail_id, objective_id) 
                                                  VALUES ($new_detail_id, {$objective['objective_id']})";
                            $conn->query($sqlInsertObjective);
                        }
                    }
                }
            }
            
            $conn->commit();
            $conn->close();
            
            echo json_encode([
                'success' => true,
                'message' => "Mix clonado exitosamente. Se copiaron $copiedDetails campañas.",
                'new_mix_id' => $new_mix_id,
                'new_mix_name' => $new_name
            ]);
            
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollback();
                $conn->close();
            }
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}

// Acción: listar
if (isset($_GET["action"]) && $_GET["action"] === "list") {
    $listar = new AjaxMediaMixRealEstate();
    $listar->ajaxListMediaMixRealEstate();
    return;
}

// Acción: editar
if (isset($_GET["mediaMixId"])) {
    $editar = new AjaxMediaMixRealEstate();
    $editar->mediaMixId = $_GET["mediaMixId"];
    $editar->ajaxEditMediaMixRealEstate();
}

// Acción: clonar
if (isset($_POST["cloneMediaMixId"]) && isset($_POST["period_id"])) {
    $clonar = new AjaxMediaMixRealEstate();
    $clonar->mediaMixId = $_POST["cloneMediaMixId"];
    $clonar->ajaxCloneMediaMixRealEstate();
}

// Acción: obtener períodos
if (isset($_POST["get_periods"])) {
    $periodos = new AjaxMediaMixRealEstate();
    $periodos->ajaxGetPeriods();
    return;
}

// Acción: obtener períodos disponibles para clonar
if (isset($_POST["action"]) && $_POST["action"] === "getAvailablePeriods" && isset($_POST["client_id"])) {
    $periodos = new AjaxMediaMixRealEstate();
    $periodos->ajaxGetAvailablePeriods();
    return;
}

// Acción: clonar mix de medios (NUEVO)
if (isset($_POST["action"]) && $_POST["action"] === "cloneMix") {
    $clone = new AjaxMediaMixRealEstate();
    $clone->ajaxCloneMediaMix();
    return;
}