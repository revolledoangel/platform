<?php
// Archivo limpio para comenzar desde cero
require_once __DIR__ . '/fees.controller.php';

class MediaMixRealEstateDetails_Controller {
    private static function hasColumn($conn, $table, $column) {
        $tableEsc = $conn->real_escape_string($table);
        $columnEsc = $conn->real_escape_string($column);
        $sql = "SELECT COUNT(*) AS cnt
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = '$tableEsc'
                  AND COLUMN_NAME = '$columnEsc'";
        $res = $conn->query($sql);
        if ($res && ($row = $res->fetch_assoc())) {
            return intval($row['cnt']) > 0;
        }
        return false;
    }

    static public function ctrGetMediaMixById($mmreId) {
        $host = 'srv1013.hstgr.io';
        $port = 3306;
        $db   = 'u961992735_plataforma';
        $user = 'u961992735_plataforma';
        $pass = 'Peru+*963.';
        $conn = new mysqli($host, $user, $pass, $db, $port);
        if ($conn->connect_error) return false;
        $mmreId = intval($mmreId);
        // Mix general con código del cliente
        $mmre = null;
        $hasSnapshotColumn = self::hasColumn($conn, 'mediamixrealestates', 'currency_usd_per_unit_snapshot');
        $snapshotSelect = $hasSnapshotColumn
            ? 'm.currency_usd_per_unit_snapshot'
            : 'NULL AS currency_usd_per_unit_snapshot';

        $sql = "SELECT m.id, m.name, m.period_id, p.name AS period_name, m.client_id, c.name AS client_name, c.code AS client_code,
                   m.currency, $snapshotSelect, m.fee, m.fee_type, m.igv, m.nationalization_fee
                FROM mediamixrealestates m
                LEFT JOIN periods p ON m.period_id = p.id
                LEFT JOIN clients c ON m.client_id = c.id
                WHERE m.id = $mmreId";
        $res = $conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            // Asegurar que fee_type tenga un valor por defecto si es NULL
            if (!isset($row['fee_type']) || $row['fee_type'] === null) {
                $row['fee_type'] = 'percentage';
            }
            $mmre = $row;
        }
        // Detalles con códigos de proyecto
        $details = [];
        $sql = "SELECT d.*, p.name AS project_name, p.code AS project_code, p.group AS project_group, p.active AS project_active,
                   ch.name AS channel_name,
                       mt.code AS metric_code,
                       (SELECT COUNT(*) FROM metrics m3
                            WHERE m3.name = d.result_type
                               OR d.result_type LIKE CONCAT(m3.name, ' (%')
                        ) AS metric_is_valid
                FROM mediamixrealestate_details d
                LEFT JOIN projects p ON d.project_id = p.id
                LEFT JOIN channels ch ON d.channel_id = ch.id
                LEFT JOIN metrics mt ON mt.id = (
                    SELECT m2.id FROM metrics m2
                    WHERE m2.name = d.result_type
                       OR d.result_type LIKE CONCAT(m2.name, ' (%')
                       OR m2.name LIKE CONCAT(d.result_type, ' (%')
                       OR m2.name LIKE CONCAT(d.result_type, ' %')
                    ORDER BY
                        CASE
                            WHEN m2.name = d.result_type THEN 0
                            WHEN d.result_type LIKE CONCAT(m2.name, ' (%') THEN 1
                            WHEN m2.name LIKE CONCAT(d.result_type, ' (%') THEN 2
                            ELSE 3
                        END,
                        LENGTH(m2.name) ASC
                    LIMIT 1
                )
                WHERE d.mediamixrealestate_id = $mmreId";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $detail = $row;
                // Platform con código
                $platform = null;
                $sqlPlat = "SELECT f.platform_id, pl.name AS platform_name, pl.code AS platform_code, pl.active AS platform_active
                            FROM mmre_details_formats mf
                            LEFT JOIN formats f ON mf.format_id = f.id
                            LEFT JOIN platforms pl ON f.platform_id = pl.id
                            WHERE mf.mmre_detail_id = {$detail['id']} LIMIT 1";
                $resPlat = $conn->query($sqlPlat);
                if ($resPlat && $platRow = $resPlat->fetch_assoc()) {
                    $detail['platform_id'] = $platRow['platform_id'];
                    $detail['platform_name'] = $platRow['platform_name'];
                    $detail['platform_code'] = $platRow['platform_code'];
                    $detail['platform_active'] = $platRow['platform_active'];
                } else {
                    $detail['platform_id'] = null;
                    $detail['platform_name'] = null;
                    $detail['platform_code'] = null;
                    $detail['platform_active'] = null;
                }
                // Formats
                $sqlF = "SELECT f.id, f.name, f.code, f.active FROM mmre_details_formats mf LEFT JOIN formats f ON mf.format_id = f.id WHERE mf.mmre_detail_id = {$detail['id']}";
                $resF = $conn->query($sqlF);
                $formats_ids = [];
                $formats_names = [];
                $formats_codes = [];
                $formats_actives = [];
                while ($resF && $f = $resF->fetch_assoc()) {
                    $formats_ids[] = intval($f['id']);
                    $formats_names[] = $f['name'];
                    $formats_codes[] = $f['code'];
                    $formats_actives[] = intval($f['active']);
                }
                $detail['formats_ids'] = $formats_ids;
                $detail['formats_names'] = $formats_names;
                $detail['formats_codes'] = $formats_codes;
                $detail['formats_actives'] = $formats_actives;
                // Name mix
                $detail['name_mix'] = $mmre ? $mmre['name'] : null;
                // Currency
                $detail['currency'] = $mmre ? $mmre['currency'] : null;
                // Period name
                $detail['period_name'] = $mmre ? $mmre['period_name'] : null;
                // Client code (agregar esto)
                $detail['client_code'] = $mmre ? $mmre['client_code'] : null;
                $details[] = $detail;
            }
        }
        // Extra fees
        $extraFees = [];
        $conn->query("CREATE TABLE IF NOT EXISTS mediamixrealestate_extra_fees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mediamixrealestate_id INT NOT NULL,
            concept VARCHAR(150) NOT NULL,
            fee_type ENUM('fixed','percentage') NOT NULL DEFAULT 'fixed',
            fee_value DECIMAL(12,2) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_mmre_ef (mediamixrealestate_id)
        )");
        $efSql = "SELECT id, concept, fee_type, fee_value FROM mediamixrealestate_extra_fees WHERE mediamixrealestate_id = $mmreId AND is_active = 1 ORDER BY id ASC";
        $efRes = $conn->query($efSql);
        if ($efRes) { while ($row = $efRes->fetch_assoc()) $extraFees[] = $row; }
        // Migrar investment a DECIMAL si aún es INT
        $conn->query("ALTER TABLE mediamixrealestate_details MODIFY investment DECIMAL(12,2) NOT NULL DEFAULT 0.00");
        $mixFeeConfig = Fees_Controller::ctrGetMixFeeConfig($mmreId);
        $conn->close();
        return [
            'success' => true,
            'mmre' => $mmre,
            'details' => $details,
            'extra_fees' => $extraFees,
            'mix_fee_rules' => $mixFeeConfig['rules'],
            'mix_fee_charges' => $mixFeeConfig['charges'],
            'mix_meta' => $mixFeeConfig['mix_meta']
        ];
    }

    static public function ctrGetProjectsByClientId($clientId) {
        $url = 'https://algoritmo.digital/backend/public/api/clients/' . intval($clientId) . '/projects';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['success']) && $data['success'] && isset($data['projects'])) {
                return $data['projects'];
            }
        }
        return [];
    }

    static public function ctrGetObjectives() {
        $url = 'https://algoritmo.digital/backend/public/api/objectives';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    static public function ctrGetPlatforms() {
        $url = 'https://algoritmo.digital/backend/public/api/platforms';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    static public function ctrGetChannels() {
        $url = 'https://algoritmo.digital/backend/public/api/channels';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    static public function ctrGetFormatsByPlatformId($platformId) {
        $url = 'https://algoritmo.digital/backend/public/api/platforms/' . intval($platformId) . '/formats';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['success']) && $data['success'] && isset($data['formats'])) {
                return $data['formats'];
            }
        }
        return [];
    }

    static public function ctrGetCampaignTypes() {
        $url = 'https://algoritmo.digital/backend/public/api/campaign_types';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    static public function eliminarDetalle($id) {
        $host = 'srv1013.hstgr.io';
        $port = 3306;
        $db   = 'u961992735_plataforma';
        $user = 'u961992735_plataforma';
        $pass = 'Peru+*963.';
        $conn = new mysqli($host, $user, $pass, $db, $port);
        if ($conn->connect_error) return false;
        $id = intval($id);
        $sql = "DELETE FROM mediamixrealestate_details WHERE id = $id";
        $result = $conn->query($sql);
        $conn->close();
        return $result ? true : false;
    }

    public function ctrUpdateMediaMixConfig()
    {
        if (isset($_POST["configMediaMixId"])) {
            $mediaMixId = intval($_POST["configMediaMixId"]);
            
            // Conexión directa a la base de datos
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
                
                // Preparar los valores a actualizar
                $name = $conn->real_escape_string($_POST["configName"]);
                $currency = $conn->real_escape_string($_POST["configCurrency"]);
                $igv = floatval($_POST["configIgv"]);
                $nationalizationFee = floatval($_POST["configNationalizationFee"]);
                
                // Query de actualización
                $sql = "UPDATE mediamixrealestates 
                        SET name = '$name',
                            currency = '$currency',
                            igv = $igv,
                            nationalization_fee = $nationalizationFee,
                            updated_at = NOW()
                        WHERE id = $mediaMixId";
                
                if ($conn->query($sql)) {
                    if (Fees_Controller::ctrIsAdminSession() && isset($_POST['configRulesJson']) && isset($_POST['configChargesJson'])) {
                        $rules = json_decode($_POST['configRulesJson'], true);
                        $charges = json_decode($_POST['configChargesJson'], true);
                        Fees_Controller::ctrSaveMixFeeConfig($mediaMixId, $rules, $charges);
                    }

                    echo '<script>
                        swal({
                            type: "success",
                            title: "Configuración actualizada",
                            text: "Los cambios se aplicaron correctamente."
                        }).then(() => { 
                            window.location = "mediaMixRealEstateDetails?mediaMixId=' . $mediaMixId . '";
                        });
                    </script>';
                } else {
                    throw new Exception("Error updating record: " . $conn->error);
                }
                
                $conn->close();
                
            } catch (Exception $e) {
                error_log("Error en ctrUpdateMediaMixConfig: " . $e->getMessage());
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al actualizar",
                        text: "No se pudieron guardar los cambios. Error: ' . $e->getMessage() . '"
                    });
                </script>';
            }
        }
    }

    private static function getExtraFeesDbConn() {
        $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
        if (!$conn->connect_error) {
            $conn->query("CREATE TABLE IF NOT EXISTS mediamixrealestate_extra_fees (
                id INT AUTO_INCREMENT PRIMARY KEY,
                mediamixrealestate_id INT NOT NULL,
                concept VARCHAR(150) NOT NULL,
                fee_type ENUM('fixed','percentage') NOT NULL DEFAULT 'fixed',
                fee_value DECIMAL(12,2) NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_mmre_ef (mediamixrealestate_id)
            )");
        }
        return $conn;
    }

    static public function ctrGetExtraFees($mmreId) {
        $conn = self::getExtraFeesDbConn();
        if ($conn->connect_error) return [];
        $mmreId = intval($mmreId);
        $res = $conn->query("SELECT id, concept, fee_type, fee_value FROM mediamixrealestate_extra_fees WHERE mediamixrealestate_id = $mmreId AND is_active = 1 ORDER BY id ASC");
        $fees = [];
        if ($res) { while ($row = $res->fetch_assoc()) $fees[] = $row; }
        $conn->close();
        return $fees;
    }

    static public function ctrSaveExtraFee($mmreId, $concept, $feeType, $feeValue) {
        $conn = self::getExtraFeesDbConn();
        if ($conn->connect_error) return false;
        $mmreId = intval($mmreId);
        $concept = $conn->real_escape_string(mb_substr(trim($concept), 0, 150));
        $feeType = in_array($feeType, ['fixed', 'percentage']) ? $feeType : 'fixed';
        $feeValue = floatval($feeValue);
        $res = $conn->query("INSERT INTO mediamixrealestate_extra_fees (mediamixrealestate_id, concept, fee_type, fee_value) VALUES ($mmreId, '$concept', '$feeType', $feeValue)");
        $id = $res ? $conn->insert_id : null;
        $conn->close();
        return $id ? ['success' => true, 'id' => $id] : false;
    }

    static public function ctrDeleteExtraFee($id) {
        $conn = self::getExtraFeesDbConn();
        if ($conn->connect_error) return false;
        $id = intval($id);
        $res = $conn->query("UPDATE mediamixrealestate_extra_fees SET is_active = 0 WHERE id = $id");
        $conn->close();
        return (bool)$res;
    }

    static public function ctrCreateDetail($data) {
        $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
        if ($conn->connect_error) return ['success' => false, 'message' => 'DB connection failed'];
        $mmreId        = intval($data['mediamixrealestate_id'] ?? 0);
        $projectId     = intval($data['project_id'] ?? 0);
        $channelId     = intval($data['channel_id'] ?? 0);
        $aon           = intval($data['aon'] ?? 0);
        $segmentation  = $conn->real_escape_string(mb_substr(trim($data['segmentation'] ?? ''), 0, 255));
        $investment    = round(floatval($data['investment'] ?? 0), 2);
        $projection    = intval($data['projection'] ?? 0);
        $comments      = $conn->real_escape_string(mb_substr(trim($data['comments'] ?? ''), 0, 65535));
        $resultType    = $conn->real_escape_string(mb_substr(trim($data['result_type'] ?? ''), 0, 255));
        $state         = $conn->real_escape_string(mb_substr(trim($data['state'] ?? 'Activa'), 0, 255));
        $campaignName  = $conn->real_escape_string(mb_substr(trim($data['campaign_name'] ?? ''), 0, 100));
        $formatsIds    = array_map('intval', (array)($data['formats_ids'] ?? []));
        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO mediamixrealestate_details
                    (mediamixrealestate_id, project_id, channel_id, aon, segmentation, investment, projection, comments, result_type, state, campaign_name, created_at, updated_at)
                VALUES
                    ($mmreId, $projectId, $channelId, $aon, '$segmentation', $investment, $projection, '$comments', '$resultType', '$state', '$campaignName', '$now', '$now')";
        if (!$conn->query($sql)) {
            $err = $conn->error; $conn->close();
            return ['success' => false, 'message' => $err];
        }
        $newId = $conn->insert_id;
        foreach ($formatsIds as $fid) {
            if ($fid > 0) $conn->query("INSERT IGNORE INTO mmre_details_formats (mmre_detail_id, format_id, created_at, updated_at) VALUES ($newId, $fid, '$now', '$now')");
        }
        $conn->close();
        return ['success' => true, 'id' => $newId];
    }

    static public function ctrUpdateDetail($detailId, $data) {
        $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
        if ($conn->connect_error) return ['success' => false, 'message' => 'DB connection failed'];
        $detailId      = intval($detailId);
        $projectId     = intval($data['project_id'] ?? 0);
        $channelId     = intval($data['channel_id'] ?? 0);
        $aon           = intval($data['aon'] ?? 0);
        $segmentation  = $conn->real_escape_string(mb_substr(trim($data['segmentation'] ?? ''), 0, 255));
        $investment    = round(floatval($data['investment'] ?? 0), 2);
        $projection    = intval($data['projection'] ?? 0);
        $comments      = $conn->real_escape_string(mb_substr(trim($data['comments'] ?? ''), 0, 65535));
        $resultType    = $conn->real_escape_string(mb_substr(trim($data['result_type'] ?? ''), 0, 255));
        $state         = $conn->real_escape_string(mb_substr(trim($data['state'] ?? 'Activa'), 0, 255));
        $campaignName  = $conn->real_escape_string(mb_substr(trim($data['campaign_name'] ?? ''), 0, 100));
        $formatsIds    = array_map('intval', (array)($data['formats_ids'] ?? []));
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE mediamixrealestate_details SET
                    project_id = $projectId, channel_id = $channelId,
                    aon = $aon, segmentation = '$segmentation', investment = $investment,
                    projection = $projection, comments = '$comments', result_type = '$resultType',
                    state = '$state', campaign_name = '$campaignName', updated_at = '$now'
                WHERE id = $detailId";
        if (!$conn->query($sql)) {
            $err = $conn->error; $conn->close();
            return ['success' => false, 'message' => $err];
        }
        $conn->query("DELETE FROM mmre_details_formats WHERE mmre_detail_id = $detailId");
        foreach ($formatsIds as $fid) {
            if ($fid > 0) $conn->query("INSERT IGNORE INTO mmre_details_formats (mmre_detail_id, format_id, created_at, updated_at) VALUES ($detailId, $fid, '$now', '$now')");
        }
        $conn->close();
        return ['success' => true, 'id' => $detailId];
    }
}