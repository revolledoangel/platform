<?php

class LookerMenu_Controller
{
    private static function getConn()
    {
        $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'DB connection failed']));
        }
        $conn->set_charset('utf8mb4');
        return $conn;
    }

    /**
     * Retorna todos los usuarios con perfil "Ejecutivo".
     */
    public static function ctrGetExecutives()
    {
        $conn = self::getConn();
        $result = $conn->query("SELECT id, name FROM users WHERE profile = 'Ejecutivo' AND active = 1 ORDER BY name ASC");
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $conn->close();
        return $rows;
    }

    /**
     * Retorna todos los clientes activos con looker_url asignada a un ejecutivo,
     * junto con los datos de asignación.
     * Si $userId = null, retorna todos los que tengan asignación.
     */
    public static function ctrGetAssignedClients($userId = null)
    {
        $conn = self::getConn();
        if ($userId) {
            $uid = (int)$userId;
            $sql = "SELECT c.id, c.name, c.looker_url FROM looker_assignments la
                    INNER JOIN clients c ON c.id = la.client_id
                    WHERE la.user_id = $uid AND c.looker_url IS NOT NULL AND c.looker_url <> ''
                    ORDER BY c.name ASC";
        } else {
            $sql = "SELECT c.id, c.name, c.looker_url, la.user_id FROM looker_assignments la
                    INNER JOIN clients c ON c.id = la.client_id
                    ORDER BY c.name ASC";
        }
        $result = $conn->query($sql);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $conn->close();
        return $rows;
    }

    /**
     * Retorna todos los clientes activos (para el selector del admin).
     */
    public static function ctrGetAllClients()
    {
        $conn = self::getConn();
        $result = $conn->query("SELECT id, name, looker_url FROM clients WHERE active = 1 ORDER BY name ASC");
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $conn->close();
        return $rows;
    }

    /**
     * Retorna las asignaciones actuales de un ejecutivo (lista de client_ids).
     */
    public static function ctrGetAssignmentsForUser($userId)
    {
        $conn = self::getConn();
        $uid = (int)$userId;
        $result = $conn->query("SELECT client_id FROM looker_assignments WHERE user_id = $uid");
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = (int)$row['client_id'];
        }
        $conn->close();
        return $ids;
    }

    /**
     * Guarda las asignaciones de un ejecutivo (reemplaza las existentes).
     */
    public static function ctrSaveAssignments($userId, $clientIds)
    {
        $conn = self::getConn();
        $uid = (int)$userId;
        $conn->query("DELETE FROM looker_assignments WHERE user_id = $uid");
        if (!empty($clientIds)) {
            foreach ($clientIds as $cid) {
                $cid = (int)$cid;
                $conn->query("INSERT IGNORE INTO looker_assignments (user_id, client_id) VALUES ($uid, $cid)");
            }
        }
        $conn->close();
        return true;
    }

    /**
     * Retorna ejecutivos que tienen al menos 1 cliente con looker_url asignado.
     * Incluye la lista de clientes (con looker_url).
     */
    public static function ctrGetMenuData()
    {
        $conn = self::getConn();
        $sql = "SELECT u.id AS user_id, u.name AS user_name,
                       c.id AS client_id, c.name AS client_name, c.looker_url
                FROM users u
                INNER JOIN looker_assignments la ON la.user_id = u.id
                INNER JOIN clients c ON c.id = la.client_id
                WHERE u.profile = 'Ejecutivo' AND u.active = 1
                  AND c.looker_url IS NOT NULL AND c.looker_url <> ''
                ORDER BY u.name ASC, c.name ASC";
        $result = $conn->query($sql);
        $grouped = [];
        while ($row = $result->fetch_assoc()) {
            $uid = $row['user_id'];
            if (!isset($grouped[$uid])) {
                $grouped[$uid] = [
                    'user_id'   => $uid,
                    'user_name' => $row['user_name'],
                    'clients'   => []
                ];
            }
            $grouped[$uid]['clients'][] = [
                'id'         => $row['client_id'],
                'name'       => $row['client_name'],
                'looker_url' => $row['looker_url']
            ];
        }
        $conn->close();
        return array_values($grouped);
    }

    /**
     * Save looker_url para un cliente en la DB local.
     */
    public static function ctrSaveLookerUrl($clientId, $lookerUrl)
    {
        $conn = self::getConn();
        $cid = (int)$clientId;
        $url = $conn->real_escape_string(trim($lookerUrl));
        $conn->query("UPDATE clients SET looker_url = '$url' WHERE id = $cid");
        $conn->close();
        return true;
    }

    /**
     * Get looker_url for a client.
     */
    public static function ctrGetLookerUrl($clientId)
    {
        $conn = self::getConn();
        $cid = (int)$clientId;
        $result = $conn->query("SELECT looker_url FROM clients WHERE id = $cid LIMIT 1");
        $row = $result->fetch_assoc();
        $conn->close();
        return $row['looker_url'] ?? '';
    }
}
