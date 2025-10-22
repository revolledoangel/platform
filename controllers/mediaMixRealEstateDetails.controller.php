<?php
// Archivo limpio para comenzar desde cero

class MediaMixRealEstateDetails_Controller {
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
        $sql = "SELECT m.id, m.name, m.period_id, p.name AS period_name, m.client_id, c.name AS client_name, c.code AS client_code, m.currency, m.fee, m.fee_type, m.igv
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
                       ch.name AS channel_name, ct.name AS campaign_type_name
                FROM mediamixrealestate_details d
                LEFT JOIN projects p ON d.project_id = p.id
                LEFT JOIN channels ch ON d.channel_id = ch.id
                LEFT JOIN campaign_types ct ON d.campaign_type_id = ct.id
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
                // Objectives
                $sqlO = "SELECT o.id, o.name, o.code FROM mmre_details_objectives mo LEFT JOIN objectives o ON mo.objective_id = o.id WHERE mo.mmre_detail_id = {$detail['id']}";
                $resO = $conn->query($sqlO);
                $objectives_ids = [];
                $objectives_names = [];
                $objectives_codes = [];
                while ($resO && $o = $resO->fetch_assoc()) {
                    $objectives_ids[] = intval($o['id']);
                    $objectives_names[] = $o['name'];
                    $objectives_codes[] = $o['code'];
                }
                $detail['objectives_ids'] = $objectives_ids;
                $detail['objectives_names'] = $objectives_names;
                $detail['objectives_codes'] = $objectives_codes;
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
        $conn->close();
        return [
            'success' => true,
            'mmre' => $mmre,
            'details' => $details
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
            $mediaMixId = $_POST["configMediaMixId"];
            
            // Agregar logs detallados al inicio
            error_log('================== INICIO UPDATE CONFIG ==================');
            error_log('POST data completa: ' . print_r($_POST, true));
            error_log('Fee Type (desde POST): ' . $_POST["configFeeType"]);
            error_log('Fee Type Hidden (desde POST): ' . $_POST["configFeeTypeHidden"]);
            error_log('Fee Value (desde POST): ' . $_POST["configFee"]);
            
            $url = 'https://algoritmo.digital/backend/public/api/mmres/' . $mediaMixId;

            $body = [
                "name" => $_POST["configName"],
                "currency" => $_POST["configCurrency"],
                "fee" => $_POST["configFee"],
                "fee_type" => $_POST["configFeeType"],
                "igv" => $_POST["configIgv"],
            ];

            // Log del body antes de enviarlo
            error_log('Body a enviar a la API: ' . print_r($body, true));
            
            $jsonData = json_encode($body);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Log de la respuesta
            error_log('Código de respuesta HTTP: ' . $httpCode);
            error_log('Respuesta de la API: ' . $response);
            error_log('================== FIN UPDATE CONFIG ==================');
            
            curl_close($ch);

            if ($httpCode === 200) {
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
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al actualizar",
                        text: "No se pudieron guardar los cambios."
                    });
                </script>';
            }
        }
    }
}